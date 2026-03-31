<?php
// اعوز بالله من الشياطين و ان يحضرون بسم الله الرحمن الرحيم الله لا إله إلا هو الحي القيوم
// Bismillahi ar-Rahmani ar-Rahim Audhu billahi min ash-shayatin wa an yahdurun Bismillah ar-Rahman ar-Rahim Allah la ilaha illa huwa al-hayy al-qayyum. Tamsa Allahu ala ayunihim
// version: x
// ======================================================
// - App Name: gusgraph-trading
// - Gusgraph LLC -
// - Author: Gus Kazem
// - https://Gusgraph.com
// - File Path: tests/Feature/Customer/CustomerTradingPagesTest.php
// ======================================================

namespace Tests\Feature\Customer;

use App\Models\ActivityLog;
use App\Models\AlpacaAccount;
use App\Models\AlpacaOrder;
use App\Models\AlpacaPosition;
use App\Models\AutomationSetting;
use App\Models\BotRun;
use App\Models\BrokerConnection;
use App\Models\BrokerCredential;
use App\Models\Signal;
use App\Models\StrategyProfile;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Concerns\CreatesAccessContext;
use Tests\Concerns\CreatesBismel1Entitlements;
use Tests\TestCase;

class CustomerTradingPagesTest extends TestCase
{
    use CreatesAccessContext;
    use CreatesBismel1Entitlements;
    use RefreshDatabase;

    public function test_customer_positions_page_renders_account_scoped_position_visibility(): void
    {
        [$user, $account, $alpacaAccount] = $this->seedTradingWorkspace();
        [, $otherAccount, $otherAlpacaAccount] = $this->seedTradingWorkspace('Other Workspace Position');

        AlpacaPosition::query()->create([
            'account_id' => $account->getKey(),
            'alpaca_account_id' => $alpacaAccount->getKey(),
            'broker_connection_id' => $alpacaAccount->broker_connection_id,
            'symbol' => 'NVDA',
            'side' => 'long',
            'qty' => 2,
            'market_value' => 940,
            'avg_entry_price' => 455,
            'current_price' => 470,
            'unrealized_pl' => 30,
            'management_state' => 'hold',
            'status_summary' => 'AI held position',
            'last_managed_at' => now()->subMinutes(12),
            'synced_at' => now()->subMinutes(5),
        ]);

        AlpacaPosition::query()->create([
            'account_id' => $otherAccount->getKey(),
            'alpaca_account_id' => $otherAlpacaAccount->getKey(),
            'broker_connection_id' => $otherAlpacaAccount->broker_connection_id,
            'symbol' => 'TSLA',
            'side' => 'long',
            'qty' => 1,
            'management_state' => 'hold',
            'status_summary' => 'AI held position',
        ]);

        ActivityLog::query()->create([
            'account_id' => $account->getKey(),
            'type' => 'bismel1_position_close',
            'level' => 'info',
            'message' => 'Bismel1 position manager closed positions with safe trailing protection handling.',
            'context' => ['safe_summary' => 'AI closed on protection'],
        ]);

        $response = $this->actingAs($user)->get(route('customer.positions.index'));

        $response->assertOk();
        $response->assertSeeText('Customer Positions');
        $response->assertSeeText('NVDA');
        $response->assertSeeText('AI held position');
        $response->assertSeeText('AI closed on protection');
        $response->assertDontSeeText('TSLA');
        $response->assertDontSeeText('EMA');
    }

    public function test_customer_orders_page_renders_safe_recent_order_visibility(): void
    {
        [$user, $account, $alpacaAccount] = $this->seedTradingWorkspace();

        AlpacaOrder::query()->create([
            'account_id' => $account->getKey(),
            'alpaca_account_id' => $alpacaAccount->getKey(),
            'broker_connection_id' => $alpacaAccount->broker_connection_id,
            'request_action' => 'open',
            'alpaca_order_id' => 'order-1',
            'client_order_id' => 'client-1',
            'symbol' => 'AAPL',
            'side' => 'buy',
            'order_type' => 'market',
            'time_in_force' => 'day',
            'status' => 'filled',
            'status_summary' => 'Broker open request for AAPL was submitted with safe execution handling.',
            'broker_message' => 'token=top-secret',
            'qty' => 1,
            'filled_qty' => 1,
            'filled_avg_price' => 190.12,
            'submitted_at' => now()->subMinutes(20),
            'filled_at' => now()->subMinutes(18),
            'synced_at' => now()->subMinutes(5),
        ]);

        ActivityLog::query()->create([
            'account_id' => $account->getKey(),
            'type' => 'bismel1_execution_submitted',
            'level' => 'info',
            'message' => 'Broker open request for AAPL was submitted with safe execution handling.',
            'context' => ['safe_summary' => 'Broker open request for AAPL was submitted with safe execution handling.'],
        ]);

        $response = $this->actingAs($user)->get(route('customer.orders.index'));

        $response->assertOk();
        $response->assertSeeText('Customer Orders');
        $response->assertSeeText('AAPL');
        $response->assertSeeText('Broker open request for AAPL was submitted with safe execution handling.');
        $response->assertSeeText('Recent Orders');
        $response->assertDontSeeText('top-secret');
    }

