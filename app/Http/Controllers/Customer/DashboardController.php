<?php
// اعوز بالله من الشياطين و ان يحضرون بسم الله الرحمن الرحيم الله لا إله إلا هو الحي القيوم
// Bismillahi ar-Rahmani ar-Rahim Audhu billahi min ash-shayatin wa an yahdurun Bismillah ar-Rahman ar-Rahim Allah la ilaha illa huwa al-hayy al-qayyum. Tamsa Allahu ala ayunihim
// version: x
// ======================================================
// - App Name: gusgraph-trading
// - Gusgraph LLC -
// - Author: Gus Kazem
// - https://Gusgraph.com
// - File Path: app/Http/Controllers/Customer/DashboardController.php
// =====================================================

namespace App\Http\Controllers\Customer;

use App\Domain\Account\Enums\AccountStatus;
use App\Domain\Billing\Enums\SubscriptionStatus;
use App\Domain\Broker\Enums\BrokerConnectionStatus;
use App\Domain\Dashboard\Services\DashboardService;
use App\Domain\License\Enums\ApiLicenseStatus;
use App\Http\Controllers\Controller;
use App\Http\Controllers\Customer\Concerns\HandlesFirestoreSummary;
use App\Support\Dashboard\CustomerDashboardSections;
use App\Support\Dashboard\CustomerStats;
use App\Support\Customer\CurrentCustomerAccountResolver;
use App\Support\Display\SafeDisplay;
use App\Support\Firestore\FirestoreBridge;
use App\Support\Labels\CustomerSectionLabels;
use App\Support\Navigation\CustomerNavigation;
use App\Support\ViewData\CustomerAlertData;
use App\Support\ViewData\CustomerPageData;

class DashboardController extends Controller
{
    use HandlesFirestoreSummary;

