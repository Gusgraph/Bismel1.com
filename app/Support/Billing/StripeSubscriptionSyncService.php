<?php
// اعوز بالله من الشياطين و ان يحضرون بسم الله الرحمن الرحيم الله لا إله إلا هو الحي القيوم
// Bismillahi ar-Rahmani ar-Rahim Audhu billahi min ash-shayatin wa an yahdurun Bismillah ar-Rahman ar-Rahim Allah la ilaha illa huwa al-hayy al-qayyum. Tamsa Allahu ala ayunihim
// version: x
// ======================================================
// - App Name: gusgraph-trading
// - Gusgraph LLC -
// - Author: Gus Kazem
// - https://Gusgraph.com
// - File Path: app/Support/Billing/StripeSubscriptionSyncService.php
// ======================================================

namespace App\Support\Billing;

use App\Models\Account;
use App\Models\AffiliateCommission;
use App\Models\BillingCheckoutSession;
use App\Models\Invoice;
use App\Models\ReferralAttribution;
use App\Models\Subscription;
use App\Models\SubscriptionItem;
use App\Models\SubscriptionPlan;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

class StripeSubscriptionSyncService
{
    public function handleCheckoutSessionCompleted(array $payload): array
    {
        $customerId = $this->stringValue($payload['customer'] ?? null);
        $subscriptionId = $this->stringValue($payload['subscription'] ?? null);
        $metadata = is_array($payload['metadata'] ?? null) ? $payload['metadata'] : [];
        $checkoutSessionId = $this->stringValue($payload['id'] ?? null);
        $checkoutSession = $this->findCheckoutSession($checkoutSessionId, $metadata);

        if (! $customerId || ! $subscriptionId) {
            return [
                'status' => 'missing_identifiers',
                'message' => 'Stripe checkout completion is missing a customer or subscription identifier.',
            ];
        }

        $account = $this->resolveAccount($customerId, $metadata, $checkoutSession)
            ?? $checkoutSession?->account;

        if (! $account) {
            return [
                'status' => 'missing_account',
                'message' => 'No local account matches the Stripe checkout confirmation yet.',
            ];
        }

        $subscriptionPayload = is_array($payload['subscription_details'] ?? null)
            ? $payload['subscription_details']
            : [];

        $lineItems = $this->normalizeLineItems($payload['line_items'] ?? []);

        return $this->syncSubscriptionState(
            $account,
            $subscriptionId,
            $customerId,
            $lineItems,
            (string) ($subscriptionPayload['status'] ?? 'active'),
            (string) config('stripe.events.checkout_completed'),
            $this->stringValue($payload['id'] ?? null),
            $this->timestampValue($payload['created'] ?? null),
            $this->timestampValue($subscriptionPayload['current_period_end'] ?? null),
            (bool) ($subscriptionPayload['cancel_at_period_end'] ?? false),
            $this->timestampValue($subscriptionPayload['cancel_at'] ?? null),
            $checkoutSession,
            $metadata
        );
    }

    public function handleInvoicePaid(array $payload): array
    {
        return $this->syncInvoiceState($payload, 'paid', (string) config('stripe.events.invoice_paid'));
    }

    public function handleInvoicePaymentFailed(array $payload): array
    {
        return $this->syncInvoiceState($payload, 'payment_failed', (string) config('stripe.events.invoice_payment_failed'));
    }

    public function handleSubscriptionUpdated(array $payload): array
    {
        return $this->syncSubscriptionPayload(
            $payload,
            (string) config('stripe.events.subscription_updated'),
            false
        );
    }

    public function handleSubscriptionDeleted(array $payload): array
    {
        return $this->syncSubscriptionPayload(
            $payload,
            (string) config('stripe.events.subscription_deleted'),
            true
        );
    }

