<?php
// اعوز بالله من الشياطين و ان يحضرون بسم الله الرحمن الرحيم الله لا إله إلا هو الحي القيوم
// Bismillahi ar-Rahmani ar-Rahim Audhu billahi min ash-shayatin wa an yahdurun Bismillah ar-Rahman ar-Rahim Allah la ilaha illa huwa al-hayy al-qayyum. Tamsa Allahu ala ayunihim
// version: x
// ======================================================
// - App Name: gusgraph-trading
// - Gusgraph LLC -
// - Author: Gus Kazem
// - https://Gusgraph.com
// - File Path: tests/Feature/Automation/Bismel1PositionManagerTest.php
// ======================================================

namespace Tests\Feature\Automation;

use App\Models\Account;
use App\Models\AlpacaAccount;
use App\Models\AlpacaOrder;
use App\Models\AlpacaPosition;
use App\Models\AutomationSetting;
use App\Models\BrokerConnection;
use App\Models\BrokerCredential;
use App\Models\Signal;
use App\Models\StrategyProfile;
use App\Support\Automation\Bismel1PositionManager;
use App\Support\Automation\Bismel1SmallSymbolScanner;
use App\Support\Broker\AlpacaAccountSyncService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Tests\Concerns\CreatesBismel1Entitlements;
use Tests\TestCase;

class Bismel1PositionManagerTest extends TestCase
{
    use RefreshDatabase;
    use CreatesBismel1Entitlements;

