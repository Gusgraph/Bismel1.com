<?php
// اعوز بالله من الشياطين و ان يحضرون بسم الله الرحمن الرحيم الله لا إله إلا هو الحي القيوم
// Bismillahi ar-Rahmani ar-Rahim Audhu billahi min ash-shayatin wa an yahdurun Bismillah ar-Rahman ar-Rahim Allah la ilaha illa huwa al-hayy al-qayyum. Tamsa Allahu ala ayunihim
// version: x
// ======================================================
// - App Name: bismel1.com
// - Gusgraph LLC -
// - Author: Gus Kazem
// - https://Gusgraph.com
// - File Path: app/Support/Automation/Bismel1SchedulerTrigger.php
// ======================================================

namespace App\Support\Automation;

use App\Models\Account;
use App\Models\AutomationSetting;
use App\Models\BotRun;
use App\Support\Billing\Bismel1EntitlementService;
use Carbon\CarbonImmutable;
use Illuminate\Support\Collection;

class Bismel1SchedulerTrigger
{
    public function __construct(
        protected Bismel1SchedulerWindow $schedulerWindow,
        protected Bismel1SmallSymbolScanner $scanner,
        protected Bismel1EntitlementService $bismel1EntitlementService,
        protected Bismel1RuntimeGuardrails $bismel1RuntimeGuardrails,
    ) {}

    public function triggerDueRuns(?CarbonImmutable $nowUtc = null, ?array $requestedTimeframes = null, ?int $accountId = null): array
    {
        $nowUtc ??= CarbonImmutable::now('UTC');
        $windows = $this->schedulerWindow->dueWindows($nowUtc, $requestedTimeframes ?? ['1H', '4H']);
        $results = [];

        foreach ($this->eligibleAccounts($accountId) as $account) {
            $automationSetting = $account->automationSettings
                ?->sortByDesc(fn ($item) => $item->updated_at?->getTimestamp() ?? 0)
                ->first();

            foreach ($this->timeframesForAccount($account, $automationSetting, array_keys($windows)) as $timeframe) {
                $window = $windows[$timeframe] ?? null;

                if (! is_array($window)) {
                    continue;
                }

                $lockSegments = [strtolower($timeframe), $window['bar_close_at']->format('YmdHis')];

                if (! $this->bismel1RuntimeGuardrails->acquireRuntimeLock('scheduler-scan', $account->getKey(), $lockSegments)) {
                    $results[] = [
                        'account_id' => $account->getKey(),
                        'timeframe' => $timeframe,
                        'status' => 'locked_skipped',
                        'bar_close_at' => $window['bar_close_at']->toIso8601String(),
                    ];

                    continue;
                }

                try {
                if ($this->alreadyTriggeredForWindow($account, $timeframe, $window['bar_close_at'])) {
                    $results[] = [
                        'account_id' => $account->getKey(),
                        'timeframe' => $timeframe,
                        'status' => 'duplicate_skipped',
                        'bar_close_at' => $window['bar_close_at']->toIso8601String(),
                    ];

                    continue;
                }

                $result = $this->scanner->scanAccount($account, null, [
                    'run_type' => 'scan_scheduler_'.strtolower($timeframe),
                    'scheduler' => [
                        'mode' => 'bar_close',
                        'timeframe' => $timeframe,
                        'bar_close_at' => $window['bar_close_at']->toIso8601String(),
                        'next_bar_close_at' => $window['next_bar_close_at']->toIso8601String(),
                    ],
                ]);

                $results[] = [
                    'account_id' => $account->getKey(),
                    'timeframe' => $timeframe,
                    'status' => $result['status'] ?? 'completed',
                    'bar_close_at' => $window['bar_close_at']->toIso8601String(),
                    'bot_run_id' => $result['bot_run_id'] ?? null,
                ];
                } finally {
                    $this->bismel1RuntimeGuardrails->releaseRuntimeLock('scheduler-scan', $account->getKey(), $lockSegments);
                }
            }
        }

        return [
            'status' => 'completed',
            'triggered_at' => $nowUtc->toIso8601String(),
            'results' => $results,
        ];
    }

    protected function eligibleAccounts(?int $accountId = null): Collection
    {
        $query = Account::query()
            ->whereHas('strategyProfiles', fn ($builder) => $builder->where('engine', 'python')->where('is_active', true))
            ->whereHas('alpacaAccounts', fn ($builder) => $builder->where('is_active', true)->where('status', 'active'))
            ->whereHas('automationSettings', fn ($builder) => $builder->where('scanner_enabled', true));

        if ($accountId !== null) {
            $query->whereKey($accountId);
        }

        return $query
            ->with(['automationSettings', 'strategyProfiles', 'alpacaAccounts'])
            ->orderBy('id')
            ->get()
            ->filter(function (Account $account): bool {
                if (! $this->bismel1EntitlementService->allowsStocksAutomation($account)) {
                    return false;
                }

                $automationSetting = $account->automationSettings
                    ?->sortByDesc(fn ($item) => $item->updated_at?->getTimestamp() ?? 0)
                    ->first();
                $alpacaAccount = $account->alpacaAccounts
                    ?->sortByDesc(fn ($item) => ($item->is_active ? 1 : 0) + (($item->last_synced_at?->getTimestamp() ?? 0) / 1000000000000))
                    ->first();

                return $this->bismel1RuntimeGuardrails->schedulerGuard($automationSetting, $alpacaAccount)['allowed'];
            })
            ->values();
    }

    protected function timeframesForAccount(Account $account, ?AutomationSetting $automationSetting, array $dueTimeframes): array
    {
        $frequency = strtolower((string) ($automationSetting?->scheduler_frequency ?? ''));

        $configured = match ($frequency) {
            '1h', 'hourly' => ['1H'],
            '4h' => ['4H'],
            '1h,4h', '4h,1h', 'both', 'mixed' => ['1H', '4H'],
            default => $this->fallbackTimeframes($account),
        };

        return array_values(array_intersect($configured, $dueTimeframes));
    }

    protected function fallbackTimeframes(Account $account): array
    {
        $timeframe = strtolower((string) ($account->strategyProfiles->sortByDesc(fn ($item) => $item->updated_at?->getTimestamp() ?? 0)->first()?->timeframe ?? ''));

        if (str_contains($timeframe, '1h') && str_contains($timeframe, '4h')) {
            return ['1H', '4H'];
        }

        if (str_contains($timeframe, '1h')) {
            return ['1H'];
        }

        if (str_contains($timeframe, '4h')) {
            return ['4H'];
        }

        return ['1H', '4H'];
    }

    protected function alreadyTriggeredForWindow(Account $account, string $timeframe, CarbonImmutable $barCloseAt): bool
    {
        return BotRun::query()
            ->where('account_id', $account->getKey())
            ->where('run_type', 'scan_scheduler_'.strtolower($timeframe))
            ->whereIn('status', ['running', 'completed'])
            ->where('summary->scheduler->bar_close_at', $barCloseAt->toIso8601String())
            ->exists();
    }
}
