<?php
// اعوز بالله من الشياطين و ان يحضرون بسم الله الرحمن الرحيم الله لا إله إلا هو الحي القيوم
// Bismillahi ar-Rahmani ar-Rahim Audhu billahi min ash-shayatin wa an yahdurun Bismillah ar-Rahman ar-Rahim Allah la ilaha illa huwa al-hayy al-qayyum. Tamsa Allahu ala ayunihim
// version: x
// ======================================================
// - App Name: bismel1.com
// - Gusgraph LLC -
// - Author: Gus Kazem
// - https://Gusgraph.com
// - File Path: app/Http/Controllers/Customer/BillingCheckoutController.php
// ======================================================

namespace App\Http\Controllers\Customer;

use App\Http\Controllers\Controller;
use App\Http\Requests\Customer\CreateCheckoutSessionRequest;
use App\Models\SubscriptionPlan;
use App\Support\Billing\StripeCheckoutService;
use App\Support\Customer\CurrentCustomerAccountResolver;
use Illuminate\Http\RedirectResponse;
use RuntimeException;

class BillingCheckoutController extends Controller
{
    public function store(
        CreateCheckoutSessionRequest $request,
        CurrentCustomerAccountResolver $currentCustomerAccountResolver,
        StripeCheckoutService $stripeCheckoutService,
    ): RedirectResponse {
        $account = $currentCustomerAccountResolver->resolveForPreset($request->user(), 'billing');

        abort_unless($account, 404);

        $basePlan = SubscriptionPlan::query()
            ->where('code', $request->string('base_plan_code')->toString())
            ->whereIn('code', SubscriptionPlan::productionBaseCodes())
            ->where('plan_type', 'base')
            ->where('status', 'active')
            ->first();

        if (! $basePlan) {
            return back()->withErrors([
                'base_plan_code' => 'The selected base plan is not available.',
            ]);
        }

        $requestedAddOnCodes = collect($request->input('addon_codes', []))
            ->filter(fn ($code) => is_string($code) && $code !== '')
            ->unique()
            ->values();

        $addOnPlans = SubscriptionPlan::query()
            ->whereIn('code', $requestedAddOnCodes)
            ->whereIn('code', SubscriptionPlan::productionAddOnCodes())
            ->where('plan_type', 'addon')
            ->where('status', 'active')
            ->get()
            ->keyBy('code');

        if ($requestedAddOnCodes->count() !== $addOnPlans->count()) {
            return back()->withErrors([
                'addon_codes' => 'One or more selected add-ons are not available.',
            ]);
        }

        $addOnQuantities = collect($request->input('addon_quantities', []));

        $addOnSelections = $addOnPlans->values()->map(fn (SubscriptionPlan $plan) => [
            'plan' => $plan,
            'quantity' => max(1, (int) ($addOnQuantities->get($plan->code, 1))),
        ])->all();

        try {
            $result = $stripeCheckoutService->createCheckoutSession(
                $request,
                $account,
                $request->user(),
                $basePlan,
                $addOnSelections,
            );
        } catch (RuntimeException $exception) {
            return back()->withErrors([
                'checkout' => $exception->getMessage(),
            ]);
        }

        $checkoutUrl = data_get($result, 'stripe_checkout_session.url');

        if (! is_string($checkoutUrl) || trim($checkoutUrl) === '') {
            return back()->withErrors([
                'checkout' => 'Stripe Checkout did not return a redirect URL.',
            ]);
        }

        return redirect()->away($checkoutUrl);
    }

    public function success(): RedirectResponse
    {
        return redirect()
            ->route('customer.billing.index')
            ->with('billing_checkout_banner', [
                'title' => 'Checkout returned successfully',
                'body' => 'Stripe Checkout returned to the app. Access will stay pending until Stripe webhook confirmation updates the local subscription state.',
            ]);
    }

    public function cancel(): RedirectResponse
    {
        return redirect()
            ->route('customer.billing.index')
            ->with('billing_checkout_banner', [
                'title' => 'Checkout was cancelled',
                'body' => 'No paid access was activated. You can adjust the package selection and start Checkout again.',
            ]);
    }
}
