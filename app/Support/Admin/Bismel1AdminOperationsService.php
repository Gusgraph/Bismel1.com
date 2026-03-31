<?php
// اعوز بالله من الشياطين و ان يحضرون بسم الله الرحمن الرحيم الله لا إله إلا هو الحي القيوم
// Bismillahi ar-Rahmani ar-Rahim Audhu billahi min ash-shayatin wa an yahdurun Bismillah ar-Rahman ar-Rahim Allah la ilaha illa huwa al-hayy al-qayyum. Tamsa Allahu ala ayunihim
// version: x
// ======================================================
// - App Name: bismel1.com
// - Gusgraph LLC -
// - Author: Gus Kazem
// - https://Gusgraph.com
// - File Path: app/Support/Admin/Bismel1AdminOperationsService.php
// ======================================================

namespace App\Support\Admin;

use App\Models\Account;
use App\Models\ActivityLog;
use App\Models\AlpacaAccount;
use App\Models\AutomationSetting;
use App\Models\BotRun;
use App\Models\Signal;
use App\Models\StrategyProfile;
use App\Support\Billing\Bismel1EntitlementService;
use App\Support\Display\SafeDisplay;
use Carbon\CarbonImmutable;
use Illuminate\Support\Collection;

class Bismel1AdminOperationsService
{
    public function __construct(
        protected Bismel1EntitlementService $bismel1EntitlementService,
        protected Bismel1OperatorToolsService $bismel1OperatorToolsService,
    ) {}

