<?php
// اعوز بالله من الشياطين و ان يحضرون بسم الله الرحمن الرحيم الله لا إله إلا هو الحي القيوم
// Bismillahi ar-Rahmani ar-Rahim Audhu billahi min ash-shayatin wa an yahdurun Bismillah ar-Rahman ar-Rahim Allah la ilaha illa huwa al-hayy al-qayyum. Tamsa Allahu ala ayunihim
// version: x
// ======================================================
// - App Name: gusgraph-trading
// - Gusgraph LLC -
// - Author: Gus Kazem
// - https://Gusgraph.com
// - File Path: app/Http/Controllers/Customer/StrategyController.php
// ======================================================

namespace App\Http\Controllers\Customer;

use App\Http\Controllers\Controller;
use App\Http\Requests\Customer\UpdateStrategySettingsRequest;
use App\Models\StrategyProfile;
use App\Support\Billing\Bismel1EntitlementService;
use App\Support\Customer\CurrentCustomerAccountResolver;
use App\Support\Navigation\CustomerNavigation;
use App\Support\ViewData\StrategyPageData;

class StrategyController extends Controller
{
    public function primeStocks()
    {
        $page = [
            'title' => 'Prime Stocks Test Console',
            'subtitle' => 'Prime Stocks visual testing surface for the customer workspace.',
            'intro' => 'Review the current Prime Stocks framing, demo runtime posture, and control concepts before live Cloud Run wiring is added.',
        ];

        $summary = [
            'title' => 'Cloud Run runs the bot while this page stays a customer control and monitoring surface.',
            'body' => 'Prime Stocks executes on Cloud Run server-side with demo-only status values shown here. Trading does not require this page to stay open.',
            'eyebrow' => 'Runtime ownership',
            'icon' => 'fa-solid fa-server',
            'tone' => 'sky',
        ];

        $statusItems = [
            ['label' => 'Bot Runtime', 'value' => 'Cloud Run', 'context' => 'Serverless runtime target for the bot process.', 'icon' => 'fa-solid fa-server', 'tone' => 'sky'],
            ['label' => 'Strategy State', 'value' => 'Enabled demo', 'context' => 'Visual placeholder only until runtime wiring is connected.', 'icon' => 'fa-solid fa-toggle-on', 'tone' => 'emerald'],
            ['label' => 'Asset Class', 'value' => 'Stocks Only', 'context' => 'Prime Stocks is intentionally limited to stocks in this phase.', 'icon' => 'fa-solid fa-chart-line', 'tone' => 'blue'],
            ['label' => 'Execution Timeframe', 'value' => '1H', 'context' => 'The execution frame decides when to act.', 'icon' => 'fa-solid fa-clock', 'tone' => 'violet'],
            ['label' => 'Trend Timeframe', 'value' => '1D', 'context' => 'The higher frame helps decide whether a setup should be taken.', 'icon' => 'fa-solid fa-arrows-up-down', 'tone' => 'amber'],
            ['label' => 'Pullback Window', 'value' => '5', 'context' => 'Default pullback lookback window for this product phase.', 'icon' => 'fa-solid fa-arrow-trend-down', 'tone' => 'rose'],
            ['label' => 'Regime Status', 'value' => 'OK demo', 'context' => 'Visual regime posture only for review.', 'icon' => 'fa-solid fa-shield-halved', 'tone' => 'emerald'],
            ['label' => 'New Basket Status', 'value' => 'GO demo', 'context' => 'Reflects the pauseNewBasket concept without live wiring.', 'icon' => 'fa-solid fa-layer-group', 'tone' => 'emerald'],
            ['label' => 'Add Status', 'value' => 'Allowed demo', 'context' => 'Reflects the pauseAdds concept without live wiring.', 'icon' => 'fa-solid fa-plus', 'tone' => 'emerald'],
            ['label' => 'Current Tier', 'value' => 'High demo', 'context' => 'Placeholder tier display for visual testing.', 'icon' => 'fa-solid fa-signal', 'tone' => 'amber'],
            ['label' => 'Last Action Candidate', 'value' => 'FirstLot demo', 'context' => 'Demo value from the approved Prime Stocks action set.', 'icon' => 'fa-solid fa-bolt', 'tone' => 'violet'],
            ['label' => 'Last Signal Time', 'value' => '2026-04-04 15:19 UTC demo', 'context' => 'Static placeholder timestamp for layout review.', 'icon' => 'fa-solid fa-calendar-check', 'tone' => 'sky'],
            ['label' => 'Notes', 'value' => 'Browser does not need to stay open', 'context' => 'The bot keeps running server-side even when the customer leaves the page.', 'icon' => 'fa-solid fa-window-maximize', 'tone' => 'blue'],
        ];

        $strategyFrame = [
            ['label' => 'Strategy Name', 'value' => 'Prime Stocks', 'context' => 'Canonical customer-facing strategy name for this testing surface.', 'icon' => 'fa-solid fa-compass-drafting', 'tone' => 'sky'],
            ['label' => 'Market Scope', 'value' => 'Stocks-only label', 'context' => 'No crypto framing is shown on this customer product surface.', 'icon' => 'fa-solid fa-building-columns', 'tone' => 'blue'],
            ['label' => '1H decides when', 'value' => 'Execution timing comes from the 1H structure.', 'context' => 'This frame is used to decide when entries and adds are considered.', 'icon' => 'fa-solid fa-hourglass-half', 'tone' => 'violet'],
            ['label' => '1D helps decide whether', 'value' => 'Higher-timeframe context filters whether the setup should be taken.', 'context' => 'The 1D trend acts as the directional participation filter.', 'icon' => 'fa-solid fa-chart-column', 'tone' => 'amber'],
            ['label' => 'Pullback Window', 'value' => '5', 'context' => 'Approved current default for Prime Stocks.', 'icon' => 'fa-solid fa-arrow-trend-down', 'tone' => 'rose'],
        ];

        $behaviorItems = [
            ['label' => 'Reclaim model summary', 'value' => 'Prime Stocks looks for reclaim behavior after the pullback instead of treating raw weakness as the entry.', 'context' => 'The customer page shows the concept only and does not execute it in the browser.', 'icon' => 'fa-solid fa-rotate', 'tone' => 'sky'],
            ['label' => 'FirstLot behavior summary', 'value' => 'FirstLot is the initial server-side entry candidate when the reclaim conditions and higher-timeframe context align.', 'context' => 'Shown as a status concept so customers can understand the first action type.', 'icon' => 'fa-solid fa-flag-checkered', 'tone' => 'emerald'],
            ['label' => 'MULTI behavior summary', 'value' => 'MULTI represents controlled add behavior after the initial participation has already been established.', 'context' => 'Adds remain a separate concept from the first entry.', 'icon' => 'fa-solid fa-layer-group', 'tone' => 'violet'],
            ['label' => 'ATR trail exit concept', 'value' => 'Exit_ATR appears when the trailing ATR protection becomes the active exit path.', 'context' => 'The surface explains the exit language without claiming live trade management here.', 'icon' => 'fa-solid fa-route', 'tone' => 'rose'],
            ['label' => 'Regime fail behavior summary', 'value' => 'If regime conditions fail, the strategy posture moves toward pause or exit behavior instead of continuing normal participation.', 'context' => 'This is presented as a risk-control concept only during this phase.', 'icon' => 'fa-solid fa-shield', 'tone' => 'amber'],
        ];

        $controlItems = [
            ['label' => 'pauseNewBasket status concept', 'value' => 'Blocks new first-entry baskets while leaving the server-side bot model intact.', 'context' => 'Useful when the runtime should stay alive but stop initiating fresh baskets.', 'icon' => 'fa-solid fa-ban', 'tone' => 'amber'],
            ['label' => 'pauseAdds status concept', 'value' => 'Blocks MULTI-style adds without implying the page itself is trading.', 'context' => 'Lets customers understand add control separately from new basket control.', 'icon' => 'fa-solid fa-circle-pause', 'tone' => 'amber'],
            ['label' => 'Bot runtime target', 'value' => 'Cloud Run serverless', 'context' => 'The bot runtime belongs on Cloud Run, not inside the customer browser session.', 'icon' => 'fa-solid fa-cloud', 'tone' => 'sky'],
            ['label' => 'User page role', 'value' => 'Control / monitoring only', 'context' => 'This page exists to review settings, posture, and status concepts.', 'icon' => 'fa-solid fa-sliders', 'tone' => 'blue'],
            ['label' => 'Stay-open requirement', 'value' => 'Trading does not require the page to stay open', 'context' => 'Runtime continuity belongs to the server-side bot process.', 'icon' => 'fa-solid fa-arrow-up-right-from-square', 'tone' => 'emerald'],
        ];

        $relatedLinks = [
            ['label' => 'Customer Dashboard', 'route' => 'customer.dashboard', 'description' => 'Return to the current workspace overview.'],
            ['label' => 'Automation', 'route' => 'customer.automation.index', 'description' => 'Review broader automation posture around the customer workspace.'],
            ['label' => 'Strategy Settings', 'route' => 'customer.strategy.index', 'description' => 'Review the existing strategy profile page separately from this Prime Stocks test console.'],
            ['label' => 'Broker', 'route' => 'customer.broker.index', 'description' => 'Review broker connection posture without changing the bot-runtime model.'],
        ];

        return view('customer.strategy.prime-stocks', [
            'navItems' => CustomerNavigation::items(),
            'page' => $page,
            'summary' => $summary,
            'statusItems' => $statusItems,
            'strategyFrame' => $strategyFrame,
            'behaviorItems' => $behaviorItems,
            'controlItems' => $controlItems,
            'relatedLinks' => $relatedLinks,
        ]);
    }

