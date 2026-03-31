<?php
// اعوز بالله من الشياطين و ان يحضرون بسم الله الرحمن الرحيم الله لا إله إلا هو الحي القيوم
// Bismillahi ar-Rahmani ar-Rahim Audhu billahi min ash-shayatin wa an yahdurun Bismillah ar-Rahman ar-Rahim Allah la ilaha illa huwa al-hayy al-qayyum. Tamsa Allahu ala ayunihim
// version: x
// ======================================================
// - App Name: bismel1.com
// - Gusgraph LLC -
// - Author: Gus Kazem
// - https://Gusgraph.com
// - File Path: database/migrations/2026_03_30_120000_normalize_bismel1_subscription_plan_catalog.php
// ======================================================

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        $now = now();

        foreach ($this->catalogDefinitions() as $code => $definition) {
            $existing = DB::table('subscription_plans')->where('code', $code)->first();
            $metadata = $this->mergedMetadata($existing?->metadata ?? null, $definition['metadata'] ?? []);

            $payload = [
                'name' => $definition['name'],
                'plan_type' => $definition['plan_type'],
                'product_family' => $definition['product_family'],
                'status' => 'active',
                'price' => $definition['price'],
                'currency' => 'USD',
                'interval' => 'monthly',
                'billing_model' => 'monthly',
                'sort_order' => $definition['sort_order'],
                'stripe_lookup_key' => $definition['stripe_lookup_key'],
                'metadata' => $metadata === [] ? null : json_encode($metadata, JSON_THROW_ON_ERROR),
                'updated_at' => $now,
            ];

            $priceId = $this->configuredPriceId($definition['price_env']);

            if ($priceId !== null) {
                $payload['stripe_price_id'] = $priceId;
            }

            if ($existing) {
                DB::table('subscription_plans')->where('id', $existing->id)->update($payload);

                continue;
            }

            DB::table('subscription_plans')->insert(array_merge($payload, [
                'code' => $code,
                'created_at' => $now,
            ]));
        }

        DB::table('subscription_plans')
            ->whereIn('code', ['STARTER_LOCAL', 'PRO_LOCAL'])
            ->update([
                'status' => 'inactive',
                'updated_at' => $now,
            ]);
    }

    public function down(): void
    {
        // Production catalog normalization is intentionally one-way in this pass.
    }

    protected function catalogDefinitions(): array
    {
        return [
            'BISMILLAH_AI_SCANNER' => [
                'name' => 'Bismel1 AI - Scanner (News catalyst)',
                'plan_type' => 'base',
                'product_family' => 'scanner',
                'price' => 49,
                'sort_order' => 11,
                'stripe_lookup_key' => 'bismillah-ai-scanner',
                'price_env' => 'STRIPE_PRICE_BISMILLAH_AI_SCANNER',
                'metadata' => [
                    'supports_additional_accounts' => false,
                ],
            ],
            'BISMILLAH1_BOT_OVERNIGHT_EQUITIES' => [
                'name' => 'Bismel1 Bot - Overnight Equities',
                'plan_type' => 'base',
                'product_family' => 'stocks',
                'price' => 97,
                'sort_order' => 19,
                'stripe_lookup_key' => 'bismillah1-bot-overnight-equities',
                'price_env' => 'STRIPE_PRICE_BISMILLAH1_BOT_OVERNIGHT_EQUITIES',
                'metadata' => [
                    'supports_additional_accounts' => true,
                ],
            ],
            'BISMILLAH1_BOT_OPTIONS' => [
                'name' => 'Bismel1 Bot - Options',
                'plan_type' => 'base',
                'product_family' => 'options',
                'price' => 97,
                'sort_order' => 27,
                'stripe_lookup_key' => 'bismillah1-bot-options',
                'price_env' => 'STRIPE_PRICE_BISMILLAH1_BOT_OPTIONS',
                'metadata' => [
                    'supports_additional_accounts' => true,
                ],
            ],
            'BISMILLAH1_BOT_CRYPTO' => [
                'name' => 'Bismel1 Bot - Crypto',
                'plan_type' => 'base',
                'product_family' => 'crypto',
                'price' => 97,
                'sort_order' => 35,
                'stripe_lookup_key' => 'bismillah1-bot-crypto',
                'price_env' => 'STRIPE_PRICE_BISMILLAH1_BOT_CRYPTO',
                'metadata' => [
                    'supports_additional_accounts' => true,
                ],
            ],
            'BISMILLAH1_BOT_PRIME' => [
                'name' => 'Bismel1 Stocks Bot - AI Prime',
                'plan_type' => 'base',
                'product_family' => 'prime',
                'price' => 97,
                'sort_order' => 43,
                'stripe_lookup_key' => 'bismillah1-bot-prime',
                'price_env' => 'STRIPE_PRICE_BISMILLAH1_BOT_PRIME',
                'metadata' => [
                    'supports_additional_accounts' => true,
                ],
            ],
            'BISMILLAH1_BOT_EXECUTE_BASIC' => [
                'name' => 'Bismel1 Stocks Bot - Execute',
                'plan_type' => 'base',
                'product_family' => 'execute',
                'price' => 29,
                'sort_order' => 51,
                'stripe_lookup_key' => 'bismillah1-bot-execute-basic',
                'price_env' => 'STRIPE_PRICE_BISMILLAH1_BOT_EXECUTE_BASIC',
                'metadata' => [
                    'supports_additional_accounts' => false,
                ],
            ],
            'BISMILLAH1_BOT_CUSTOM_STRATEGY_ADDON' => [
                'name' => 'Bismel1 Bot - Custom Strategy (Add-on)',
                'plan_type' => 'addon',
                'product_family' => 'strategy_addon',
                'price' => 97,
                'sort_order' => 111,
                'stripe_lookup_key' => 'bismillah1-bot-custom-strategy-addon',
                'price_env' => 'STRIPE_PRICE_BISMILLAH1_BOT_CUSTOM_STRATEGY_ADDON',
                'metadata' => [],
            ],
            'BISMILLAH1_BOT_ADDITIONAL_ACCOUNT_ADDON' => [
                'name' => 'Bismel1 Bot - Additional Account (Add-On)',
                'plan_type' => 'addon',
                'product_family' => 'account_addon',
                'price' => 29,
                'sort_order' => 119,
                'stripe_lookup_key' => 'bismillah1-bot-additional-account-addon',
                'price_env' => 'STRIPE_PRICE_BISMILLAH1_BOT_ADDITIONAL_ACCOUNT_ADDON',
                'metadata' => [],
            ],
            'BISMILLAH1_BOT_SPEED_EXECUTE' => [
                'name' => 'Speed Executor',
                'plan_type' => 'base',
                'product_family' => 'testing',
                'price' => 10,
                'sort_order' => 131,
                'stripe_lookup_key' => 'bismillah1-bot-speed-execute',
                'price_env' => 'STRIPE_PRICE_BISMILLAH1_BOT_SPEED_EXECUTE',
                'metadata' => [
                    'testing_plan' => true,
                    'supports_additional_accounts' => false,
                ],
            ],
        ];
    }

    protected function configuredPriceId(string $envKey): ?string
    {
        $value = env($envKey);

        if (! is_string($value) || trim($value) === '') {
            return null;
        }

        return trim($value);
    }

    protected function mergedMetadata(mixed $existingMetadata, array $overrides): array
    {
        $decoded = [];

        if (is_string($existingMetadata) && trim($existingMetadata) !== '') {
            $decoded = json_decode($existingMetadata, true);
        } elseif (is_array($existingMetadata)) {
            $decoded = $existingMetadata;
        }

        return array_merge(is_array($decoded) ? $decoded : [], $overrides);
    }
};