    public function platformOverview(): array
    {
        $accounts = Account::query()
            ->with($this->accountRelations())
            ->orderBy('name')
            ->get();

        $snapshots = $accounts
            ->map(fn (Account $account) => $this->accountSnapshot($account))
            ->values();

        $trackedCount = $snapshots->count();
        $activeCount = $snapshots->where('automation_state.value', 'active')->count();
        $stoppedCount = $snapshots->where('automation_state.value', 'stopped')->count();
        $blockedCount = $snapshots->where('automation_state.value', 'blocked')->count();
        $readyBrokerCount = $snapshots->where('broker_readiness.value', 'ready')->count();
        $warningCount = $snapshots->filter(fn (array $snapshot) => $snapshot['warning_summary'] !== null)->count();
        $entitlementMismatchCount = $snapshots->filter(fn (array $snapshot) => $snapshot['entitlement_mismatch_summary'] !== null)->count();

        $blockedReasonItems = $snapshots
            ->where('automation_state.value', 'blocked')
            ->groupBy(fn (array $snapshot) => $snapshot['blocked_reason_category'] ?: 'Runtime review')
            ->map(fn (Collection $items, string $category) => [
                'label' => $category,
                'value' => (string) $items->count(),
                'context' => $items->pluck('account_name')->take(3)->implode(', '),
            ])
            ->sortByDesc(fn (array $item) => (int) $item['value'])
            ->values()
            ->all();

        $brokerReadinessItems = $snapshots
            ->groupBy(fn (array $snapshot) => $snapshot['broker_readiness']['category'] ?? 'Broker review')
            ->map(fn (Collection $items, string $category) => [
                'label' => $category,
                'value' => (string) $items->count(),
                'context' => $items->pluck('account_name')->take(3)->implode(', '),
            ])
            ->sortByDesc(fn (array $item) => (int) $item['value'])
            ->values()
            ->all();

        return [
            'summary_items' => [
                [
                    'label' => 'Active Automation',
                    'value' => (string) $activeCount,
                    'context' => $trackedCount > 0 ? $trackedCount.' customer accounts tracked' : 'No customer accounts tracked yet',
                ],
                [
                    'label' => 'Stopped Automation',
                    'value' => (string) $stoppedCount,
                    'context' => 'Customer bots currently not running',
                ],
                [
                    'label' => 'Blocked Accounts',
                    'value' => (string) $blockedCount,
                    'context' => $blockedCount > 0 ? 'Needs admin review' : 'No blocked states detected',
                ],
                [
                    'label' => 'Broker Ready',
                    'value' => (string) $readyBrokerCount,
                    'context' => max($trackedCount - $readyBrokerCount, 0).' customer accounts still unready',
                ],
                [
                    'label' => 'Recent Warnings',
                    'value' => (string) $warningCount,
                    'context' => $warningCount > 0 ? 'Safe runtime warnings summarized below' : 'No warning summaries detected',
                ],
                [
                    'label' => 'Entitlement Mismatches',
                    'value' => (string) $entitlementMismatchCount,
                    'context' => $entitlementMismatchCount > 0 ? 'Plan or add-on follow-up is needed' : 'No entitlement mismatches detected',
                ],
            ],
            'blocked_reason_items' => $blockedReasonItems,
            'broker_readiness_items' => $brokerReadinessItems,
            'recovery_order_items' => [
                [
                    'label' => '1. Check readiness',
                    'value' => 'Confirm the current blocked or warning summary',
                    'context' => 'Start with the readiness state before triggering a recovery action.',
                ],
                [
                    'label' => '2. Sync broker',
                    'value' => 'Refresh broker account, orders, and positions',
                    'context' => 'Use broker sync first when account state or sync freshness is unclear.',
                ],
                [
                    'label' => '3. Reconcile',
                    'value' => 'Bring broker and local position state back into alignment',
                    'context' => 'Run reconciliation after broker sync when positions or orders look out of step.',
                ],
                [
                    'label' => '4. Resume only when safe',
                    'value' => 'Resume automation after readiness is clear',
                    'context' => 'Resume only when broker readiness, plan access, and strategy mapping are all ready.',
                ],
            ],
            'account_rows' => $snapshots
                ->map(fn (array $snapshot) => [
                    'account' => $snapshot['account_name'],
                    'route' => route('admin.account-detail.index', ['account' => $snapshot['account_id']]),
                    'automation' => $snapshot['automation_state']['label'],
                    'blocked' => $snapshot['blocked_reason_summary'],
                    'broker' => $snapshot['broker_readiness']['label'],
                    'priority' => $snapshot['operator_priority_label'],
                    'last_run' => $snapshot['last_run_label'],
                    'next_run' => $snapshot['next_run_label'],
                    'execution' => $snapshot['recent_execution_summary'],
                ])
                ->values()
                ->all(),
            'recent_execution_items' => $this->recentExecutionItems(),
            'recent_risk_items' => $this->recentRiskItems(),
            'recent_position_items' => $this->recentPositionItems(),
            'runtime_warning_items' => $snapshots
                ->filter(fn (array $snapshot) => $snapshot['warning_summary'] !== null)
                ->take(6)
                ->map(fn (array $snapshot) => [
                    'title' => $snapshot['account_name'],
                    'summary' => $snapshot['warning_summary'],
                    'status' => $snapshot['automation_state']['label'],
                    'details' => [
                        ['label' => 'Broker', 'value' => $snapshot['broker_readiness']['label']],
                        ['label' => 'Last run', 'value' => $snapshot['last_run_label']],
                    ],
                    'route' => route('admin.account-detail.index', ['account' => $snapshot['account_id']]),
                ])
                ->values()
                ->all(),
            'has_operations_data' => $trackedCount > 0,
        ];
    }

