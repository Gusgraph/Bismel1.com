<?php
// اعوز بالله من الشياطين و ان يحضرون بسم الله الرحمن الرحيم الله لا إله إلا هو الحي القيوم
// Bismillahi ar-Rahmani ar-Rahim Audhu billahi min ash-shayatin wa an yahdurun Bismillah ar-Rahman ar-Rahim Allah la ilaha illa huwa al-hayy al-qayyum. Tamsa Allahu ala ayunihim
// version: x
// ======================================================
// - App Name: gusgraph-trading
// - Gusgraph LLC -
// - Author: Gus Kazem
// - https://Gusgraph.com
// - File Path: database/migrations/2026_03_29_040000_prepare_stripe_confirmed_subscription_state.php
// ======================================================

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('accounts', function (Blueprint $table) {
            $table->string('stripe_customer_id')->nullable()->after('owner_user_id')->index();
        });

        Schema::table('subscription_plans', function (Blueprint $table) {
            $table->string('stripe_product_id')->nullable()->after('stripe_lookup_key');
            $table->string('stripe_price_id')->nullable()->after('stripe_product_id');
        });

        Schema::table('subscriptions', function (Blueprint $table) {
            $table->string('stripe_subscription_id')->nullable()->after('subscription_plan_id')->index();
            $table->string('stripe_price_id')->nullable()->after('stripe_subscription_id');
            $table->string('stripe_status')->nullable()->after('status');
            $table->string('last_stripe_event_id')->nullable()->after('stripe_status');
            $table->string('last_stripe_event_type')->nullable()->after('last_stripe_event_id');
            $table->timestamp('stripe_confirmed_at')->nullable()->after('last_stripe_event_type');
            $table->timestamp('cancel_at')->nullable()->after('ends_at');
            $table->boolean('cancel_at_period_end')->default(false)->after('cancel_at');
            $table->json('metadata')->nullable()->after('cancel_at_period_end');
        });

        Schema::table('invoices', function (Blueprint $table) {
            $table->string('stripe_invoice_id')->nullable()->after('subscription_id')->index();
            $table->string('stripe_payment_intent_id')->nullable()->after('stripe_invoice_id');
            $table->string('stripe_status')->nullable()->after('status');
            $table->string('last_stripe_event_id')->nullable()->after('stripe_status');
            $table->timestamp('stripe_confirmed_at')->nullable()->after('last_stripe_event_id');
            $table->json('metadata')->nullable()->after('stripe_confirmed_at');
        });

        Schema::create('subscription_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('subscription_id')->constrained()->cascadeOnDelete();
            $table->foreignId('subscription_plan_id')->nullable()->constrained()->nullOnDelete();
            $table->string('stripe_subscription_item_id')->nullable()->index();
            $table->string('stripe_price_id')->nullable()->index();
            $table->string('plan_type')->default('base');
            $table->string('status')->default('pending');
            $table->unsignedInteger('quantity')->default(1);
            $table->timestamp('starts_at')->nullable();
            $table->timestamp('ends_at')->nullable();
            $table->json('metadata')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('subscription_items');

        Schema::table('invoices', function (Blueprint $table) {
            $table->dropColumn([
                'stripe_invoice_id',
                'stripe_payment_intent_id',
                'stripe_status',
                'last_stripe_event_id',
                'stripe_confirmed_at',
                'metadata',
            ]);
        });

        Schema::table('subscriptions', function (Blueprint $table) {
            $table->dropColumn([
                'stripe_subscription_id',
                'stripe_price_id',
                'stripe_status',
                'last_stripe_event_id',
                'last_stripe_event_type',
                'stripe_confirmed_at',
                'cancel_at',
                'cancel_at_period_end',
                'metadata',
            ]);
        });

        Schema::table('subscription_plans', function (Blueprint $table) {
            $table->dropColumn([
                'stripe_product_id',
                'stripe_price_id',
            ]);
        });

        Schema::table('accounts', function (Blueprint $table) {
            $table->dropColumn('stripe_customer_id');
        });
    }
};
