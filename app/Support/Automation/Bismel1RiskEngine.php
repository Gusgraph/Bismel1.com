<?php
// اعوز بالله من الشياطين و ان يحضرون بسم الله الرحمن الرحيم الله لا إله إلا هو الحي القيوم
// Bismillahi ar-Rahmani ar-Rahim Audhu billahi min ash-shayatin wa an yahdurun Bismillah ar-Rahman ar-Rahim Allah la ilaha illa huwa al-hayy al-qayyum. Tamsa Allahu ala ayunihim
// version: x
// ======================================================
// - App Name: gusgraph-trading
// - Gusgraph LLC -
// - Author: Gus Kazem
// - https://Gusgraph.com
// - File Path: app/Support/Automation/Bismel1RiskEngine.php
// ======================================================

namespace App\Support\Automation;

use App\Models\Account;
use App\Models\AlpacaAccount;
use App\Models\AlpacaPosition;
use App\Models\AutomationSetting;
use App\Models\Signal;
use App\Models\StrategyProfile;
use Carbon\CarbonImmutable;

class Bismel1RiskEngine
{
    public function evaluate(
        Account $account,
        StrategyProfile $strategyProfile,
        ?AutomationSetting $automationSetting,
        AlpacaAccount $alpacaAccount,
        ?AlpacaPosition $position,
        string $symbol,
        string $timeframe,
        array $evaluation,
    ): array {
        $proposedAction = (string) ($evaluation['action'] ?? 'skip');
        $internal = is_array($evaluation['internal_strategy_state'] ?? null) ? $evaluation['internal_strategy_state'] : [];
        $safeFlags = is_array($evaluation['safe_flags'] ?? null) ? $evaluation['safe_flags'] : [];
        $unresolvedGaps = is_array($internal['unresolved_gaps'] ?? null) ? $internal['unresolved_gaps'] : [];
        $barCloseAt = $this->barCloseAt($internal);
        $latestDuplicateSignal = $this->latestDuplicateSignal($account, $symbol, $timeframe, $proposedAction, $barCloseAt);

        $blockReason = match (true) {
            ! $automationSetting instanceof AutomationSetting => 'automation_settings_missing',
            ! ($automationSetting?->ai_enabled ?? false) => 'automation_disabled',
            ! ($automationSetting?->scanner_enabled ?? false) => 'scanner_disabled',
            ! in_array((string) ($automationSetting?->status ?? 'draft'), ['review', 'armed'], true) => 'automation_not_ready',
            strtolower((string) ($alpacaAccount->data_feed ?? 'iex')) !== 'iex' => 'broker_feed_unavailable',
            ! in_array((string) ($alpacaAccount->status ?? 'inactive'), ['active'], true) => 'broker_account_unready',
            ! $this->hasSufficientAccountState($alpacaAccount) => 'broker_state_stale',
            $proposedAction === 'skip' => 'scanner_skipped_setup',
            $unresolvedGaps !== [] => 'missing_required_bars_state',
            $this->exceedsMaxPositionGuardrail($account, $strategyProfile, $position) => 'max_positions_guardrail',
            $proposedAction === 'open' && $this->hasOpenPosition($position) => 'position_already_exists',
            $proposedAction === 'add' && ! $this->hasOpenPosition($position) => 'position_missing_for_add',
            $proposedAction === 'close' && ! $this->hasOpenPosition($position) => 'position_missing_for_close',
            $proposedAction === 'add' && ! $this->canAddToPosition($internal, $position) => 'unsupported_add_state',
            $latestDuplicateSignal instanceof Signal => 'duplicate_signal_protection',
            ! in_array($proposedAction, ['open', 'add', 'close', 'skip'], true) => 'unsupported_action',
            default => null,
        };

        $allowed = $blockReason === null && in_array($proposedAction, ['open', 'add', 'close'], true);
        $finalAction = $allowed ? $proposedAction : 'skip';

        return [
            'allowed' => $allowed,
            'proposed_action' => $proposedAction,
            'final_action' => $finalAction,
            'status' => $allowed ? 'allow_action' : 'block_action',
            'reason_code' => $blockReason ?? 'allow_action',
            'public_summary' => $this->publicSummary($allowed, $finalAction, $blockReason, $safeFlags),
            'admin_summary' => $this->adminSummary($allowed, $proposedAction, $finalAction, $blockReason),
            'internal_status' => [
                'allowed' => $allowed,
                'reason_code' => $blockReason ?? 'allow_action',
                'duplicate_signal_id' => $latestDuplicateSignal?->getKey(),
                'bar_close_at' => $barCloseAt?->toIso8601String(),
            ],
        ];
    }

