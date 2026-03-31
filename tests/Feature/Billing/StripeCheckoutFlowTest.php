<?php
// اعوز بالله من الشياطين و ان يحضرون بسم الله الرحمن الرحيم الله لا إله إلا هو الحي القيوم
// Bismillahi ar-Rahmani ar-Rahim Audhu billahi min ash-shayatin wa an yahdurun Bismillah ar-Rahman ar-Rahim Allah la ilaha illa huwa al-hayy al-qayyum. Tamsa Allahu ala ayunihim
// version: x
// ======================================================
// - App Name: gusgraph-trading
// - Gusgraph LLC -
// - Author: Gus Kazem
// - https://Gusgraph.com
// - File Path: tests/Feature/Billing/StripeCheckoutFlowTest.php
// ======================================================

namespace Tests\Feature\Billing;

use App\Models\Account;
use App\Models\BillingCheckoutSession;
use App\Models\ReferralAttribution;
use App\Models\SubscriptionPlan;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class StripeCheckoutFlowTest extends TestCase
{
    use RefreshDatabase;

    public function test_referral_landing_capture_and_affiliate_checkout_creation_are_persisted(): void
    {
        config()->set('app.url', 'http://localhost');
        config()->set('stripe.secret', 'sk_test_123');
        config()->set('stripe.price_ids.BISMILLAH1_BOT_PRIME', 'price_prime');
        config()->set('stripe.affiliate_price_ids.BISMILLAH1_BOT_PRIME', 'price_prime_affiliate');
        config()->set('stripe.price_ids.BISMILLAH1_BOT_ADDITIONAL_ACCOUNT_ADDON', 'price_addon');
        config()->set('stripe.affiliate_display_prices.BISMILLAH1_BOT_PRIME', '79');

        Http::fake([
            'https://api.stripe.com/v1/checkout/sessions' => Http::response([
                'id' => 'cs_test_123',
                'url' => 'https://checkout.stripe.test/session/cs_test_123',
                'customer' => 'cus_test_123',
            ]),
        ]);

        $user = User::factory()->create();
        $account = Account::factory()->create([
            'owner_user_id' => $user->getKey(),
        ]);

        tap(
            SubscriptionPlan::query()->firstWhere('code', 'BISMILLAH1_BOT_PRIME')
                ?? new SubscriptionPlan(['code' => 'BISMILLAH1_BOT_PRIME'])
        )->forceFill([
            'name' => 'Bismel1 Bot - Prime',
            'plan_type' => 'base',
            'product_family' => 'prime',
            'status' => 'active',
            'price' => 97,
            'currency' => 'USD',
            'interval' => 'monthly',
            'billing_model' => 'monthly',
            'sort_order' => 50,
        ])->save();

        tap(
            SubscriptionPlan::query()->firstWhere('code', 'BISMILLAH1_BOT_ADDITIONAL_ACCOUNT_ADDON')
                ?? new SubscriptionPlan(['code' => 'BISMILLAH1_BOT_ADDITIONAL_ACCOUNT_ADDON'])
        )->forceFill([
            'name' => 'Bismel1 Bot - Additional Account (Add-On)',
            'plan_type' => 'addon',
            'product_family' => 'account_addon',
            'status' => 'active',
            'price' => 29,
            'currency' => 'USD',
            'interval' => 'monthly',
            'billing_model' => 'monthly',
            'sort_order' => 120,
        ])->save();

        $this->get('/?ref=affiliateuser')
            ->assertStatus(200);

        $response = $this->actingAs($user)->post(route('customer.billing.checkout.store'), [
            'base_plan_code' => 'BISMILLAH1_BOT_PRIME',
            'addon_codes' => ['BISMILLAH1_BOT_ADDITIONAL_ACCOUNT_ADDON'],
            'addon_quantities' => [
                'BISMILLAH1_BOT_ADDITIONAL_ACCOUNT_ADDON' => 2,
            ],
        ]);

        $response->assertRedirect('https://checkout.stripe.test/session/cs_test_123');

        Http::assertSent(function ($request) {
            $lineItems = $request['line_items'] ?? [];
            $metadata = $request['metadata'] ?? [];

            return $request->url() === 'https://api.stripe.com/v1/checkout/sessions'
                && ($lineItems[0]['price'] ?? null) === 'price_prime_affiliate'
                && ($lineItems[1]['price'] ?? null) === 'price_addon'
                && ($metadata['referral_code'] ?? null) === 'AFFILIATEUSER'
                && ($metadata['uses_affiliate_pricing'] ?? null) === '1';
        });

        $this->assertDatabaseHas('referral_attributions', [
            'referral_code' => 'AFFILIATEUSER',
            'account_id' => $account->getKey(),
            'checkout_session_id' => 'cs_test_123',
        ]);

        $this->assertDatabaseHas('billing_checkout_sessions', [
            'account_id' => $account->getKey(),
            'user_id' => $user->getKey(),
            'subscription_plan_id' => SubscriptionPlan::query()->where('code', 'BISMILLAH1_BOT_PRIME')->value('id'),
            'referral_code' => 'AFFILIATEUSER',
            'stripe_checkout_session_id' => 'cs_test_123',
            'status' => 'open',
            'uses_affiliate_pricing' => 1,
        ]);
    }

    public function test_webhook_completion_and_first_paid_invoice_create_affiliate_commission_once(): void
    {
        config()->set('stripe.secret', 'sk_test_123');
        config()->set('stripe.webhook_secret', 'whsec_test_123');
        config()->set('stripe.price_ids.BISMILLAH1_BOT_PRIME', 'price_prime');
        config()->set('stripe.affiliate_price_ids.BISMILLAH1_BOT_PRIME', 'price_prime_affiliate');
        config()->set('stripe.price_ids.BISMILLAH1_BOT_ADDITIONAL_ACCOUNT_ADDON', 'price_addon');
        config()->set('stripe.referral.first_payment_commission_rate', 0.73);

        Http::fake([
            'https://api.stripe.com/v1/checkout/sessions/cs_live_123/line_items*' => Http::response([
                'data' => [
                    [
                        'id' => 'si_base',
                        'quantity' => 1,
                        'price' => [
                            'id' => 'price_prime_affiliate',
                            'lookup_key' => 'bismillah1-bot-prime',
                        ],
                    ],
                    [
                        'id' => 'si_addon',
                        'quantity' => 1,
                        'price' => [
                            'id' => 'price_addon',
                            'lookup_key' => 'bismillah1-bot-additional-account-addon',
                        ],
                    ],
                ],
            ]),
            'https://api.stripe.com/v1/subscriptions/sub_live_123*' => Http::response([
                'id' => 'sub_live_123',
                'status' => 'active',
                'current_period_end' => now()->addMonth()->timestamp,
            ]),
        ]);

        $user = User::factory()->create();
        $account = Account::factory()->create([
            'owner_user_id' => $user->getKey(),
        ]);

        $basePlan = tap(
            SubscriptionPlan::query()->firstWhere('code', 'BISMILLAH1_BOT_PRIME')
                ?? new SubscriptionPlan(['code' => 'BISMILLAH1_BOT_PRIME'])
        )->forceFill([
            'name' => 'Bismel1 Bot - Prime',
            'plan_type' => 'base',
            'product_family' => 'prime',
            'status' => 'active',
            'price' => 97,
            'currency' => 'USD',
            'interval' => 'monthly',
            'billing_model' => 'monthly',
            'sort_order' => 50,
        ]);
        $basePlan->save();

        $addOnPlan = tap(
            SubscriptionPlan::query()->firstWhere('code', 'BISMILLAH1_BOT_ADDITIONAL_ACCOUNT_ADDON')
                ?? new SubscriptionPlan(['code' => 'BISMILLAH1_BOT_ADDITIONAL_ACCOUNT_ADDON'])
        )->forceFill([
            'name' => 'Bismel1 Bot - Additional Account (Add-On)',
            'plan_type' => 'addon',
            'product_family' => 'account_addon',
            'status' => 'active',
            'price' => 29,
            'currency' => 'USD',
            'interval' => 'monthly',
            'billing_model' => 'monthly',
            'sort_order' => 120,
        ]);
        $addOnPlan->save();

        $attribution = ReferralAttribution::query()->create([
            'session_id' => 'test-session',
            'user_id' => $user->getKey(),
            'account_id' => $account->getKey(),
            'referral_code' => 'AFFILIATEUSER',
            'first_seen_at' => now(),
            'last_seen_at' => now(),
        ]);

        $checkoutSession = BillingCheckoutSession::query()->create([
            'account_id' => $account->getKey(),
            'user_id' => $user->getKey(),
            'subscription_plan_id' => $basePlan->getKey(),
            'referral_attribution_id' => $attribution->getKey(),
            'referral_code' => 'AFFILIATEUSER',
            'stripe_checkout_session_id' => 'cs_live_123',
            'status' => 'open',
            'uses_affiliate_pricing' => true,
        ]);

        $checkoutPayload = json_encode([
            'id' => 'evt_checkout_completed',
            'type' => 'checkout.session.completed',
            'data' => [
                'object' => [
                    'id' => 'cs_live_123',
                    'customer' => 'cus_live_123',
                    'subscription' => 'sub_live_123',
                    'created' => now()->timestamp,
                    'metadata' => [
                        'account_id' => (string) $account->getKey(),
                        'local_checkout_session_id' => (string) $checkoutSession->getKey(),
                        'referral_code' => 'AFFILIATEUSER',
                        'referral_attribution_id' => (string) $attribution->getKey(),
                        'uses_affiliate_pricing' => '1',
                    ],
                ],
            ],
        ], JSON_THROW_ON_ERROR);

        $timestamp = time();
        $signature = hash_hmac('sha256', $timestamp.'.'.$checkoutPayload, 'whsec_test_123');

        $this->call('POST', '/stripe/webhook', [], [], [], [
            'HTTP_STRIPE_SIGNATURE' => 't='.$timestamp.',v1='.$signature,
            'CONTENT_TYPE' => 'application/json',
        ], $checkoutPayload)->assertOk();

        $invoicePayload = json_encode([
            'id' => 'evt_invoice_paid',
            'type' => 'invoice.paid',
            'data' => [
                'object' => [
                    'id' => 'in_123',
                    'subscription' => 'sub_live_123',
                    'number' => 'INV-123',
                    'status' => 'paid',
                    'subtotal' => 7900,
                    'total' => 7900,
                    'currency' => 'usd',
                    'created' => now()->timestamp,
                    'billing_reason' => 'subscription_create',
                    'status_transitions' => [
                        'paid_at' => now()->timestamp,
                    ],
                ],
            ],
        ], JSON_THROW_ON_ERROR);

        $invoiceSignature = hash_hmac('sha256', $timestamp.'.'.$invoicePayload, 'whsec_test_123');

        $this->call('POST', '/stripe/webhook', [], [], [], [
            'HTTP_STRIPE_SIGNATURE' => 't='.$timestamp.',v1='.$invoiceSignature,
            'CONTENT_TYPE' => 'application/json',
        ], $invoicePayload)->assertOk();

        $this->call('POST', '/stripe/webhook', [], [], [], [
            'HTTP_STRIPE_SIGNATURE' => 't='.$timestamp.',v1='.$invoiceSignature,
            'CONTENT_TYPE' => 'application/json',
        ], $invoicePayload)->assertOk();

        $this->assertDatabaseHas('subscriptions', [
            'account_id' => $account->getKey(),
            'subscription_plan_id' => $basePlan->getKey(),
            'stripe_subscription_id' => 'sub_live_123',
            'stripe_price_id' => 'price_prime_affiliate',
            'referral_attribution_id' => $attribution->getKey(),
            'referral_code' => 'AFFILIATEUSER',
            'uses_affiliate_pricing' => 1,
        ]);

        $this->assertDatabaseHas('subscription_items', [
            'subscription_plan_id' => $addOnPlan->getKey(),
            'stripe_price_id' => 'price_addon',
            'plan_type' => 'addon',
        ]);

        $this->assertDatabaseHas('affiliate_commissions', [
            'referral_attribution_id' => $attribution->getKey(),
            'affiliate_username' => 'AFFILIATEUSER',
            'commission_status' => 'earned',
            'commission_base_amount' => '79.00',
            'commission_amount' => '57.67',
            'currency' => 'USD',
            'stripe_invoice_id' => 'in_123',
        ]);

        $this->assertSame(1, \App\Models\AffiliateCommission::query()->count());
    }
}
