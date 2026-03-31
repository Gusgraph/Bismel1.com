<?php
// اعوز بالله من الشياطين و ان يحضرون بسم الله الرحمن الرحيم الله لا إله إلا هو الحي القيوم
// Bismillahi ar-Rahmani ar-Rahim Audhu billahi min ash-shayatin wa an yahdurun Bismillah ar-Rahman ar-Rahim Allah la ilaha illa huwa al-hayy al-qayyum. Tamsa Allahu ala ayunihim
// version: x
// ======================================================
// - App Name: gusgraph-trading
// - Gusgraph LLC -
// - Author: Gus Kazem
// - https://Gusgraph.com
// - File Path: tests/Feature/Automation/Bismel1SmallSymbolScannerTest.php
// ======================================================

namespace Tests\Feature\Automation;

use App\Models\Account;
use App\Models\AlpacaAccount;
use App\Models\AlpacaBar;
use App\Models\AutomationSetting;
use App\Models\BotRun;
use App\Models\BrokerConnection;
use App\Models\Signal;
use App\Models\StrategyProfile;
use App\Models\Watchlist;
use App\Models\WatchlistSymbol;
use App\Support\Automation\Bismel1PythonStrategyBridge;
use App\Support\Automation\Bismel1SmallSymbolScanner;
use App\Support\Broker\AlpacaMarketDataService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Concerns\CreatesBismel1Entitlements;
use Tests\TestCase;

class Bismel1SmallSymbolScannerTest extends TestCase
{
    use RefreshDatabase;
    use CreatesBismel1Entitlements;

    public function test_it_stores_safe_signals_and_bot_runs_for_a_small_symbol_scan(): void
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
            'status' => 'review',
            'risk_level' => 'balanced',
            'scanner_enabled' => true,
            'execution_enabled' => false,
        ]);

        $brokerConnection = BrokerConnection::query()->create([
            'account_id' => $account->getKey(),
            'name' => 'Primary Alpaca',
            'broker' => 'alpaca',
            'status' => 'connected',
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
            'name' => 'Primary Symbols',
            'status' => 'active',
        ]);

        $aapl = WatchlistSymbol::query()->create([
            'watchlist_id' => $watchlist->getKey(),
            'symbol' => 'AAPL',
            'asset_class' => 'equity',
            'status' => 'active',
        ]);

        $msft = WatchlistSymbol::query()->create([
            'watchlist_id' => $watchlist->getKey(),
            'symbol' => 'MSFT',
            'asset_class' => 'equity',
            'status' => 'active',
        ]);

        foreach (['AAPL', 'MSFT'] as $symbol) {
            AlpacaBar::query()->create([
                'account_id' => $account->getKey(),
                'alpaca_account_id' => $alpacaAccount->getKey(),
                'broker_connection_id' => $brokerConnection->getKey(),
                'symbol' => $symbol,
                'timeframe' => '1H',
                'feed' => 'iex',
                'starts_at' => now()->subHours(3),
                'ends_at' => now()->subHours(2),
                'open' => 100,
                'high' => 101,
                'low' => 99,
                'close' => 100.5,
                'volume' => 1000,
                'fetched_at' => now(),
            ]);

            AlpacaBar::query()->create([
                'account_id' => $account->getKey(),
                'alpaca_account_id' => $alpacaAccount->getKey(),
                'broker_connection_id' => $brokerConnection->getKey(),
                'symbol' => $symbol,
                'timeframe' => '4H',
                'feed' => 'iex',
                'starts_at' => now()->subHours(4),
                'ends_at' => now(),
                'open' => 100,
                'high' => 101,
                'low' => 99,
                'close' => 100.5,
                'volume' => 1000,
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
                ->twice()
                ->andReturnUsing(function (array $payload): array {
                    if (($payload['symbol'] ?? null) === 'AAPL') {
                        return [
                            'symbol' => 'AAPL',
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
                                    'quantity' => 1.0,
                                    'average_price' => 100.5,
                                    'add_count' => 0,
                                    'last_add_price' => 100.5,
                                    'dollars_used' => 100.0,
                                    'pos_high' => 101.0,
                                ],
                                'unresolved_gaps' => [],
                            ],
                        ];
                    }

                    return [
                        'symbol' => 'MSFT',
                        'action' => 'skip',
                        'safe_flags' => [
                            'trend_aligned' => false,
                            'pullback_detected' => false,
                            'reclaim_confirmed' => false,
                            'risk_blocked' => true,
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
                            'unresolved_gaps' => ['insufficient_daily_history_for_htf_ema_slow'],
                        ],
                    ];
                });
        });

        $result = app(Bismel1SmallSymbolScanner::class)->scanAccount($account);

        $this->assertSame('completed', $result['status']);
        $this->assertDatabaseCount('signals', 2);
        $this->assertDatabaseCount('bot_runs', 1);

        $openSignal = Signal::query()->where('symbol', 'AAPL')->firstOrFail();
        $skipSignal = Signal::query()->where('symbol', 'MSFT')->firstOrFail();
        $botRun = BotRun::query()->firstOrFail();

        $this->assertSame($watchlist->getKey(), $openSignal->watchlist_id);
        $this->assertSame($aapl->getKey(), $openSignal->watchlist_symbol_id);
        $this->assertSame('open', $openSignal->status);
        $this->assertSame('trend aligned, pullback detected, reclaim confirmed', data_get($openSignal->payload, 'public_summary'));
        $this->assertSame('safe_summary_only', data_get($openSignal->payload, 'visibility.customer'));
        $this->assertSame('allow_action', data_get($openSignal->payload, 'risk_engine.status'));
        $this->assertTrue((bool) data_get($openSignal->payload, 'risk_engine.allowed'));
        $this->assertSame('skip', $skipSignal->status);
        $this->assertSame('AI skipped setup', data_get($skipSignal->payload, 'public_summary'));
        $this->assertIsArray(data_get($skipSignal->payload, 'internal_strategy_state'));
        $this->assertSame('block_action', data_get($skipSignal->payload, 'risk_engine.status'));
        $this->assertSame('scanner_skipped_setup', data_get($skipSignal->payload, 'risk_engine.reason_code'));
        $this->assertStringNotContainsString('EMA 200', (string) data_get($skipSignal->payload, 'public_summary'));
        $this->assertSame('completed', $botRun->status);
        $this->assertSame('Small-symbol Bismel1 scanner completed with safe summaries only.', data_get($botRun->summary, 'safe_summary'));
        $this->assertSame(1, (int) data_get($botRun->summary, 'counts.open'));
        $this->assertSame(1, (int) data_get($botRun->summary, 'counts.skip'));
        $this->assertSame(0, (int) data_get($botRun->summary, 'counts.blocked'));
        $this->assertSame($automationSetting->fresh()->run_health, 'scanned');
        $this->assertNotNull($automationSetting->fresh()->last_checked_at);
        $this->assertSame($msft->getKey(), $skipSignal->watchlist_symbol_id);
    }
}
