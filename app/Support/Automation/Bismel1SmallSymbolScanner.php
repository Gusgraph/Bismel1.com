<?php
// اعوز بالله من الشياطين و ان يحضرون بسم الله الرحمن الرحيم الله لا إله إلا هو الحي القيوم
// Bismillahi ar-Rahmani ar-Rahim Audhu billahi min ash-shayatin wa an yahdurun Bismillah ar-Rahman ar-Rahim Allah la ilaha illa huwa al-hayy al-qayyum. Tamsa Allahu ala ayunihim
// version: x
// ======================================================
// - App Name: gusgraph-trading
// - Gusgraph LLC -
// - Author: Gus Kazem
// - https://Gusgraph.com
// - File Path: app/Support/Automation/Bismel1SmallSymbolScanner.php
// ======================================================

namespace App\Support\Automation;

use App\Models\Account;
use App\Models\ActivityLog;
use App\Models\AlpacaAccount;
use App\Models\AlpacaBar;
use App\Models\AlpacaPosition;
use App\Models\AutomationSetting;
use App\Models\BotRun;
use App\Models\Signal;
use App\Models\StrategyProfile;
use App\Models\WatchlistSymbol;
use App\Support\Billing\Bismel1EntitlementService;
use App\Support\Broker\AlpacaMarketDataService;
use Carbon\CarbonImmutable;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use RuntimeException;
use Throwable;

class Bismel1SmallSymbolScanner
{
    protected const STALE_RUNTIME_MINUTES = 30;

    public function __construct(
        protected AlpacaMarketDataService $marketDataService,
        protected Bismel1PythonStrategyBridge $pythonBridge,
        protected Bismel1SignalVisibility $visibility,
        protected Bismel1RiskEngine $riskEngine,
        protected Bismel1EntitlementService $bismel1EntitlementService,
        protected Bismel1RuntimeGuardrails $bismel1RuntimeGuardrails,
    ) {}