    public function accountOverview(?Account $account): array
    {
        if (! $account instanceof Account) {
            return [
                'summary_items' => [],
                'operator_summary_items' => [],
                'operator_actions' => [],
                'recent_execution_items' => [],
                'recent_risk_items' => [],
                'recent_position_items' => [],
                'recent_activity_items' => [],
                'recent_operator_items' => [],
                'has_operations_data' => false,
            ];
        }

        $account->loadMissing($this->accountRelations());
        $snapshot = $this->accountSnapshot($account);
        $operatorState = $this->operatorState($account);

        return [
            'summary_items' => [
                [
                    'label' => 'Automation State',
                    'value' => $snapshot['automation_state']['label'],
                    'context' => $snapshot['blocked_reason_summary'],
                ],
                [
                    'label' => 'Broker Readiness',
                    'value' => $snapshot['broker_readiness']['label'],
                    'context' => $snapshot['broker_readiness']['summary'],
                ],
                [
                    'label' => 'Recovery Priority',
                    'value' => $snapshot['operator_priority_label'],
                    'context' => $snapshot['operator_priority_summary'],
                ],
                [
                    'label' => 'Last Run',
                    'value' => $snapshot['last_run_label'],
                    'context' => $snapshot['last_run_status'],
                ],
                [
                    'label' => 'Next Intended Run',
                    'value' => $snapshot['next_run_label'],
                    'context' => 'Scheduler visibility only',
                ],
                [
                    'label' => 'Recent Execution',
                    'value' => $snapshot['recent_execution_summary'],
                    'context' => 'Safe broker outcome summary only',
                ],
                [
                    'label' => 'Recent Position Manager',
                    'value' => $snapshot['recent_position_summary'],
                    'context' => 'Safe reconciliation and management summary only',
                ],
                [
                    'label' => 'Execution Environment',
                    'value' => $snapshot['execution_environment_label'],
                    'context' => $snapshot['execution_environment_summary'],
                ],
            ],
            'operator_summary_items' => [
                [
                    'label' => 'Last Operator Action',
                    'value' => $operatorState['last_action_label'],
                    'context' => $operatorState['last_action_time'],
                ],
                [
                    'label' => 'Last Operator Result',
                    'value' => $operatorState['last_action_result'],
                    'context' => $operatorState['last_action_summary'],
                ],
                [
                    'label' => 'Operator Priority',
                    'value' => $snapshot['operator_priority_label'],
                    'context' => $snapshot['operator_priority_summary'],
                ],
            ],
            'operator_actions' => $this->bismel1OperatorToolsService->allowedActionsFor($account),
            'recent_execution_items' => $this->accountExecutionItems($account),
            'recent_risk_items' => $this->accountRiskItems($account),
            'recent_position_items' => $this->accountPositionItems($account),
            'recent_operator_items' => $this->accountOperatorItems($account),
            'recent_activity_items' => $account->activityLogs
                ->sortByDesc(fn ($item) => $item->created_at?->getTimestamp() ?? 0)
                ->take(5)
                ->map(fn (ActivityLog $item) => [
                    'title' => SafeDisplay::dateTime($item->created_at, 'Recent activity'),
                    'summary' => SafeDisplay::sanitizedText($item->message, 'Runtime activity recorded'),
                    'status' => $item->level ?? 'info',
                    'details' => [
                        ['label' => 'Type', 'value' => ucfirst(str_replace('_', ' ', (string) $item->type))],
                    ],
                ])
                ->values()
                ->all(),
            'has_operations_data' => true,
        ];
    }

    protected function accountRelations(): array
    {
        return [
            'owner',
            'brokerConnections.brokerCredentials',
            'automationSettings.strategyProfile',
            'strategyProfiles',
            'alpacaAccounts',
            'botRuns',
            'signals',
            'alpacaOrders',
            'alpacaPositions',
            'activityLogs',
        ];
    }

