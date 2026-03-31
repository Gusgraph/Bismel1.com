<?php
// اعوز بالله من الشياطين و ان يحضرون بسم الله الرحمن الرحيم الله لا إله إلا هو الحي القيوم
// Bismillahi ar-Rahmani ar-Rahim Audhu billahi min ash-shayatin wa an yahdurun Bismillah ar-Rahman ar-Rahim Allah la ilaha illa huwa al-hayy al-qayyum. Tamsa Allahu ala ayunihim
// version: x
// ======================================================
// - App Name: gusgraph-trading
// - Gusgraph LLC -
// - Author: Gus Kazem
// - https://Gusgraph.com
// - File Path: database/migrations/2026_03_29_030000_align_alpaca_multi_account_and_subscription_packages.php
// ======================================================

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasColumn('alpaca_accounts', 'is_primary')) {
            Schema::table('alpaca_accounts', function (Blueprint $table) {
                $table->boolean('is_primary')->default(false)->after('trade_stream_status');
            });
        }

        if (! Schema::hasColumn('alpaca_accounts', 'is_active')) {
            Schema::table('alpaca_accounts', function (Blueprint $table) {
                $table->boolean('is_active')->default(true)->after('is_primary');
            });
        }

        if (! Schema::hasColumn('subscription_plans', 'plan_type')) {
            Schema::table('subscription_plans', function (Blueprint $table) {
                $table->string('plan_type')->default('base')->after('code');
            });
        }

        if (! Schema::hasColumn('subscription_plans', 'product_family')) {
            Schema::table('subscription_plans', function (Blueprint $table) {
                $table->string('product_family')->nullable()->after('plan_type');
            });
        }

        if (! Schema::hasColumn('subscription_plans', 'billing_model')) {
            Schema::table('subscription_plans', function (Blueprint $table) {
                $table->string('billing_model')->default('monthly')->after('interval');
            });
        }

        if (! Schema::hasColumn('subscription_plans', 'sort_order')) {
            Schema::table('subscription_plans', function (Blueprint $table) {
                $table->unsignedInteger('sort_order')->default(0)->after('billing_model');
            });
        }

        if (! Schema::hasColumn('subscription_plans', 'stripe_lookup_key')) {
            Schema::table('subscription_plans', function (Blueprint $table) {
                $table->string('stripe_lookup_key')->nullable()->after('sort_order');
            });
        }

        if (! Schema::hasColumn('subscription_plans', 'metadata')) {
            Schema::table('subscription_plans', function (Blueprint $table) {
                $table->json('metadata')->nullable()->after('stripe_lookup_key');
            });
        }

        $this->syncPackages();
    }

    public function down(): void
    {
        $subscriptionPlanColumns = array_values(array_filter([
            Schema::hasColumn('subscription_plans', 'plan_type') ? 'plan_type' : null,
            Schema::hasColumn('subscription_plans', 'product_family') ? 'product_family' : null,
            Schema::hasColumn('subscription_plans', 'billing_model') ? 'billing_model' : null,
            Schema::hasColumn('subscription_plans', 'sort_order') ? 'sort_order' : null,
            Schema::hasColumn('subscription_plans', 'stripe_lookup_key') ? 'stripe_lookup_key' : null,
            Schema::hasColumn('subscription_plans', 'metadata') ? 'metadata' : null,
        ]));

        if ($subscriptionPlanColumns !== []) {
            Schema::table('subscription_plans', function (Blueprint $table) use ($subscriptionPlanColumns) {
                $table->dropColumn($subscriptionPlanColumns);
            });
        }

        $alpacaColumns = array_values(array_filter([
            Schema::hasColumn('alpaca_accounts', 'is_primary') ? 'is_primary' : null,
            Schema::hasColumn('alpaca_accounts', 'is_active') ? 'is_active' : null,
        ]));

        if ($alpacaColumns !== []) {
            Schema::table('alpaca_accounts', function (Blueprint $table) use ($alpacaColumns) {
                $table->dropColumn($alpacaColumns);
            });
        }
    }

    protected function syncPackages(): void
    {
        $now = now();

        foreach ($this->packages() as $package) {
            $record = array_merge($package, [
                'status' => 'active',
                'currency' => 'USD',
                'interval' => 'monthly',
                'created_at' => $now,
                'updated_at' => $now,
            ]);

            foreach (['stripe_product_id', 'stripe_price_id'] as $column) {
                if (! Schema::hasColumn('subscription_plans', $column)) {
                    unset($record[$column]);
                }
            }

            DB::table('subscription_plans')->updateOrInsert(
                ['code' => $package['code']],
                $record
            );
        }
    }

    protected function packages(): array
    {
        return [
            [
                'name' => 'Bismel1 AI - Scanner (News catalyst)',
                'code' => 'BISMILLAH_AI_SCANNER',
                'plan_type' => 'base',
                'product_family' => 'scanner',
                'price' => 49,
                'billing_model' => 'monthly',
                'sort_order' => 10,
                'stripe_lookup_key' => 'bismillah-ai-scanner',
                'stripe_product_id' => null,
                'stripe_price_id' => null,
                'metadata' => json_encode([
                    'supports_execution' => false,
                    'supports_additional_accounts' => false,
                ], JSON_THROW_ON_ERROR),
            ],
            [
                'name' => 'Bismel1 Bot - Overnight equities',
                'code' => 'BISMILLAH1_BOT_OVERNIGHT_EQUITIES',
                'plan_type' => 'base',
                'product_family' => 'bot',
                'price' => 79,
                'billing_model' => 'monthly',
                'sort_order' => 20,
                'stripe_lookup_key' => 'bismillah1-bot-overnight-equities',
                'stripe_product_id' => null,
                'stripe_price_id' => null,
                'metadata' => json_encode([
                    'supports_execution' => false,
                    'supports_additional_accounts' => true,
                ], JSON_THROW_ON_ERROR),
            ],
            [
                'name' => 'Bismel1 Bot - Options',
                'code' => 'BISMILLAH1_BOT_OPTIONS',
                'plan_type' => 'base',
                'product_family' => 'bot',
                'price' => 79,
                'billing_model' => 'monthly',
                'sort_order' => 30,
                'stripe_lookup_key' => 'bismillah1-bot-options',
                'stripe_product_id' => null,
                'stripe_price_id' => null,
                'metadata' => json_encode([
                    'supports_execution' => false,
                    'supports_additional_accounts' => true,
                ], JSON_THROW_ON_ERROR),
            ],
            [
                'name' => 'Bismel1 Bot - Crypto',
                'code' => 'BISMILLAH1_BOT_CRYPTO',
                'plan_type' => 'base',
                'product_family' => 'bot',
                'price' => 79,
                'billing_model' => 'monthly',
                'sort_order' => 40,
                'stripe_lookup_key' => 'bismillah1-bot-crypto',
                'stripe_product_id' => null,
                'stripe_price_id' => null,
                'metadata' => json_encode([
                    'supports_execution' => false,
                    'supports_additional_accounts' => true,
                ], JSON_THROW_ON_ERROR),
            ],
            [
                'name' => 'Bismel1 Bot - Prime',
                'code' => 'BISMILLAH1_BOT_PRIME',
                'plan_type' => 'base',
                'product_family' => 'prime',
                'price' => 97,
                'billing_model' => 'monthly',
                'sort_order' => 50,
                'stripe_lookup_key' => 'bismillah1-bot-prime',
                'stripe_product_id' => null,
                'stripe_price_id' => null,
                'metadata' => json_encode([
                    'supports_execution' => true,
                    'supports_additional_accounts' => true,
                ], JSON_THROW_ON_ERROR),
            ],
            [
                'name' => 'Bismel1 Bot - Custom Strategy (Add-on)',
                'code' => 'BISMILLAH1_BOT_CUSTOM_STRATEGY_ADDON',
                'plan_type' => 'addon',
                'product_family' => 'strategy_addon',
                'price' => 97,
                'billing_model' => 'monthly',
                'sort_order' => 110,
                'stripe_lookup_key' => 'bismillah1-bot-custom-strategy-addon',
                'stripe_product_id' => null,
                'stripe_price_id' => null,
                'metadata' => json_encode([
                    'supports_execution' => false,
                    'supports_additional_accounts' => false,
                ], JSON_THROW_ON_ERROR),
            ],
            [
                'name' => 'Bismel1 Bot - Additional Account (Add-On)',
                'code' => 'BISMILLAH1_BOT_ADDITIONAL_ACCOUNT_ADDON',
                'plan_type' => 'addon',
                'product_family' => 'account_addon',
                'price' => 29,
                'billing_model' => 'monthly',
                'sort_order' => 120,
                'stripe_lookup_key' => 'bismillah1-bot-additional-account-addon',
                'stripe_product_id' => null,
                'stripe_price_id' => null,
                'metadata' => json_encode([
                    'supports_execution' => false,
                    'supports_additional_accounts' => true,
                ], JSON_THROW_ON_ERROR),
            ],
            [
                'name' => 'Bismel1 Bot - Execute (any basic strategy)',
                'code' => 'BISMILLAH1_BOT_EXECUTE_BASIC',
                'plan_type' => 'base',
                'product_family' => 'execution_addon',
                'price' => 29,
                'billing_model' => 'monthly',
                'sort_order' => 60,
                'stripe_lookup_key' => 'bismillah1-bot-execute-basic',
                'stripe_product_id' => null,
                'stripe_price_id' => null,
                'metadata' => json_encode([
                    'supports_execution' => true,
                    'supports_additional_accounts' => false,
                ], JSON_THROW_ON_ERROR),
            ],
        ];
    }
};
