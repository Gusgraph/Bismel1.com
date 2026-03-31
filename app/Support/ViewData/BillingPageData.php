<?php
// اعوز بالله من الشياطين و ان يحضرون بسم الله الرحمن الرحيم الله لا إله إلا هو الحي القيوم
// Bismillahi ar-Rahmani ar-Rahim Audhu billahi min ash-shayatin wa an yahdurun Bismillah ar-Rahman ar-Rahim Allah la ilaha illa huwa al-hayy al-qayyum. Tamsa Allahu ala ayunihim
// version: x
// ======================================================
// - App Name: bismel1.com
// - Gusgraph LLC -
// - Author: Gus Kazem
// - https://Gusgraph.com
// - File Path: app/Support/ViewData/BillingPageData.php
// ======================================================

namespace App\Support\ViewData;

use App\Domain\Billing\Enums\SubscriptionStatus;
use App\Models\Account;
use App\Models\SubscriptionPlan;
use Illuminate\Support\Collection;

class BillingPageData
{
    public static function make(
        ?Account $account = null,
        ?Collection $plans = null,
        ?string $activeReferralCode = null,
        ?array $checkoutBanner = null,
    ): array {
        $subscription = $account?->subscriptions
            ->sortByDesc(fn ($item) => $item->starts_at?->getTimestamp() ?? $item->created_at?->getTimestamp() ?? 0)
            ->first();
        $statusLabels = SubscriptionStatus::labels();
        $subscriptionStatus = $subscription?->status instanceof SubscriptionStatus
            ? $subscription->status->value
            : ($subscription?->status ?? 'trial');
        $currentPlan = $subscription?->subscriptionPlan;
        $subscriptionItems = $subscription?->items ?? collect();
        $confirmedAt = $subscription?->stripe_confirmed_at;
        $basePlans = ($plans ?? collect())->filter(fn (SubscriptionPlan $plan) => ! $plan->isAddOn())->values();
        $addOnPlans = ($plans ?? collect())->filter(fn (SubscriptionPlan $plan) => $plan->isAddOn())->values();
        $planItems = ($plans ?? collect())->map(function (SubscriptionPlan $plan) use ($currentPlan, $activeReferralCode) {
            $affiliatePrice = $plan->isAddOn() ? null : $plan->affiliateDisplayPrice();
            $summary = $currentPlan && $currentPlan->is($plan)
                ? 'Current active package for this workspace.'
                : ($plan->isAddOn()
                    ? 'Optional add-on package for an active base subscription.'
                    : 'Primary monthly package available for purchase.');

            return [
                'code' => $plan->code,
                'label' => $plan->name,
                'type' => $plan->isAddOn() ? 'Add-On' : 'Base Plan',
                'price' => strtoupper($plan->currency).' '.number_format((float) $plan->price, 2).' / month',
                'affiliate_price' => $affiliatePrice ? strtoupper($plan->currency).' '.$affiliatePrice.' / month' : null,
                'summary' => implode(' / ', array_filter([
                    $summary,
                    $plan->product_family ? 'Family '.ucfirst(str_replace('_', ' ', (string) $plan->product_family)) : null,
                    $activeReferralCode && $affiliatePrice ? 'Affiliate pricing available for '.$activeReferralCode : null,
                    $plan->isAddOn() ? 'Add-ons use standard monthly pricing and require a base plan.' : null,
                ])),
                'checkout_ready' => $plan->resolvedStripePriceId() !== null,
                'affiliate_checkout_ready' => $plan->isAddOn() ? false : $plan->resolvedStripePriceId(true) !== null,
                'is_current' => $currentPlan?->is($plan) ?? false,
            ];
        })->values()->all();

        return [
            'page' => [
                'title' => 'Billing',
                'intro' => 'Choose a package, review current access, and manage billing for the current workspace.',
                'subtitle' => $account
                    ? 'Choose the right Bismel1 package, continue through Stripe, and keep workspace access aligned with the active subscription.'
                    : 'No billing records were found yet, so the page stays clear while you choose the first package for this workspace.',
                'sections' => [
                    ['heading' => 'Current Subscription', 'description' => 'The current workspace package, status, and billing posture stay visible in one place.'],
                    ['heading' => 'Base Plans', 'description' => 'Core scanner and bot packages stay separate from add-ons so primary package selection is explicit.'],
                    ['heading' => 'Add-On Packages', 'description' => 'Custom strategy and additional-account packages stay distinct for later multi-account expansion.'],
                    ['heading' => 'Referral Attribution', 'description' => 'Referral attribution stays attached through signup, purchase, and confirmed subscription activation.'],
                    ['heading' => 'Affiliate Pricing', 'description' => 'Eligible base plans can show affiliate pricing while add-ons stay on standard monthly pricing.'],
                ],
            ],
            'membership' => $account ? [
                ['label' => 'Workspace Name', 'value' => $account->name],
                ['label' => 'Workspace Slug', 'value' => $account->slug],
                ['label' => 'Workspace Owner', 'value' => $account->owner?->name ?? 'No owner assigned'],
                ['label' => 'Billing Contact', 'value' => $account->owner?->email ?? $account->owner?->name ?? 'No owner assigned'],
                ['label' => 'Current Plan', 'value' => $currentPlan?->name ?? 'No linked plan'],
                ['label' => 'Subscription Status', 'value' => $statusLabels[$subscriptionStatus] ?? ucfirst(str_replace('_', ' ', (string) $subscriptionStatus))],
                ['label' => 'Stripe Customer', 'value' => $account->stripe_customer_id ?? 'Not mapped'],
                ['label' => 'Stripe Subscription', 'value' => $subscription?->stripe_subscription_id ?? 'Not mapped'],
                ['label' => 'Plan Price', 'value' => $currentPlan ? strtoupper($currentPlan->currency).' '.number_format((float) $currentPlan->price, 2).' / '.$currentPlan->interval : 'No plan pricing available'],
                ['label' => 'Affiliate Pricing Path', 'value' => $subscription?->uses_affiliate_pricing ? 'Affiliate checkout pricing used' : 'Standard checkout pricing'],
                ['label' => 'Stripe-Confirmed At', 'value' => $confirmedAt?->toDateTimeString() ?? 'Not confirmed'],
                ['label' => 'Additional Account Package', 'value' => $addOnPlans->contains(fn ($plan) => $plan->code === 'BISMILLAH1_BOT_ADDITIONAL_ACCOUNT_ADDON') ? 'Available in catalog' : 'Not available'],
                ['label' => 'Active Add-Ons', 'value' => (string) $subscriptionItems->where('plan_type', 'addon')->count()],
                ['label' => 'Referral Code', 'value' => $subscription?->referral_code ?? $activeReferralCode ?? 'No referral tracked'],
                ['label' => 'Subscription Start', 'value' => $subscription?->starts_at?->toDateString() ?? 'Not started'],
                ['label' => 'Subscription End', 'value' => $subscription?->ends_at?->toDateString() ?? 'No end date recorded'],
                ['label' => 'Trial Ends', 'value' => $subscription?->trial_ends_at?->toDateString() ?? 'No trial end recorded'],
                ['label' => 'Invoice Count', 'value' => (string) ($account->invoices->count() ?? 0)],
            ] : [],
            'subscriptionDetails' => $subscription ? [
                [
                    'label' => $currentPlan?->name ?? 'Current subscription',
                    'value' => implode(' / ', array_filter([
                        $statusLabels[$subscriptionStatus] ?? ucfirst(str_replace('_', ' ', (string) $subscriptionStatus)),
                        $currentPlan ? ($currentPlan->isAddOn() ? 'Add-On' : 'Base Plan') : null,
                        $subscription?->stripe_status ? 'Stripe '.str_replace('_', ' ', $subscription->stripe_status) : 'No Stripe state',
                        $subscription?->stripe_price_id ? 'Price '.$subscription->stripe_price_id : 'No Stripe price',
                        $subscription?->uses_affiliate_pricing ? 'Affiliate price path' : 'Standard price path',
                        $subscription?->referral_code ? 'Referral '.$subscription->referral_code : null,
                        $account?->name ? 'Account '.$account->name : null,
                        $account?->slug ? 'Slug '.$account->slug : null,
                        $currentPlan ? strtoupper($currentPlan->currency).' '.number_format((float) $currentPlan->price, 2).' / '.$currentPlan->interval : 'No linked plan pricing',
                        $subscription->starts_at ? 'Starts '.$subscription->starts_at->toDateTimeString() : 'Not started',
                        $subscription->trial_ends_at ? 'Trial ends '.$subscription->trial_ends_at->toDateTimeString() : 'No trial end recorded',
                        $subscription->ends_at ? 'Ends '.$subscription->ends_at->toDateTimeString() : 'No end date recorded',
                        $subscription->created_at ? 'Created '.$subscription->created_at->toDateTimeString() : null,
                    ])),
                ],
            ] : [],
            'planCatalog' => $planItems,
            'basePlans' => array_values(array_filter($planItems, fn (array $plan) => $plan['type'] === 'Base Plan')),
            'addOnPlans' => array_values(array_filter($planItems, fn (array $plan) => $plan['type'] === 'Add-On')),
            'basePlanCount' => $basePlans->count(),
            'addOnPlanCount' => $addOnPlans->count(),
            'statusLabels' => $statusLabels,
            'activeReferralCode' => $activeReferralCode,
            'checkoutBanner' => $checkoutBanner,
            'summary' => [
                'headline' => $subscription
                    ? 'Billing shows the current package structure, active subscription status, and any referral-linked pricing for this workspace.'
                    : 'No active subscription is available for this workspace yet.',
                'details' => $subscription
                    ? 'Review the current plan, active add-ons, available package lineup, and any referral-linked pricing from one billing view.'
                    : 'Choose a package to start billing for this workspace and unlock the matching Bismel1 product access after the subscription becomes active.',
            ],
            'hasBillingData' => (bool) ($subscription || ($plans ?? collect())->isNotEmpty()),
        ];
    }
}