    protected function accountSnapshot(Account $account): array
    {
        $automationSetting = $account->automationSettings
            ->sortByDesc(fn ($item) => $item->updated_at?->getTimestamp() ?? $item->created_at?->getTimestamp() ?? 0)
            ->first();
        $strategyProfile = $this->strategyProfile($account, $automationSetting);
        $brokerConnections = $account->brokerConnections ?? collect();
        $brokerCredentials = $brokerConnections->flatMap->brokerCredentials;
        $alpacaAccount = $this->primaryAlpacaAccount($account);
        $botRuns = $account->botRuns
            ->sortByDesc(fn ($item) => $item->started_at?->getTimestamp() ?? $item->created_at?->getTimestamp() ?? 0)
            ->values();
        $signals = $account->signals
            ->sortByDesc(fn ($item) => $item->generated_at?->getTimestamp() ?? $item->created_at?->getTimestamp() ?? 0)
            ->values();
        $latestRun = $botRuns->first();
        $latestExecutionRun = $botRuns->first(fn (BotRun $run) => $run->run_type === 'execution');
        $latestPositionRun = $botRuns->first(fn (BotRun $run) => $run->run_type === 'position_management');
        $latestRiskSignal = $signals->first(fn (Signal $signal) => $this->signalIsRiskBlocked($signal));
        $settings = is_array($automationSetting?->settings) ? $automationSetting->settings : [];
        $runtimeState = is_array($settings['bismel1_runtime'] ?? null) ? $settings['bismel1_runtime'] : [];
        $schedulerState = is_array($settings['bismel1_scheduler'] ?? null) ? $settings['bismel1_scheduler'] : [];
        $executionState = is_array($settings['bismel1_execution'] ?? null) ? $settings['bismel1_execution'] : [];
        $positionManagerState = is_array($settings['bismel1_position_manager'] ?? null) ? $settings['bismel1_position_manager'] : [];
        $entitlements = $this->bismel1EntitlementService->resolve($account);
        $brokerReadiness = $this->brokerReadinessMeta($brokerConnections->count(), $brokerCredentials->count(), $alpacaAccount);
        $strategyReady = $strategyProfile instanceof StrategyProfile && (bool) ($strategyProfile->is_active ?? false);
        $automationEnabled = (bool) ($automationSetting?->ai_enabled ?? false) && (bool) ($automationSetting?->scanner_enabled ?? false);
        $automationState = $this->automationStateMeta(
            $automationEnabled,
            $strategyReady,
            $brokerReadiness,
            (string) ($runtimeState['last_runtime_status'] ?? '')
        );
        $blockedReasonSummary = $this->blockedReasonSummary(
            $automationState['value'],
            $strategyReady,
            $brokerReadiness,
            SafeDisplay::sanitizedText((string) ($runtimeState['last_runtime_summary'] ?? ''), ''),
            SafeDisplay::sanitizedText((string) data_get($entitlements, 'mismatch_summary', data_get($entitlements, 'admin_blocked_summary', '')), '')
        );
        $recentExecutionSummary = $this->recentExecutionSummary($executionState, $latestExecutionRun);
        $recentPositionSummary = $this->recentPositionSummary($positionManagerState, $latestPositionRun);
        $recentRiskSummary = $latestRiskSignal instanceof Signal
            ? SafeDisplay::sanitizedText((string) data_get($latestRiskSignal->payload, 'admin_summary', data_get($latestRiskSignal->payload, 'public_summary', 'Risk block recorded.')))
            : 'No recent risk block recorded yet';

        return [
            'account_id' => $account->getKey(),
            'account_name' => $account->name,
            'automation_state' => $automationState,
            'blocked_reason_category' => $automationState['value'] === 'blocked'
                ? $this->blockedReasonCategory($strategyReady, $brokerReadiness, data_get($entitlements, 'mismatch_summary'))
                : null,
            'blocked_reason_summary' => $blockedReasonSummary,
            'entitlement_mismatch_summary' => data_get($entitlements, 'mismatch_summary'),
            'broker_readiness' => $brokerReadiness,
            'last_run_label' => $latestRun instanceof BotRun
                ? SafeDisplay::dateTime($latestRun->started_at, 'No run started yet')
                : 'No run started yet',
            'last_run_status' => $latestRun instanceof BotRun
                ? ucfirst(str_replace('_', ' ', (string) $latestRun->status))
                : 'No relational bot run recorded yet',
            'next_run_label' => $this->nextRunLabel($schedulerState, $automationEnabled),
            'recent_execution_summary' => $recentExecutionSummary,
            'recent_risk_summary' => $recentRiskSummary,
            'recent_position_summary' => $recentPositionSummary,
            'execution_environment_label' => $alpacaAccount instanceof AlpacaAccount
                ? strtoupper((string) ($alpacaAccount->environment ?? 'paper'))
                : 'Not connected',
            'execution_environment_summary' => $alpacaAccount instanceof AlpacaAccount
                ? ($this->normalizedEnvironment($alpacaAccount) === 'live'
                    ? 'Live broker access is connected. Review operator actions carefully before running them.'
                    : 'Paper broker access is connected for safer validation and recovery work.')
                : 'Connect Alpaca to establish broker environment visibility.',
            'operator_priority_label' => $this->operatorPriorityLabel($automationState['value'], $brokerReadiness, $recentExecutionSummary),
            'operator_priority_summary' => $this->operatorPrioritySummary($automationState['value'], $blockedReasonSummary, $brokerReadiness, $recentExecutionSummary),
            'warning_summary' => $this->warningSummary($automationState['value'], $blockedReasonSummary, $recentExecutionSummary, $latestRun),
        ];
    }

