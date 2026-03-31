<?php
// اعوز بالله من الشياطين و ان يحضرون بسم الله الرحمن الرحيم الله لا إله إلا هو الحي القيوم
// Bismillahi ar-Rahmani ar-Rahim Audhu billahi min ash-shayatin wa an yahdurun Bismillah ar-Rahman ar-Rahim Allah la ilaha illa huwa al-hayy al-qayyum. Tamsa Allahu ala ayunihim
// version: x
// ======================================================
// - App Name: bismel1.com
// - Gusgraph LLC -
// - Author: Gus Kazem
// - https://Gusgraph.com
// - File Path: app/Support/Automation/Bismel1PositionManager.php
// ======================================================

namespace App\Support\Automation;

use App\Models\Account;
use App\Models\ActivityLog;
use App\Models\AlpacaAccount;
use App\Models\AlpacaOrder;
use App\Models\AlpacaPosition;
use App\Models\AutomationSetting;
use App\Models\BotRun;
use App\Models\Signal;
use App\Models\StrategyProfile;
use App\Support\Billing\Bismel1EntitlementService;
use App\Support\Broker\AlpacaAccountSyncService;
use Illuminate\Support\Facades\DB;
use RuntimeException;
use Throwable;

class Bismel1PositionManager
{
    protected const STALE_RUNTIME_MINUTES = 30;

    public function __construct(
        protected Bismel1SmallSymbolScanner $scanner,
        protected Bismel1ExecutionEngine $executionEngine,
        protected Bismel1SignalVisibility $visibility,
        protected AlpacaAccountSyncService $accountSyncService,
        protected Bismel1EntitlementService $bismel1EntitlementService,
        protected Bismel1RuntimeGuardrails $bismel1RuntimeGuardrails,
    ) {}

