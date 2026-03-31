<?php
// اعوز بالله من الشياطين و ان يحضرون بسم الله الرحمن الرحيم الله لا إله إلا هو الحي القيوم
// Bismillahi ar-Rahmani ar-Rahim Audhu billahi min ash-shayatin wa an yahdurun Bismillah ar-Rahman ar-Rahim Allah la ilaha illa huwa al-hayy al-qayyum. Tamsa Allahu ala ayunihim
// version: x
// ======================================================
// - App Name: gusgraph-trading
// - Gusgraph LLC -
// - Author: Gus Kazem
// - https://Gusgraph.com
// - File Path: tests/Feature/Automation/Bismel1RiskEngineTest.php
// ======================================================

namespace Tests\Feature\Automation;

use App\Models\Account;
use App\Models\AlpacaAccount;
use App\Models\AlpacaPosition;
use App\Models\AutomationSetting;
use App\Models\BrokerConnection;
use App\Models\Signal;
use App\Models\StrategyProfile;
use App\Support\Automation\Bismel1RiskEngine;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class Bismel1RiskEngineTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_blocks_a_duplicate_open_signal_with_safe_summary(): void
    {
        $account = Account::factory()->create();
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

        Signal::query()->create([
            'account_id' => $account->getKey(),
            'strategy_profile_id' => $strategyProfile->getKey(),
            'symbol' => 'AAPL',
            'timeframe' => '4H',
            'direction' => 'long',
            'strength' => 1.0,
            'status' => 'open',
            'generated_at' => now()->subMinutes(30),
            'expires_at' => now()->addHours(4),
            'payload' => ['public_summary' => 'trend aligned, pullback detected, reclaim confirmed'],
        ]);

        $result = app(Bismel1RiskEngine::class)->evaluate(
            $account,
            $strategyProfile,
            $automationSetting,
            $alpacaAccount,
            null,
            'AAPL',
            '4H',
            [
                'action' => 'open',
                'safe_flags' => [
                    'trend_aligned' => true,
                    'pullback_detected' => true,
                    'reclaim_confirmed' => true,
                    'risk_blocked' => false,
                    'trailing_exit' => false,
                ],
                'internal_strategy_state' => [
                    'current' => [
                        'bar_close_time' => now()->toIso8601String(),
                    ],
                    'unresolved_gaps' => [],
                ],
            ],
        );

        $this->assertFalse($result['allowed']);
        $this->assertSame('skip', $result['final_action']);
        $this->assertSame('duplicate_signal_protection', $result['reason_code']);
        $this->assertSame('risk blocked', $result['public_summary']);
    }

    public function test_it_blocks_open_when_position_already_exists(): void
    {
        $account = Account::factory()->create();
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

        $position = AlpacaPosition::query()->create([
            'account_id' => $account->getKey(),
            'alpaca_account_id' => $alpacaAccount->getKey(),
            'broker_connection_id' => $brokerConnection->getKey(),
            'symbol' => 'AAPL',
            'asset_class' => 'equity',
            'side' => 'long',
            'qty' => 2,
            'qty_available' => 2,
            'market_value' => 205,
            'cost_basis' => 200,
            'current_price' => 102.5,
            'avg_entry_price' => 100,
            'synced_at' => now()->subMinutes(5),
        ]);

        $result = app(Bismel1RiskEngine::class)->evaluate(
            $account,
            $strategyProfile,
            $automationSetting,
            $alpacaAccount,
            $position,
            'AAPL',
            '4H',
            [
                'action' => 'open',
                'safe_flags' => [
                    'trend_aligned' => true,
                    'pullback_detected' => true,
                    'reclaim_confirmed' => true,
                    'risk_blocked' => false,
                    'trailing_exit' => false,
                ],
                'internal_strategy_state' => [
                    'current' => [
                        'bar_close_time' => now()->toIso8601String(),
                    ],
                    'unresolved_gaps' => [],
                    'position_state' => [
                        'add_count' => 0,
                    ],
                ],
            ],
        );

        $this->assertFalse($result['allowed']);
        $this->assertSame('position_already_exists', $result['reason_code']);
        $this->assertSame('risk blocked', $result['public_summary']);
    }
}
