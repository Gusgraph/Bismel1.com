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
use Illuminate\Contracts\Auth\Authenticatable;

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
        $currentUser = $state['user'] instanceof Authenticatable ? $state['user'] : null;
        $entitlements = is_array($state['entitlements'] ?? null) ? $state['entitlements'] : [];
        $brokerGuard = is_array($state['broker_guard'] ?? null) ? $state['broker_guard'] : ['allowed' => false, 'summary' => 'broker not ready'];
        $hasAutomationData = (bool) ($account || $automationSetting);
        $latestRun = $botRuns->first();
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
        $subscriptionActive = (bool) data_get($entitlements, 'subscription_active', false);
        $basePlanLabel = (string) data_get($entitlements, 'base_plan.label', 'No active base plan');
        $basePlanCode = (string) data_get($entitlements, 'base_plan.code', '');
        $entitlementSummary = (string) data_get($runtimeState, 'entitlement_summary', data_get($entitlements, 'blocked_summary', 'Plan required'));
        $localFullAccessOverride = self::hasLocalFullAccessOverride($currentUser?->email);
        $demoAccessProduct = self::isDemoAccessProduct($entitlements);
        $accessState = self::accessState($subscriptionActive, $automationEntitled, $localFullAccessOverride, $demoAccessProduct);
        $productLabel = $accessState === 'active_subscribed_product' ? 'Prime Stocks Bot Trader' : ($accessState === 'demo_access_product' ? 'Demo Access product' : 'No active product');
        $productStatus = match ($accessState) {
            'active_subscribed_product' => 'Active subscribed product',
            'demo_access_product' => 'Demo Access product',
            default => 'No active product',
        };
        $accessAction = match ($accessState) {
            'active_subscribed_product' => 'Manage in this control / monitoring zone',
            'demo_access_product' => 'Demo access active now; subscribed/live rollout comes later',
            default => 'Upgrade / subscribe later when Stripe-backed subscriptions are introduced',
        };
        $accessActionContext = match ($accessState) {
            'active_subscribed_product' => $localFullAccessOverride
                ? 'customer.local@gusgraph.test and admin.local@gusgraph.test currently render as full-access local product users until Stripe subscriptions are wired later.'
                : 'The current paid access posture allows this product inside Automation.',
            'demo_access_product' => 'This workspace can review the product shape now without live subscribed runtime access yet.',
            default => 'No active automation product is attached to this workspace yet.',
        };
        $latestSignal = $signals->first();
        $lastSignalValue = $latestSignal
            ? strtoupper((string) ($latestSignal->signal_type ?? $latestSignal->direction ?? 'signal'))
            : ($accessState === 'active_subscribed_product' ? 'FirstLot demo' : 'No live signal yet');
        $lastSignalTime = $latestSignal?->generated_at?->format('Y-m-d H:i').' UTC'
            ?? ($accessState === 'no_active_product' ? 'No signal yet' : '2026-04-04 15:19 UTC demo');
        $runtimeHeadline = self::accessHeadline($accessState, $productLabel);
        $runtimeDetails = self::accessDetails($accessState, $localFullAccessOverride);
        $recentActivityItems = $activityLogs
            ->take(5)
            ->map(fn ($item) => [
                'label' => $item->created_at?->format('Y-m-d H:i') ?? 'Recent activity',
                'value' => $item->message ?? 'Runtime activity recorded',
                'context' => ucfirst(str_replace('_', ' ', (string) ($item->type ?? 'activity'))),
            ])
            ->values()
            ->all();
        $accessItems = [
            ['label' => 'Current automation access', 'value' => $productStatus, 'context' => $accessActionContext, 'icon' => 'fa-solid fa-wallet', 'tone' => $accessState === 'active_subscribed_product' ? 'emerald' : ($accessState === 'demo_access_product' ? 'amber' : 'rose')],
            ['label' => 'Product name', 'value' => $productLabel, 'context' => 'Prime Stocks is the current automation product family inside this page.', 'icon' => 'fa-solid fa-tag', 'tone' => 'amber'],
            ['label' => 'Product state / status', 'value' => $productStatus, 'context' => $subscriptionActive ? 'Base plan: '.$basePlanLabel : 'No Stripe-confirmed subscription is active yet.', 'icon' => 'fa-solid fa-signal', 'tone' => $accessState === 'active_subscribed_product' ? 'emerald' : ($accessState === 'demo_access_product' ? 'amber' : 'rose')],
            ['label' => 'Upgrade / subscribe / manage state', 'value' => $accessAction, 'context' => $accessActionContext, 'icon' => 'fa-solid fa-credit-card', 'tone' => 'blue'],
        ];
        $productItems = [
            ['label' => 'Asset class', 'value' => 'Stocks Only', 'context' => 'Prime Stocks remains stocks-only in this product phase.', 'icon' => 'fa-solid fa-chart-line', 'tone' => 'blue'],
            ['label' => 'Execution timeframe', 'value' => '1H', 'context' => '1H decides when.', 'icon' => 'fa-solid fa-clock', 'tone' => 'violet'],
            ['label' => 'Trend timeframe', 'value' => '1D', 'context' => '1D helps decide whether.', 'icon' => 'fa-solid fa-chart-column', 'tone' => 'amber'],
            ['label' => 'Pullback window', 'value' => '5', 'context' => 'Approved current Prime Stocks pullback default.', 'icon' => 'fa-solid fa-arrow-trend-down', 'tone' => 'rose'],
            ['label' => 'Runtime target', 'value' => 'Cloud Run Serverless Bot', 'context' => 'Cloud Run runs the Serverless Bot server-side.', 'icon' => 'fa-solid fa-server', 'tone' => 'sky'],
            ['label' => 'Browser stay-open requirement', 'value' => 'Not required', 'context' => 'Trading does not require the page to stay open.', 'icon' => 'fa-solid fa-window-maximize', 'tone' => 'emerald'],
        ];
        $signalItems = [
            ['label' => 'Last action candidate or demo signal state', 'value' => $lastSignalValue, 'context' => $accessState === 'no_active_product' ? 'A signal will appear here after the product becomes active.' : 'Demo/static signal state is shown until live runtime wiring is ready.', 'icon' => 'fa-solid fa-bolt', 'tone' => 'violet'],
            ['label' => 'Last signal time', 'value' => $lastSignalTime, 'context' => $latestSignal ? 'Most recent stored signal time from the workspace.' : 'Static placeholder time is used when live signal data is not ready.', 'icon' => 'fa-solid fa-calendar-check', 'tone' => 'sky'],
            ['label' => 'Runtime summary', 'value' => $runtimeState['last_runtime_summary'] ?? $runtimeHeadline, 'context' => $runtimeState['last_runtime_status'] ?? 'No runtime status recorded yet', 'icon' => 'fa-solid fa-wave-square', 'tone' => 'sky'],
            ['label' => 'Recent activity', 'value' => $recentActivityItems[0]['value'] ?? 'No recent activity', 'context' => $recentActivityItems[0]['context'] ?? 'Recent automation activity will appear here when available.', 'icon' => 'fa-solid fa-list-check', 'tone' => 'amber'],
        ];
        $controlZoneItems = [
            ['label' => 'Control zone', 'value' => 'Control / monitoring zone', 'context' => 'This Laravel page is the control / monitoring zone for the automation product.', 'icon' => 'fa-solid fa-sliders', 'tone' => 'blue'],
            ['label' => 'Serverless Bot', 'value' => 'Cloud Run runs the Serverless Bot server-side', 'context' => 'The browser does not own runtime continuity.', 'icon' => 'fa-solid fa-cloud', 'tone' => 'sky'],
            ['label' => 'AI control state', 'value' => $automationEnabled ? 'Enabled' : 'Disabled', 'context' => $automationEnabled ? 'Automation is currently enabled for this workspace.' : 'Automation is currently disabled for this workspace.', 'icon' => 'fa-solid fa-toggle-on', 'tone' => $automationEnabled ? 'emerald' : 'rose'],
            ['label' => 'Saved risk posture', 'value' => ucfirst((string) ($automationSetting?->risk_level ?? (($brokerReady && $strategyReady) ? 'balanced' : 'conservative'))), 'context' => 'This remains the saved operating posture for the current automation configuration.', 'icon' => 'fa-solid fa-shield-halved', 'tone' => 'amber'],
        ];
        $supportItems = [
            ['label' => 'Plan access', 'value' => $subscriptionActive ? $basePlanLabel : 'No active plan', 'context' => $subscriptionActive ? ($basePlanCode !== '' ? 'Current base plan code: '.$basePlanCode : 'Current subscription is active.') : 'Subscriptions will be built with Stripe later.', 'icon' => 'fa-solid fa-wallet', 'tone' => 'emerald'],
            ['label' => 'Broker readiness', 'value' => $brokerReady ? 'Ready' : 'Action needed', 'context' => $brokerReady ? 'The broker connection and market-data path are ready.' : (string) ($brokerGuard['summary'] ?? 'Check the broker connection and recent sync status.'), 'icon' => 'fa-solid fa-plug-circle-bolt', 'tone' => $brokerReady ? 'emerald' : 'amber'],
            ['label' => 'Strategy readiness', 'value' => $strategyReady ? 'Ready' : 'Action needed', 'context' => $strategyReady ? 'A strategy is connected to automation.' : 'Create or activate a strategy before starting automation.', 'icon' => 'fa-solid fa-compass-drafting', 'tone' => $strategyReady ? 'emerald' : 'amber'],
            ['label' => 'Subscription build stage', 'value' => 'Stripe later stage', 'context' => 'Billing and subscriptions will be built with Stripe later; this page uses current entitlement/demo state now.', 'icon' => 'fa-solid fa-credit-card', 'tone' => 'blue'],
        ];
        $productNotes = [
            ['label' => 'Product naming', 'value' => $accessState === 'active_subscribed_product' ? 'Prime Stocks Bot Trader' : 'Demo Access product', 'context' => 'The page is structured so later subscribed/live naming becomes Prime Stocks Bot Trader without a layout rewrite.', 'icon' => 'fa-solid fa-tag', 'tone' => 'amber'],
            ['label' => 'Runtime ownership', 'value' => 'Cloud Run server-side', 'context' => 'The product runs as a Serverless Bot on Cloud Run, separate from the Laravel app shell.', 'icon' => 'fa-solid fa-server', 'tone' => 'sky'],
            ['label' => 'Browser role', 'value' => 'Control / monitoring only', 'context' => 'Users do not need to keep this page open for trading to continue.', 'icon' => 'fa-solid fa-window-maximize', 'tone' => 'emerald'],
        ];
        if ($localFullAccessOverride) {
            $productNotes[] = [
                'label' => 'Local full access',
                'value' => 'Enabled for local auth users',
                'context' => 'customer.local@gusgraph.test and admin.local@gusgraph.test currently render as full-access local product users across all product/plan states until Stripe-backed subscriptions are implemented.',
                'icon' => 'fa-solid fa-user-group',
                'tone' => 'emerald',
            ];
        }

        return [
            'page' => [
                'title' => 'Automation',
                'intro' => 'Review actual automation product access for this workspace, then manage the active control / monitoring zone from the same page.',
                'subtitle' => $account
                    ? 'Automation now renders around real access state: no active product, Demo Access product, or active subscribed product.'
                    : 'No workspace is available yet, so automation will stay focused on setup until account details are ready.',
                'sections' => [
                    ['heading' => 'Current automation access', 'description' => 'Show whether this workspace currently has no active product, Demo Access product, or active subscribed product access.'],
                    ['heading' => 'Prime Stocks product state', 'description' => 'Render Prime Stocks as Demo Access product now or Prime Stocks Bot Trader when subscribed/live access is active.'],
                    ['heading' => 'Control / monitoring zone', 'description' => 'Keep configuration and runtime oversight in this Laravel page while Cloud Run stays the Serverless Bot runtime target.'],
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
            'accessItems' => $accessItems,
            'productItems' => $productItems,
            'signalItems' => $signalItems,
            'controlZoneItems' => $controlZoneItems,
            'supportItems' => $supportItems,
            'productNotes' => $productNotes,
            'relatedLinks' => [
                ['route' => 'customer.billing.index', 'label' => 'Plans & Billing', 'description' => $accessState === 'no_active_product' ? 'Review billing posture before subscribed automation access is introduced later.' : 'Review the billing surface that will own subscription management later.'],
                ['route' => 'customer.broker.index', 'label' => 'Broker', 'description' => 'Confirm broker connectivity and masked credential posture for the current product state.'],
                ['route' => 'customer.strategy.index', 'label' => 'Strategy', 'description' => 'Review strategy mapping before deeper runtime wiring is added.'],
            ],
            'hasAutomationData' => $hasAutomationData,
        ];
    }

    protected static function accessState(bool $subscriptionActive, bool $automationEntitled, bool $localFullAccessOverride, bool $demoAccessProduct): string
    {
        if ($localFullAccessOverride || ($subscriptionActive && $automationEntitled)) {
            return 'active_subscribed_product';
        }

        if ($demoAccessProduct) {
            return 'demo_access_product';
        }

        return 'no_active_product';
    }

    protected static function accessHeadline(string $accessState, string $productLabel): string
    {
        return match ($accessState) {
            'active_subscribed_product' => $productLabel.' access active',
            'demo_access_product' => 'Demo Access product visible in Automation',
            default => 'No active automation product',
        };
    }

    protected static function accessDetails(string $accessState, bool $localFullAccessOverride): string
    {
        return match ($accessState) {
            'active_subscribed_product' => $localFullAccessOverride
                ? 'Local full-access users currently see Prime Stocks Bot Trader as active here while Stripe-backed subscriptions are still a later-stage build. Cloud Run remains the Serverless Bot runtime, and trading does not require this page to stay open.'
                : 'This workspace currently renders an active subscribed automation product here. Cloud Run remains the Serverless Bot runtime, and trading does not require this page to stay open.',
            'demo_access_product' => 'This workspace currently renders Prime Stocks as a Demo Access product inside Automation. Cloud Run remains the Serverless Bot runtime target, and this page stays a control / monitoring zone only.',
            default => 'No active automation product is attached to this workspace yet. This page still acts as the control / monitoring zone, and Cloud Run remains the intended Serverless Bot runtime once subscribed access is available.',
        };
    }

    protected static function hasLocalFullAccessOverride(?string $email): bool
    {
        return in_array(strtolower(trim((string) $email)), [
            'customer.local@gusgraph.test',
            'admin.local@gusgraph.test',
        ], true);
    }

    protected static function isDemoAccessProduct(array $entitlements): bool
    {
        return (bool) data_get($entitlements, 'capabilities.can_use_speed_execute', false)
            || str_contains(strtolower((string) data_get($entitlements, 'blocked_summary', '')), 'demo plan')
            || str_contains(strtolower((string) data_get($entitlements, 'mismatch_summary', '')), 'demo access');
    }
}