    public function manageAccount(Account $account, ?array $symbols = null, array $triggerContext = []): array
    {
        if (! $this->bismel1EntitlementService->allowsStocksAutomation($account)) {
            throw new RuntimeException('Bismel1 position management is blocked because plan does not include this automation mode.');
        }

        $strategyProfile = $account->strategyProfiles()
            ->where('engine', 'python')
            ->where('is_active', true)
            ->latest('id')
            ->first();

        if (! $strategyProfile instanceof StrategyProfile) {
            throw new RuntimeException('No active Python-backed Bismel1 strategy profile is available for position management.');
        }

        $automationSetting = $account->automationSettings()
            ->where(function ($query) use ($strategyProfile): void {
                $query->whereNull('strategy_profile_id')->orWhere('strategy_profile_id', $strategyProfile->getKey());
            })
            ->latest('id')
            ->first();

        $alpacaAccount = $account->alpacaAccounts()
            ->where('is_active', true)
            ->latest('id')
            ->first();

        if (! $alpacaAccount instanceof AlpacaAccount) {
            throw new RuntimeException('No active Alpaca account is available for Bismel1 position management.');
        }

        $accountGuard = $this->bismel1RuntimeGuardrails->runtimeAccountGuard($alpacaAccount);

        if (! $accountGuard['allowed']) {
            throw new RuntimeException((string) $accountGuard['summary']);
        }

        $startedAt = now();
        $this->cleanupStaleRuns($account);
        $botRun = BotRun::query()->create([
            'account_id' => $account->getKey(),
            'strategy_profile_id' => $strategyProfile->getKey(),
            'automation_setting_id' => $automationSetting?->getKey(),
            'alpaca_account_id' => $alpacaAccount->getKey(),
            'run_type' => 'position_management',
            'status' => 'running',
            'risk_level' => $automationSetting?->risk_level ?? 'balanced',
            'started_at' => $startedAt,
            'summary' => [
                'engine' => 'bismel1',
                'scope' => 'position_management',
                'visibility' => 'safe_summary_only',
                'trigger' => $triggerContext,
            ],
        ]);

        try {
            $sync = $this->accountSyncService->syncLatestForAccount($account);

            if (! in_array((string) ($sync['status'] ?? 'failed'), ['verified', 'partial_success', 'fresh'], true)) {
                throw new RuntimeException((string) ($sync['message'] ?? 'Broker state could not be synced for position management.'));
            }

            $symbolsToManage = $this->resolveSymbols($account, $alpacaAccount, $symbols);
            $results = [];
            $counts = [
                'hold' => 0,
                'add' => 0,
                'close' => 0,
                'reconcile' => 0,
                'skip' => 0,
            ];

            foreach ($symbolsToManage as $symbol) {
                $position = AlpacaPosition::query()
                    ->where('account_id', $account->getKey())
                    ->where('alpaca_account_id', $alpacaAccount->getKey())
                    ->where('symbol', $symbol)
                    ->first();

                if (! $position instanceof AlpacaPosition || (float) ($position->qty ?? 0) <= 0) {
                    $result = $this->reconcileSymbol($account, $automationSetting, $strategyProfile, $alpacaAccount, $botRun, $symbol);
                } else {
                    $result = $this->manageOpenPosition($account, $automationSetting, $strategyProfile, $alpacaAccount, $botRun, $position);
                }

                $counts[$result['action']] = ($counts[$result['action']] ?? 0) + 1;
                $results[] = $result;
            }

            $finishedAt = now();
            $safeSummary = $this->overallSummary($counts);

            $botRun->forceFill([
                'status' => 'completed',
                'finished_at' => $finishedAt,
                'runtime_seconds' => max(0, $startedAt->diffInSeconds($finishedAt)),
                'summary' => array_merge(is_array($botRun->summary) ? $botRun->summary : [], [
                    'counts' => $counts,
                    'recent_results' => $results,
                    'safe_summary' => $safeSummary,
                    'sync_status' => $sync['status'] ?? null,
                ]),
                'error_message' => null,
            ])->save();

            if ($automationSetting instanceof AutomationSetting) {
                $automationSetting->forceFill([
                    'last_checked_at' => $finishedAt,
                    'run_health' => 'position_managed',
                    'settings' => $this->mergedPositionManagerSettings($automationSetting, $finishedAt, 'completed', $safeSummary),
                ])->save();
            }

            return [
                'status' => 'completed',
                'bot_run_id' => $botRun->getKey(),
                'counts' => $counts,
                'message' => $safeSummary,
            ];
        } catch (Throwable $exception) {
            $finishedAt = now();
            $botRun->forceFill([
                'status' => 'failed',
                'finished_at' => $finishedAt,
                'runtime_seconds' => max(0, $startedAt->diffInSeconds($finishedAt)),
                'summary' => array_merge(is_array($botRun->summary) ? $botRun->summary : [], [
                    'safe_summary' => 'Bismel1 position manager stopped before management could complete.',
                ]),
                'error_message' => $exception->getMessage(),
            ])->save();

            if ($automationSetting instanceof AutomationSetting) {
                $automationSetting->forceFill([
                    'last_checked_at' => $finishedAt,
                    'run_health' => 'position_management_failed',
                    'settings' => $this->mergedPositionManagerSettings(
                        $automationSetting,
                        $finishedAt,
                        'failed',
                        'Bismel1 position manager stopped before management could complete.'
                    ),
                ])->save();
            }

            throw $exception;
        }
    }

    protected function manageOpenPosition(
        Account $account,
        ?AutomationSetting $automationSetting,
        StrategyProfile $strategyProfile,
        AlpacaAccount $alpacaAccount,
        BotRun $botRun,
        AlpacaPosition $position,
    ): array {
        $managed = $this->scanner->evaluateManagedSymbol($account, $position->symbol);
        $evaluation = $managed['evaluation'];
        $riskResult = $managed['risk_result'];
        $payload = $managed['payload'];
        $action = (string) ($riskResult['final_action'] ?? 'skip');
        $summary = $this->holdSummary();
        $signal = null;
        $executionResult = null;
        $managementState = 'holding';

        if ($action === 'add' || $action === 'close') {
            $signal = Signal::query()->create([
                'account_id' => $account->getKey(),
                'strategy_profile_id' => $strategyProfile->getKey(),
                'symbol' => $position->symbol,
                'timeframe' => '4H',
                'direction' => $this->visibility->signalDirection($action),
                'strength' => $this->visibility->signalStrength($action),
                'status' => $action,
                'generated_at' => $managed['generated_at'],
                'expires_at' => $managed['generated_at']?->addHours(4),
                'payload' => $payload,
            ]);

            $executionResult = $this->executionEngine->executeSignal($account, $signal, [
                'run_type' => 'position_management',
                'position_manager_bot_run_id' => $botRun->getKey(),
            ]);

            if (($executionResult['status'] ?? null) === 'submitted') {
                $summary = $action === 'add'
                    ? 'AI added to position'
                    : 'AI closed on trailing protection';
                $managementState = $action === 'add' ? 'add_pending' : 'close_pending';
            } else {
                $summary = 'AI skipped management action';
                $action = 'skip';
                $managementState = 'holding';
            }
        }

        $this->updatePositionState(
            $position,
            $strategyProfile,
            $signal,
            $botRun,
            $managementState,
            $summary,
            $evaluation,
        );

        $this->recordActivity($account, 'bismel1_position_'.$managementState, $summary, [
            'symbol' => $position->symbol,
            'action' => $action === 'skip' ? 'hold' : $action,
            'execution_status' => $executionResult['status'] ?? null,
        ]);

        return [
            'symbol' => $position->symbol,
            'action' => $action === 'skip' ? 'hold' : $action,
            'public_summary' => $summary,
        ];
    }

