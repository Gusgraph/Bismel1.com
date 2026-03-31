<?php
// اعوز بالله من الشياطين و ان يحضرون بسم الله الرحمن الرحيم الله لا إله إلا هو الحي القيوم
// Bismillahi ar-Rahmani ar-Rahim Audhu billahi min ash-shayatin wa an yahdurun Bismillah ar-Rahman ar-Rahim Allah la ilaha illa huwa al-hayy al-qayyum. Tamsa Allahu ala ayunihim
// version: x
// ======================================================
// - App Name: bismel1.com
// - Gusgraph LLC -
// - Author: Gus Kazem
// - https://Gusgraph.com
// - File Path: app/Support/Automation/Bismel1RuntimeGuardrails.php
// ======================================================

namespace App\Support\Automation;

use App\Models\AlpacaAccount;
use App\Models\AutomationSetting;
use App\Models\Signal;
use Carbon\CarbonImmutable;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;

class Bismel1RuntimeGuardrails
{
    public function configGuard(): array
    {
        $timeout = (int) config('alpaca.timeout', 10);
        $ordersLimit = (int) config('alpaca.recent_orders_limit', 50);
        $paperBaseUrl = trim((string) config('alpaca.environments.paper.base_url', ''));

        if ($timeout < 1) {
            return ['allowed' => false, 'summary' => 'runtime config blocked broker requests'];
        }

        if ($ordersLimit < 1) {
            return ['allowed' => false, 'summary' => 'runtime config blocked broker requests'];
        }

        if ($paperBaseUrl === '' || filter_var($paperBaseUrl, FILTER_VALIDATE_URL) === false) {
            return ['allowed' => false, 'summary' => 'runtime config blocked broker requests'];
        }

        if ($this->allowLiveOrderSubmission()) {
            $liveBaseUrl = trim((string) config('alpaca.environments.live.base_url', ''));

            if ($liveBaseUrl === '' || filter_var($liveBaseUrl, FILTER_VALIDATE_URL) === false) {
                return ['allowed' => false, 'summary' => 'runtime config blocked broker requests'];
            }
        }

        return ['allowed' => true, 'summary' => 'paper-trading runtime guardrails ready'];
    }

    public function executionEnvironmentGuard(?string $environment): array
    {
        $normalized = $this->normalizeEnvironment($environment);

        if ($normalized === 'live' && ! $this->allowLiveOrderSubmission()) {
            return ['allowed' => false, 'summary' => 'paper trading only: live broker environment blocked'];
        }

        return ['allowed' => true, 'summary' => 'paper-trading execution environment allowed'];
    }

    public function runtimeAccountGuard(?AlpacaAccount $alpacaAccount): array
    {
        if (! $alpacaAccount instanceof AlpacaAccount) {
            return ['allowed' => false, 'summary' => 'broker not ready'];
        }

        if ($this->normalizeEnvironment((string) ($alpacaAccount->environment ?? 'paper')) === 'live'
            && ! $this->allowLiveOrderSubmission()) {
            return ['allowed' => false, 'summary' => 'paper trading only: live broker environment blocked'];
        }

        if (! in_array((string) ($alpacaAccount->status ?? 'inactive'), ['active'], true)) {
            return ['allowed' => false, 'summary' => 'broker not ready'];
        }

        if ((bool) config('alpaca.guards.require_iex_for_runtime', true)
            && strtolower((string) ($alpacaAccount->data_feed ?? 'iex')) !== 'iex') {
            return ['allowed' => false, 'summary' => 'approved market data path is unavailable'];
        }

        if (! $alpacaAccount->last_synced_at instanceof \DateTimeInterface) {
            return ['allowed' => false, 'summary' => 'broker sync is stale and needs refresh'];
        }

        $cutoff = CarbonImmutable::now('UTC')->subMinutes($this->maxRuntimeStaleMinutes());

        if (CarbonImmutable::instance($alpacaAccount->last_synced_at)->lte($cutoff)) {
            return ['allowed' => false, 'summary' => 'broker sync is stale and needs refresh'];
        }

        if ($this->syncSnapshotIncomplete($alpacaAccount)) {
            return ['allowed' => false, 'summary' => 'broker sync is incomplete and needs refresh'];
        }

        if ($this->detailedSyncSnapshotStale($alpacaAccount, $cutoff)) {
            return ['allowed' => false, 'summary' => 'broker sync is stale and needs refresh'];
        }

        return ['allowed' => true, 'summary' => 'paper-trading broker runtime is ready'];
    }