    protected function strategyProfile(Account $account, ?AutomationSetting $automationSetting): ?StrategyProfile
    {
        $strategyProfile = $automationSetting?->strategyProfile;

        if ($strategyProfile instanceof StrategyProfile) {
            return $strategyProfile;
        }

        return $account->strategyProfiles
            ->sortByDesc(function (StrategyProfile $profile): int {
                return (int) (($profile->is_active ? 1 : 0) * 1000000000) + ($profile->updated_at?->getTimestamp() ?? $profile->created_at?->getTimestamp() ?? 0);
            })
            ->first();
    }

    protected function operatorState(Account $account): array
    {
        $automationSetting = $account->automationSettings
            ->sortByDesc(fn ($item) => $item->updated_at?->getTimestamp() ?? $item->created_at?->getTimestamp() ?? 0)
            ->first();
        $settings = is_array($automationSetting?->settings) ? $automationSetting->settings : [];
        $operatorState = is_array($settings['bismel1_operator'] ?? null) ? $settings['bismel1_operator'] : [];
        $lastAction = SafeDisplay::sanitizedText((string) ($operatorState['last_action'] ?? ''), '');
        $lastActionLabel = $lastAction !== ''
            ? ucfirst(str_replace('_', ' ', $lastAction))
            : 'No operator action yet';
        $lastActionResult = SafeDisplay::sanitizedText((string) ($operatorState['last_action_result'] ?? ''), '');
        $lastActionSummary = SafeDisplay::sanitizedText((string) ($operatorState['last_action_summary'] ?? ''), '');

        return [
            'last_action_label' => $lastActionLabel,
            'last_action_result' => $lastActionResult !== '' ? ucfirst(str_replace('_', ' ', $lastActionResult)) : 'No result recorded',
            'last_action_summary' => $lastActionSummary !== '' ? $lastActionSummary : 'No operator summary recorded yet.',
            'last_action_time' => $this->displayDateOrRaw((string) ($operatorState['last_action_at'] ?? 'No operator action recorded yet')),
        ];
    }

    protected function primaryAlpacaAccount(Account $account): ?AlpacaAccount
    {
        $sorted = $account->alpacaAccounts
            ->sortByDesc(function (AlpacaAccount $alpacaAccount): int {
                return ((int) ($alpacaAccount->is_active ?? false) * 1000000000)
                    + ((int) ($alpacaAccount->is_primary ?? false) * 100000000)
                    + ($alpacaAccount->last_synced_at?->getTimestamp() ?? $alpacaAccount->created_at?->getTimestamp() ?? 0);
            })
            ->values();

        return $sorted->first();
    }

    protected function brokerReadinessMeta(int $connectionCount, int $credentialCount, ?AlpacaAccount $alpacaAccount): array
    {
        if ($connectionCount === 0) {
            return [
                'value' => 'blocked',
                'label' => 'Connection missing',
                'category' => 'Missing broker connection',
                'summary' => 'No broker connection is linked to this workspace yet. Connect Alpaca before running recovery actions.',
            ];
        }

        if ($credentialCount === 0) {
            return [
                'value' => 'blocked',
                'label' => 'Credentials missing',
                'category' => 'Missing broker credential',
                'summary' => 'Broker credentials are missing for this workspace. Update the connection before running recovery actions.',
            ];
        }

        if (! $alpacaAccount instanceof AlpacaAccount) {
            return [
                'value' => 'blocked',
                'label' => 'Broker account missing',
                'category' => 'Missing broker account',
                'summary' => 'No synced broker account is available yet. Run broker sync after the connection is in place.',
            ];
        }

        if (! in_array((string) ($alpacaAccount->status ?? 'inactive'), ['active'], true)) {
            return [
                'value' => 'blocked',
                'label' => 'Account not active',
                'category' => 'Broker account unready',
                'summary' => 'The broker account is connected but not active yet. Review broker status before resuming automation.',
            ];
        }

        if (strtolower((string) ($alpacaAccount->data_feed ?? 'iex')) !== 'iex') {
            return [
                'value' => 'blocked',
                'label' => 'Market data unavailable',
                'category' => 'Market data unavailable',
                'summary' => 'The broker account is connected, but the approved market-data path is unavailable for runtime use.',
            ];
        }

        if ($alpacaAccount->last_synced_at === null && $alpacaAccount->last_account_sync_at === null) {
            return [
                'value' => 'warning',
                'label' => 'Sync overdue',
                'category' => 'Broker sync stale',
                'summary' => 'The broker account is connected, but there is no recent sync timestamp. Sync broker now before other recovery actions.',
            ];
        }

        return [
            'value' => 'ready',
            'label' => 'Ready',
            'category' => 'Broker ready',
            'summary' => 'Broker account, credentials, and sync posture are ready for runtime use.',
        ];
    }