    protected function reconcileSymbol(
        Account $account,
        ?AutomationSetting $automationSetting,
        StrategyProfile $strategyProfile,
        AlpacaAccount $alpacaAccount,
        BotRun $botRun,
        string $symbol,
    ): array {
        $recentSignal = Signal::query()
            ->where('account_id', $account->getKey())
            ->where('symbol', $symbol)
            ->whereIn('status', ['open', 'add', 'close'])
            ->latest('id')
            ->first();
        $recentOrder = AlpacaOrder::query()
            ->where('account_id', $account->getKey())
            ->where('alpaca_account_id', $alpacaAccount->getKey())
            ->where('symbol', $symbol)
            ->latest('id')
            ->first();

        $action = ($recentSignal instanceof Signal || $recentOrder instanceof AlpacaOrder) ? 'reconcile' : 'skip';
        $summary = $action === 'reconcile'
            ? $this->reconcileSummary($recentOrder)
            : 'AI skipped management action';

        if ($recentOrder instanceof AlpacaOrder) {
            $recentOrder->forceFill([
                'strategy_profile_id' => $strategyProfile->getKey(),
                'bot_run_id' => $botRun->getKey(),
                'status_summary' => $summary,
            ])->save();
        }

        if ($recentSignal instanceof Signal) {
            $payload = is_array($recentSignal->payload) ? $recentSignal->payload : [];
            $payload['position_manager'] = [
                'status' => $action,
                'summary' => $summary,
            ];
            $recentSignal->forceFill([
                'payload' => $payload,
            ])->save();
        }

        if ($automationSetting instanceof AutomationSetting && $action === 'reconcile') {
            $automationSetting->forceFill([
                'run_health' => 'position_reconciled',
            ])->save();
        }

        $this->recordActivity($account, 'bismel1_position_'.$action, $summary, [
            'symbol' => $symbol,
        ]);

        return [
            'symbol' => $symbol,
            'action' => $action,
            'public_summary' => $summary,
        ];
    }

    protected function updatePositionState(
        AlpacaPosition $position,
        StrategyProfile $strategyProfile,
        ?Signal $signal,
        BotRun $botRun,
        string $managementState,
        string $summary,
        array $evaluation,
    ): void {
        $currentPrice = (float) ($position->current_price ?? 0.0);
        $internalHigh = (float) data_get($evaluation, 'internal_strategy_state.position_state.pos_high', 0.0);
        $storedHigh = (float) ($position->high_water_price ?? 0.0);
        $highWater = max($currentPrice, $internalHigh, $storedHigh);

        $position->forceFill([
            'strategy_profile_id' => $strategyProfile->getKey(),
            'last_signal_id' => $signal?->getKey(),
            'last_bot_run_id' => $botRun->getKey(),
            'high_water_price' => $highWater > 0 ? number_format($highWater, 6, '.', '') : $position->high_water_price,
            'management_state' => $managementState,
            'status_summary' => $summary,
            'last_managed_at' => now(),
        ])->save();
    }

    protected function resolveSymbols(Account $account, AlpacaAccount $alpacaAccount, ?array $symbols = null): array
    {
        $requested = collect($symbols ?? [])
            ->filter(fn ($item) => is_string($item) && trim($item) !== '')
            ->map(fn (string $item) => strtoupper(trim($item)));

        if ($requested->isNotEmpty()) {
            return $requested->unique()->values()->all();
        }

        $positionSymbols = AlpacaPosition::query()
            ->where('account_id', $account->getKey())
            ->where('alpaca_account_id', $alpacaAccount->getKey())
            ->where('qty', '>', 0)
            ->pluck('symbol');

        $recentOrderSymbols = AlpacaOrder::query()
            ->where('account_id', $account->getKey())
            ->where('alpaca_account_id', $alpacaAccount->getKey())
            ->whereIn('request_action', ['open', 'add', 'close'])
            ->latest('id')
            ->limit(5)
            ->pluck('symbol');

        return $positionSymbols
            ->merge($recentOrderSymbols)
            ->map(fn ($item) => strtoupper(trim((string) $item)))
            ->filter()
            ->unique()
            ->values()
            ->all();
    }