    public function scanAccount(Account $account, ?array $symbols = null, array $triggerContext = []): array
    {
        ['strategy_profile' => $strategyProfile, 'automation_setting' => $automationSetting, 'alpaca_account' => $alpacaAccount] = $this->resolveRuntimeContext($account);

        $symbolsToScan = $this->resolveSymbols($strategyProfile, $symbols);
        $startedAt = now();
        $runType = is_string($triggerContext['run_type'] ?? null) && trim((string) $triggerContext['run_type']) !== ''
            ? (string) $triggerContext['run_type']
            : 'scan';
        $schedulerSummary = is_array($triggerContext['scheduler'] ?? null) ? $triggerContext['scheduler'] : null;
        $this->cleanupStaleRuns($account, $runType);

        $botRun = BotRun::query()->create([
            'account_id' => $account->getKey(),
            'strategy_profile_id' => $strategyProfile->getKey(),
            'automation_setting_id' => $automationSetting?->getKey(),
            'alpaca_account_id' => $alpacaAccount->getKey(),
            'run_type' => $runType,
            'status' => 'running',
            'risk_level' => $automationSetting?->risk_level ?? 'balanced',
            'started_at' => $startedAt,
            'summary' => [
                'engine' => 'bismel1',
                'scope' => 'small_symbol_internal_scanner',
                'feed' => 'IEX',
                'timeframes' => ['1H', '4H'],
                'symbols' => $symbolsToScan,
                'visibility' => 'safe_summary_only',
                'scheduler' => $schedulerSummary,
            ],
        ]);

        try {
            $sync = $this->marketDataService->syncLatestForAccount($account, $symbolsToScan, ['1H', '4H'], 420);

            if (($sync['status'] ?? null) !== 'synced') {
                $this->failRun($botRun, $automationSetting, (string) ($sync['message'] ?? 'Bismel1 small-symbol scan could not sync bars.'));

                return [
                    'status' => 'failed',
                    'message' => $botRun->error_message,
                    'bot_run_id' => $botRun->getKey(),
                ];
            }

            $results = [];
            $counts = [
                'open' => 0,
                'add' => 0,
                'close' => 0,
                'skip' => 0,
                'blocked' => 0,
            ];

            foreach ($symbolsToScan as $symbol) {
                $watchlistSymbol = $this->resolveWatchlistSymbol($strategyProfile, $symbol);
                $evaluation = $this->evaluateSymbol($account, $alpacaAccount, $symbol);
                $position = AlpacaPosition::query()
                    ->where('account_id', $account->getKey())
                    ->where('alpaca_account_id', $alpacaAccount->getKey())
                    ->where('symbol', $symbol)
                    ->first();
                $riskResult = $this->riskEngine->evaluate(
                    $account,
                    $strategyProfile,
                    $automationSetting,
                    $alpacaAccount,
                    $position,
                    $symbol,
                    '4H',
                    $evaluation,
                );
                $payload = $this->visibility->buildSignalPayload($evaluation, $riskResult);
                $action = (string) ($riskResult['final_action'] ?? 'skip');
                $proposedAction = (string) ($riskResult['proposed_action'] ?? $evaluation['action'] ?? 'skip');
                $counts[$action] = ($counts[$action] ?? 0) + 1;
                if (! (bool) ($riskResult['allowed'] ?? false) && in_array($proposedAction, ['open', 'add', 'close'], true)) {
                    $counts['blocked']++;
                }

                $generatedAt = $this->signalGeneratedAt($evaluation);
                $expiresAt = $generatedAt?->addHours(4);

                $signal = Signal::query()->create([
                    'account_id' => $account->getKey(),
                    'strategy_profile_id' => $strategyProfile->getKey(),
                    'watchlist_id' => $watchlistSymbol?->watchlist_id,
                    'watchlist_symbol_id' => $watchlistSymbol?->getKey(),
                    'symbol' => $symbol,
                    'timeframe' => '4H',
                    'direction' => $this->visibility->signalDirection($action),
                    'strength' => $this->visibility->signalStrength($action),
                    'status' => $action,
                    'generated_at' => $generatedAt,
                    'expires_at' => $expiresAt,
                    'payload' => $payload,
                ]);

                $results[] = [
                    'signal_id' => $signal->getKey(),
                    'symbol' => $symbol,
                    'action' => $action,
                    'proposed_action' => $proposedAction,
                    'public_summary' => $payload['public_summary'],
                ];
            }

            $finishedAt = now();
            $summary = [
                'engine' => 'bismel1',
                'scope' => 'small_symbol_internal_scanner',
                'feed' => 'IEX',
                'timeframes' => ['1H', '4H'],
                'symbols' => $symbolsToScan,
                'counts' => $counts,
                'recent_signals' => $results,
                'visibility' => 'safe_summary_only',
                'safe_summary' => 'Small-symbol Bismel1 scanner completed with safe summaries only.',
                'risk_engine' => [
                    'status' => 'applied',
                    'blocked_actions' => $counts['blocked'],
                    'visibility' => 'safe_summary_only',
                ],
                'scheduler' => $schedulerSummary,
            ];

            $botRun->forceFill([
                'status' => 'completed',
                'finished_at' => $finishedAt,
                'runtime_seconds' => max(0, $startedAt->diffInSeconds($finishedAt)),
                'summary' => $summary,
                'error_message' => null,
            ])->save();

            if ($automationSetting instanceof AutomationSetting) {
                $automationSetting->forceFill([
                    'last_checked_at' => $finishedAt,
                    'run_health' => 'scanned',
                    'settings' => $this->mergedAutomationSettings(
                        $automationSetting,
                        $schedulerSummary,
                        $finishedAt,
                        'Scheduler-ready internal scan posture is active.',
                        'active',
                        $nextBarCloseAt = is_string($schedulerSummary['next_bar_close_at'] ?? null)
                            ? 'waiting for next bar close'
                            : 'recent run completed'
                    ),
                ])->save();
            }

            return [
                'status' => 'completed',
                'message' => 'Bismel1 small-symbol scanner stored safe relational signals and bot-run visibility.',
                'bot_run_id' => $botRun->getKey(),
                'counts' => $counts,
            ];
        } catch (Throwable $exception) {
            $this->failRun($botRun, $automationSetting, $exception->getMessage());

            throw $exception;
        }
    }

