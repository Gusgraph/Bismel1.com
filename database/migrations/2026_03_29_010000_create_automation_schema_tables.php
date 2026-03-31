<?php
// اعوز بالله من الشياطين و ان يحضرون بسم الله الرحمن الرحيم الله لا إله إلا هو الحي القيوم
// Bismillahi ar-Rahmani ar-Rahim Audhu billahi min ash-shayatin wa an yahdurun Bismillah ar-Rahman ar-Rahim Allah la ilaha illa huwa al-hayy al-qayyum. Tamsa Allahu ala ayunihim
// version: x
// ======================================================
// - App Name: gusgraph-trading
// - Gusgraph LLC -
// - Author: Gus Kazem
// - https://Gusgraph.com
// - File Path: database/migrations/2026_03_29_010000_create_automation_schema_tables.php
// ======================================================

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('alpaca_accounts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('account_id')->constrained()->cascadeOnDelete();
            $table->foreignId('broker_connection_id')->nullable()->constrained()->nullOnDelete();
            $table->string('name');
            $table->string('environment')->default('paper');
            $table->string('status')->default('pending');
            $table->string('alpaca_account_id')->nullable()->index();
            $table->string('account_number')->nullable();
            $table->decimal('buying_power', 14, 2)->nullable();
            $table->decimal('cash', 14, 2)->nullable();
            $table->decimal('equity', 14, 2)->nullable();
            $table->timestamp('last_synced_at')->nullable();
            $table->json('metadata')->nullable();
            $table->timestamps();
        });

        Schema::create('strategy_profiles', function (Blueprint $table) {
            $table->id();
            $table->foreignId('account_id')->constrained()->cascadeOnDelete();
            $table->string('name');
            $table->string('mode')->default('review_first');
            $table->string('timeframe')->default('mixed');
            $table->string('symbol_scope')->default('focused');
            $table->string('style')->default('balanced');
            $table->string('engine')->default('python');
            $table->boolean('is_active')->default(true);
            $table->json('settings')->nullable();
            $table->timestamps();
        });

        Schema::create('automation_settings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('account_id')->constrained()->cascadeOnDelete();
            $table->foreignId('strategy_profile_id')->nullable()->constrained()->nullOnDelete();
            $table->string('name');
            $table->boolean('ai_enabled')->default(false);
            $table->string('status')->default('draft');
            $table->string('scheduler_frequency')->nullable();
            $table->string('run_health')->default('idle');
            $table->string('risk_level')->default('conservative');
            $table->boolean('scanner_enabled')->default(false);
            $table->boolean('execution_enabled')->default(false);
            $table->timestamp('last_checked_at')->nullable();
            $table->json('settings')->nullable();
            $table->timestamps();
        });

        Schema::create('watchlists', function (Blueprint $table) {
            $table->id();
            $table->foreignId('account_id')->constrained()->cascadeOnDelete();
            $table->foreignId('strategy_profile_id')->nullable()->constrained()->nullOnDelete();
            $table->string('name');
            $table->string('scope')->default('workspace');
            $table->string('status')->default('active');
            $table->string('source')->default('manual');
            $table->json('metadata')->nullable();
            $table->timestamps();
        });

        Schema::create('watchlist_symbols', function (Blueprint $table) {
            $table->id();
            $table->foreignId('watchlist_id')->constrained()->cascadeOnDelete();
            $table->string('symbol');
            $table->string('asset_class')->default('equity');
            $table->string('status')->default('active');
            $table->timestamp('added_at')->nullable();
            $table->string('notes')->nullable();
            $table->timestamps();

            $table->unique(['watchlist_id', 'symbol']);
        });

        Schema::create('signals', function (Blueprint $table) {
            $table->id();
            $table->foreignId('account_id')->constrained()->cascadeOnDelete();
            $table->foreignId('strategy_profile_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('watchlist_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('watchlist_symbol_id')->nullable()->constrained()->nullOnDelete();
            $table->string('symbol');
            $table->string('timeframe')->nullable();
            $table->string('direction');
            $table->decimal('strength', 5, 2)->nullable();
            $table->string('status')->default('generated');
            $table->timestamp('generated_at')->nullable();
            $table->timestamp('expires_at')->nullable();
            $table->json('payload')->nullable();
            $table->timestamps();

            $table->index(['account_id', 'symbol']);
            $table->index(['status', 'generated_at']);
        });

        Schema::create('bot_runs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('account_id')->constrained()->cascadeOnDelete();
            $table->foreignId('strategy_profile_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('automation_setting_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('alpaca_account_id')->nullable()->constrained()->nullOnDelete();
            $table->string('run_type')->default('scan');
            $table->string('status')->default('queued');
            $table->string('risk_level')->default('conservative');
            $table->timestamp('started_at')->nullable();
            $table->timestamp('finished_at')->nullable();
            $table->unsignedInteger('runtime_seconds')->nullable();
            $table->json('summary')->nullable();
            $table->text('error_message')->nullable();
            $table->timestamps();

            $table->index(['account_id', 'status']);
            $table->index(['run_type', 'started_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('bot_runs');
        Schema::dropIfExists('signals');
        Schema::dropIfExists('watchlist_symbols');
        Schema::dropIfExists('watchlists');
        Schema::dropIfExists('automation_settings');
        Schema::dropIfExists('strategy_profiles');
        Schema::dropIfExists('alpaca_accounts');
    }
};
