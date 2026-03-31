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
