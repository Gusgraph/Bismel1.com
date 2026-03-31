<?php
// اعوز بالله من الشياطين و ان يحضرون بسم الله الرحمن الرحيم الله لا إله إلا هو الحي القيوم
// Bismillahi ar-Rahmani ar-Rahim Audhu billahi min ash-shayatin wa an yahdurun Bismillah ar-Rahman ar-Rahim Allah la ilaha illa huwa al-hayy al-qayyum. Tamsa Allahu ala ayunihim
// version: x
// ======================================================
// - App Name: gusgraph-trading
// - Gusgraph LLC -
// - Author: Gus Kazem
// - https://Gusgraph.com
// - File Path: database/migrations/2026_03_29_080000_add_alpaca_market_data_bars_table.php
// ======================================================

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('alpaca_bars', function (Blueprint $table) {
            $table->id();
            $table->foreignId('account_id')->constrained()->cascadeOnDelete();
            $table->foreignId('alpaca_account_id')->constrained()->cascadeOnDelete();
            $table->foreignId('broker_connection_id')->nullable()->constrained()->nullOnDelete();
            $table->string('symbol');
            $table->string('timeframe', 8);
            $table->string('feed', 8)->default('iex');
            $table->timestamp('starts_at');
            $table->timestamp('ends_at')->nullable();
            $table->decimal('open', 18, 6);
            $table->decimal('high', 18, 6);
            $table->decimal('low', 18, 6);
            $table->decimal('close', 18, 6);
            $table->unsignedBigInteger('volume')->nullable();
            $table->unsignedInteger('trade_count')->nullable();
            $table->decimal('vwap', 18, 6)->nullable();
            $table->timestamp('fetched_at')->nullable();
            $table->timestamps();

            $table->unique(['alpaca_account_id', 'symbol', 'timeframe', 'feed', 'starts_at'], 'alpaca_bars_unique_snapshot');
            $table->index(['account_id', 'symbol', 'timeframe']);
            $table->index(['alpaca_account_id', 'timeframe', 'starts_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('alpaca_bars');
    }
};
