<?php
// اعوز بالله من الشياطين و ان يحضرون بسم الله الرحمن الرحيم الله لا إله إلا هو الحي القيوم
// Bismillahi ar-Rahmani ar-Rahim Audhu billahi min ash-shayatin wa an yahdurun Bismillah ar-Rahman ar-Rahim Allah la ilaha illa huwa al-hayy al-qayyum. Tamsa Allahu ala ayunihim
// version: x
// ======================================================
// - App Name: gusgraph-trading
// - Gusgraph LLC -
// - Author: Gus Kazem
// - https://Gusgraph.com
// - File Path: database/migrations/2026_03_29_050000_add_checkout_and_referral_tracking_to_billing.php
// ======================================================

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('referral_attributions', function (Blueprint $table) {
            $table->id();
            $table->string('session_id')->nullable()->index();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('account_id')->nullable()->constrained()->nullOnDelete();
            $table->string('referral_code')->index();
            $table->string('landing_path')->nullable();
            $table->text('landing_url')->nullable();
            $table->string('checkout_session_id')->nullable()->index();
            $table->string('stripe_customer_id')->nullable()->index();
            $table->string('stripe_subscription_id')->nullable()->index();
            $table->timestamp('first_seen_at')->nullable();
            $table->timestamp('last_seen_at')->nullable();
            $table->timestamp('checkout_started_at')->nullable();
            $table->timestamp('converted_at')->nullable();
            $table->json('metadata')->nullable();
            $table->timestamps();
        });

        Schema::create('billing_checkout_sessions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('account_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('subscription_plan_id')->constrained()->cascadeOnDelete();
            $table->foreignId('referral_attribution_id')->nullable()->constrained()->nullOnDelete();
            $table->string('referral_code')->nullable()->index();
            $table->string('stripe_checkout_session_id')->nullable()->unique();
            $table->string('stripe_customer_id')->nullable()->index();
            $table->string('status')->default('pending')->index();
            $table->json('selected_add_on_plan_codes')->nullable();
            $table->text('success_url')->nullable();
            $table->text('cancel_url')->nullable();
            $table->json('metadata')->nullable();
            $table->timestamps();
        });

        Schema::table('subscriptions', function (Blueprint $table) {
            $table->foreignId('referral_attribution_id')->nullable()->after('cancel_at_period_end')->constrained()->nullOnDelete();
            $table->string('referral_code')->nullable()->after('referral_attribution_id')->index();
        });
    }

    public function down(): void
    {
        Schema::table('subscriptions', function (Blueprint $table) {
            $table->dropConstrainedForeignId('referral_attribution_id');
            $table->dropColumn('referral_code');
        });

        Schema::dropIfExists('billing_checkout_sessions');
        Schema::dropIfExists('referral_attributions');
    }
};