    protected function syncSubscriptionPayload(array $payload, string $eventType, bool $deleted): array
    {
        $customerId = $this->stringValue($payload['customer'] ?? null);
        $subscriptionId = $this->stringValue($payload['id'] ?? null);
        $metadata = is_array($payload['metadata'] ?? null) ? $payload['metadata'] : [];

        if (! $customerId || ! $subscriptionId) {
            return [
                'status' => 'missing_identifiers',
                'message' => 'Stripe subscription payload is missing a customer or subscription identifier.',
            ];
        }

        $existingSubscription = Subscription::query()
            ->where('stripe_subscription_id', $subscriptionId)
            ->first();

        $account = $existingSubscription?->account
            ?? $this->resolveAccount($customerId, $metadata)
            ?? Account::query()->where('stripe_customer_id', $customerId)->first();

        if (! $account) {
            return [
                'status' => 'missing_account',
                'message' => 'No local account matches the Stripe customer identifier yet.',
            ];
        }

        $lineItems = $this->normalizeLineItems(data_get($payload, 'items.data', []));
        $status = $deleted ? 'cancelled' : ($payload['status'] ?? 'active');

        return $this->syncSubscriptionState(
            $account,
            $subscriptionId,
            $customerId,
            $lineItems,
            (string) $status,
            $eventType,
            $this->stringValue($payload['latest_invoice'] ?? null),
            $this->timestampValue($payload['current_period_start'] ?? $payload['created'] ?? null),
            $this->timestampValue($payload['current_period_end'] ?? null),
            (bool) ($payload['cancel_at_period_end'] ?? false),
            $this->timestampValue($payload['cancel_at'] ?? null),
            null,
            $metadata
        );
    }

    protected function syncInvoiceState(array $payload, string $invoiceStatus, string $eventType): array
    {
        $subscriptionId = $this->stringValue($payload['subscription'] ?? null);
        $invoiceId = $this->stringValue($payload['id'] ?? null);

        if (! $subscriptionId || ! $invoiceId) {
            return [
                'status' => 'missing_identifiers',
                'message' => 'Stripe invoice payload is missing a subscription or invoice identifier.',
            ];
        }

        $subscription = Subscription::query()
            ->where('stripe_subscription_id', $subscriptionId)
            ->first();

        if (! $subscription) {
            return [
                'status' => 'missing_subscription',
                'message' => 'No local subscription matches the Stripe subscription identifier yet.',
            ];
        }

        DB::transaction(function () use ($subscription, $payload, $invoiceId, $invoiceStatus, $eventType): void {
            $subscription->forceFill([
                'status' => $invoiceStatus === 'paid' ? 'active' : 'past_due',
                'stripe_status' => $payload['status'] ?? $invoiceStatus,
                'last_stripe_event_id' => $invoiceId,
                'last_stripe_event_type' => $eventType,
                'stripe_confirmed_at' => $invoiceStatus === 'paid'
                    ? $this->timestampValue($payload['status_transitions']['paid_at'] ?? $payload['created'] ?? null)
                    : $subscription->stripe_confirmed_at,
                'metadata' => array_merge($subscription->metadata ?? [], [
                    'last_invoice_event' => $eventType,
                ]),
            ])->save();

            $invoice = Invoice::query()->updateOrCreate(
                ['stripe_invoice_id' => $invoiceId],
                [
                    'account_id' => $subscription->account_id,
                    'subscription_id' => $subscription->getKey(),
                    'number' => $payload['number'] ?? $invoiceId,
                    'status' => $invoiceStatus === 'paid' ? 'paid' : 'open',
                    'stripe_status' => $payload['status'] ?? $invoiceStatus,
                    'stripe_payment_intent_id' => $this->stringValue($payload['payment_intent'] ?? null),
                    'subtotal' => $this->moneyValue($payload['subtotal'] ?? null),
                    'total' => $this->moneyValue($payload['total'] ?? null),
                    'currency' => strtoupper((string) ($payload['currency'] ?? 'usd')),
                    'issued_at' => $this->timestampValue($payload['created'] ?? null),
                    'paid_at' => $invoiceStatus === 'paid'
                        ? $this->timestampValue($payload['status_transitions']['paid_at'] ?? $payload['created'] ?? null)
                        : null,
                    'last_stripe_event_id' => $invoiceId,
                    'stripe_confirmed_at' => $invoiceStatus === 'paid'
                        ? $this->timestampValue($payload['status_transitions']['paid_at'] ?? $payload['created'] ?? null)
                        : null,
                    'metadata' => [
                        'billing_reason' => $payload['billing_reason'] ?? null,
                        'referral_code' => $subscription->referral_code,
                        'uses_affiliate_pricing' => $subscription->uses_affiliate_pricing,
                    ],
                ]
            );

            if ($invoiceStatus === 'paid') {
                $this->syncFirstPaymentCommission($subscription, $invoice);
            }
        });

        return [
            'status' => 'synced',
            'message' => 'Stripe invoice state was synced into the local subscription record.',
        ];
    }