    public function evaluateManagedSymbol(Account $account, string $symbol): array
    {
        ['strategy_profile' => $strategyProfile, 'automation_setting' => $automationSetting, 'alpaca_account' => $alpacaAccount] = $this->resolveRuntimeContext($account);

        $sync = $this->marketDataService->syncLatestForAccount($account, [strtoupper(trim($symbol))], ['1H', '4H'], 420);

        if (! in_array((string) ($sync['status'] ?? 'failed'), ['synced', 'fresh'], true)) {
            throw new RuntimeException((string) ($sync['message'] ?? 'Bismel1 position management could not refresh bars for the symbol.'));
        }

        $normalizedSymbol = strtoupper(trim($symbol));
        $evaluation = $this->evaluateSymbol($account, $alpacaAccount, $normalizedSymbol);
        $position = AlpacaPosition::query()
            ->where('account_id', $account->getKey())
            ->where('alpaca_account_id', $alpacaAccount->getKey())
            ->where('symbol', $normalizedSymbol)
            ->first();
        $riskResult = $this->riskEngine->evaluate(
            $account,
            $strategyProfile,
            $automationSetting,
            $alpacaAccount,
            $position,
            $normalizedSymbol,
            '4H',
            $evaluation,
        );

        return [
            'strategy_profile' => $strategyProfile,
            'automation_setting' => $automationSetting,
            'alpaca_account' => $alpacaAccount,
            'position' => $position,
            'evaluation' => $evaluation,
            'risk_result' => $riskResult,
            'payload' => $this->visibility->buildSignalPayload($evaluation, $riskResult),
            'generated_at' => $this->signalGeneratedAt($evaluation),
        ];
    }

    protected function resolveSymbols(StrategyProfile $strategyProfile, ?array $symbols = null): array
    {
        $resolved = collect($symbols ?? [])
            ->filter(fn ($item) => is_string($item))
            ->map(fn (string $item) => strtoupper(trim($item)))
            ->filter(fn (string $item) => $item !== '' && preg_match('/^[A-Z0-9.\-]+$/', $item) === 1)
            ->unique()
            ->values();

        if ($resolved->isEmpty()) {
            $resolved = WatchlistSymbol::query()
                ->select('watchlist_symbols.symbol')
                ->join('watchlists', 'watchlists.id', '=', 'watchlist_symbols.watchlist_id')
                ->where('watchlists.strategy_profile_id', $strategyProfile->getKey())
                ->where('watchlists.status', 'active')
                ->where('watchlist_symbols.status', 'active')
                ->where('watchlist_symbols.asset_class', 'equity')
                ->orderBy('watchlist_symbols.symbol')
                ->limit(3)
                ->pluck('watchlist_symbols.symbol');
        }

        if ($resolved->isEmpty()) {
            $resolved = collect(['AAPL', 'MSFT', 'NVDA']);
        }

        return $resolved->take(3)->values()->all();
    }

    protected function resolveWatchlistSymbol(StrategyProfile $strategyProfile, string $symbol): ?WatchlistSymbol
    {
        return WatchlistSymbol::query()
            ->select('watchlist_symbols.*')
            ->join('watchlists', 'watchlists.id', '=', 'watchlist_symbols.watchlist_id')
            ->where('watchlists.strategy_profile_id', $strategyProfile->getKey())
            ->where('watchlist_symbols.symbol', $symbol)
            ->orderByDesc('watchlist_symbols.id')
            ->first();
    }