    public function test_it_adds_to_an_open_position_and_updates_safe_management_state(): void
    {
        Http::fake([
            'https://paper-api.alpaca.markets/v2/orders' => Http::response([
                'id' => 'alpaca-order-add-1',
                'client_order_id' => 'bismel1-add-1-test',
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

        [$account, $strategyProfile, $automationSetting, $alpacaAccount, $position] = $this->managementContext();

        $this->mock(AlpacaAccountSyncService::class, function ($mock): void {
            $mock->shouldReceive('syncLatestForAccount')->once()->andReturn([
                'status' => 'fresh',
                'message' => 'Broker state is fresh.',
            ]);
        });

        $this->mock(Bismel1SmallSymbolScanner::class, function ($mock) use ($account, $strategyProfile, $automationSetting, $alpacaAccount, $position): void {
            $mock->shouldReceive('evaluateManagedSymbol')
                ->once()
                ->with($account, 'AAPL')
                ->andReturn([
                    'strategy_profile' => $strategyProfile,
                    'automation_setting' => $automationSetting,
                    'alpaca_account' => $alpacaAccount,
                    'position' => $position,
                    'evaluation' => [
                        'action' => 'add',
                        'safe_flags' => [
                            'trend_aligned' => true,
                            'pullback_detected' => true,
                            'reclaim_confirmed' => true,
                            'trailing_exit' => false,
                        ],
                        'internal_strategy_state' => [
                            'current' => ['bar_close_time' => now()->toIso8601String()],
                            'position_state' => ['pos_high' => 112.5],
                        ],
                    ],
                    'risk_result' => [
                        'allowed' => true,
                        'status' => 'allow_action',
                        'reason_code' => 'allow_action',
                        'proposed_action' => 'add',
                        'final_action' => 'add',
                    ],
                    'payload' => [
                        'public_summary' => 'trend aligned, pullback detected, reclaim confirmed',
                        'risk_engine' => [
                            'allowed' => true,
                            'status' => 'allow_action',
                            'reason_code' => 'allow_action',
                            'proposed_action' => 'add',
                            'final_action' => 'add',
                        ],
                    ],
                    'generated_at' => now(),
                ]);
        });

        $result = app(Bismel1PositionManager::class)->manageAccount($account, ['AAPL']);

        $this->assertSame('completed', $result['status']);
        $this->assertSame(1, (int) data_get($result, 'counts.add'));
        $this->assertDatabaseHas('alpaca_orders', [
            'account_id' => $account->getKey(),
            'symbol' => 'AAPL',
            'request_action' => 'add',
            'status' => 'submitted',
        ]);
        $this->assertSame('add_pending', $position->fresh()->management_state);
        $this->assertSame('AI added to position', $position->fresh()->status_summary);
        $this->assertSame('position_managed', $automationSetting->fresh()->run_health);
    }

    public function test_it_closes_on_trailing_protection_and_updates_position_state(): void
    {
        Http::fake([
            'https://paper-api.alpaca.markets/v2/orders' => Http::response([
                'id' => 'alpaca-order-close-1',
                'client_order_id' => 'bismel1-close-1-test',
                'asset_id' => 'asset-aapl',
                'asset_class' => 'us_equity',
                'side' => 'sell',
                'type' => 'market',
                'time_in_force' => 'day',
                'status' => 'accepted',
                'qty' => '2.000000',
                'filled_qty' => '0.000000',
                'submitted_at' => '2026-03-29T10:10:00Z',
            ], 200),
        ]);

        [$account, $strategyProfile, $automationSetting, $alpacaAccount, $position] = $this->managementContext();

        $this->mock(AlpacaAccountSyncService::class, function ($mock): void {
            $mock->shouldReceive('syncLatestForAccount')->once()->andReturn([
                'status' => 'fresh',
                'message' => 'Broker state is fresh.',
            ]);
        });

        $this->mock(Bismel1SmallSymbolScanner::class, function ($mock) use ($account, $strategyProfile, $automationSetting, $alpacaAccount, $position): void {
            $mock->shouldReceive('evaluateManagedSymbol')
                ->once()
                ->with($account, 'AAPL')
                ->andReturn([
                    'strategy_profile' => $strategyProfile,
                    'automation_setting' => $automationSetting,
                    'alpaca_account' => $alpacaAccount,
                    'position' => $position,
                    'evaluation' => [
                        'action' => 'close',
                        'safe_flags' => [
                            'trend_aligned' => false,
                            'pullback_detected' => false,
                            'reclaim_confirmed' => false,
                            'trailing_exit' => true,
                        ],
                        'internal_strategy_state' => [
                            'current' => ['bar_close_time' => now()->toIso8601String()],
                            'position_state' => ['pos_high' => 112.5],
                        ],
                    ],
                    'risk_result' => [
                        'allowed' => true,
                        'status' => 'allow_action',
                        'reason_code' => 'allow_action',
                        'proposed_action' => 'close',
                        'final_action' => 'close',
                    ],
                    'payload' => [
                        'public_summary' => 'AI exited on trailing protection',
                        'risk_engine' => [
                            'allowed' => true,
                            'status' => 'allow_action',
                            'reason_code' => 'allow_action',
                            'proposed_action' => 'close',
                            'final_action' => 'close',
                        ],
                    ],
                    'generated_at' => now(),
                ]);
        });

        $result = app(Bismel1PositionManager::class)->manageAccount($account, ['AAPL']);

        $this->assertSame('completed', $result['status']);
        $this->assertSame(1, (int) data_get($result, 'counts.close'));
        $this->assertDatabaseHas('alpaca_orders', [
            'account_id' => $account->getKey(),
            'symbol' => 'AAPL',
            'request_action' => 'close',
            'status' => 'submitted',
        ]);
        $this->assertSame('close_pending', $position->fresh()->management_state);
        $this->assertSame('AI closed on trailing protection', $position->fresh()->status_summary);
    }

    public function test_it_reconciles_local_state_when_broker_position_is_missing(): void
    {
        Http::fake();

        [$account, $strategyProfile, $automationSetting, $alpacaAccount] = $this->managementContext(false);

        Signal::query()->create([
            'account_id' => $account->getKey(),
            'strategy_profile_id' => $strategyProfile->getKey(),
            'symbol' => 'AAPL',
            'timeframe' => '4H',
            'direction' => 'long',
            'strength' => 1.0,
            'status' => 'open',
            'generated_at' => now()->subHour(),
            'expires_at' => now()->addHours(3),
            'payload' => ['public_summary' => 'trend aligned, pullback detected, reclaim confirmed'],
        ]);

        AlpacaOrder::query()->create([
            'account_id' => $account->getKey(),
            'alpaca_account_id' => $alpacaAccount->getKey(),
            'broker_connection_id' => $alpacaAccount->broker_connection_id,
            'strategy_profile_id' => $strategyProfile->getKey(),
            'alpaca_order_id' => 'existing-order-1',
            'client_order_id' => 'existing-client-1',
            'symbol' => 'AAPL',
            'asset_class' => 'equity',
            'side' => 'buy',
            'order_type' => 'market',
            'time_in_force' => 'day',
            'request_action' => 'open',
            'status' => 'submitted',
            'qty' => 1,
            'submitted_at' => now()->subHour(),
            'synced_at' => now()->subHour(),
        ]);

        $this->mock(AlpacaAccountSyncService::class, function ($mock): void {
            $mock->shouldReceive('syncLatestForAccount')->once()->andReturn([
                'status' => 'fresh',
                'message' => 'Broker state is fresh.',
            ]);
        });

        $this->mock(Bismel1SmallSymbolScanner::class, function ($mock): void {
            $mock->shouldNotReceive('evaluateManagedSymbol');
        });

        $result = app(Bismel1PositionManager::class)->manageAccount($account, ['AAPL']);

        $this->assertSame('completed', $result['status']);
        $this->assertSame(1, (int) data_get($result, 'counts.reconcile'));
        $this->assertSame('AI reconciled broker state', AlpacaOrder::query()->where('symbol', 'AAPL')->firstOrFail()->status_summary);
        $this->assertSame('position_managed', $automationSetting->fresh()->run_health);
    }

    protected function managementContext(bool $withPosition = true): array
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
            'execution_enabled' => true,
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
            'environment' => 'paper',
            'access_mode' => 'trade',
            'credential_payload' => [
                'provider' => 'alpaca',
                'environment' => 'paper',
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
            'environment' => 'paper',
            'data_feed' => 'iex',
            'status' => 'active',
            'sync_status' => 'success',
            'trade_stream_status' => 'credentials_verified',
            'is_primary' => true,
            'is_active' => true,
            'equity' => 12500,
            'last_synced_at' => now()->subMinutes(5),
            'last_account_sync_at' => now()->subMinutes(5),
            'last_positions_sync_at' => now()->subMinutes(5),
            'last_orders_sync_at' => now()->subMinutes(5),
            'metadata' => [],
        ]);

        $position = null;

        if ($withPosition) {
            $position = AlpacaPosition::query()->create([
                'account_id' => $account->getKey(),
                'alpaca_account_id' => $alpacaAccount->getKey(),
                'broker_connection_id' => $brokerConnection->getKey(),
                'symbol' => 'AAPL',
                'asset_class' => 'equity',
                'side' => 'long',
                'qty' => 2,
                'qty_available' => 2,
                'market_value' => 220,
                'cost_basis' => 200,
                'current_price' => 110,
                'avg_entry_price' => 100,
                'synced_at' => now()->subMinutes(5),
            ]);
        }

        return [$account, $strategyProfile, $automationSetting, $alpacaAccount, $position];
    }
}