    protected function syncFirstPaymentCommission(Subscription $subscription, Invoice $invoice): void
    {
        if (! $subscription->referral_attribution_id || ! $subscription->referral_code) {
            return;
        }

        if (AffiliateCommission::query()->where('subscription_id', $subscription->getKey())->exists()) {
            return;
        }

        $baseAmount = (float) ($invoice->total ?? 0);

        if ($baseAmount <= 0) {
            return;
        }

        $rate = (float) config('stripe.referral.first_payment_commission_rate', 0.73);

        AffiliateCommission::query()->create([
            'referral_attribution_id' => $subscription->referral_attribution_id,
            'subscription_id' => $subscription->getKey(),
            'invoice_id' => $invoice->getKey(),
            'affiliate_username' => $subscription->referral_code,
            'commission_status' => 'earned',
            'commission_rate' => number_format($rate, 4, '.', ''),
            'commission_base_amount' => number_format($baseAmount, 2, '.', ''),
            'commission_amount' => number_format($baseAmount * $rate, 2, '.', ''),
            'currency' => $invoice->currency ?? 'USD',
            'stripe_invoice_id' => $invoice->stripe_invoice_id,
            'earned_at' => $invoice->paid_at ?? now(),
            'metadata' => [
                'uses_affiliate_pricing' => $subscription->uses_affiliate_pricing,
                'stripe_price_id' => $subscription->stripe_price_id,
                'first_payment_only' => true,
            ],
        ]);
    }

