<?php
// اعوز بالله من الشياطين و ان يحضرون بسم الله الرحمن الرحيم الله لا إله إلا هو الحي القيوم
// Bismillahi ar-Rahmani ar-Rahim Audhu billahi min ash-shayatin wa an yahdurun Bismillah ar-Rahman ar-Rahim Allah la ilaha illa huwa al-hayy al-qayyum. Tamsa Allahu ala ayunihim
// version: x
// ======================================================
// - App Name: gusgraph-trading
// - Gusgraph LLC -
// - Author: Gus Kazem
// - https://Gusgraph.com
// - File Path: database/migrations/2026_03_29_110000_add_execution_linkage_to_alpaca_orders_table.php
// ======================================================

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('alpaca_orders', function (Blueprint $table) {
            $table->foreignId('strategy_profile_id')->nullable()->after('broker_connection_id')->constrained()->nullOnDelete();
            $table->foreignId('signal_id')->nullable()->after('strategy_profile_id')->constrained()->nullOnDelete();
            $table->foreignId('bot_run_id')->nullable()->after('signal_id')->constrained()->nullOnDelete();
            $table->string('request_action')->nullable()->after('time_in_force');
            $table->string('status_summary')->nullable()->after('status');
            $table->text('broker_message')->nullable()->after('status_summary');
        });
    }

    public function down(): void
    {
        Schema::table('alpaca_orders', function (Blueprint $table) {
            $table->dropConstrainedForeignId('strategy_profile_id');
            $table->dropConstrainedForeignId('signal_id');
            $table->dropConstrainedForeignId('bot_run_id');
            $table->dropColumn(['request_action', 'status_summary', 'broker_message']);
        });
    }
};