    public function test_customer_activity_page_renders_safe_runtime_feed(): void
    {
        [$user, $account, $alpacaAccount, $strategyProfile, $automationSetting] = $this->seedTradingWorkspaceWithRuntime();

        BotRun::query()->create([
            'account_id' => $account->getKey(),
            'strategy_profile_id' => $strategyProfile->getKey(),
            'automation_setting_id' => $automationSetting->getKey(),
            'alpaca_account_id' => $alpacaAccount->getKey(),
            'run_type' => 'scan',
            'status' => 'completed',
            'risk_level' => 'balanced',
            'started_at' => now()->subMinutes(40),
            'finished_at' => now()->subMinutes(39),
            'summary' => ['safe_summary' => 'Small-symbol Bismel1 scanner completed with safe summaries only.'],
        ]);

        Signal::query()->create([
            'account_id' => $account->getKey(),
            'strategy_profile_id' => $strategyProfile->getKey(),
            'symbol' => 'MSFT',
            'timeframe' => '4H',
            'direction' => 'flat',
            'strength' => 0.40,
            'status' => 'skip',
            'generated_at' => now()->subMinutes(35),
            'payload' => [
                'public_summary' => 'risk blocked',
                'risk_engine' => [
                    'allowed' => false,
                    'status' => 'block_action',
                ],
                'safe_flags' => [
                    'risk_blocked' => true,
                ],
            ],
        ]);

        ActivityLog::query()->create([
            'account_id' => $account->getKey(),
            'type' => 'bismel1_execution_skipped',
            'level' => 'warning',
            'message' => 'Execution skipped because the action was not risk-approved.',
            'context' => ['safe_summary' => 'Execution skipped because the action was not risk-approved.'],
        ]);

        ActivityLog::query()->create([
            'account_id' => $account->getKey(),
            'type' => 'bismel1_position_reconcile',
            'level' => 'info',
            'message' => 'Bismel1 position manager reconciled broker and local state.',
            'context' => ['safe_summary' => 'AI reconciled broker state'],
        ]);

        $response = $this->actingAs($user)->get(route('customer.activity.index'));

        $response->assertOk();
        $response->assertSeeText('Customer Activity');
        $response->assertSeeText('AI skipped setup');
        $response->assertSeeText('AI reconciled broker state');
        $response->assertSeeText('risk blocked');
        $response->assertDontSeeText('EMA');
    }

    protected function seedTradingWorkspace(string $name = 'Trading Workspace'): array
    {
        [$user, $account] = $this->createAccessContext([
            'name' => $name,
            'slug' => str($name)->slug()->toString(),
        ]);
        $this->seedConfirmedBismel1Subscription($account, 'BISMILLAH1_BOT_EXECUTE_BASIC');

        $connection = BrokerConnection::query()->create([
            'account_id' => $account->getKey(),
            'name' => $name.' Broker',
            'broker' => 'alpaca',
            'status' => 'connected',
            'connected_at' => now()->subHour(),
        ]);

        $credential = BrokerCredential::query()->create([
            'broker_connection_id' => $connection->getKey(),
            'label' => $name.' Credential',
            'status' => 'verified',
            'provider' => 'alpaca',
            'environment' => 'paper',
            'access_mode' => 'trade',
            'credential_payload' => [
                'access_key_id' => 'PKTEST1234',
                'access_secret' => 'SECRET99',
                'environment' => 'paper',
            ],
        ]);

        $alpacaAccount = AlpacaAccount::query()->create([
            'account_id' => $account->getKey(),
            'broker_connection_id' => $connection->getKey(),
            'broker_credential_id' => $credential->getKey(),
            'name' => $name.' Paper',
            'environment' => 'paper',
            'data_feed' => 'iex',
            'status' => 'active',
            'sync_status' => 'fresh',
            'is_primary' => true,
            'is_active' => true,
            'alpaca_account_id' => 'paper-'.$account->getKey(),
            'account_number' => 'PAPER-'.$account->getKey(),
            'buying_power' => 10000,
            'cash' => 10000,
            'equity' => 10000,
            'last_synced_at' => now()->subMinutes(5),
            'last_positions_sync_at' => now()->subMinutes(5),
            'last_orders_sync_at' => now()->subMinutes(5),
        ]);

        return [$user, $account, $alpacaAccount];
    }

    protected function seedTradingWorkspaceWithRuntime(): array
    {
        [$user, $account, $alpacaAccount] = $this->seedTradingWorkspace();

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
            'status' => 'armed',
            'risk_level' => 'balanced',
            'ai_enabled' => true,
            'scanner_enabled' => true,
            'execution_enabled' => true,
            'settings' => [
                'bismel1_runtime' => [
                    'last_runtime_summary' => 'recent run completed',
                ],
            ],
        ]);

        return [$user, $account, $alpacaAccount, $strategyProfile, $automationSetting];
    }
}