    protected function syncSubscriptionState(
        Account $account,
        string $subscriptionId,
        string $customerId,
        array $lineItems,
        string $stripeStatus,
        string $eventType,
        ?string $eventId,
        ?Carbon $confirmedAt,
        ?Carbon $endsAt = null,
        bool $cancelAtPeriodEnd = false,
        ?Carbon $cancelAt = null,
        ?BillingCheckoutSession $checkoutSession = null,
        array $metadata = [],
    ): array {
        $baseItem = collect($lineItems)->first(fn (array $item) => ($item['plan_type'] ?? 'base') === 'base');

        if (! $baseItem) {
            return [
                'status' => 'missing_base_plan',
                'message' => 'Stripe payload does not contain a base plan price mapping yet.',
            ];
        }

        $basePlan = SubscriptionPlan::findByResolvedStripePriceId($baseItem['stripe_price_id'])
            ?? SubscriptionPlan::query()->where('code', $baseItem['code'] ?? null)->first();

        if (! $basePlan) {
            return [
                'status' => 'missing_price_mapping',
                'message' => 'No local subscription plan matches the Stripe base price identifier yet.',
            ];
        }

        $referralAttribution = $this->resolveReferralAttribution($checkoutSession, $metadata);
        $referralCode = $this->stringValue($metadata['referral_code'] ?? null)
            ?? $checkoutSession?->referral_code
            ?? $referralAttribution?->referral_code;
        $usesAffiliatePricing = $this->boolValue($metadata['uses_affiliate_pricing'] ?? null)
            ?? $checkoutSession?->uses_affiliate_pricing
            ?? false;
        $baseStripePriceId = $baseItem['stripe_price_id'] ?? $basePlan->resolvedStripePriceId($usesAffiliatePricing);

        DB::transaction(function () use ($account, $customerId, $subscriptionId, $lineItems, $stripeStatus, $eventType, $eventId, $confirmedAt, $endsAt, $cancelAtPeriodEnd, $cancelAt, $basePlan, $baseStripePriceId, $checkoutSession, $referralAttribution, $referralCode, $usesAffiliatePricing, $metadata): void {
            $account->forceFill([
                'stripe_customer_id' => $customerId,
            ])->save();

            $subscription = Subscription::query()->updateOrCreate(
                ['stripe_subscription_id' => $subscriptionId],
                [
                    'account_id' => $account->getKey(),
                    'subscription_plan_id' => $basePlan->getKey(),
                    'stripe_price_id' => $baseStripePriceId,
                    'status' => $this->localStatusFromStripe($stripeStatus),
                    'stripe_status' => $stripeStatus,
                    'last_stripe_event_id' => $eventId,
                    'last_stripe_event_type' => $eventType,
                    'stripe_confirmed_at' => $confirmedAt,
                    'starts_at' => $confirmedAt,
                    'ends_at' => $endsAt,
                    'cancel_at' => $cancelAt,
                    'cancel_at_period_end' => $cancelAtPeriodEnd,
                    'referral_attribution_id' => $referralAttribution?->getKey(),
                    'referral_code' => $referralCode,
                    'uses_affiliate_pricing' => $usesAffiliatePricing,
                    'metadata' => [
                        'line_item_count' => count($lineItems),
                        'checkout_session_id' => $checkoutSession?->stripe_checkout_session_id,
                        'base_plan_code' => $basePlan->code,
                        'stripe_metadata' => $metadata,
                    ],
                ]
            );

            SubscriptionItem::query()
                ->where('subscription_id', $subscription->getKey())
                ->delete();

            foreach ($lineItems as $item) {
                $matchedPlan = SubscriptionPlan::findByResolvedStripePriceId($item['stripe_price_id'])
                    ?? SubscriptionPlan::query()->where('code', $item['code'] ?? null)->first();

                SubscriptionItem::query()->create([
                    'subscription_id' => $subscription->getKey(),
                    'subscription_plan_id' => $matchedPlan?->getKey(),
                    'stripe_subscription_item_id' => $item['stripe_subscription_item_id'],
                    'stripe_price_id' => $item['stripe_price_id'],
                    'plan_type' => $item['plan_type'] ?? 'base',
                    'status' => $this->localStatusFromStripe($stripeStatus),
                    'quantity' => max(1, (int) ($item['quantity'] ?? 1)),
                    'starts_at' => $confirmedAt,
                    'ends_at' => $endsAt,
                    'metadata' => [
                        'code' => $item['code'] ?? null,
                        'uses_affiliate_pricing' => $usesAffiliatePricing,
                    ],
                ]);
            }

            if ($checkoutSession) {
                $checkoutSession->forceFill([
                    'stripe_customer_id' => $customerId,
                    'status' => 'completed',
                    'uses_affiliate_pricing' => $usesAffiliatePricing,
                    'metadata' => array_merge($checkoutSession->metadata ?? [], [
                        'stripe_subscription_id' => $subscriptionId,
                    ]),
                ])->save();
            }

            if ($referralAttribution) {
                $referralAttribution->forceFill([
                    'account_id' => $account->getKey(),
                    'stripe_customer_id' => $customerId,
                    'stripe_subscription_id' => $subscriptionId,
                    'checkout_session_id' => $checkoutSession?->stripe_checkout_session_id ?? $referralAttribution->checkout_session_id,
                    'converted_at' => $confirmedAt ?? now(),
                    'last_seen_at' => now(),
                    'metadata' => array_merge($referralAttribution->metadata ?? [], [
                        'uses_affiliate_pricing' => $usesAffiliatePricing,
                    ]),
                ])->save();
            }
        });

        return [
            'status' => 'synced',
            'message' => 'Stripe-confirmed subscription state was synced into local product-access records.',
        ];
    }

