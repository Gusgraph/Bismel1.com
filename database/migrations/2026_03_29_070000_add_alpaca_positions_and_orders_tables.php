<?php
// اعوز بالله من الشياطين و ان يحضرون بسم الله الرحمن الرحيم الله لا إله إلا هو الحي القيوم
// Bismillahi ar-Rahmani ar-Rahim Audhu billahi min ash-shayatin wa an yahdurun Bismillah ar-Rahman ar-Rahim Allah la ilaha illa huwa al-hayy al-qayyum. Tamsa Allahu ala ayunihim
// version: x
// ======================================================
// - App Name: gusgraph-trading
// - Gusgraph LLC -
// - Author: Gus Kazem
// - https://Gusgraph.com
// - File Path: database/migrations/2026_03_29_070000_add_alpaca_positions_and_orders_tables.php
// ======================================================

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('alpaca_positions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('account_id')->constrained()->cascadeOnDelete();
            $table->foreignId('alpaca_account_id')->constrained()->cascadeOnDelete();
            $table->foreignId('broker_connection_id')->nullable()->constrained()->nullOnDelete();
            $table->string('symbol');
            $table->string('alpaca_asset_id')->nullable()->index();
            $table->string('asset_class')->default('equity');
            $table->string('exchange')->nullable();
            $table->string('side')->nullable();
            $table->decimal('qty', 18, 6)->nullable();
            $table->decimal('qty_available', 18, 6)->nullable();
            $table->decimal('market_value', 18, 2)->nullable();
            $table->decimal('cost_basis', 18, 2)->nullable();
            $table->decimal('current_price', 18, 6)->nullable();
            $table->decimal('avg_entry_price', 18, 6)->nullable();
            $table->decimal('unrealized_pl', 18, 2)->nullable();
            $table->decimal('unrealized_plpc', 12, 6)->nullable();
            $table->decimal('change_today', 12, 6)->nullable();
            $table->timestamp('synced_at')->nullable();
            $table->timestamps();

            $table->unique(['alpaca_account_id', 'symbol']);
            $table->index(['account_id', 'symbol']);
        });

        Schema::create('alpaca_orders', function (Blueprint $table) {
            $table->id();
            $table->foreignId('account_id')->constrained()->cascadeOnDelete();
            $table->foreignId('alpaca_account_id')->constrained()->cascadeOnDelete();
            $table->foreignId('broker_connection_id')->nullable()->constrained()->nullOnDelete();
            $table->string('alpaca_order_id')->unique();
            $table->string('client_order_id')->nullable()->index();
            $table->string('alpaca_asset_id')->nullable()->index();
            $table->string('symbol');
            $table->string('asset_class')->default('equity');
            $table->string('side')->nullable();
            $table->string('order_type')->nullable();
            $table->string('time_in_force')->nullable();
            $table->string('status')->nullable()->index();
            $table->decimal('qty', 18, 6)->nullable();
            $table->decimal('filled_qty', 18, 6)->nullable();
            $table->decimal('notional', 18, 2)->nullable();
            $table->decimal('limit_price', 18, 6)->nullable();
            $table->decimal('stop_price', 18, 6)->nullable();
            $table->decimal('filled_avg_price', 18, 6)->nullable();
            $table->timestamp('submitted_at')->nullable();
            $table->timestamp('filled_at')->nullable();
            $table->timestamp('canceled_at')->nullable();
            $table->timestamp('expired_at')->nullable();
            $table->timestamp('failed_at')->nullable();
            $table->timestamp('synced_at')->nullable();
            $table->timestamps();

            $table->index(['account_id', 'symbol']);
            $table->index(['alpaca_account_id', 'submitted_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('alpaca_orders');
        Schema::dropIfExists('alpaca_positions');
    }
};