    protected function barCloseAt(array $internal): ?CarbonImmutable
    {
        $timestamp = $internal['current']['bar_close_time'] ?? null;

        if (! is_string($timestamp) || trim($timestamp) === '') {
            return null;
        }

        try {
            return CarbonImmutable::parse($timestamp);
        } catch (\Throwable) {
            return null;
        }
    }

    protected function latestDuplicateSignal(
        Account $account,
        string $symbol,
        string $timeframe,
        string $action,
        ?CarbonImmutable $barCloseAt,
    ): ?Signal {
        if (! in_array($action, ['open', 'add', 'close'], true)) {
            return null;
        }

        $query = Signal::query()
            ->where('account_id', $account->getKey())
            ->where('symbol', $symbol)
            ->where('timeframe', $timeframe)
            ->where('status', $action)
            ->orderByDesc('generated_at')
            ->orderByDesc('id');

        if ($barCloseAt instanceof CarbonImmutable) {
            $query->where('generated_at', '>=', $barCloseAt->subHours(4));
        }

        return $query->first();
    }

    protected function hasSufficientAccountState(AlpacaAccount $alpacaAccount): bool
    {
        return $alpacaAccount->equity !== null
            && ($alpacaAccount->last_synced_at !== null || $alpacaAccount->last_account_sync_at !== null);
    }

    protected function hasOpenPosition(?AlpacaPosition $position): bool
    {
        return $position instanceof AlpacaPosition
            && $position->qty !== null
            && (float) $position->qty > 0;
    }

    protected function canAddToPosition(array $internal, ?AlpacaPosition $position): bool
    {
        $addCount = (int) ($internal['position_state']['add_count'] ?? 0);

        return $this->hasOpenPosition($position) && $addCount >= 0;
    }

    protected function exceedsMaxPositionGuardrail(Account $account, StrategyProfile $strategyProfile, ?AlpacaPosition $currentPosition): bool
    {
        $settings = array_merge(
            is_array($strategyProfile->settings) ? $strategyProfile->settings : [],
            is_array($account->settings ?? null) ? $account->settings : [],
        );

        $maxPositions = (int) data_get($settings, 'risk_guardrails.max_open_positions', 0);

        if ($maxPositions <= 0) {
            return false;
        }

        if ($this->hasOpenPosition($currentPosition)) {
            return false;
        }

        $openPositions = AlpacaPosition::query()
            ->where('account_id', $account->getKey())
            ->where('qty', '>', 0)
            ->count();

        return $openPositions >= $maxPositions;
    }

    protected function publicSummary(bool $allowed, string $finalAction, ?string $reasonCode, array $safeFlags): string
    {
        if ($allowed) {
            return match ($finalAction) {
                'close' => 'AI exited on trailing protection',
                'add', 'open' => 'trend aligned, pullback detected, reclaim confirmed',
                default => 'AI skipped setup',
            };
        }

        return match ($reasonCode) {
            'automation_disabled', 'scanner_disabled', 'automation_not_ready' => 'AI skipped setup',
            'duplicate_signal_protection', 'position_already_exists', 'position_missing_for_add', 'position_missing_for_close', 'unsupported_add_state', 'max_positions_guardrail', 'broker_feed_unavailable', 'broker_account_unready', 'broker_state_stale' => 'risk blocked',
            'missing_required_bars_state', 'scanner_skipped_setup', 'automation_settings_missing' => 'AI skipped setup',
            default => ((bool) ($safeFlags['risk_blocked'] ?? false)) ? 'risk blocked' : 'AI skipped setup',
        };
    }

    protected function adminSummary(bool $allowed, string $proposedAction, string $finalAction, ?string $reasonCode): string
    {
        if ($allowed) {
            return 'Risk engine allowed '.strtoupper($finalAction).' after account and automation guardrails passed.';
        }

        return match ($reasonCode) {
            'duplicate_signal_protection' => 'Risk engine blocked a duplicate signal for the current bar-close window.',
            'position_already_exists' => 'Risk engine blocked a new open because broker state already shows a position.',
            'position_missing_for_add', 'position_missing_for_close', 'unsupported_add_state' => 'Risk engine blocked the requested action because position state does not support it.',
            'broker_feed_unavailable', 'broker_account_unready', 'broker_state_stale' => 'Risk engine blocked the action because broker readiness or synced account state is incomplete.',
            'max_positions_guardrail' => 'Risk engine blocked the action because the current account guardrail is already full.',
            'automation_disabled', 'scanner_disabled', 'automation_not_ready', 'automation_settings_missing' => 'Risk engine blocked the action because automation is not enabled for internal operation.',
            'missing_required_bars_state', 'scanner_skipped_setup' => 'Risk engine downgraded the scanner decision to a safe skip because required state is missing.',
            default => 'Risk engine downgraded '.$proposedAction.' to skip for a safe internal block.',
        };
    }
}
