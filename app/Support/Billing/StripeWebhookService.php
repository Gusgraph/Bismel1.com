<?php
// اعوز بالله من الشياطين و ان يحضرون بسم الله الرحمن الرحيم الله لا إله إلا هو الحي القيوم
// Bismillahi ar-Rahmani ar-Rahim Audhu billahi min ash-shayatin wa an yahdurun Bismillah ar-Rahman ar-Rahim Allah la ilaha illa huwa al-hayy al-qayyum. Tamsa Allahu ala ayunihim
// version: x
// ======================================================
// - App Name: gusgraph-trading
// - Gusgraph LLC -
// - Author: Gus Kazem
// - https://Gusgraph.com
// - File Path: app/Support/Billing/StripeWebhookService.php
// ======================================================

namespace App\Support\Billing;

use RuntimeException;

class StripeWebhookService
{
    public function __construct(
        protected StripeApiClient $stripeApiClient,
        protected StripeSubscriptionSyncService $stripeSubscriptionSyncService,
    ) {
    }

    public function handle(string $payload, ?string $signatureHeader): array
    {
        $event = $this->verifyAndDecodeEvent($payload, $signatureHeader);
        $eventType = (string) ($event['type'] ?? '');
        $object = is_array(data_get($event, 'data.object')) ? data_get($event, 'data.object') : [];

        return match ($eventType) {
            (string) config('stripe.events.checkout_completed') => $this->handleCheckoutCompleted($object),
            (string) config('stripe.events.invoice_paid') => $this->stripeSubscriptionSyncService->handleInvoicePaid($object),
            (string) config('stripe.events.invoice_payment_failed') => $this->stripeSubscriptionSyncService->handleInvoicePaymentFailed($object),
            (string) config('stripe.events.subscription_updated') => $this->stripeSubscriptionSyncService->handleSubscriptionUpdated($object),
            (string) config('stripe.events.subscription_deleted') => $this->stripeSubscriptionSyncService->handleSubscriptionDeleted($object),
            default => [
                'status' => 'ignored',
                'message' => 'Stripe event type is not handled by this billing pass.',
            ],
        };
    }

    protected function handleCheckoutCompleted(array $object): array
    {
        $subscriptionId = is_string($object['subscription'] ?? null) ? $object['subscription'] : null;
        $sessionId = is_string($object['id'] ?? null) ? $object['id'] : null;

        if (! $subscriptionId || ! $sessionId) {
            throw new RuntimeException('Stripe checkout session payload is missing the subscription or session identifier.');
        }

        $lineItems = $this->stripeApiClient->fetchCheckoutSessionLineItems($sessionId);
        $subscription = $this->stripeApiClient->fetchSubscription($subscriptionId);

        $payload = $object;
        $payload['line_items'] = $lineItems['data'] ?? [];
        $payload['subscription_details'] = $subscription;

        return $this->stripeSubscriptionSyncService->handleCheckoutSessionCompleted($payload);
    }

    protected function verifyAndDecodeEvent(string $payload, ?string $signatureHeader): array
    {
        $secret = config('stripe.webhook_secret');

        if (! is_string($secret) || trim($secret) === '') {
            throw new RuntimeException('Missing Stripe configuration value: STRIPE_WEBHOOK_SECRET');
        }

        if (! is_string($signatureHeader) || trim($signatureHeader) === '') {
            throw new RuntimeException('Missing Stripe-Signature header.');
        }

        $timestamp = null;
        $signatures = [];

        foreach (explode(',', $signatureHeader) as $part) {
            [$key, $value] = array_pad(explode('=', trim($part), 2), 2, null);

            if ($key === 't') {
                $timestamp = is_numeric($value) ? (int) $value : null;
            }

            if ($key === 'v1' && is_string($value)) {
                $signatures[] = $value;
            }
        }

        if (! $timestamp || $signatures === []) {
            throw new RuntimeException('Invalid Stripe signature header format.');
        }

        $tolerance = (int) config('stripe.webhook_tolerance', 300);

        if (abs(time() - $timestamp) > $tolerance) {
            throw new RuntimeException('Stripe webhook timestamp is outside the allowed tolerance.');
        }

        $expectedSignature = hash_hmac('sha256', $timestamp.'.'.$payload, trim($secret));
        $matches = collect($signatures)->contains(fn (string $signature) => hash_equals($expectedSignature, $signature));

        if (! $matches) {
            throw new RuntimeException('Stripe webhook signature verification failed.');
        }

        $event = json_decode($payload, true);

        if (! is_array($event)) {
            throw new RuntimeException('Stripe webhook payload is not valid JSON.');
        }

        return $event;
    }
}
