<?php
// اعوز بالله من الشياطين و ان يحضرون بسم الله الرحمن الرحيم الله لا إله إلا هو الحي القيوم
// Bismillahi ar-Rahmani ar-Rahim Audhu billahi min ash-shayatin wa an yahdurun Bismillah ar-Rahman ar-Rahim Allah la ilaha illa huwa al-hayy al-qayyum. Tamsa Allahu ala ayunihim
// version: x
// ======================================================
// - App Name: gusgraph-trading
// - Gusgraph LLC -
// - Author: Gus Kazem
// - https://Gusgraph.com
// - File Path: tests/Feature/Billing/StripeSubscriptionSyncServiceTest.php
// ======================================================

namespace Tests\Feature\Billing;

use App\Models\Account;
use App\Models\SubscriptionPlan;
use App\Support\Billing\StripeSubscriptionSyncService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class StripeSubscriptionSyncServiceTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_syncs_checkout_completion_into_local_subscription_records(): void
    {
        $account = Account::factory()->create([
            'stripe_customer_id' => 'cus_123',
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
            'stripe_lookup_key' => 'bismillah1-bot-prime',
            'stripe_price_id' => 'price_prime',
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
            'stripe_lookup_key' => 'bismillah1-bot-additional-account-addon',
            'stripe_price_id' => 'price_addon',
        ]);
        $addOnPlan->save();

        $result = app(StripeSubscriptionSyncService::class)->handleCheckoutSessionCompleted([
            'id' => 'evt_checkout_completed',
            'customer' => 'cus_123',
            'subscription' => 'sub_123',
            'created' => now()->timestamp,
            'subscription_details' => [
                'status' => 'active',
            ],
            'line_items' => [
                [
                    'id' => 'si_base',
                    'quantity' => 1,
                    'price' => [
                        'id' => 'price_prime',
                        'lookup_key' => 'bismillah1-bot-prime',
                    ],
                ],
                [
                    'id' => 'si_addon',
                    'quantity' => 2,
                    'price' => [
                        'id' => 'price_addon',
                        'lookup_key' => 'bismillah1-bot-additional-account-addon',
                    ],
                ],
            ],
        ]);

        $this->assertSame('synced', $result['status']);

        $subscription = $account->subscriptions()->first();

        $this->assertNotNull($subscription);
        $this->assertTrue($basePlan->is($subscription->subscriptionPlan));
        $this->assertSame('sub_123', $subscription->stripe_subscription_id);
        $this->assertSame('price_prime', $subscription->stripe_price_id);
        $this->assertSame('active', $subscription->status->value);
        $this->assertCount(2, $subscription->items);
        $this->assertTrue($subscription->items->contains(fn ($item) => $item->subscriptionPlan && $item->subscriptionPlan->is($addOnPlan) && $item->quantity === 2));
    }
}
