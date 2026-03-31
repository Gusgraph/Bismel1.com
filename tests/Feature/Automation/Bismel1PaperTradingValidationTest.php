<?php
// اعوز بالله من الشياطين و ان يحضرون بسم الله الرحمن الرحيم الله لا إله إلا هو الحي القيوم
// Bismillahi ar-Rahmani ar-Rahim Audhu billahi min ash-shayatin wa an yahdurun Bismillah ar-Rahman ar-Rahim Allah la ilaha illa huwa al-hayy al-qayyum. Tamsa Allahu ala ayunihim
// version: x
// ======================================================
// - App Name: gusgraph-trading
// - Gusgraph LLC -
// - Author: Gus Kazem
// - https://Gusgraph.com
// - File Path: tests/Feature/Automation/Bismel1PaperTradingValidationTest.php
// ======================================================

namespace Tests\Feature\Automation;

use App\Models\AlpacaAccount;
use App\Models\AlpacaBar;
use App\Models\AlpacaPosition;
use App\Models\AutomationSetting;
use App\Models\BotRun;
use App\Models\BrokerConnection;
use App\Models\BrokerCredential;
use App\Models\Signal;
use App\Models\StrategyProfile;
use App\Models\SystemSetting;
use App\Models\Watchlist;
use App\Models\WatchlistSymbol;
use App\Support\Automation\Bismel1ExecutionEngine;
use App\Support\Automation\Bismel1PositionManager;
use App\Support\Automation\Bismel1PythonStrategyBridge;
use App\Support\Automation\Bismel1SmallSymbolScanner;
use App\Support\Broker\AlpacaAccountSyncService;
use App\Support\Broker\AlpacaMarketDataService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Tests\Concerns\CreatesAccessContext;
use Tests\Concerns\CreatesBismel1Entitlements;
use Tests\TestCase;

class Bismel1PaperTradingValidationTest extends TestCase
{
    use CreatesAccessContext;
    use CreatesBismel1Entitlements;
    use RefreshDatabase;

