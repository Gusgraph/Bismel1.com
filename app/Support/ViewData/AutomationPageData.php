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
        $primeStocksRuntime = is_array($state['prime_stocks_runtime'] ?? null) ? $state['prime_stocks_runtime'] : [];
        $primeStocksRuntimeStatus = (string) ($primeStocksRuntime['status'] ?? 'not_found');
        $primeStocksRuntimeDetails = (string) ($primeStocksRuntime['details'] ?? 'Prime Stocks runtime documents are not available yet.');
        $primeStocksRuntimeDocuments = is_array($primeStocksRuntime['documents'] ?? null) ? $primeStocksRuntime['documents'] : [];
        $primeStocksStateDocument = is_array($primeStocksRuntimeDocuments['state'] ?? null) ? $primeStocksRuntimeDocuments['state'] : [];
        $primeStocksSnapshotDocument = is_array($primeStocksRuntimeDocuments['snapshot'] ?? null) ? $primeStocksRuntimeDocuments['snapshot'] : [];
        $primeStocksSignalDocument = is_array($primeStocksRuntimeDocuments['signal'] ?? null) ? $primeStocksRuntimeDocuments['signal'] : [];
        $primeStocksExecutionDocument = is_array($primeStocksRuntimeDocuments['execution'] ?? null) ? $primeStocksRuntimeDocuments['execution'] : [];
        $primeStocksActionDocument = is_array($primeStocksRuntimeDocuments['action'] ?? null) ? $primeStocksRuntimeDocuments['action'] : [];
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
        $accessState = self::accessState($subscriptionActive, $automationEntitled, $localFullAccessOverride);
        $productLabel = $accessState === 'active_plan_access' ? 'Prime Stocks Bot Trader' : 'No active product';
        $productStatus = match ($accessState) {
            'active_plan_access' => 'Active plan access',
            default => 'No active product',
        };
        $runtimeStatusValue = self::runtimeValue(
            $primeStocksStateDocument['latest_status'] ?? null,
            $primeStocksSnapshotDocument['status'] ?? null,
            $accessState === 'active_plan_access' ? 'No runtime record yet' : 'No runtime product',
        );
        $latestCandidateAction = self::runtimeValue(
            $primeStocksStateDocument['latest_candidate_action'] ?? null,
            $primeStocksSignalDocument['candidate_action'] ?? null,
            $primeStocksSnapshotDocument['candidate_action'] ?? null,
            'No runtime record yet',
        );
        $latestExecutionDecision = self::runtimeValue(
            $primeStocksStateDocument['latest_execution_decision'] ?? null,
            $primeStocksExecutionDocument['execution_decision'] ?? null,
            $primeStocksActionDocument['execution_decision'] ?? null,
            'No runtime record yet',
        );
        $lastProcessedBarTime = self::formatRuntimeTimestamp(
            self::runtimeValue($primeStocksStateDocument['last_processed_bar_time'] ?? null, null)
        );
        $lastSignalTime = self::formatRuntimeTimestamp(
            self::runtimeValue(
                $primeStocksSignalDocument['latest_signal_time'] ?? null,
                $primeStocksSnapshotDocument['latest_signal_time'] ?? null,
                null
            )
        );
        $triggerType = self::runtimeValue(
            $primeStocksExecutionDocument['trigger_type'] ?? null,
            $primeStocksActionDocument['trigger_type'] ?? null,
            $primeStocksStateDocument['trigger_type'] ?? null,
            'Not recorded',
        );
        $triggerSource = self::runtimeValue(
            $primeStocksExecutionDocument['trigger_source'] ?? null,
            $primeStocksActionDocument['trigger_source'] ?? null,
            $primeStocksStateDocument['trigger_source'] ?? null,
            'Not recorded',
        );
        $lastActionOrderResult = self::buildLastActionOrderResult(
            $primeStocksExecutionDocument,
            $primeStocksActionDocument,
            $primeStocksRuntimeStatus
        );
        $runtimeAvailabilityValue = match ($primeStocksRuntimeStatus) {
            'ok' => 'Live runtime linked',
            'not_found' => 'No runtime records yet',
            'disabled' => 'Firestore runtime read disabled',
            'missing_client' => 'Firestore client missing',
            'misconfigured' => 'Firestore config incomplete',
            default => 'Runtime read unavailable',
        };
        $accessAction = match ($accessState) {
            'active_plan_access' => 'Manage in this control / monitoring zone',
            default => 'Upgrade / subscribe later when Stripe-backed subscriptions are introduced',
        };
        $accessActionContext = match ($accessState) {
            'active_plan_access' => $localFullAccessOverride
                ? 'Local full-access test state is active for customer.local@gusgraph.test and admin.local@gusgraph.test until Stripe subscription wiring is completed.'
                : 'The current paid plan posture allows this product inside Automation.',
            default => 'No active automation product is attached to this workspace yet.',
        };
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
            ['label' => 'Current automation access', 'value' => $productStatus, 'context' => $accessActionContext, 'icon' => 'fa-solid fa-wallet', 'tone' => $accessState === 'active_plan_access' ? 'emerald' : 'rose'],
            ['label' => 'Product name', 'value' => $productLabel, 'context' => 'Prime Stocks is the current automation product family inside this page.', 'icon' => 'fa-solid fa-tag', 'tone' => 'amber'],
            ['label' => 'Product state / status', 'value' => $productStatus, 'context' => $localFullAccessOverride ? 'Local active-plan test state is enabled until Stripe subscription wiring is completed.' : ($subscriptionActive ? 'Base plan: '.$basePlanLabel : 'No Stripe-confirmed subscription is active yet.'), 'icon' => 'fa-solid fa-signal', 'tone' => $accessState === 'active_plan_access' ? 'emerald' : 'rose'],
            ['label' => 'Upgrade / subscribe / manage state', 'value' => $accessAction, 'context' => $accessActionContext, 'icon' => 'fa-solid fa-credit-card', 'tone' => 'blue'],
        ];
        $productItems = [
            ['label' => 'Asset class', 'value' => 'Stocks Only', 'context' => 'Prime Stocks remains stocks-only in this product phase.', 'icon' => 'fa-solid fa-chart-line', 'tone' => 'blue'],
            ['label' => 'Execution timeframe', 'value' => '1H', 'context' => '1H decides when.', 'icon' => 'fa-solid fa-clock', 'tone' => 'violet'],
            ['label' => 'Trend timeframe', 'value' => '1D', 'context' => '1D helps decide whether.', 'icon' => 'fa-solid fa-chart-column', 'tone' => 'amber'],
            ['label' => 'Pullback window', 'value' => '5', 'context' => 'Approved current Prime Stocks pullback default.', 'icon' => 'fa-solid fa-arrow-trend-down', 'tone' => 'rose'],
            ['label' => 'Product runtime status', 'value' => $runtimeStatusValue, 'context' => $primeStocksRuntimeDetails, 'icon' => 'fa-solid fa-wave-square', 'tone' => $primeStocksRuntimeStatus === 'ok' ? 'emerald' : 'amber'],
            ['label' => 'Runtime target', 'value' => 'Cloud Run Serverless Bot', 'context' => 'Cloud Run runs the Serverless Bot server-side.', 'icon' => 'fa-solid fa-server', 'tone' => 'sky'],
            ['label' => 'Browser stay-open requirement', 'value' => 'Not required', 'context' => 'Trading does not require the page to stay open.', 'icon' => 'fa-solid fa-window-maximize', 'tone' => 'emerald'],
        ];
        $signalItems = [
            ['label' => 'Latest candidate action', 'value' => $latestCandidateAction, 'context' => $primeStocksRuntimeStatus === 'ok' ? 'Read-only candidate action from the Firestore-backed Prime Stocks runtime signal path.' : $primeStocksRuntimeDetails, 'icon' => 'fa-solid fa-bolt', 'tone' => 'violet'],
            ['label' => 'Last signal time', 'value' => $lastSignalTime, 'context' => $primeStocksRuntimeStatus === 'ok' ? 'Most recent runtime signal time from Firestore.' : 'No runtime signal record has been written yet.', 'icon' => 'fa-solid fa-calendar-check', 'tone' => 'sky'],
            ['label' => 'Last processed bar time', 'value' => $lastProcessedBarTime, 'context' => $primeStocksRuntimeStatus === 'ok' ? 'Latest closed execution bar time recorded by the server-side runtime.' : 'This will appear after the runtime processes its first closed bar.', 'icon' => 'fa-solid fa-clock-rotate-left', 'tone' => 'amber'],
            ['label' => 'Runtime feed', 'value' => $runtimeAvailabilityValue, 'context' => $primeStocksRuntimeDetails, 'icon' => 'fa-solid fa-database', 'tone' => $primeStocksRuntimeStatus === 'ok' ? 'emerald' : 'amber'],
        ];
        $controlZoneItems = [
            ['label' => 'Control zone', 'value' => 'Control / monitoring zone', 'context' => 'This Laravel page is the control / monitoring zone for the automation product.', 'icon' => 'fa-solid fa-sliders', 'tone' => 'blue'],
            ['label' => 'Serverless Bot', 'value' => 'Cloud Run runs the Serverless Bot server-side', 'context' => 'The browser does not own runtime continuity.', 'icon' => 'fa-solid fa-cloud', 'tone' => 'sky'],
            ['label' => 'Latest execution decision', 'value' => $latestExecutionDecision, 'context' => $primeStocksRuntimeStatus === 'ok' ? 'Latest execution decision from the Firestore-backed runtime execution path.' : $primeStocksRuntimeDetails, 'icon' => 'fa-solid fa-toggle-on', 'tone' => $primeStocksRuntimeStatus === 'ok' ? 'emerald' : 'amber'],
            ['label' => 'Last action / order result', 'value' => $lastActionOrderResult['value'], 'context' => $lastActionOrderResult['context'], 'icon' => 'fa-solid fa-shield-halved', 'tone' => $lastActionOrderResult['tone']],
        ];
        $supportItems = [
            ['label' => 'Plan access', 'value' => $accessState === 'active_plan_access' ? 'Active plan access' : 'No active plan', 'context' => $localFullAccessOverride ? 'Local full-access test state stands in for active plan access until Stripe subscription wiring is completed.' : ($subscriptionActive ? ($basePlanCode !== '' ? 'Current base plan code: '.$basePlanCode : 'Current subscription is active.') : 'Subscriptions will be built with Stripe later.'), 'icon' => 'fa-solid fa-wallet', 'tone' => $accessState === 'active_plan_access' ? 'emerald' : 'amber'],
            ['label' => 'Broker readiness', 'value' => $brokerReady ? 'Ready' : 'Action needed', 'context' => $brokerReady ? 'The broker connection and market-data path are ready.' : (string) ($brokerGuard['summary'] ?? 'Check the broker connection and recent sync status.'), 'icon' => 'fa-solid fa-plug-circle-bolt', 'tone' => $brokerReady ? 'emerald' : 'amber'],
            ['label' => 'Trigger type / source', 'value' => $triggerType.' / '.$triggerSource, 'context' => $primeStocksRuntimeStatus === 'ok' ? 'Latest runtime trigger metadata from Firestore.' : 'Trigger metadata will appear after runtime records exist.', 'icon' => 'fa-solid fa-compass-drafting', 'tone' => $primeStocksRuntimeStatus === 'ok' ? 'emerald' : 'amber'],
            ['label' => 'Runtime fallback', 'value' => $runtimeAvailabilityValue, 'context' => $primeStocksRuntimeStatus === 'ok' ? 'Live read-only runtime values are shown where Firestore records exist.' : 'Automation falls back gracefully when Firestore runtime records are missing or unreadable.', 'icon' => 'fa-solid fa-credit-card', 'tone' => 'blue'],
        ];
        $productNotes = [
            ['label' => 'Product naming', 'value' => $accessState === 'active_plan_access' ? 'Prime Stocks Bot Trader' : 'No active product', 'context' => 'Prime Stocks Bot Trader is the active-plan product name for local testing on this page.', 'icon' => 'fa-solid fa-tag', 'tone' => 'amber'],
            ['label' => 'Runtime ownership', 'value' => 'Cloud Run server-side', 'context' => 'The product runs as a Serverless Bot on Cloud Run, separate from the Laravel app shell.', 'icon' => 'fa-solid fa-server', 'tone' => 'sky'],
            ['label' => 'Browser role', 'value' => 'Control / monitoring only', 'context' => 'Users do not need to keep this page open for trading to continue.', 'icon' => 'fa-solid fa-window-maximize', 'tone' => 'emerald'],
            ['label' => 'Runtime records', 'value' => $runtimeAvailabilityValue, 'context' => $primeStocksRuntimeDetails, 'icon' => 'fa-solid fa-database', 'tone' => $primeStocksRuntimeStatus === 'ok' ? 'emerald' : 'amber'],
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
                    ? ('Automation now renders around an honest active-plan test state for Prime Stocks Bot Trader or a clean no-active-product fallback.'
                        .($primeStocksRuntimeStatus === 'ok'
                            ? ' Firestore-backed Prime Stocks runtime values now appear here in read-only form where records exist.'
                            : ' Firestore-backed runtime values will appear here once the server-side runtime writes its first records.'))
                    : 'No workspace is available yet, so automation will stay focused on setup until account details are ready.',
                'sections' => [
                    ['heading' => 'Current automation access', 'description' => 'Show whether this workspace currently has active plan access or no active product.'],
                    ['heading' => 'Prime Stocks product state', 'description' => 'Render Prime Stocks Bot Trader as the active local plan product surface inside Automation.'],
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
                ['route' => 'customer.billing.index', 'label' => 'Plans & Billing', 'description' => $accessState === 'no_active_product' ? 'Review billing posture before active plan access is introduced later.' : 'Review the billing surface that will own Stripe-backed subscription management later.'],
                ['route' => 'customer.broker.index', 'label' => 'Broker', 'description' => 'Confirm broker connectivity and masked credential posture for the current product state.'],
                ['route' => 'customer.strategy.index', 'label' => 'Strategy', 'description' => 'Review strategy mapping before deeper runtime wiring is added.'],
            ],
            'hasAutomationData' => $hasAutomationData,
        ];
    }

    protected static function accessState(bool $subscriptionActive, bool $automationEntitled, bool $localFullAccessOverride): string
    {
        if ($localFullAccessOverride || ($subscriptionActive && $automationEntitled)) {
            return 'active_plan_access';
        }

        return 'no_active_product';
    }

    protected static function accessHeadline(string $accessState, string $productLabel): string
    {
        return match ($accessState) {
            'active_plan_access' => $productLabel.' active for local plan testing',
            default => 'No active automation product',
        };
    }

    protected static function accessDetails(string $accessState, bool $localFullAccessOverride): string
    {
        return match ($accessState) {
            'active_plan_access' => $localFullAccessOverride
                ? 'Prime Stocks Bot Trader is active here in a local full-access test state while Stripe subscription wiring is still a later-stage build. Cloud Run runs the bot server-side, and trading does not require this page to stay open.'
                : 'Prime Stocks Bot Trader is active here for the current paid plan posture. Cloud Run runs the bot server-side, and trading does not require this page to stay open.',
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

    protected static function runtimeValue(mixed ...$values): string
    {
        $fallback = 'Unknown';

        if ($values !== []) {
            $fallback = (string) array_pop($values);
        }

        foreach ($values as $value) {
            if (is_string($value) && trim($value) !== '') {
                return trim($value);
            }

            if (is_numeric($value)) {
                return (string) $value;
            }

            if (is_bool($value)) {
                return $value ? 'Yes' : 'No';
            }
        }

        return $fallback;
    }

    protected static function formatRuntimeTimestamp(?string $value): string
    {
        if (! is_string($value) || trim($value) === '') {
            return 'No runtime record yet';
        }

        $timestamp = strtotime($value);

        if ($timestamp === false) {
            return $value;
        }

        return gmdate('Y-m-d H:i', $timestamp).' UTC';
    }

    protected static function buildLastActionOrderResult(array $executionDocument, array $actionDocument, string $runtimeStatus): array
    {
        $orderStatus = self::runtimeValue(
            $executionDocument['order_status'] ?? null,
            $actionDocument['execution']['order_status'] ?? null,
            'No runtime record yet',
        );
        $skippedReason = self::runtimeValue(
            $executionDocument['skipped_reason'] ?? null,
            $actionDocument['execution']['skipped_reason'] ?? null,
            ''
        );
        $orderId = self::runtimeValue(
            $executionDocument['order_id'] ?? null,
            $actionDocument['execution']['order_id'] ?? null,
            ''
        );

        if ($runtimeStatus !== 'ok') {
            return [
                'value' => 'No runtime record yet',
                'context' => 'No execution or action document has been read from Firestore yet.',
                'tone' => 'amber',
            ];
        }

        $context = collect([
            $skippedReason !== '' ? 'Reason: '.$skippedReason : null,
            $orderId !== '' ? 'Order ID: '.$orderId : null,
        ])->filter()->implode(' · ');

        return [
            'value' => $orderStatus,
            'context' => $context !== '' ? $context : 'Latest execution result from the Firestore-backed Prime Stocks runtime.',
            'tone' => in_array(strtolower($orderStatus), ['accepted', 'submitted', 'filled', 'new', 'partially_filled'], true) ? 'emerald' : 'amber',
        ];
    }

}
