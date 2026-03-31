<?php
// اعوز بالله من الشياطين و ان يحضرون بسم الله الرحمن الرحيم الله لا إله إلا هو الحي القيوم
// Bismillahi ar-Rahmani ar-Rahim Audhu billahi min ash-shayatin wa an yahdurun Bismillah ar-Rahman ar-Rahim Allah la ilaha illa huwa al-hayy al-qayyum. Tamsa Allahu ala ayunihim
// version: x
// ======================================================
// - App Name: gusgraph-trading
// - Gusgraph LLC -
// - Author: Gus Kazem
// - https://Gusgraph.com
// - File Path: tests/Feature/Automation/Bismel1ExecutionEngineTest.php
// ======================================================

namespace Tests\Feature\Automation;

use App\Models\Account;
use App\Models\AlpacaAccount;
use App\Models\AutomationSetting;
use App\Models\BotRun;
use App\Models\BrokerConnection;
use App\Models\BrokerCredential;
use App\Models\Signal;
use App\Models\StrategyProfile;
use App\Support\Automation\Bismel1ExecutionEngine;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Tests\Concerns\CreatesBismel1Entitlements;
use Tests\TestCase;

class Bismel1ExecutionEngineTest extends TestCase
{
    use RefreshDatabase;
    use CreatesBismel1Entitlements;

    public function test_it_submits_a_risk_approved_open_order_and_persists_safe_state(): void
    {
        Http::fake([
            'https://paper-api.alpaca.markets/v2/orders' => Http::response([
                'id' => 'alpaca-order-1',
                'client_order_id' => 'bismel1-open-1-test',
                'asset_id' => 'asset-aapl',
                'asset_class' => 'us_equity',
                'side' => 'buy',
                'type' => 'market',
                'time_in_force' => 'day',
                'status' => 'accepted',
                'qty' => '1.000000',
                'filled_qty' => '0.000000',
                'submitted_at' => '2026-03-29T10:00:00Z',
            ], 200),
        ]);

        [$account, $automationSetting, $signal] = array_slice($this->executionContext(true), 0, 3);

        $result = app(Bismel1ExecutionEngine::class)->executeSignal($account, $signal);

        $this->assertSame('submitted', $result['status']);
        $this->assertDatabaseHas('alpaca_orders', [
            'account_id' => $account->getKey(),
            'signal_id' => $signal->getKey(),
            'alpaca_order_id' => 'alpaca-order-1',
            'request_action' => 'open',
            'status' => 'submitted',
        ]);
        $this->assertDatabaseHas('bot_runs', [
            'account_id' => $account->getKey(),
            'run_type' => 'execution',
            'status' => 'completed',
        ]);
        $this->assertDatabaseHas('activity_logs', [
            'account_id' => $account->getKey(),
            'type' => 'bismel1_execution_submitted',
        ]);
        $this->assertSame('execution_submitted', $automationSetting->fresh()->run_health);
        $this->assertSame('submitted', data_get($automationSetting->fresh()->settings, 'bismel1_execution.last_execution_result'));
    }

    public function test_it_skips_when_execution_is_not_enabled_even_if_signal_is_risk_approved(): void
    {
        Http::fake();

        [$account, $automationSetting, $signal] = array_slice($this->executionContext(false), 0, 3);

        $result = app(Bismel1ExecutionEngine::class)->executeSignal($account, $signal);

        $this->assertSame('skipped', $result['status']);
        $this->assertDatabaseCount('alpaca_orders', 0);
        $this->assertDatabaseHas('bot_runs', [
            'account_id' => $account->getKey(),
            'run_type' => 'execution',
            'status' => 'skipped',
        ]);
        $this->assertDatabaseHas('activity_logs', [
            'account_id' => $account->getKey(),
            'type' => 'bismel1_execution_skipped',
        ]);
        $this->assertSame('execution_skipped', $automationSetting->fresh()->run_health);
    }

