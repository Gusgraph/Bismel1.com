<?php
// اعوز بالله من الشياطين و ان يحضرون بسم الله الرحمن الرحيم الله لا إله إلا هو الحي القيوم
// Bismillahi ar-Rahmani ar-Rahim Audhu billahi min ash-shayatin wa an yahdurun Bismillah ar-Rahman ar-Rahim Allah la ilaha illa huwa al-hayy al-qayyum. Tamsa Allahu ala ayunihim
// version: x
// ======================================================
// - App Name: gusgraph-trading
// - Gusgraph LLC -
// - Author: Gus Kazem
// - https://Gusgraph.com
// - File Path: database/migrations/2026_03_29_060000_add_affiliate_discount_and_commission_tracking.php
// ======================================================

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('billing_checkout_sessions', function (Blueprint $table) {
            $table->boolean('uses_affiliate_pricing')->default(false)->after('status');
        });

        Schema::table('subscriptions', function (Blueprint $table) {
            $table->boolean('uses_affiliate_pricing')->default(false)->after('referral_code');
        });

        Schema::create('affiliate_commissions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('referral_attribution_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('subscription_id')->constrained()->cascadeOnDelete();
            $table->foreignId('invoice_id')->nullable()->constrained()->nullOnDelete();
            $table->string('affiliate_username')->index();
            $table->string('commission_status')->default('earned')->index();
            $table->decimal('commission_rate', 6, 4)->default(0);
            $table->decimal('commission_base_amount', 10, 2)->default(0);
            $table->decimal('commission_amount', 10, 2)->default(0);
            $table->string('currency', 3)->default('USD');
            $table->string('stripe_invoice_id')->nullable()->index();
            $table->timestamp('earned_at')->nullable();
            $table->json('metadata')->nullable();
            $table->timestamps();

            $table->unique('subscription_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('affiliate_commissions');

        Schema::table('subscriptions', function (Blueprint $table) {
            $table->dropColumn('uses_affiliate_pricing');
        });

        Schema::table('billing_checkout_sessions', function (Blueprint $table) {
            $table->dropColumn('uses_affiliate_pricing');
        });
    }
};