    protected function holdSummary(): string
    {
        return 'AI held position';
    }

    protected function reconcileSummary(?AlpacaOrder $recentOrder): string
    {
        $status = strtolower(trim((string) ($recentOrder?->status ?? '')));

        return match ($status) {
            'canceled', 'cancelled', 'rejected', 'expired' => 'AI reconciled broker state after the broker order did not remain active',
            'partially_filled' => 'AI reconciled broker state after a partial broker fill',
            default => 'AI reconciled broker state',
        };
    }

    protected function overallSummary(array $counts): string
    {
        if (($counts['close'] ?? 0) > 0) {
            return 'Bismel1 position manager closed positions with safe trailing protection handling.';
        }

        if (($counts['add'] ?? 0) > 0) {
            return 'Bismel1 position manager added to positions with safe execution handling.';
        }

        if (($counts['reconcile'] ?? 0) > 0) {
            return 'Bismel1 position manager reconciled broker and local state.';
        }

        if (($counts['hold'] ?? 0) > 0) {
            return 'Bismel1 position manager held existing positions with no further action required.';
        }

        return 'Bismel1 position manager completed with no management action required.';
    }

    protected function mergedPositionManagerSettings(AutomationSetting $automationSetting, $checkedAt, string $result, string $summary): array
    {
        $settings = is_array($automationSetting->settings) ? $automationSetting->settings : [];
        $current = is_array($settings['bismel1_position_manager'] ?? null) ? $settings['bismel1_position_manager'] : [];
        $currentRuntime = is_array($settings['bismel1_runtime'] ?? null) ? $settings['bismel1_runtime'] : [];

        $settings['bismel1_position_manager'] = array_merge($current, [
            'last_management_at' => $checkedAt?->toIso8601String(),
            'last_management_result' => $result,
            'last_management_summary' => $summary,
        ]);

        $settings['bismel1_runtime'] = array_merge($currentRuntime, [
            'last_runtime_status' => $result === 'failed' ? 'blocked' : 'active',
            'last_runtime_summary' => $result === 'failed'
                ? 'Position management requires review before the next paper-trading cycle.'
                : 'recent run completed',
            'last_runtime_updated_at' => $checkedAt?->toIso8601String(),
            'last_run_at' => $checkedAt?->toIso8601String(),
            'last_stage' => 'position_manager',
            'last_stage_result' => $result,
            'last_stage_summary' => $summary,
        ]);

        return $settings;
    }

    protected function cleanupStaleRuns(Account $account): void
    {
        $cutoff = now()->subMinutes(self::STALE_RUNTIME_MINUTES);

        BotRun::query()
            ->where('account_id', $account->getKey())
            ->where('run_type', 'position_management')
            ->where('status', 'running')
            ->where(function ($query) use ($cutoff): void {
                $query->where('started_at', '<=', $cutoff)
                    ->orWhereNull('started_at');
            })
            ->get()
            ->each(function (BotRun $run) use ($account): void {
                $finishedAt = now();
                $run->forceFill([
                    'status' => 'failed',
                    'finished_at' => $finishedAt,
                    'runtime_seconds' => max(0, ($run->started_at ?? $finishedAt)->diffInSeconds($finishedAt)),
                    'summary' => array_merge(is_array($run->summary) ? $run->summary : [], [
                        'safe_summary' => 'Position management validation closed a stale running management cycle before retry.',
                    ]),
                    'error_message' => 'Stale running position-management cycle was closed before retry.',
                ])->save();

                $this->recordActivity($account, 'bismel1_position_recovery', 'Position management validation closed a stale running management cycle before retry.', [
                    'stale_run_id' => $run->getKey(),
                    'safe_summary' => 'Position management validation closed a stale running management cycle before retry.',
                ]);
            });
    }

    protected function recordActivity(Account $account, string $type, string $message, array $context = []): void
    {
        ActivityLog::query()->create([
            'account_id' => $account->getKey(),
            'type' => $type,
            'level' => 'info',
            'message' => $message,
            'context' => $context,
        ]);
    }
}