    public function index(
        CurrentCustomerAccountResolver $currentCustomerAccountResolver,
        Bismel1EntitlementService $bismel1EntitlementService,
    )
    {
        $account = $currentCustomerAccountResolver->resolveForPreset(request()->user(), 'strategy');
        $brokerConnections = $account?->brokerConnections ?? collect();
        $brokerCredentials = $brokerConnections->flatMap->brokerCredentials;
        $currentSubscription = $account?->subscriptions
            ?->sortByDesc(fn ($item) => $item->starts_at?->getTimestamp() ?? $item->created_at?->getTimestamp() ?? 0)
            ->first();
        $strategyProfile = $account?->strategyProfiles
            ?->sortByDesc(fn ($item) => ($item->is_active ? 1 : 0) + (($item->updated_at?->getTimestamp() ?? 0) / 1000000000000))
            ->first();
        $entitlements = $bismel1EntitlementService->resolve($account);
        $strategyAccess = $bismel1EntitlementService->strategyAccess($account, $strategyProfile);
        $data = StrategyPageData::make($account, [
            'current_subscription' => $currentSubscription,
            'strategy_profile' => $strategyProfile,
            'broker_connections' => $brokerConnections,
            'broker_credentials' => $brokerCredentials,
            'licenses' => $account?->apiLicenses ?? collect(),
            'activity_logs' => $account?->activityLogs ?? collect(),
            'watchlists' => $strategyProfile?->watchlists ?? ($account?->watchlists ?? collect()),
            'entitlements' => $entitlements,
            'strategy_access' => $strategyAccess,
        ]);

        return view('customer.strategy.index', [
            'navItems' => CustomerNavigation::items(),
            'page' => $data['page'],
            'summary' => $data['summary'],
            'form' => $data['form'],
            'strategyFrame' => $data['strategyFrame'],
            'watchlistSummary' => $data['watchlistSummary'],
            'styleProfiles' => $data['styleProfiles'],
            'linkageItems' => $data['linkageItems'],
            'relatedLinks' => $data['relatedLinks'],
            'hasStrategyData' => $data['hasStrategyData'],
        ]);
    }

