<?php
// اعوز بالله من الشياطين و ان يحضرون بسم الله الرحمن الرحيم الله لا إله إلا هو الحي القيوم
// Bismillahi ar-Rahmani ar-Rahim Audhu billahi min ash-shayatin wa an yahdurun Bismillah ar-Rahman ar-Rahim Allah la ilaha illa huwa al-hayy al-qayyum. Tamsa Allahu ala ayunihim
// version: x
// ======================================================
// - App Name: gusgraph-trading
// - Gusgraph LLC -
// - Author: Gus Kazem
// - https://Gusgraph.com
// - File Path: database/migrations/2026_03_29_120000_add_position_manager_state_to_alpaca_positions_table.php
// ======================================================

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('alpaca_positions', function (Blueprint $table) {
            $table->foreignId('strategy_profile_id')->nullable()->after('broker_connection_id')->constrained()->nullOnDelete();
            $table->foreignId('last_signal_id')->nullable()->after('strategy_profile_id')->constrained('signals')->nullOnDelete();
            $table->foreignId('last_bot_run_id')->nullable()->after('last_signal_id')->constrained('bot_runs')->nullOnDelete();
            $table->decimal('high_water_price', 18, 6)->nullable()->after('change_today');
            $table->string('management_state')->nullable()->after('high_water_price');
            $table->string('status_summary')->nullable()->after('management_state');
            $table->timestamp('last_managed_at')->nullable()->after('status_summary');
        });
    }

    public function down(): void
    {
        Schema::table('alpaca_positions', function (Blueprint $table) {
            $table->dropConstrainedForeignId('strategy_profile_id');
            $table->dropConstrainedForeignId('last_signal_id');
            $table->dropConstrainedForeignId('last_bot_run_id');
            $table->dropColumn(['high_water_price', 'management_state', 'status_summary', 'last_managed_at']);
        });
    }
};
