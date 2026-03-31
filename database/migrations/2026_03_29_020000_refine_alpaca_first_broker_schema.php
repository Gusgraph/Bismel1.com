<?php
// اعوز بالله من الشياطين و ان يحضرون بسم الله الرحمن الرحيم الله لا إله إلا هو الحي القيوم
// Bismillahi ar-Rahmani ar-Rahim Audhu billahi min ash-shayatin wa an yahdurun Bismillah ar-Rahman ar-Rahim Allah la ilaha illa huwa al-hayy al-qayyum. Tamsa Allahu ala ayunihim
// version: x
// ======================================================
// - App Name: gusgraph-trading
// - Gusgraph LLC -
// - Author: Gus Kazem
// - https://Gusgraph.com
// - File Path: database/migrations/2026_03_29_020000_refine_alpaca_first_broker_schema.php
// ======================================================

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('broker_connections', function (Blueprint $table) {
            $table->foreignId('managed_by_user_id')->nullable()->after('account_id')->constrained('users')->nullOnDelete();
        });

        Schema::table('broker_credentials', function (Blueprint $table) {
            $table->string('provider')->default('alpaca')->after('label');
            $table->string('environment')->default('paper')->after('status');
            $table->string('access_mode')->default('read_only')->after('environment');
            $table->string('key_last_four', 16)->nullable()->after('credential_payload');
            $table->string('secret_hint', 16)->nullable()->after('key_last_four');
            $table->boolean('is_encrypted')->default(true)->after('secret_hint');
        });

        Schema::table('alpaca_accounts', function (Blueprint $table) {
            $table->foreignId('broker_credential_id')->nullable()->after('broker_connection_id')->constrained('broker_credentials')->nullOnDelete();
            $table->string('data_feed')->default('iex')->after('environment');
            $table->string('sync_status')->default('pending')->after('status');
            $table->string('trade_stream_status')->default('not_ready')->after('sync_status');
            $table->timestamp('last_account_sync_at')->nullable()->after('last_synced_at');
            $table->timestamp('last_positions_sync_at')->nullable()->after('last_account_sync_at');
            $table->timestamp('last_orders_sync_at')->nullable()->after('last_positions_sync_at');
        });
    }

    public function down(): void
    {
        Schema::table('alpaca_accounts', function (Blueprint $table) {
            $table->dropConstrainedForeignId('broker_credential_id');
            $table->dropColumn([
                'data_feed',
                'sync_status',
                'trade_stream_status',
                'last_account_sync_at',
                'last_positions_sync_at',
                'last_orders_sync_at',
            ]);
        });

        Schema::table('broker_credentials', function (Blueprint $table) {
            $table->dropColumn([
                'provider',
                'environment',
                'access_mode',
                'key_last_four',
                'secret_hint',
                'is_encrypted',
            ]);
        });

        Schema::table('broker_connections', function (Blueprint $table) {
            $table->dropConstrainedForeignId('managed_by_user_id');
        });
    }
};