    protected function normalizeLineItems(mixed $lineItems): array
    {
        return collect(is_array($lineItems) ? $lineItems : [])
            ->map(function ($item) {
                $priceId = $this->stringValue(data_get($item, 'price.id') ?? data_get($item, 'price'));
                $lookupKey = $this->stringValue(data_get($item, 'price.lookup_key'));
                $code = $this->codeFromLookupKey($lookupKey);
                $matchedPlan = SubscriptionPlan::findByResolvedStripePriceId($priceId)
                    ?? ($code ? SubscriptionPlan::query()->where('code', $code)->first() : null);

                return [
                    'stripe_subscription_item_id' => $this->stringValue($item['id'] ?? null),
                    'stripe_price_id' => $priceId,
                    'quantity' => (int) ($item['quantity'] ?? 1),
                    'code' => $matchedPlan?->code ?? $code,
                    'plan_type' => $matchedPlan?->plan_type ?? ($code && str_contains($code, 'ADDON') ? 'addon' : 'base'),
                ];
            })
            ->filter(fn (array $item) => $item['stripe_price_id'])
            ->values()
            ->all();
    }

    protected function resolveAccount(?string $customerId, array $metadata = [], ?BillingCheckoutSession $checkoutSession = null): ?Account
    {
        if ($customerId) {
            $account = Account::query()->where('stripe_customer_id', $customerId)->first();

            if ($account) {
                return $account;
            }
        }

        $accountId = $this->stringValue($metadata['account_id'] ?? null);

        if ($accountId) {
            $account = Account::query()->find($accountId);

            if ($account) {
                return $account;
            }
        }

        return $checkoutSession?->account;
    }

    protected function findCheckoutSession(?string $stripeCheckoutSessionId, array $metadata = []): ?BillingCheckoutSession
    {
        if ($stripeCheckoutSessionId) {
            $checkoutSession = BillingCheckoutSession::query()
                ->where('stripe_checkout_session_id', $stripeCheckoutSessionId)
                ->first();

            if ($checkoutSession) {
                return $checkoutSession;
            }
        }

        $localCheckoutId = $this->stringValue($metadata['local_checkout_session_id'] ?? null);

        return $localCheckoutId
            ? BillingCheckoutSession::query()->find($localCheckoutId)
            : null;
    }

    protected function resolveReferralAttribution(?BillingCheckoutSession $checkoutSession, array $metadata = []): ?ReferralAttribution
    {
        if ($checkoutSession?->referralAttribution) {
            return $checkoutSession->referralAttribution;
        }

        $referralAttributionId = $this->stringValue($metadata['referral_attribution_id'] ?? null);

        return $referralAttributionId
            ? ReferralAttribution::query()->find($referralAttributionId)
            : null;
    }

    protected function localStatusFromStripe(string $status): string
    {
        return match ($status) {
            'trialing' => 'trial',
            'active', 'paid' => 'active',
            'past_due', 'unpaid', 'incomplete', 'payment_failed' => 'past_due',
            'canceled', 'cancelled', 'incomplete_expired' => 'cancelled',
            default => 'trial',
        };
    }

    protected function codeFromLookupKey(?string $lookupKey): ?string
    {
        return $lookupKey ? strtoupper(str_replace(['-', '.'], '_', $lookupKey)) : null;
    }

    protected function stringValue(mixed $value): ?string
    {
        if (! is_string($value) || trim($value) === '') {
            return null;
        }

        return trim($value);
    }

    protected function boolValue(mixed $value): ?bool
    {
        if (is_bool($value)) {
            return $value;
        }

        if (is_string($value)) {
            return match (strtolower(trim($value))) {
                '1', 'true', 'yes' => true,
                '0', 'false', 'no' => false,
                default => null,
            };
        }

        return null;
    }

    protected function timestampValue(mixed $value): ?Carbon
    {
        if (is_numeric($value)) {
            return Carbon::createFromTimestamp((int) $value);
        }

        if (is_string($value) && trim($value) !== '') {
            return Carbon::parse($value);
        }

        return null;
    }

    protected function moneyValue(mixed $value): ?string
    {
        if (! is_numeric($value)) {
            return null;
        }

        return number_format(((float) $value) / 100, 2, '.', '');
    }
}
