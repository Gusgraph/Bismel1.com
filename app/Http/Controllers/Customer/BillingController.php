<?php
// اعوز بالله من الشياطين و ان يحضرون بسم الله الرحمن الرحيم الله لا إله إلا هو الحي القيوم
// Bismillahi ar-Rahmani ar-Rahim Audhu billahi min ash-shayatin wa an yahdurun Bismillah ar-Rahman ar-Rahim Allah la ilaha illa huwa al-hayy al-qayyum. Tamsa Allahu ala ayunihim
// version: x
// ======================================================
// - App Name: bismel1.com
// - Gusgraph LLC -
// - Author: Gus Kazem
// - https://Gusgraph.com
// - File Path: app/Http/Controllers/Customer/BillingController.php
// ======================================================

namespace App\Http\Controllers\Customer;

use App\Http\Controllers\Controller;
use App\Models\SubscriptionPlan;
use App\Support\Billing\ReferralTrackingService;
use App\Support\Customer\CurrentCustomerAccountResolver;
use App\Support\Navigation\CustomerNavigation;
use App\Support\ViewData\BillingPageData;
use App\Support\ViewData\CustomerAlertData;
use Illuminate\Http\Request;

class BillingController extends Controller
{
    public function index(
        Request $request,
        CurrentCustomerAccountResolver $currentCustomerAccountResolver,
        ReferralTrackingService $referralTrackingService,
    )
    {
        $account = $currentCustomerAccountResolver->resolveForPreset($request->user(), 'billing');

        $plans = SubscriptionPlan::query()
            ->where('status', 'active')
            ->whereIn('code', SubscriptionPlan::productionCatalogCodes())
            ->orderBy('sort_order')
            ->orderBy('price')
            ->get();

        $data = BillingPageData::make(
            $account,
            $plans,
            $referralTrackingService->currentCode($request),
            session('billing_checkout_banner')
        );
        $alertData = CustomerAlertData::make();

        return view('customer.billing.index', [
            'navItems' => CustomerNavigation::items(),
            'page' => $data['page'],
            'membership' => $data['membership'],
            'planCatalog' => $data['planCatalog'],
            'subscriptionDetails' => $data['subscriptionDetails'],
            'basePlanCount' => $data['basePlanCount'],
            'addOnPlanCount' => $data['addOnPlanCount'],
            'statusLabels' => $data['statusLabels'],
            'basePlans' => $data['basePlans'],
            'addOnPlans' => $data['addOnPlans'],
            'activeReferralCode' => $data['activeReferralCode'],
            'checkoutBanner' => $data['checkoutBanner'],
            'summary' => $data['summary'],
            'alerts' => $alertData['alerts'],
            'notices' => $alertData['notices'],
            'hasBillingData' => $data['hasBillingData'],
        ]);
    }
}