    protected function evaluateSymbol(Account $account, AlpacaAccount $alpacaAccount, string $symbol): array
    {
        $oneHourBars = AlpacaBar::query()
            ->where('account_id', $account->getKey())
            ->where('alpaca_account_id', $alpacaAccount->getKey())
            ->where('symbol', $symbol)
            ->where('timeframe', '1H')
            ->where('feed', 'iex')
            ->orderByDesc('starts_at')
            ->limit(2200)
            ->get()
            ->sortBy('starts_at')
            ->values();

        $fourHourBars = AlpacaBar::query()
            ->where('account_id', $account->getKey())
            ->where('alpaca_account_id', $alpacaAccount->getKey())
            ->where('symbol', $symbol)
            ->where('timeframe', '4H')
            ->where('feed', 'iex')
            ->orderByDesc('starts_at')
            ->limit(260)
            ->get()
            ->sortBy('starts_at')
            ->values();

        if ($fourHourBars->isEmpty()) {
            return [
                'symbol' => $symbol,
                'action' => 'skip',
                'safe_flags' => [
                    'trend_aligned' => false,
                    'pullback_detected' => false,
                    'reclaim_confirmed' => false,
                    'risk_blocked' => true,
                    'trailing_exit' => false,
                    'regime_fail' => false,
                ],
                'internal_strategy_state' => [
                    'raw_reason' => 'No closed 4H bars are available yet.',
                    'unresolved_gaps' => ['missing_4h_bars'],
                    'position_state' => [
                        'quantity' => 0.0,
                        'average_price' => 0.0,
                        'add_count' => 0,
                        'last_add_price' => null,
                        'dollars_used' => 0.0,
                        'pos_high' => null,
                    ],
                ],
            ];
        }

        $position = AlpacaPosition::query()
            ->where('account_id', $account->getKey())
            ->where('alpaca_account_id', $alpacaAccount->getKey())
            ->where('symbol', $symbol)
            ->first();

        $latestSignal = Signal::query()
            ->where('account_id', $account->getKey())
            ->where('symbol', $symbol)
            ->orderByDesc('generated_at')
            ->orderByDesc('id')
            ->first();

        $internalPositionState = is_array(data_get($latestSignal?->payload, 'internal_strategy_state.position_state'))
            ? data_get($latestSignal?->payload, 'internal_strategy_state.position_state')
            : [];

        $payload = [
            'symbol' => $symbol,
            'bars_1h' => $this->pythonBridge->formatBars($oneHourBars),
            'bars_4h' => $this->pythonBridge->formatBars($fourHourBars),
            'strategy_equity' => (float) ($alpacaAccount->equity ?? 10000.0),
            'position' => [
                'quantity' => $position?->qty !== null ? (float) $position->qty : 0.0,
                'average_price' => $position?->avg_entry_price !== null ? (float) $position->avg_entry_price : 0.0,
                'add_count' => (int) ($internalPositionState['add_count'] ?? 0),
                'last_add_price' => $position?->avg_entry_price !== null
                    ? ((float) ($internalPositionState['last_add_price'] ?? $position->avg_entry_price))
                    : null,
                'dollars_used' => $position?->cost_basis !== null
                    ? ((float) ($internalPositionState['dollars_used'] ?? $position->cost_basis))
                    : 0.0,
                'pos_high' => $internalPositionState['pos_high'] ?? null,
            ],
        ];

        return $this->pythonBridge->evaluateSymbol($payload);
    }

    protected function signalGeneratedAt(array $evaluation): ?CarbonImmutable
    {
        $timestamp = data_get($evaluation, 'internal_strategy_state.current.bar_close_time');

        if (! is_string($timestamp) || trim($timestamp) === '') {
            return null;
        }

        try {
            return CarbonImmutable::parse($timestamp);
        } catch (Throwable) {
            return null;
        }
    }

    protected function failRun(BotRun $botRun, ?AutomationSetting $automationSetting, string $message): void
    {
        $finishedAt = now();

        DB::transaction(function () use ($botRun, $automationSetting, $message, $finishedAt): void {
            $botRun->forceFill([
                'status' => 'failed',
                'finished_at' => $finishedAt,
                'runtime_seconds' => max(0, ($botRun->started_at ?? $finishedAt)->diffInSeconds($finishedAt)),
                'summary' => array_merge(is_array($botRun->summary) ? $botRun->summary : [], [
                    'safe_summary' => 'Small-symbol Bismel1 scanner stopped before signal storage completed.',
                ]),
                'error_message' => $message,
            ])->save();

            if ($automationSetting instanceof AutomationSetting) {
                $automationSetting->forceFill([
                    'last_checked_at' => $finishedAt,
                    'run_health' => 'scan_failed',
                    'settings' => $this->mergedAutomationSettings(
                        $automationSetting,
                        is_array(data_get($botRun->summary, 'scheduler')) ? data_get($botRun->summary, 'scheduler') : null,
                        $finishedAt,
                        'Scheduler trigger attempted a scan but the run failed.',
                        'blocked',
                        'Scanner validation failed before signal storage completed.'
                    ),
                ])->save();
            }
        });
    }

