<?php
// اعوز بالله من الشياطين و ان يحضرون بسم الله الرحمن الرحيم الله لا إله إلا هو الحي القيوم
// Bismillahi ar-Rahmani ar-Rahim Audhu billahi min ash-shayatin wa an yahdurun Bismillah ar-Rahman ar-Rahim Allah la ilaha illa huwa al-hayy al-qayyum. Tamsa Allahu ala ayunihim
// version: x
// ======================================================
// - App Name: gusgraph-trading
// - Gusgraph LLC -
// - Author: Gus Kazem
// - https://Gusgraph.com
// - File Path: app/Http/Controllers/Customer/TradingPagesController.php
// ======================================================

namespace App\Http\Controllers\Customer;

use App\Http\Controllers\Controller;
use App\Support\Billing\Bismel1EntitlementService;
use App\Support\Customer\CurrentCustomerAccountResolver;
use App\Support\Navigation\CustomerNavigation;
use App\Support\ViewData\Bismel1CustomerTradingPageData;

class TradingPagesController extends Controller
{
    public function positions(
        CurrentCustomerAccountResolver $currentCustomerAccountResolver,
        Bismel1EntitlementService $bismel1EntitlementService,
    )
    {
        return $this->renderTradingPage('positions', $currentCustomerAccountResolver, $bismel1EntitlementService);
    }

    public function orders(
        CurrentCustomerAccountResolver $currentCustomerAccountResolver,
        Bismel1EntitlementService $bismel1EntitlementService,
    )
    {
        return $this->renderTradingPage('orders', $currentCustomerAccountResolver, $bismel1EntitlementService);
    }

    public function activity(
        CurrentCustomerAccountResolver $currentCustomerAccountResolver,
        Bismel1EntitlementService $bismel1EntitlementService,
    )
    {
        return $this->renderTradingPage('activity', $currentCustomerAccountResolver, $bismel1EntitlementService);
    }

    protected function renderTradingPage(
        string $pageType,
        CurrentCustomerAccountResolver $currentCustomerAccountResolver,
        Bismel1EntitlementService $bismel1EntitlementService,
    ) {
        $account = $currentCustomerAccountResolver->resolveForPreset(request()->user(), 'trading');
        $positions = $account?->alpacaPositions
            ?->sortByDesc(fn ($item) => $item->last_managed_at?->getTimestamp() ?? $item->updated_at?->getTimestamp() ?? 0)
            ->values() ?? collect();
        $orders = $account?->alpacaOrders
            ?->sortByDesc(fn ($item) => $item->submitted_at?->getTimestamp() ?? $item->created_at?->getTimestamp() ?? 0)
            ->values() ?? collect();
        $activityLogs = $account?->activityLogs
            ?->sortByDesc(fn ($item) => $item->created_at?->getTimestamp() ?? 0)
            ->values() ?? collect();
        $botRuns = $account?->botRuns
            ?->sortByDesc(fn ($item) => $item->started_at?->getTimestamp() ?? $item->created_at?->getTimestamp() ?? 0)
            ->values() ?? collect();
        $signals = $account?->signals
            ?->sortByDesc(fn ($item) => $item->generated_at?->getTimestamp() ?? $item->created_at?->getTimestamp() ?? 0)
            ->values() ?? collect();
        $alpacaAccounts = $account?->alpacaAccounts
            ?->sortByDesc(fn ($item) => $item->last_synced_at?->getTimestamp() ?? $item->created_at?->getTimestamp() ?? 0)
            ->values() ?? collect();
        $alpacaAccount = $alpacaAccounts->first(fn ($item) => (bool) ($item->is_active ?? false)) ?? $alpacaAccounts->first();
        $automationSetting = $account?->automationSettings
            ?->sortByDesc(fn ($item) => $item->updated_at?->getTimestamp() ?? $item->created_at?->getTimestamp() ?? 0)
            ->first();

        $data = Bismel1CustomerTradingPageData::make($pageType, $account, [
            'positions' => $positions,
            'orders' => $orders,
            'activity_logs' => $activityLogs,
            'bot_runs' => $botRuns,
            'signals' => $signals,
            'alpaca_account' => $alpacaAccount,
            'automation_setting' => $automationSetting,
            'entitlements' => $bismel1EntitlementService->resolve($account),
        ]);

        return view('customer.trading.index', [
            'navItems' => CustomerNavigation::items(),
            'page' => $data['page'],
            'summary' => $data['summary'],
            'summaryItems' => $data['summaryItems'],
            'primaryTitle' => $data['primaryTitle'],
            'primaryItems' => $data['primaryItems'],
            'primaryEmptyMessage' => $data['primaryEmptyMessage'],
            'secondaryTitle' => $data['secondaryTitle'],
            'secondaryItems' => $data['secondaryItems'],
            'secondaryEmptyMessage' => $data['secondaryEmptyMessage'],
            'relatedLinks' => $data['relatedLinks'],
            'hasTradingData' => $data['hasTradingData'],
            'emptyStateTitle' => $data['emptyStateTitle'],
            'emptyStateMessage' => $data['emptyStateMessage'],
        ]);
    }
}