    public function index(
        DashboardService $dashboardService,
        CurrentCustomerAccountResolver $currentCustomerAccountResolver,
        FirestoreBridge $firestoreBridge
    )
    {
        $labels = CustomerSectionLabels::make();
        $account = $currentCustomerAccountResolver->resolveForPreset(request()->user(), 'dashboard');
        $dashboard = $dashboardService->getCustomerDashboardData($account);
        $alertData = CustomerAlertData::make();
        $firestoreReadSummary = $this->safeFirestoreReadSummary($firestoreBridge, request()->user(), $account);
        $brokerConnections = $account?->brokerConnections ?? collect();
        $brokerCredentials = $brokerConnections->flatMap->brokerCredentials;
        $licenses = $account?->apiLicenses ?? collect();
        $apiKeys = $licenses->flatMap->apiKeys;
        $subscription = $account?->subscriptions
            ?->sortByDesc(fn ($item) => $item->updated_at?->getTimestamp() ?? $item->created_at?->getTimestamp() ?? 0)
            ->first();
        $positions = $account?->alpacaPositions
            ?->sortByDesc(fn ($item) => $item->last_managed_at?->getTimestamp() ?? $item->updated_at?->getTimestamp() ?? 0)
            ->values() ?? collect();
        $orders = $account?->alpacaOrders
            ?->sortByDesc(fn ($item) => $item->submitted_at?->getTimestamp() ?? $item->created_at?->getTimestamp() ?? 0)
            ->values() ?? collect();
        $signals = $account?->signals
            ?->sortByDesc(fn ($item) => $item->generated_at?->getTimestamp() ?? $item->created_at?->getTimestamp() ?? 0)
            ->values() ?? collect();
        $botRuns = $account?->botRuns
            ?->sortByDesc(fn ($item) => $item->started_at?->getTimestamp() ?? $item->created_at?->getTimestamp() ?? 0)
            ->values() ?? collect();
        $activityLogs = $account?->activityLogs
            ?->sortByDesc(fn ($item) => $item->created_at?->getTimestamp() ?? 0)
            ->values() ?? collect();
        $alpacaAccounts = $account?->alpacaAccounts
            ?->sortByDesc(fn ($item) => $item->last_synced_at?->getTimestamp() ?? $item->created_at?->getTimestamp() ?? 0)
            ->values() ?? collect();
        $alpacaAccount = $alpacaAccounts->first(fn ($item) => (bool) ($item->is_active ?? false)) ?? $alpacaAccounts->first();
        $automationSetting = $account?->automationSettings
            ?->sortByDesc(fn ($item) => $item->updated_at?->getTimestamp() ?? $item->created_at?->getTimestamp() ?? 0)
            ->first();
        $runtimeState = is_array($automationSetting?->settings['bismel1_runtime'] ?? null)
            ? $automationSetting->settings['bismel1_runtime']
            : [];
        $marketClock = $this->marketClock();
        $readinessScore = collect([
            $account ? 1 : 0,
            $subscription ? 1 : 0,
            $alpacaAccount ? 1 : 0,
            $licenses->isNotEmpty() ? 1 : 0,
            $apiKeys->isNotEmpty() ? 1 : 0,
        ])->sum();
        $surface = [
            'stats' => CustomerStats::items([
                'alpacaAccount' => $alpacaAccount,
                'automationSetting' => $automationSetting,
                'runtimeState' => $runtimeState,
            ]),
            'sections' => CustomerDashboardSections::items([
                'positions' => $positions,
                'orders' => $orders,
                'activityLogs' => $activityLogs,
                'signals' => $signals,
                'actions' => $this->actionNeededItems($subscription, $alpacaAccount, $automationSetting, $runtimeState, $signals),
            ]),
            'readinessPanel' => [
                'title' => 'Trading Readiness',
                'message' => 'The key trading checks stay on the side rail so you can confirm broker, plan, bot, market window, and sync state at a glance. '
                    .($account ? 'Current account '.$account->name.' / '.($account->slug ?? 'no-slug').'.' : ''),
                'scoreLabel' => 'Readiness Score',
                'scoreValue' => $readinessScore.'/5',
                'items' => [
                    [
                        'label' => 'Broker connected?',
                        'value' => $alpacaAccount ? 'Yes' : 'No',
                        'context' => $alpacaAccount
                            ? 'Alpaca '.$this->upperOrFallback($alpacaAccount->environment, 'local').' account is linked for '.($account?->name ?? 'this customer account').' / '.($account?->slug ?? 'no-slug').'. '.$brokerConnections->count().' connections / '.$brokerCredentials->count().' credentials'
                            : 'Connect Alpaca before you rely on the trading desk.',
                        'route' => 'customer.broker.index',
                    ],
                    [
                        'label' => 'Plan active?',
                        'value' => $subscription ? SafeDisplay::status($subscription->status) : 'Missing',
                        'context' => $subscription
                            ? (($subscription->subscriptionPlan?->name ?? 'Plan recorded locally').' for '.($account?->name ?? 'this account'))
                            : 'Activate billing before runtime can arm.',
                        'route' => 'customer.billing.index',
                    ],
                    [
                        'label' => 'Bot ready?',
                        'value' => $this->botReadyLabel($alpacaAccount, $subscription, $automationSetting, $runtimeState),
                        'context' => $this->botReadyContext($alpacaAccount, $subscription, $automationSetting, $runtimeState, $licenses, $apiKeys),
                        'route' => 'customer.automation.index',
                    ],
                    [
                        'label' => 'Market open?',
                        'value' => $marketClock['label'],
                        'context' => $marketClock['context'],
                        'route' => 'customer.activity.index',
                    ],
                    [
                        'label' => 'Last sync time',
                        'value' => SafeDisplay::dateTime($alpacaAccount?->last_synced_at, 'Not synced yet'),
                        'context' => $alpacaAccount
                            ? 'Broker sync '.SafeDisplay::status($alpacaAccount->sync_status ?? 'pending')
                            : 'Sync starts after the broker account is linked.',
                        'route' => 'customer.broker.index',
                    ],
                ],
            ],
        ];

        return view('customer.dashboard', [
            'dashboard' => $dashboard,
            'navItems' => CustomerNavigation::items(),
            'page' => CustomerPageData::make(
                'Customer Dashboard',
                'The main trading control surface for broker visibility, runtime state, action queues, and recent local market activity.',
                $surface['sections'] ?: ($dashboard['sections'] ?: $labels['dashboard']),
                $surface['stats']
            ),
            'statusGroups' => [
                'Account' => AccountStatus::labels(),
                'Billing' => SubscriptionStatus::labels(),
                'Broker' => BrokerConnectionStatus::labels(),
                'License' => ApiLicenseStatus::labels(),
            ],
            'alerts' => $alertData['alerts'],
            'notices' => $alertData['notices'],
            'hasDashboardData' => $dashboard['hasDashboardData'] ?? false,
            'firestoreReadSummary' => $firestoreReadSummary,
            'dashboardSurface' => $surface,
        ]);
    }