    protected function automationStateMeta(bool $automationEnabled, bool $strategyReady, array $brokerReadiness, string $runtimeStatus): array
    {
        if (! $automationEnabled) {
            return ['value' => 'stopped', 'label' => 'Stopped'];
        }

        if (! $strategyReady || $brokerReadiness['value'] !== 'ready' || $runtimeStatus === 'blocked') {
            return ['value' => 'blocked', 'label' => 'Blocked'];
        }

        return ['value' => 'active', 'label' => 'Active'];
    }

    protected function blockedReasonCategory(bool $strategyReady, array $brokerReadiness, ?string $entitlementMismatch = null): string
    {
        if (is_string($entitlementMismatch) && trim($entitlementMismatch) !== '') {
            return 'Entitlement';
        }

        if (! $strategyReady) {
            return 'Strategy mapping missing';
        }

        return $brokerReadiness['category'] ?? 'Runtime review';
    }

    protected function blockedReasonSummary(string $automationState, bool $strategyReady, array $brokerReadiness, string $runtimeSummary, string $entitlementSummary = ''): string
    {
        if ($entitlementSummary !== '') {
            return $entitlementSummary;
        }

        if ($automationState === 'stopped') {
            return 'Automation is stopped for this customer account.';
        }

        if (! $strategyReady) {
            return 'Active Bismel1 strategy mapping is missing for this customer account.';
        }

        if (($brokerReadiness['value'] ?? null) !== 'ready') {
            return (string) ($brokerReadiness['summary'] ?? 'Broker readiness is blocking runtime work.');
        }

        if ($runtimeSummary !== '') {
            return $runtimeSummary;
        }

        return 'Runtime review is required before the next automation cycle.';
    }

    protected function nextRunLabel(array $schedulerState, bool $automationEnabled): string
    {
        $rawValue = $schedulerState['next_intended_run'] ?? null;

        if (is_string($rawValue) && trim($rawValue) !== '') {
            return $this->displayDateOrRaw($rawValue);
        }

        return $automationEnabled ? 'Waiting for next bar close' : 'AI stopped';
    }

    protected function recentExecutionSummary(array $executionState, ?BotRun $latestExecutionRun): string
    {
        $summary = SafeDisplay::sanitizedText((string) ($executionState['last_execution_summary'] ?? ''), '');

        if ($summary !== '') {
            return $summary;
        }

        return $latestExecutionRun instanceof BotRun
            ? SafeDisplay::sanitizedText((string) data_get($latestExecutionRun->summary, 'safe_summary', ucfirst((string) $latestExecutionRun->status)))
            : 'No execution attempt recorded yet';
    }

    protected function recentPositionSummary(array $positionManagerState, ?BotRun $latestPositionRun): string
    {
        $summary = SafeDisplay::sanitizedText((string) ($positionManagerState['last_management_summary'] ?? ''), '');

        if ($summary !== '') {
            return $summary;
        }

        return $latestPositionRun instanceof BotRun
            ? SafeDisplay::sanitizedText((string) data_get($latestPositionRun->summary, 'safe_summary', ucfirst((string) $latestPositionRun->status)))
            : 'No reconciliation or position-manager outcome recorded yet';
    }

    protected function warningSummary(string $automationState, string $blockedReasonSummary, string $recentExecutionSummary, ?BotRun $latestRun): ?string
    {
        if ($automationState === 'blocked') {
            return $blockedReasonSummary;
        }

        if (str_contains(strtolower($recentExecutionSummary), 'failed') || str_contains(strtolower($recentExecutionSummary), 'skipped')) {
            return $recentExecutionSummary;
        }

        if ($latestRun instanceof BotRun && str_contains(strtolower((string) $latestRun->status), 'failed')) {
            return SafeDisplay::sanitizedText((string) data_get($latestRun->summary, 'safe_summary', 'Latest runtime failed and needs review.'));
        }

        return null;
    }