    public function update(
        UpdateStrategySettingsRequest $request,
        CurrentCustomerAccountResolver $currentCustomerAccountResolver,
        Bismel1EntitlementService $bismel1EntitlementService,
    )
    {
        $account = $currentCustomerAccountResolver->resolveCurrent($request->user());

        if (! $account) {
            return redirect()
                ->route('customer.strategy.index')
                ->with('status', 'Strategy settings were not saved because no current customer workspace record is available.');
        }

        $validated = $request->validated();
        $strategyAccess = $bismel1EntitlementService->strategyAccess($account, null, $validated);
        $currentProfile = StrategyProfile::query()
            ->where('account_id', $account->getKey())
            ->where('engine', 'python')
            ->where('is_active', true)
            ->latest('id')
            ->first();

        $strategyProfile = $currentProfile ?? new StrategyProfile([
            'account_id' => $account->getKey(),
            'engine' => 'python',
        ]);

        $strategyProfile->fill([
            'name' => $validated['name'],
            'mode' => $validated['mode'],
            'timeframe' => $validated['timeframe'],
            'symbol_scope' => $validated['symbol_scope'],
            'style' => $validated['style'],
            'is_active' => (bool) $validated['is_active'] && ($strategyAccess['allowed'] ?? false),
            'settings' => [
                'managed_via' => 'customer_strategy_update',
                'entitlement_summary' => $strategyAccess['blocked_summary'] ?? null,
            ],
        ]);
        $strategyProfile->account()->associate($account);
        $strategyProfile->save();

        if ($strategyProfile->is_active) {
            StrategyProfile::query()
                ->where('account_id', $account->getKey())
                ->whereKeyNot($strategyProfile->getKey())
                ->update(['is_active' => false]);
        }

        return redirect()
            ->route('customer.strategy.index')
            ->with('status', ($validated['is_active'] ?? false) && ! ($strategyAccess['allowed'] ?? false)
                ? 'Strategy settings were saved, but the profile was not activated because plan access is not allowed for this strategy mode.'
                : 'Strategy settings were saved to the current workspace profile.');
    }
}