    public function schedulerGuard(?AutomationSetting $automationSetting, ?AlpacaAccount $alpacaAccount): array
    {
        if (! $automationSetting instanceof AutomationSetting) {
            return ['allowed' => false, 'summary' => 'automation settings missing'];
        }

        if (! (bool) ($automationSetting->ai_enabled ?? false) || ! (bool) ($automationSetting->scanner_enabled ?? false)) {
            return ['allowed' => false, 'summary' => 'automation is stopped'];
        }

        if ((string) ($automationSetting->status ?? 'draft') !== 'armed') {
            return ['allowed' => false, 'summary' => 'automation is not armed'];
        }

        return $this->runtimeAccountGuard($alpacaAccount);
    }

    public function duplicateOrderKey(Signal $signal, string $action): string
    {
        $timestamp = $signal->generated_at?->timestamp ?? $signal->created_at?->timestamp ?? $signal->getKey();

        return Str::lower(sprintf(
            'bismel1-%s-%d-%s',
            $action,
            $signal->getKey(),
            substr(sha1((string) $timestamp), 0, 10),
        ));
    }

    public function maxRuntimeStaleMinutes(): int
    {
        return max(5, (int) config('alpaca.guards.max_runtime_stale_minutes', 30));
    }

    public function acquireRuntimeLock(string $scope, int|string $accountId, array $segments = [], ?int $minutes = null): bool
    {
        return Cache::add(
            $this->runtimeLockKey($scope, $accountId, $segments),
            now()->toIso8601String(),
            now()->addMinutes($minutes ?? $this->maxRuntimeStaleMinutes())
        );
    }

    public function runtimeLockActive(string $scope, int|string $accountId, array $segments = []): bool
    {
        return Cache::has($this->runtimeLockKey($scope, $accountId, $segments));
    }

    public function releaseRuntimeLock(string $scope, int|string $accountId, array $segments = []): void
    {
        Cache::forget($this->runtimeLockKey($scope, $accountId, $segments));
    }

    public function orderStatusSummary(?string $status): string
    {
        return match (strtolower(trim((string) $status))) {
            'filled' => 'AI broker order filled',
            'partially_filled' => 'AI broker order partially filled',
            'canceled', 'cancelled' => 'AI broker order canceled',
            'rejected' => 'AI broker order rejected',
            'expired' => 'AI broker order expired',
            'accepted', 'pending_new', 'new', 'accepted_for_bidding' => 'AI broker order submitted',
            default => 'AI broker order synced',
        };
    }

    protected function runtimeLockKey(string $scope, int|string $accountId, array $segments = []): string
    {
        $normalizedSegments = collect($segments)
            ->map(fn ($segment) => Str::slug((string) $segment, '-'))
            ->filter()
            ->implode(':');

        return trim(sprintf(
            'bismel1:runtime:%s:%s%s',
            Str::slug($scope, '-'),
            $accountId,
            $normalizedSegments !== '' ? ':'.$normalizedSegments : ''
        ), ':');
    }

    protected function syncSnapshotIncomplete(AlpacaAccount $alpacaAccount): bool
    {
        $metadata = is_array($alpacaAccount->metadata) ? $alpacaAccount->metadata : [];
        $syncStatus = strtolower((string) ($alpacaAccount->sync_status ?? ''));
        $positionsSync = strtolower((string) ($metadata['positions_sync_result'] ?? 'verified'));
        $ordersSync = strtolower((string) ($metadata['orders_sync_result'] ?? 'verified'));

        if (! in_array($syncStatus, ['success', 'fresh'], true)) {
            return true;
        }

        return ! in_array($positionsSync, ['verified', 'fresh'], true)
            || ! in_array($ordersSync, ['verified', 'fresh'], true);
    }

    protected function detailedSyncSnapshotStale(AlpacaAccount $alpacaAccount, CarbonImmutable $cutoff): bool
    {
        foreach ([$alpacaAccount->last_account_sync_at, $alpacaAccount->last_positions_sync_at, $alpacaAccount->last_orders_sync_at] as $timestamp) {
            if (! $timestamp instanceof \DateTimeInterface) {
                return true;
            }

            if (CarbonImmutable::instance($timestamp)->lte($cutoff)) {
                return true;
            }
        }

        return false;
    }

    protected function allowLiveOrderSubmission(): bool
    {
        return (bool) config('alpaca.guards.allow_live_order_submission', false);
    }

    protected function normalizeEnvironment(?string $environment): string
    {
        return match (strtolower(trim((string) $environment))) {
            'live' => 'live',
            default => 'paper',
        };
    }
}