    protected function mergedAutomationSettings(
        AutomationSetting $automationSetting,
        ?array $schedulerSummary,
        $checkedAt,
        string $statusSummary,
        string $runtimeStatus = 'active',
        ?string $runtimeSummary = null,
    ): array {
        $settings = is_array($automationSetting->settings) ? $automationSetting->settings : [];
        $current = is_array($settings['bismel1_scheduler'] ?? null) ? $settings['bismel1_scheduler'] : [];
        $currentRuntime = is_array($settings['bismel1_runtime'] ?? null) ? $settings['bismel1_runtime'] : [];
        $timeframe = is_string($schedulerSummary['timeframe'] ?? null) ? (string) $schedulerSummary['timeframe'] : null;
        $nextBarCloseAt = is_string($schedulerSummary['next_bar_close_at'] ?? null) ? $schedulerSummary['next_bar_close_at'] : null;
        $barCloseAt = is_string($schedulerSummary['bar_close_at'] ?? null) ? $schedulerSummary['bar_close_at'] : null;

        $settings['bismel1_scheduler'] = array_merge($current, [
            'last_scheduler_run_at' => $checkedAt?->toIso8601String(),
            'last_due_timeframe' => $timeframe,
            'last_bar_close_at' => $barCloseAt,
            'next_intended_run' => $nextBarCloseAt,
            'scheduler_status_summary' => $statusSummary,
        ]);

        $settings['bismel1_runtime'] = array_merge($currentRuntime, [
            'last_runtime_status' => $runtimeStatus,
            'last_runtime_summary' => $runtimeSummary ?? $statusSummary,
            'last_runtime_updated_at' => $checkedAt?->toIso8601String(),
            'last_run_at' => $checkedAt?->toIso8601String(),
            'next_intended_run_at' => $nextBarCloseAt,
            'last_stage' => 'scanner',
            'last_stage_result' => $runtimeStatus,
            'last_stage_summary' => $runtimeSummary ?? $statusSummary,
        ]);

        return $settings;
    }

    protected function cleanupStaleRuns(Account $account, string $runType): void
    {
        $cutoff = now()->subMinutes(self::STALE_RUNTIME_MINUTES);

        BotRun::query()
            ->where('account_id', $account->getKey())
            ->where('run_type', $runType)
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
                        'safe_summary' => 'Scanner validation closed a stale running scan before retry.',
                    ]),
                    'error_message' => 'Stale running scanner run was closed before retry.',
                ])->save();

                ActivityLog::query()->create([
                    'account_id' => $account->getKey(),
                    'type' => 'bismel1_scanner_recovery',
                    'level' => 'warning',
                    'message' => 'Scanner validation closed a stale running scan before retry.',
                    'context' => [
                        'stale_run_id' => $run->getKey(),
                        'safe_summary' => 'Scanner validation closed a stale running scan before retry.',
                    ],
                ]);
            });
    }

    protected function resolveRuntimeContext(Account $account): array
    {
        if (! $this->bismel1EntitlementService->allowsStocksAutomation($account)) {
            throw new RuntimeException('Bismel1 scanner is blocked because plan does not include this automation mode.');
        }

        $strategyProfile = $account->strategyProfiles()
            ->where('engine', 'python')
            ->where('is_active', true)
            ->latest('id')
            ->first();

        if (! $strategyProfile instanceof StrategyProfile) {
            throw new RuntimeException('No active Python-backed Bismel1 strategy profile is available for this workspace.');
        }

        $automationSetting = $account->automationSettings()
            ->where(function ($query) use ($strategyProfile): void {
                $query->whereNull('strategy_profile_id')->orWhere('strategy_profile_id', $strategyProfile->getKey());
            })
            ->latest('id')
            ->first();

        $alpacaAccount = $account->alpacaAccounts()
            ->where('is_active', true)
            ->where('status', 'active')
            ->latest('id')
            ->first();

        if (! $alpacaAccount instanceof AlpacaAccount) {
            throw new RuntimeException('No active Alpaca account is available for the Bismel1 scanner.');
        }

        $accountGuard = $this->bismel1RuntimeGuardrails->runtimeAccountGuard($alpacaAccount);

        if (! $accountGuard['allowed']) {
            throw new RuntimeException((string) $accountGuard['summary']);
        }

        return [
            'strategy_profile' => $strategyProfile,
            'automation_setting' => $automationSetting,
            'alpaca_account' => $alpacaAccount,
        ];
    }
}