    public function test_it_blocks_live_execution_when_paper_trading_guardrail_is_active(): void
    {
        Http::fake();
        config(['alpaca.guards.allow_live_order_submission' => false]);

        [$account, $automationSetting, $signal, $alpacaAccount] = $this->executionContext(true, 'live');

        $result = app(Bismel1ExecutionEngine::class)->executeSignal($account, $signal);

        $this->assertSame('skipped', $result['status']);
        $this->assertSame('live', $alpacaAccount->environment);
        $this->assertDatabaseCount('alpaca_orders', 0);
        $this->assertSame('execution_skipped', $automationSetting->fresh()->run_health);
        $this->assertSame(
            'paper trading only: live broker environment blocked',
            data_get($automationSetting->fresh()->settings, 'bismel1_execution.last_execution_attempt_summary')
        );
    }

    protected function executionContext(bool $executionEnabled, string $environment = 'paper'): array
    {
        $account = Account::factory()->create();
        $this->seedConfirmedBismel1Subscription($account, 'BISMILLAH1_BOT_EXECUTE_BASIC');
        $strategyProfile = StrategyProfile::query()->create([
            'account_id' => $account->getKey(),
            'name' => 'Bismel1 Swing',
            'mode' => 'stocks_swing',
            'timeframe' => '4h_1d',
            'symbol_scope' => 'watchlist',
            'style' => 'balanced',
            'engine' => 'python',
            'is_active' => true,
        ]);

        $automationSetting = AutomationSetting::query()->create([
            'account_id' => $account->getKey(),
            'strategy_profile_id' => $strategyProfile->getKey(),
            'name' => 'Bismel1 Automation',
            'ai_enabled' => true,
            'status' => 'armed',
            'risk_level' => 'balanced',
            'scanner_enabled' => true,
            'execution_enabled' => $executionEnabled,
        ]);

        $brokerConnection = BrokerConnection::query()->create([
            'account_id' => $account->getKey(),
            'name' => 'Primary Alpaca',
            'broker' => 'alpaca',
            'status' => 'connected',
        ]);

        BrokerCredential::query()->create([
            'broker_connection_id' => $brokerConnection->getKey(),
            'label' => 'Primary Credential',
            'provider' => 'alpaca',
            'status' => 'verified',
            'environment' => $environment,
            'access_mode' => 'trade',
            'credential_payload' => [
                'provider' => 'alpaca',
                'environment' => $environment,
                'access_mode' => 'trade',
                'market_data_feed' => 'iex',
                'access_key_id' => 'PKTEST1234',
                'access_secret' => 'SECRET99',
            ],
            'is_encrypted' => true,
        ]);

        $alpacaAccount = AlpacaAccount::query()->create([
            'account_id' => $account->getKey(),
            'broker_connection_id' => $brokerConnection->getKey(),
            'name' => 'Primary Alpaca',
            'environment' => $environment,
            'data_feed' => 'iex',
            'status' => 'active',
            'sync_status' => 'success',
            'trade_stream_status' => 'credentials_verified',
            'is_primary' => true,
            'is_active' => true,
            'equity' => 12500,
            'last_synced_at' => now()->subMinutes(5),
            'last_account_sync_at' => now()->subMinutes(5),
            'metadata' => [],
        ]);

        $signal = Signal::query()->create([
            'account_id' => $account->getKey(),
            'strategy_profile_id' => $strategyProfile->getKey(),
            'symbol' => 'AAPL',
            'timeframe' => '4H',
            'direction' => 'long',
            'strength' => 1.0,
            'status' => 'open',
            'generated_at' => now(),
            'expires_at' => now()->addHours(4),
            'payload' => [
                'public_summary' => 'trend aligned, pullback detected, reclaim confirmed',
                'risk_engine' => [
                    'allowed' => true,
                    'status' => 'allow_action',
                    'reason_code' => 'allow_action',
                    'final_action' => 'open',
                    'proposed_action' => 'open',
                ],
            ],
        ]);

        $this->assertNotNull($alpacaAccount);

        return [$account, $automationSetting, $signal, $alpacaAccount];
    }
}