    protected function operatorPriorityLabel(string $automationState, array $brokerReadiness, string $recentExecutionSummary): string
    {
        if (($brokerReadiness['value'] ?? null) !== 'ready') {
            return 'Sync broker and review readiness';
        }

        if ($automationState === 'blocked') {
            return 'Resolve blocked state first';
        }

        if (str_contains(strtolower($recentExecutionSummary), 'failed') || str_contains(strtolower($recentExecutionSummary), 'skipped')) {
            return 'Review recent execution result';
        }

        return 'Workspace is ready for normal operator review';
    }

    protected function operatorPrioritySummary(string $automationState, string $blockedReasonSummary, array $brokerReadiness, string $recentExecutionSummary): string
    {
        if (($brokerReadiness['value'] ?? null) !== 'ready') {
            return (string) ($brokerReadiness['summary'] ?? 'Broker readiness needs attention before other actions.');
        }

        if ($automationState === 'blocked') {
            return $blockedReasonSummary;
        }

        if (str_contains(strtolower($recentExecutionSummary), 'failed') || str_contains(strtolower($recentExecutionSummary), 'skipped')) {
            return $recentExecutionSummary;
        }

        return 'Review recent operator history and run readiness checks only when the workspace needs manual follow-up.';
    }

    protected function normalizedEnvironment(AlpacaAccount $alpacaAccount): string
    {
        return strtolower(trim((string) ($alpacaAccount->environment ?? 'paper'))) === 'live'
            ? 'live'
            : 'paper';
    }

    protected function recentExecutionItems(): array
    {
        return BotRun::query()
            ->with('account')
            ->where('run_type', 'execution')
            ->orderByDesc('started_at')
            ->orderByDesc('id')
            ->limit(6)
            ->get()
            ->map(fn (BotRun $run) => [
                'title' => $run->account?->name ?? 'Customer account',
                'summary' => SafeDisplay::sanitizedText((string) data_get($run->summary, 'safe_summary', ucfirst((string) $run->status))),
                'status' => (string) $run->status,
                'details' => [
                    ['label' => 'Started', 'value' => SafeDisplay::dateTime($run->started_at, 'Not available')],
                    ['label' => 'Type', 'value' => ucfirst(str_replace('_', ' ', (string) $run->run_type))],
                ],
                'route' => $run->account ? route('admin.account-detail.index', ['account' => $run->account]) : null,
            ])
            ->values()
            ->all();
    }

    protected function accountOperatorItems(Account $account): array
    {
        return $account->activityLogs
            ->filter(fn (ActivityLog $item) => str_starts_with((string) $item->type, 'bismel1_operator_'))
            ->sortByDesc(fn (ActivityLog $item) => $item->created_at?->getTimestamp() ?? 0)
            ->take(5)
            ->map(fn (ActivityLog $item) => [
                'title' => SafeDisplay::dateTime($item->created_at, 'Recent operator action'),
                'summary' => SafeDisplay::sanitizedText((string) data_get($item->context, 'safe_summary', $item->message), 'Operator action recorded'),
                'status' => $item->level ?? 'info',
                'details' => [
                    ['label' => 'Action', 'value' => ucfirst(str_replace('_', ' ', preg_replace('/^bismel1_operator_/', '', (string) $item->type)))],
                ],
            ])
            ->values()
            ->all();
    }

    protected function recentRiskItems(): array
    {
        return Signal::query()
            ->with('account')
            ->orderByDesc('generated_at')
            ->orderByDesc('id')
            ->limit(40)
            ->get()
            ->filter(fn (Signal $signal) => $this->signalIsRiskBlocked($signal))
            ->take(6)
            ->map(fn (Signal $signal) => [
                'title' => $signal->account?->name ?? 'Customer account',
                'summary' => SafeDisplay::sanitizedText((string) data_get($signal->payload, 'admin_summary', data_get($signal->payload, 'public_summary', 'Risk block recorded.'))),
                'status' => 'blocked',
                'details' => [
                    ['label' => 'Signal', 'value' => $signal->symbol.' / '.strtoupper((string) $signal->direction)],
                    ['label' => 'Generated', 'value' => SafeDisplay::dateTime($signal->generated_at, 'Not available')],
                ],
                'route' => $signal->account ? route('admin.account-detail.index', ['account' => $signal->account]) : null,
            ])
            ->values()
            ->all();
    }