    protected function actionNeededItems($subscription, $alpacaAccount, $automationSetting, array $runtimeState, $signals): array
    {
        $items = [];
        $runtimeStatus = strtolower(trim((string) ($runtimeState['last_runtime_status'] ?? '')));

        if (! $subscription) {
            $items[] = [
                'title' => 'Activate your plan',
                'status' => 'Billing required',
                'summary' => 'Billing is still missing, so runtime should stay parked until the trading plan is active.',
                'route' => 'customer.billing.index',
                'routeLabel' => 'Open billing',
            ];
        }

        if (! $alpacaAccount) {
            $items[] = [
                'title' => 'Connect Alpaca',
                'status' => 'Broker required',
                'summary' => 'Broker sync cannot start until an Alpaca account is connected for this customer account.',
                'route' => 'customer.broker.index',
                'routeLabel' => 'Open broker',
            ];
        }

        if (! $automationSetting || ! (bool) ($automationSetting->ai_enabled ?? false)) {
            $items[] = [
                'title' => 'Review automation state',
                'status' => 'Bot parked',
                'summary' => 'Automation is not armed yet. Review runtime controls before the next session.',
                'route' => 'customer.automation.index',
                'routeLabel' => 'Open automation',
            ];
        } elseif (in_array($runtimeStatus, ['blocked', 'review'], true)) {
            $items[] = [
                'title' => 'Clear runtime blocker',
                'status' => ucfirst($runtimeStatus),
                'summary' => SafeDisplay::sanitizedText((string) ($runtimeState['last_runtime_summary'] ?? 'Runtime review is still required.')),
                'route' => 'customer.automation.index',
                'routeLabel' => 'Review runtime',
            ];
        }

        if ($signals->isNotEmpty()) {
            $latestSignal = $signals->first();
            $signalStatus = strtolower(trim((string) ($latestSignal->status ?? '')));

            if (in_array($signalStatus, ['blocked', 'review', 'rejected', 'expired'], true)) {
                $items[] = [
                    'title' => 'Check latest signal',
                    'status' => SafeDisplay::status($latestSignal->status ?? 'review'),
                    'summary' => strtoupper((string) ($latestSignal->symbol ?? 'Signal')).' is the latest local signal and may need review before the next run.',
                    'route' => 'customer.activity.index',
                    'routeLabel' => 'Open activity',
                ];
            }
        }

        if ($items === []) {
            $items[] = [
                'title' => 'No immediate action',
                'status' => 'Desk clear',
                'summary' => 'Broker, billing, and runtime checks do not show an immediate local blocker right now.',
                'route' => 'customer.positions.index',
                'routeLabel' => 'Open positions',
            ];
        }

        return array_slice($items, 0, 5);
    }

    protected function botReadyLabel($alpacaAccount, $subscription, $automationSetting, array $runtimeState): string
    {
        if (! $alpacaAccount || ! $subscription) {
            return 'No';
        }

        if (! $automationSetting || ! (bool) ($automationSetting->ai_enabled ?? false)) {
            return 'Parked';
        }

        $runtimeStatus = strtolower(trim((string) ($runtimeState['last_runtime_status'] ?? '')));

        return match ($runtimeStatus) {
            'active', 'armed' => 'Yes',
            'blocked', 'review' => 'Needs review',
            default => 'Standby',
        };
    }

    protected function botReadyContext($alpacaAccount, $subscription, $automationSetting, array $runtimeState, $licenses, $apiKeys): string
    {
        if (! $alpacaAccount) {
            return 'Broker link is still missing.';
        }

        if (! $subscription) {
            return 'Billing must be active before runtime can arm.';
        }

        if ($apiKeys->isEmpty()) {
            return 'License not ready. No saved API key';
        }

        if (! $automationSetting) {
            return trim($licenses->count().' licenses / '.$apiKeys->count().' keys. '
                .$apiKeys->first()->maskedTokenSummary().' Automation settings have not been saved yet.');
        }

        return trim($licenses->count().' licenses / '.$apiKeys->count().' keys. '
            .$apiKeys->first()->maskedTokenSummary().' '
            .SafeDisplay::sanitizedText((string) ($runtimeState['last_runtime_summary'] ?? 'Runtime is waiting on the next local control update.')));
    }

    protected function marketClock(): array
    {
        $marketNow = now()->setTimezone('America/New_York');
        $isWeekday = $marketNow->dayOfWeek >= 1 && $marketNow->dayOfWeek <= 5;
        $minutes = ((int) $marketNow->format('H') * 60) + (int) $marketNow->format('i');
        $isOpen = $isWeekday && $minutes >= 570 && $minutes < 960;

        return [
            'label' => $isOpen ? 'Open' : 'Closed',
            'context' => 'App clock placeholder based on the New York session window. '.$marketNow->format('D g:i A').' ET',
        ];
    }

    protected function upperOrFallback(?string $value, string $fallback): string
    {
        $trimmed = trim((string) $value);

        return $trimmed !== '' ? strtoupper($trimmed) : $fallback;
    }
}