    public function test_it_validates_the_first_end_to_end_paper_trading_chain_with_safe_visibility(): void
    {
        [$user, $account] = $this->createAccessContext([
            'name' => 'Paper Validation Account',
            'slug' => 'paper-validation-account',
        ]);
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
            'name' => 'Primary Automation',
            'ai_enabled' => true,
            'status' => 'armed',
            'risk_level' => 'balanced',
            'scanner_enabled' => true,
            'execution_enabled' => true,
            'run_health' => 'customer_runtime_ready',
            'settings' => [
                'bismel1_runtime' => [
                    'last_runtime_status' => 'active',
                    'last_runtime_summary' => 'waiting for next bar close',
                ],
            ],
        ]);

        $brokerConnection = BrokerConnection::query()->create([
            'account_id' => $account->getKey(),
            'name' => 'Primary Alpaca',
            'broker' => 'alpaca',
            'status' => 'connected',
            'connected_at' => now()->subHour(),
            'last_synced_at' => now()->subMinutes(10),
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
            'metadata' => [],
        ]);

        $watchlist = Watchlist::query()->create([
            'account_id' => $account->getKey(),
            'strategy_profile_id' => $strategyProfile->getKey(),
            'name' => 'Validation Symbols',
            'status' => 'active',
        ]);

        WatchlistSymbol::query()->create([
            'watchlist_id' => $watchlist->getKey(),
            'symbol' => 'NVDA',
            'asset_class' => 'equity',
            'status' => 'active',
        ]);

        foreach (range(1, 3) as $offset) {
            AlpacaBar::query()->create([
                'account_id' => $account->getKey(),
                'alpaca_account_id' => $alpacaAccount->getKey(),
                'broker_connection_id' => $brokerConnection->getKey(),
                'symbol' => 'NVDA',
                'timeframe' => '1H',
                'feed' => 'iex',
                'starts_at' => now()->subHours(6 - $offset),
                'ends_at' => now()->subHours(5 - $offset),
                'open' => 100 + $offset,
                'high' => 101 + $offset,
                'low' => 99 + $offset,
                'close' => 100.5 + $offset,
                'volume' => 1000 + $offset,
                'fetched_at' => now(),
            ]);
        }

        foreach (range(1, 2) as $offset) {
            AlpacaBar::query()->create([
                'account_id' => $account->getKey(),
                'alpaca_account_id' => $alpacaAccount->getKey(),
                'broker_connection_id' => $brokerConnection->getKey(),
                'symbol' => 'NVDA',
                'timeframe' => '4H',
                'feed' => 'iex',
                'starts_at' => now()->subHours(8 * $offset),
                'ends_at' => now()->subHours((8 * $offset) - 4),
                'open' => 100 + $offset,
                'high' => 101 + $offset,
                'low' => 99 + $offset,
                'close' => 100.5 + $offset,
                'volume' => 1200 + $offset,
                'fetched_at' => now(),
            ]);
        }

        $this->mock(AlpacaMarketDataService::class, function ($mock): void {
            $mock->shouldReceive('syncLatestForAccount')
                ->once()
                ->andReturn([
                    'status' => 'synced',
                    'message' => 'Bars synced.',
                ]);
        });

        $this->mock(Bismel1PythonStrategyBridge::class, function ($mock): void {
            $mock->shouldReceive('formatBars')->andReturnUsing(fn ($bars) => $bars->map(fn ($bar) => [
                'starts_at' => $bar->starts_at?->toIso8601String(),
                'ends_at' => $bar->ends_at?->toIso8601String(),
                'open' => (float) $bar->open,
                'high' => (float) $bar->high,
                'low' => (float) $bar->low,
                'close' => (float) $bar->close,
                'volume' => (float) $bar->volume,
            ])->values()->all());

            $mock->shouldReceive('evaluateSymbol')
                ->once()
                ->andReturn([
                    'symbol' => 'NVDA',
                    'action' => 'open',
                    'safe_flags' => [
                        'trend_aligned' => true,
                        'pullback_detected' => true,
                        'reclaim_confirmed' => true,
                        'risk_blocked' => false,
                        'trailing_exit' => false,
                        'regime_fail' => false,
                    ],
                    'internal_strategy_state' => [
                        'current' => [
                            'bar_close_time' => now()->toIso8601String(),
                        ],
                        'position_state' => [
                            'quantity' => 0.0,
                            'average_price' => 0.0,
                            'add_count' => 0,
                            'last_add_price' => null,
                            'dollars_used' => 0.0,
                            'pos_high' => null,
                        ],
                        'unresolved_gaps' => [],
                    ],
                ]);
        });

        $scanResult = app(Bismel1SmallSymbolScanner::class)->scanAccount($account, ['NVDA'], [
            'run_type' => 'scan_scheduler_4h',
            'scheduler' => [
                'mode' => 'bar_close',
                'timeframe' => '4H',
                'bar_close_at' => now()->subHour()->toIso8601String(),
                'next_bar_close_at' => now()->addHours(3)->toIso8601String(),
            ],
        ]);

        $this->assertSame('completed', $scanResult['status']);

        $signal = Signal::query()->where('symbol', 'NVDA')->latest('id')->firstOrFail();
        $this->assertSame('open', $signal->status);

        BotRun::query()->create([
            'account_id' => $account->getKey(),
            'strategy_profile_id' => $strategyProfile->getKey(),
            'automation_setting_id' => $automationSetting->getKey(),
            'alpaca_account_id' => $alpacaAccount->getKey(),
            'run_type' => 'execution',
            'status' => 'running',
            'risk_level' => 'balanced',
            'started_at' => now()->subMinutes(45),
            'summary' => [
                'signal_id' => $signal->getKey(),
                'safe_summary' => 'Stale execution placeholder',
            ],
        ]);

        Http::fake([
            'https://paper-api.alpaca.markets/v2/orders' => Http::sequence()
                ->push([
                    'id' => 'alpaca-order-open-1',
                    'client_order_id' => 'bismel1-open-1-test',
                    'asset_id' => 'asset-nvda',
                    'asset_class' => 'us_equity',
                    'side' => 'buy',
                    'type' => 'market',
                    'time_in_force' => 'day',
                    'status' => 'accepted',
                    'qty' => '1.000000',
                    'filled_qty' => '0.000000',
                    'submitted_at' => '2026-03-29T10:00:00Z',
                ], 200)
                ->push([
                    'id' => 'alpaca-order-close-1',
                    'client_order_id' => 'bismel1-close-1-test',
                    'asset_id' => 'asset-nvda',
                    'asset_class' => 'us_equity',
                    'side' => 'sell',
                    'type' => 'market',
                    'time_in_force' => 'day',
                    'status' => 'accepted',
                    'qty' => '1.000000',
                    'filled_qty' => '0.000000',
                    'submitted_at' => '2026-03-29T12:00:00Z',
                ], 200),
        ]);

        $executionEngine = app(Bismel1ExecutionEngine::class);
        $executionResult = $executionEngine->executeSignal($account, $signal, ['run_type' => 'paper_validation']);

        $this->assertSame('submitted', $executionResult['status']);
        $this->assertDatabaseMissing('bot_runs', [
            'account_id' => $account->getKey(),
            'run_type' => 'execution',
            'status' => 'running',
            'summary->signal_id' => $signal->getKey(),
        ]);

        $duplicateResult = $executionEngine->executeSignal($account, $signal, ['run_type' => 'paper_validation_duplicate']);
        $this->assertSame('skipped', $duplicateResult['status']);
        $this->assertSame(
            'Execution skipped because a recent broker action already exists for this setup.',
            $duplicateResult['message']
        );

        AlpacaPosition::query()->create([
            'account_id' => $account->getKey(),
            'alpaca_account_id' => $alpacaAccount->getKey(),
            'broker_connection_id' => $brokerConnection->getKey(),
            'strategy_profile_id' => $strategyProfile->getKey(),
            'last_signal_id' => $signal->getKey(),
            'symbol' => 'NVDA',
            'asset_class' => 'equity',
            'side' => 'long',
            'qty' => 1,
            'qty_available' => 1,
            'market_value' => 121.5,
            'cost_basis' => 120,
            'current_price' => 121.5,
            'avg_entry_price' => 120,
            'high_water_price' => 123.0,
            'management_state' => 'holding',
            'status_summary' => 'AI held position',
            'synced_at' => now()->subMinutes(2),
        ]);

        $this->mock(AlpacaAccountSyncService::class, function ($mock): void {
            $mock->shouldReceive('syncLatestForAccount')->once()->andReturn([
                'status' => 'fresh',
                'message' => 'Broker state is fresh.',
            ]);
        });

        $this->mock(Bismel1SmallSymbolScanner::class, function ($mock) use ($account, $strategyProfile, $automationSetting, $alpacaAccount): void {
            $mock->shouldReceive('evaluateManagedSymbol')
                ->once()
                ->with($account, 'NVDA')
                ->andReturn([
                    'strategy_profile' => $strategyProfile,
                    'automation_setting' => $automationSetting,
                    'alpaca_account' => $alpacaAccount,
                    'position' => AlpacaPosition::query()->where('symbol', 'NVDA')->first(),
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
                            'position_state' => ['pos_high' => 124.0],
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

        $positionResult = app(Bismel1PositionManager::class)->manageAccount($account, ['NVDA'], ['run_type' => 'paper_validation']);

        $this->assertSame('completed', $positionResult['status']);
        $this->assertSame(1, (int) data_get($positionResult, 'counts.close'));

        $automationSetting = $automationSetting->fresh();
        $this->assertSame('position_managed', $automationSetting->run_health);
        $this->assertSame('position_manager', data_get($automationSetting->settings, 'bismel1_runtime.last_stage'));
        $this->assertSame('completed', data_get($automationSetting->settings, 'bismel1_runtime.last_stage_result'));
        $this->assertSame('submitted', data_get($automationSetting->settings, 'bismel1_execution.last_execution_result'));
        $this->assertSame('completed', data_get($automationSetting->settings, 'bismel1_position_manager.last_management_result'));

        SystemSetting::query()->create([
            'runtime_mode' => 'local',
            'review_channel' => 'manual',
            'status_level' => 'medium',
        ]);

        $customerResponse = $this->actingAs($user)->get(route('customer.automation.index'));
        $customerResponse->assertOk();
        $customerResponse->assertSeeText('Latest Stage');
        $customerResponse->assertSeeText('Position manager');
        $customerResponse->assertSeeText('Broker open request for NVDA was submitted with safe execution handling.');
        $customerResponse->assertSeeText('Bismel1 position manager closed positions with safe trailing protection handling.');
        $customerResponse->assertDontSeeText('EMA');
        $customerResponse->assertDontSeeText('SECRET99');

        $adminResponse = $this->actingAs($user)->get(route('admin.dashboard'));
        $adminResponse->assertOk();
        $adminResponse->assertSeeText('Customer automation control surface');
        $adminResponse->assertSeeText('Paper Validation Account');
        $adminResponse->assertSeeText('Broker open request for NVDA was submitted with safe execution handling.');
        $adminResponse->assertSeeText('Bismel1 position manager closed positions with safe trailing protection handling.');
        $adminResponse->assertDontSeeText('EMA');
        $adminResponse->assertDontSeeText('SECRET99');
    }
}