    protected function recentPositionItems(): array
    {
        return BotRun::query()
            ->with('account')
            ->where('run_type', 'position_management')
            ->orderByDesc('started_at')
            ->orderByDesc('id')
            ->limit(6)
            ->get()
            ->map(fn (BotRun $run) => [
                'title' => $run->account?->name ?? 'Customer account',
                'summary' => SafeDisplay::sanitizedText((string) data_get($run->summary, 'safe_summary', ucfirst((string) $run->status))),
                'status' => (string) $run->status,
                'details' => [
                    ['label' => 'Managed', 'value' => SafeDisplay::dateTime($run->started_at, 'Not available')],
                    ['label' => 'Scope', 'value' => 'Reconciliation / position manager'],
                ],
                'route' => $run->account ? route('admin.account-detail.index', ['account' => $run->account]) : null,
            ])
            ->values()
            ->all();
    }

    protected function accountExecutionItems(Account $account): array
    {
        return $account->botRuns
            ->where('run_type', 'execution')
            ->sortByDesc(fn (BotRun $run) => $run->started_at?->getTimestamp() ?? $run->created_at?->getTimestamp() ?? 0)
            ->take(4)
            ->map(fn (BotRun $run) => [
                'title' => SafeDisplay::dateTime($run->started_at, 'Execution run'),
                'summary' => SafeDisplay::sanitizedText((string) data_get($run->summary, 'safe_summary', ucfirst((string) $run->status))),
                'status' => (string) $run->status,
                'details' => [
                    ['label' => 'Run type', 'value' => ucfirst(str_replace('_', ' ', (string) $run->run_type))],
                ],
            ])
            ->values()
            ->all();
    }

    protected function accountRiskItems(Account $account): array
    {
        return $account->signals
            ->sortByDesc(fn (Signal $signal) => $signal->generated_at?->getTimestamp() ?? $signal->created_at?->getTimestamp() ?? 0)
            ->filter(fn (Signal $signal) => $this->signalIsRiskBlocked($signal))
            ->take(4)
            ->map(fn (Signal $signal) => [
                'title' => SafeDisplay::dateTime($signal->generated_at, 'Risk review'),
                'summary' => SafeDisplay::sanitizedText((string) data_get($signal->payload, 'admin_summary', data_get($signal->payload, 'public_summary', 'Risk block recorded.'))),
                'status' => 'blocked',
                'details' => [
                    ['label' => 'Signal', 'value' => $signal->symbol.' / '.strtoupper((string) $signal->direction)],
                ],
            ])
            ->values()
            ->all();
    }

    protected function accountPositionItems(Account $account): array
    {
        return $account->botRuns
            ->where('run_type', 'position_management')
            ->sortByDesc(fn (BotRun $run) => $run->started_at?->getTimestamp() ?? $run->created_at?->getTimestamp() ?? 0)
            ->take(4)
            ->map(fn (BotRun $run) => [
                'title' => SafeDisplay::dateTime($run->started_at, 'Position manager'),
                'summary' => SafeDisplay::sanitizedText((string) data_get($run->summary, 'safe_summary', ucfirst((string) $run->status))),
                'status' => (string) $run->status,
                'details' => [
                    ['label' => 'Scope', 'value' => 'Reconciliation / position manager'],
                ],
            ])
            ->values()
            ->all();
    }

    protected function signalIsRiskBlocked(Signal $signal): bool
    {
        return (bool) data_get($signal->payload, 'safe_flags.risk_blocked', false)
            || (bool) (data_get($signal->payload, 'risk_engine.allowed', true) === false)
            || (string) data_get($signal->payload, 'risk_engine.status', '') === 'block_action';
    }

    protected function displayDateOrRaw(string $value): string
    {
        try {
            return SafeDisplay::dateTime(CarbonImmutable::parse($value));
        } catch (\Throwable) {
            return SafeDisplay::sanitizedText($value, 'Not available');
        }
    }
}
