<?php
// اعوز بالله من الشياطين و ان يحضرون بسم الله الرحمن الرحيم الله لا إله إلا هو الحي القيوم
// Bismillahi ar-Rahmani ar-Rahim Audhu billahi min ash-shayatin wa an yahdurun Bismillah ar-Rahman ar-Rahim Allah la ilaha illa huwa al-hayy al-qayyum. Tamsa Allahu ala ayunihim
// version: x
// ======================================================
// - App Name: bismel1.com
// - Gusgraph LLC -
// - Author: Gus Kazem
// - https://Gusgraph.com
// - File Path: app/Support/ViewData/AutomationPageData.php
// ======================================================

namespace App\Support\ViewData;

use App\Models\Account;
use App\Models\AlpacaAccount;
use App\Models\AutomationSetting;

class AutomationPageData
{
    public static function make(?Account $account = null, array $state = []): array
    {
        $automationSetting = $state['automation_setting'] instanceof AutomationSetting ? $state['automation_setting'] : null;
        $strategyProfile = $state['strategy_profile'] ?? null;
        $botRuns = $state['bot_runs'] ?? collect();
        $brokerConnections = $state['broker_connections'] ?? collect();
        $brokerCredentials = $state['broker_credentials'] ?? collect();
        $alpacaAccount = $state['alpaca_account'] instanceof AlpacaAccount ? $state['alpaca_account'] : null;
        $positions = $state['positions'] ?? collect();
        $orders = $state['orders'] ?? collect();
        $signals = $state['signals'] ?? collect();
        $licenses = $state['licenses'] ?? collect();
        $apiKeys = $state['api_keys'] ?? collect();
        $activityLogs = $state['activity_logs'] ?? collect();
        $entitlements = is_array($state['entitlements'] ?? null) ? $state['entitlements'] : [];
        $brokerGuard = is_array($state['broker_guard'] ?? null) ? $state['broker_guard'] : ['allowed' => false, 'summary' => 'broker not ready'];
        $hasAutomationData = (bool) ($account || $automationSetting);
        $latestRun = $botRuns->first();
        $lastStoppedRun = $botRuns->first(fn ($item) => $item->finished_at !== null);
        $strategyReady = $strategyProfile && (bool) ($strategyProfile->is_active ?? false);
        $brokerReady = $brokerConnections->isNotEmpty()
            && $brokerCredentials->isNotEmpty()
            && $alpacaAccount
            && (bool) ($brokerGuard['allowed'] ?? false);
        $marketDataReady = $brokerReady && strtolower((string) ($alpacaAccount?->data_feed ?? 'iex')) === 'iex';
        $automationEnabled = (bool) ($automationSetting?->ai_enabled ?? false) && (bool) ($automationSetting?->scanner_enabled ?? false);
        $schedulerState = is_array($automationSetting?->settings)
            && is_array($automationSetting->settings['bismel1_scheduler'] ?? null)
            ? $automationSetting->settings['bismel1_scheduler']
            : [];
        $executionState = is_array($automationSetting?->settings)
            && is_array($automationSetting->settings['bismel1_execution'] ?? null)
            ? $automationSetting->settings['bismel1_execution']
            : [];
        $positionManagerState = is_array($automationSetting?->settings)
            && is_array($automationSetting->settings['bismel1_position_manager'] ?? null)
            ? $automationSetting->settings['bismel1_position_manager']
            : [];
        $runtimeState = is_array($automationSetting?->settings)
            && is_array($automationSetting->settings['bismel1_runtime'] ?? null)
            ? $automationSetting->settings['bismel1_runtime']
            : [];
        $automationEntitled = (bool) data_get($entitlements, 'capabilities.can_use_stocks_automation', false);
        $planLabel = (string) data_get($entitlements, 'base_plan.label', 'No active base plan');
        $entitlementSummary = (string) data_get($runtimeState, 'entitlement_summary', data_get($entitlements, 'blocked_summary', 'Plan required'));
        $latestStageSummary = is_string($runtimeState['last_stage_summary'] ?? null)
            ? (string) $runtimeState['last_stage_summary']
            : null;
        $latestStageResult = is_string($runtimeState['last_stage_result'] ?? null)
            ? (string) $runtimeState['last_stage_result']
            : null;
        $latestStage = is_string($runtimeState['last_stage'] ?? null)
            ? ucfirst(str_replace('_', ' ', (string) $runtimeState['last_stage']))
            : 'No stage recorded yet';
        $runtimeHeadline = self::runtimeHeadline($automationEnabled, $brokerReady, $strategyReady, $schedulerState, $latestRun);
        $runtimeDetails = self::runtimeDetails($automationEnabled, $brokerReady, $strategyReady, $marketDataReady, $latestRun, $schedulerState);
        $recentActivityItems = $activityLogs
            ->take(5)
            ->map(fn ($item) => [
                'label' => $item->created_at?->format('Y-m-d H:i') ?? 'Recent activity',
                'value' => $item->message ?? 'Runtime activity recorded',
                'context' => ucfirst(str_replace('_', ' ', (string) ($item->type ?? 'activity'))),
            ])
            ->values()
            ->all();
        $primeStocksProduct = [
            'label' => 'Demo Access product',
            'title' => 'Prime Stocks',
            'body' => 'Prime Stocks is presented here as a Demo Access product inside Automation now, with wording already shaped so the later subscribed/live name can become Prime Stocks Bot Trader without rebuilding this page.',
            'future_label' => 'Prime Stocks Bot Trader',
        ];
        $primeStocksStatusItems = [
            ['label' => 'Presentation State', 'value' => 'Demo Access product', 'context' => 'Later subscribed/live presentation state: Prime Stocks Bot Trader.', 'icon' => 'fa-solid fa-tag', 'tone' => 'amber'],
            ['label' => 'Asset Class', 'value' => 'Stocks Only', 'context' => 'Prime Stocks remains a stocks-only product in this phase.', 'icon' => 'fa-solid fa-chart-line', 'tone' => 'blue'],
            ['label' => 'Execution Frame', 'value' => '1H decides when', 'context' => 'The 1H frame controls when participation is considered.', 'icon' => 'fa-solid fa-clock', 'tone' => 'violet'],
            ['label' => 'Trend Frame', 'value' => '1D helps decide whether', 'context' => 'The 1D frame helps decide whether a setup should be taken.', 'icon' => 'fa-solid fa-chart-column', 'tone' => 'amber'],
            ['label' => 'Pullback Window', 'value' => '5', 'context' => 'Approved current Prime Stocks pullback default.', 'icon' => 'fa-solid fa-arrow-trend-down', 'tone' => 'rose'],
            ['label' => 'Bot Runtime', 'value' => 'Cloud Run', 'context' => 'Cloud Run runs the bot server-side for this product.', 'icon' => 'fa-solid fa-server', 'tone' => 'sky'],
            ['label' => 'Page Role', 'value' => 'Control / monitoring only', 'context' => 'This Laravel page presents the product state and controls. It does not run the bot.', 'icon' => 'fa-solid fa-sliders', 'tone' => 'blue'],
            ['label' => 'Stay-open Requirement', 'value' => 'Not required', 'context' => 'The user does not need to keep the page open for trading to continue.', 'icon' => 'fa-solid fa-window-maximize', 'tone' => 'emerald'],
        ];
        $primeStocksConceptItems = [
            ['label' => 'Reclaim model summary', 'value' => 'Prime Stocks waits for reclaim behavior after the pullback instead of treating raw weakness as the entry.', 'context' => 'This remains a visual explanation only in this phase.', 'icon' => 'fa-solid fa-rotate', 'tone' => 'sky'],
            ['label' => 'FirstLot behavior summary', 'value' => 'FirstLot is the initial participation candidate once the reclaim setup and higher-timeframe conditions align.', 'context' => 'The customer sees the first-entry concept without live wiring here.', 'icon' => 'fa-solid fa-flag-checkered', 'tone' => 'emerald'],
            ['label' => 'MULTI behavior summary', 'value' => 'MULTI describes controlled add behavior after the initial participation has already been established.', 'context' => 'Adds stay conceptually separate from the first entry.', 'icon' => 'fa-solid fa-layer-group', 'tone' => 'violet'],
            ['label' => 'pauseNewBasket status concept', 'value' => 'Blocks new basket starts while the server-side bot model remains intact.', 'context' => 'Useful when fresh first-entry baskets should pause.', 'icon' => 'fa-solid fa-ban', 'tone' => 'amber'],
            ['label' => 'pauseAdds status concept', 'value' => 'Blocks add behavior without implying the browser is managing the bot.', 'context' => 'Adds can be paused independently from new baskets.', 'icon' => 'fa-solid fa-circle-pause', 'tone' => 'amber'],
            ['label' => 'ATR trail exit concept', 'value' => 'ATR trail exit language is reserved for the server-side exit path once protection takes over.', 'context' => 'Shown as product language only for now.', 'icon' => 'fa-solid fa-route', 'tone' => 'rose'],
            ['label' => 'Regime fail behavior summary', 'value' => 'If regime conditions fail, the product posture shifts toward pause or exit behavior instead of normal participation.', 'context' => 'This remains a control concept inside Automation.', 'icon' => 'fa-solid fa-shield-halved', 'tone' => 'amber'],
        ];

        return [
            'page' => [
                'title' => 'Automation',
                'intro' => 'Review automation status, readiness, timing, and recent activity for this workspace.',
                'subtitle' => $account
                    ? 'Automation controls, readiness, and recent status stay visible here.'
                    : 'No workspace is available yet, so automation will stay focused on setup until account details are ready.',
                'sections' => [
                    ['heading' => 'AI Control', 'description' => 'Start or stop automation for this workspace from one place.'],
                    ['heading' => 'Prime Stocks Demo Access', 'description' => 'Review Prime Stocks as a Demo Access product inside Automation without introducing a standalone page.'],
                    ['heading' => 'Runtime Status', 'description' => 'See whether automation is active, paused, waiting, or blocked.'],
                    ['heading' => 'Broker and Strategy Readiness', 'description' => 'Check whether the broker connection and strategy setup are ready to support automation.'],
                    ['heading' => 'Recent Activity', 'description' => 'Review the latest high-level automation events without exposing internal logic.'],
                ],
            ],
            'summary' => [
                'headline' => $runtimeHeadline,
                'details' => $runtimeDetails,
            ],
            'form' => [
                'name' => old('name', $automationSetting?->name ?? (($account?->name ? $account->name.' Automation' : 'Primary Automation'))),
                'status' => old('status', $automationSetting?->status ?? (($brokerReady && $strategyReady) ? 'armed' : 'review')),
                'risk_level' => old('risk_level', $automationSetting?->risk_level ?? (($brokerReady && $strategyReady) ? 'balanced' : 'conservative')),
                'ai_enabled' => old('ai_enabled', $automationSetting?->ai_enabled ?? false),
                'action_mode' => old('action_mode', 'save'),
            ],
            'primeStocksProduct' => $primeStocksProduct,
            'primeStocksStatusItems' => $primeStocksStatusItems,
            'primeStocksConceptItems' => $primeStocksConceptItems,
            'runtimeItems' => [
                ['label' => 'Current Summary', 'value' => $runtimeState['last_runtime_summary'] ?? $runtimeHeadline, 'context' => $runtimeState['last_runtime_status'] ?? 'No status recorded yet'],
                ['label' => 'Broker Readiness', 'value' => $brokerReady ? 'Ready' : 'Action needed', 'context' => $brokerReady ? 'The broker connection and market-data path are ready.' : (string) ($brokerGuard['summary'] ?? 'Check the broker connection and recent sync status.')],
                ['label' => 'Strategy Readiness', 'value' => $strategyReady ? 'Ready' : 'Action needed', 'context' => $strategyReady ? 'A strategy is connected to automation.' : 'Create or activate a strategy before starting automation.'],
                ['label' => 'Recent Activity', 'value' => $recentActivityItems !== [] ? 'Available' : 'No recent activity', 'context' => $recentActivityItems !== [] ? 'The latest workspace activity is shown below.' : 'Recent activity will appear here after automation begins running.'],
            ],
            'automationState' => [
                ['label' => 'AI Control', 'value' => $automationEnabled ? 'Enabled' : 'Disabled', 'context' => $automationEnabled ? 'Automation is on.' : 'Automation is currently off.'],
                ['label' => 'Plan Access', 'value' => $automationEntitled ? 'Allowed' : 'Blocked', 'context' => $automationEntitled ? 'The paid plan includes this automation mode.' : $entitlementSummary],
                ['label' => 'Automation Status', 'value' => ucfirst(str_replace('_', ' ', (string) ($automationSetting?->status ?? (($brokerReady && $strategyReady) ? 'review' : 'draft')))), 'context' => ($brokerReady && $strategyReady) ? 'This workspace is set up to run when started.' : 'Something still needs attention before automation can run smoothly.'],
                ['label' => 'Risk Level', 'value' => ucfirst((string) ($automationSetting?->risk_level ?? (($brokerReady && $strategyReady) ? 'balanced' : 'conservative'))), 'context' => 'This is the saved operating posture for automation.'],
                ['label' => 'Strategy', 'value' => $strategyProfile?->name ?? 'Not connected', 'context' => $strategyReady ? 'A strategy is connected to automation.' : 'Choose or activate a strategy.'],
                ['label' => 'Run Health', 'value' => ucfirst(str_replace('_', ' ', (string) ($automationSetting?->run_health ?? 'idle'))), 'context' => $runtimeState['last_runtime_summary'] ?? 'Health updates appear here after automation runs.'],
                ['label' => 'Position Manager', 'value' => $positionManagerState['last_management_summary'] ?? 'No position updates yet', 'context' => 'Position handling updates stay high level here.'],
            ],
            'runWindow' => [
                ['label' => 'Last Scheduler Run', 'value' => $schedulerState['last_scheduler_run_at'] ?? 'No scheduler run yet', 'context' => $schedulerState['last_due_timeframe'] ?? 'No run window has been recorded yet'],
                ['label' => 'Last Execution Attempt', 'value' => $executionState['last_execution_at'] ?? 'No execution updates yet', 'context' => $executionState['last_execution_result'] ?? 'No execution result has been recorded yet'],
                ['label' => 'Last Position Management', 'value' => $positionManagerState['last_management_at'] ?? 'No position updates yet', 'context' => $positionManagerState['last_management_result'] ?? 'No position result has been recorded yet'],
            ],
            'healthItems' => [
                ['label' => 'Broker Support', 'value' => $brokerReady ? 'Ready' : 'Action needed', 'context' => $brokerReady ? ($alpacaAccount?->last_synced_at ? 'Last broker update '.$alpacaAccount->last_synced_at->toDateTimeString() : 'Broker connection is available') : (string) ($brokerGuard['summary'] ?? 'No recent broker update is available')],
                ['label' => 'Market Data Path', 'value' => $marketDataReady ? 'Ready' : 'Action needed', 'context' => $marketDataReady ? 'Market data is available for the current automation cycle.' : 'Market data is not ready yet.'],
                ['label' => 'Strategy Mapping', 'value' => $strategyReady ? 'Ready' : 'Action needed', 'context' => $strategyReady ? 'A strategy is connected to automation.' : 'No strategy is connected yet.'],
                ['label' => 'Open Positions', 'value' => (string) $positions->count(), 'context' => 'Open positions in this workspace'],
                ['label' => 'Recent Orders', 'value' => (string) $orders->count(), 'context' => 'Recent orders in this workspace'],
                ['label' => 'Signals Stored', 'value' => (string) $signals->count(), 'context' => 'Recent signals in this workspace'],
                ['label' => 'API Support', 'value' => (string) $licenses->count().' licenses', 'context' => (string) $apiKeys->count().' saved API keys are available'],
            ],
            'linkageItems' => [
                ['label' => 'Position Management', 'value' => $positionManagerState['last_management_result'] ?? 'Waiting for position updates', 'context' => 'Position updates appear here after trades begin moving through the workspace.'],
            ],
            'recentActivityItems' => $recentActivityItems,
            'relatedLinks' => [
                ['route' => 'customer.strategy.index', 'label' => 'Strategy', 'description' => 'Refine strategy intent before expanding runtime automation.'],
                ['route' => 'customer.broker.index', 'label' => 'Broker', 'description' => 'Confirm broker connectivity and masked credential posture.'],
                ['route' => 'customer.onboarding.index', 'label' => 'Onboarding', 'description' => 'Review readiness gaps that still block a controlled automation rollout.'],
            ],
            'hasAutomationData' => $hasAutomationData,
        ];
    }

