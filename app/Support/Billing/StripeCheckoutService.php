<?php
// اعوز بالله من الشياطين و ان يحضرون بسم الله الرحمن الرحيم الله لا إله إلا هو الحي القيوم
// Bismillahi ar-Rahmani ar-Rahim Audhu billahi min ash-shayatin wa an yahdurun Bismillah ar-Rahman ar-Rahim Allah la ilaha illa huwa al-hayy al-qayyum. Tamsa Allahu ala ayunihim
// version: x
// ======================================================
// - App Name: gusgraph-trading
// - Gusgraph LLC -
// - Author: Gus Kazem
// - https://Gusgraph.com
// - File Path: app/Support/Billing/StripeCheckoutService.php
// ======================================================

namespace App\Support\Billing;

use App\Models\Account;
use App\Models\BillingCheckoutSession;
use App\Models\SubscriptionPlan;
use App\Models\User;
use Illuminate\Http\Request;
use RuntimeException;

class StripeCheckoutService
{
    public function __construct(
        protected StripeApiClient $stripeApiClient,
        protected ReferralTrackingService $referralTrackingService,
    ) {
    }

    public function createCheckoutSession(
        Request $request,
        Account $account,
        User $user,
        SubscriptionPlan $basePlan,
        array $addOnSelections = [],
    ): array {
        if ($basePlan->plan_type !== 'base') {
            throw new RuntimeException('The selected base plan is not configured as a base package.');
        }

        $attribution = $this->referralTrackingService->currentAttribution($request, $account);
        $usesAffiliatePricing = $attribution !== null;

        $lineItems = [];
        $selectedAddOnPlanCodes = [];
        $selectedPriceMode = [];

        $basePriceId = $this->resolvedPriceId($basePlan, $usesAffiliatePricing);
        $lineItems[] = [
            'price' => $basePriceId,
            'quantity' => 1,
        ];
        $selectedPriceMode[$basePlan->code] = $basePriceId;

        foreach ($addOnSelections as $selection) {
            /** @var SubscriptionPlan $plan */
            $plan = $selection['plan'];
            $quantity = max(1, (int) ($selection['quantity'] ?? 1));
            $priceId = $this->resolvedPriceId($plan, false);

            $lineItems[] = [
                'price' => $priceId,
                'quantity' => $quantity,
            ];

            $selectedAddOnPlanCodes[$plan->code] = $quantity;
            $selectedPriceMode[$plan->code] = $priceId;
        }

        $successUrl = route('customer.billing.checkout.success', [], true).'?session_id={CHECKOUT_SESSION_ID}';
        $cancelUrl = route('customer.billing.checkout.cancel', [], true);

        $checkoutSession = BillingCheckoutSession::query()->create([
            'account_id' => $account->getKey(),
            'user_id' => $user->getKey(),
            'subscription_plan_id' => $basePlan->getKey(),
            'status' => 'pending',
            'uses_affiliate_pricing' => $usesAffiliatePricing,
            'selected_add_on_plan_codes' => $selectedAddOnPlanCodes,
            'success_url' => $successUrl,
            'cancel_url' => $cancelUrl,
            'metadata' => [
                'base_plan_code' => $basePlan->code,
                'add_on_quantities' => $selectedAddOnPlanCodes,
                'selected_price_ids' => $selectedPriceMode,
                'base_plan_list_price' => number_format((float) $basePlan->price, 2, '.', ''),
                'base_plan_affiliate_price' => $usesAffiliatePricing ? $basePlan->affiliateDisplayPrice() : null,
            ],
        ]);

        $attribution = $this->referralTrackingService->attachCheckout($request, $checkoutSession, $account);

        $metadata = array_filter([
            'local_checkout_session_id' => (string) $checkoutSession->getKey(),
            'account_id' => (string) $account->getKey(),
            'user_id' => (string) $user->getKey(),
            'base_plan_code' => $basePlan->code,
            'add_on_codes' => implode(',', array_keys($selectedAddOnPlanCodes)),
            'referral_code' => $attribution?->referral_code,
            'referral_attribution_id' => $attribution ? (string) $attribution->getKey() : null,
            'uses_affiliate_pricing' => $usesAffiliatePricing ? '1' : '0',
            'add_ons_use_standard_pricing' => $selectedAddOnPlanCodes !== [] ? '1' : null,
        ], fn ($value) => $value !== null && $value !== '');

        $stripePayload = array_filter([
            'mode' => 'subscription',
            'success_url' => $successUrl,
            'cancel_url' => $cancelUrl,
            'client_reference_id' => (string) $account->getKey(),
            'customer' => $account->stripe_customer_id ?: null,
            'customer_email' => $account->stripe_customer_id ? null : $user->email,
            'line_items' => $lineItems,
            'metadata' => $metadata,
            'subscription_data' => [
                'metadata' => $metadata,
            ],
            'allow_promotion_codes' => true,
        ], fn ($value) => $value !== null);

        $stripeSession = $this->stripeApiClient->createCheckoutSession($stripePayload);

        $checkoutSession->forceFill([
            'stripe_checkout_session_id' => $stripeSession['id'] ?? null,
            'stripe_customer_id' => $stripeSession['customer'] ?? $account->stripe_customer_id,
            'status' => 'open',
            'uses_affiliate_pricing' => $usesAffiliatePricing,
            'metadata' => array_merge($checkoutSession->metadata ?? [], [
                'selected_price_ids' => $selectedPriceMode,
            ]),
        ])->save();

        if ($attribution) {
            $attribution->forceFill([
                'checkout_session_id' => $stripeSession['id'] ?? null,
                'stripe_customer_id' => $stripeSession['customer'] ?? $attribution->stripe_customer_id,
                'metadata' => array_merge($attribution->metadata ?? [], [
                    'uses_affiliate_pricing' => $usesAffiliatePricing,
                ]),
            ])->save();
        }

        return [
            'billing_checkout_session' => $checkoutSession->fresh(),
            'stripe_checkout_session' => $stripeSession,
        ];
    }

    protected function resolvedPriceId(SubscriptionPlan $plan, bool $usesAffiliatePricing): string
    {
        $priceId = $plan->resolvedStripePriceId($usesAffiliatePricing);

        if (! $priceId) {
            throw new RuntimeException('Missing Stripe price ID configuration for plan ['.$plan->code.'].');
        }

        if ($plan->stripe_price_id !== $priceId && ! $usesAffiliatePricing) {
            $plan->forceFill(['stripe_price_id' => $priceId])->save();
        }

        return $priceId;
    }
}
