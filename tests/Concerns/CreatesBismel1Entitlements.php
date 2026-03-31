<?php
// اعوز بالله من الشياطين و ان يحضرون بسم الله الرحمن الرحيم الله لا إله إلا هو الحي القيوم
// Bismillahi ar-Rahmani ar-Rahim Audhu billahi min ash-shayatin wa an yahdurun Bismillah ar-Rahman ar-Rahim Allah la ilaha illa huwa al-hayy al-qayyum. Tamsa Allahu ala ayunihim
// version: x
// ======================================================
// - App Name: bismel1.com
// - Gusgraph LLC -
// - Author: Gus Kazem
// - https://Gusgraph.com
// - File Path: tests/Concerns/CreatesBismel1Entitlements.php
// ======================================================

namespace Tests\Concerns;

use App\Models\Account;
use App\Models\Subscription;
use App\Models\SubscriptionItem;
use App\Models\SubscriptionPlan;

trait CreatesBismel1Entitlements
{
    protected function seedConfirmedBismel1Subscription(Account $account, string $basePlanCode = 'BISMILLAH1_BOT_EXECUTE_BASIC', array $addOns = []): Subscription
    {
        $basePlan = $this->ensureBismel1Plan($basePlanCode);

        $subscription = Subscription::query()->create([
            'account_id' => $account->getKey(),
            'subscription_plan_id' => $basePlan->getKey(),
            'stripe_subscription_id' => 'sub_test_'.$account->getKey().'_'.strtolower(bin2hex(random_bytes(4))),
            'stripe_price_id' => $basePlan->stripe_price_id ?? 'price_'.strtolower($basePlanCode),
            'status' => 'active',
            'stripe_status' => 'active',
            'last_stripe_event_id' => 'evt_test_'.$account->getKey(),
            'last_stripe_event_type' => 'invoice.paid',
            'stripe_confirmed_at' => now()->subMinute(),
            'starts_at' => now()->subDay(),
            'metadata' => [
                'base_plan_code' => $basePlan->code,
                'seeded_via' => 'tests',
            ],
        ]);

        foreach ($addOns as $code => $quantity) {
            $plan = $this->ensureBismel1Plan($code);

            SubscriptionItem::query()->create([
                'subscription_id' => $subscription->getKey(),
                'subscription_plan_id' => $plan->getKey(),
                'stripe_subscription_item_id' => 'si_test_'.$subscription->getKey().'_'.strtolower(bin2hex(random_bytes(4))),
                'stripe_price_id' => $plan->stripe_price_id ?? 'price_'.strtolower($code),
                'plan_type' => 'addon',
                'status' => 'active',
                'quantity' => max(1, (int) $quantity),
                'starts_at' => now()->subDay(),
                'metadata' => [
                    'seeded_via' => 'tests',
                ],
            ]);
        }

        return $subscription->fresh(['subscriptionPlan', 'items.subscriptionPlan']);
    }

    protected function ensureBismel1Plan(string $code): SubscriptionPlan
    {
        $definition = $this->bismel1PlanDefinitions()[$code] ?? [
            'name' => $code,
            'plan_type' => 'base',
            'product_family' => 'stocks',
            'price' => 97,
            'sort_order' => 999,
            'metadata' => [],
        ];

        $plan = SubscriptionPlan::query()->firstWhere('code', $code) ?? new SubscriptionPlan([
            'code' => $code,
        ]);

        $plan->forceFill([
            'name' => $definition['name'],
            'code' => $code,
            'plan_type' => $definition['plan_type'],
            'product_family' => $definition['product_family'],
            'status' => 'active',
            'price' => $definition['price'],
            'currency' => 'USD',
            'interval' => 'monthly',
            'billing_model' => 'monthly',
            'sort_order' => $definition['sort_order'],
            'stripe_lookup_key' => strtolower(str_replace('_', '-', $code)),
            'stripe_price_id' => 'price_'.strtolower($code),
            'metadata' => $definition['metadata'],
        ])->save();

        return $plan;
    }

    protected function bismel1PlanDefinitions(): array
    {
        return [
            'BISMILLAH_AI_SCANNER' => [
                'name' => 'Bismel1 AI - Scanner (News catalyst)',
                'plan_type' => 'base',
                'product_family' => 'scanner',
                'price' => 49,
                'sort_order' => 10,
                'metadata' => [
                    'supports_additional_accounts' => false,
                ],
            ],
            'BISMILLAH1_BOT_OVERNIGHT_EQUITIES' => [
                'name' => 'Bismel1 Bot - Overnight equities',
                'plan_type' => 'base',
                'product_family' => 'stocks',
                'price' => 97,
                'sort_order' => 20,
                'metadata' => [
                    'supports_additional_accounts' => true,
                ],
            ],
            'BISMILLAH1_BOT_OPTIONS' => [
                'name' => 'Bismel1 Bot - Options',
                'plan_type' => 'base',
                'product_family' => 'options',
                'price' => 97,
                'sort_order' => 30,
                'metadata' => [
                    'supports_additional_accounts' => true,
                ],
            ],
            'BISMILLAH1_BOT_CRYPTO' => [
                'name' => 'Bismel1 Bot - Crypto',
                'plan_type' => 'base',
                'product_family' => 'crypto',
                'price' => 97,
                'sort_order' => 40,
                'metadata' => [
                    'supports_additional_accounts' => true,
                ],
            ],
            'BISMILLAH1_BOT_PRIME' => [
                'name' => 'Bismel1 Stocks Bot - AI Prime',
                'plan_type' => 'base',
                'product_family' => 'prime',
                'price' => 97,
                'sort_order' => 50,
                'metadata' => [
                    'supports_additional_accounts' => true,
                ],
            ],
            'BISMILLAH1_BOT_EXECUTE_BASIC' => [
                'name' => 'Bismel1 Stocks Bot - Execute',
                'plan_type' => 'base',
                'product_family' => 'execute',
                'price' => 29,
                'sort_order' => 60,
                'metadata' => [
                    'supports_additional_accounts' => false,
                ],
            ],
            'BISMILLAH1_BOT_CUSTOM_STRATEGY_ADDON' => [
                'name' => 'Bismel1 Bot - Custom Strategy',
                'plan_type' => 'addon',
                'product_family' => 'strategy_addon',
                'price' => 97,
                'sort_order' => 110,
                'metadata' => [],
            ],
            'BISMILLAH1_BOT_ADDITIONAL_ACCOUNT_ADDON' => [
                'name' => 'Bismel1 Bot - Additional Account',
                'plan_type' => 'addon',
                'product_family' => 'account_addon',
                'price' => 29,
                'sort_order' => 120,
                'metadata' => [],
            ],
            'BISMILLAH1_BOT_SPEED_EXECUTE' => [
                'name' => 'Speed Executor',
                'plan_type' => 'base',
                'product_family' => 'testing',
                'price' => 10,
                'sort_order' => 130,
                'metadata' => [
                    'testing_plan' => true,
                    'supports_additional_accounts' => false,
                ],
            ],
        ];
    }
}