    protected static function runtimeHeadline(bool $automationEnabled, bool $brokerReady, bool $strategyReady, array $schedulerState, $latestRun): string
    {
        if (! $automationEnabled) {
            return 'Automation off';
        }

        if (! $brokerReady) {
            return 'broker not ready';
        }

        if (! $strategyReady) {
            return 'strategy not mapped';
        }

        if (is_string($schedulerState['next_intended_run'] ?? null) && trim((string) $schedulerState['next_intended_run']) !== '') {
            return 'waiting for next bar close';
        }

        if (($latestRun?->status ?? null) === 'completed') {
            return 'recent run completed';
        }

        return 'AI active';
    }

    protected static function runtimeDetails(bool $automationEnabled, bool $brokerReady, bool $strategyReady, bool $marketDataReady, $latestRun, array $schedulerState): string
    {
        if (! $automationEnabled) {
            return 'Automation is currently off for this workspace. Start AI when you are ready to resume monitoring.';
        }

        if (! $brokerReady) {
            return 'Automation is on, but the broker connection still needs attention before the next run can proceed.';
        }

        if (! $strategyReady) {
            return 'Automation is on, but a strategy still needs to be connected before the next run can proceed.';
        }

        if (! $marketDataReady) {
            return 'Automation is on, but market data is not ready yet for the current workspace.';
        }

        if (($latestRun?->status ?? null) === 'completed') {
            return 'The most recent automation cycle finished cleanly and the workspace is waiting for the next scheduled run.';
        }

        if (is_string($schedulerState['next_intended_run'] ?? null) && trim((string) $schedulerState['next_intended_run']) !== '') {
            return 'Automation is ready and waiting for the next scheduled run window.';
        }

        return 'Automation is active and the workspace is aligned for the next run.';
    }
}
