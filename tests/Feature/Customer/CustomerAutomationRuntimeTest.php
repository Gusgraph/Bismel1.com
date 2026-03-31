<?php
// اعوز بالله من الشياطين و ان يحضرون بسم الله الرحمن الرحيم الله لا إله إلا هو الحي القيوم
// Bismillahi ar-Rahmani ar-Rahim Audhu billahi min ash-shayatin wa an yahdurun Bismillah ar-Rahman ar-Rahim Allah la ilaha illa huwa al-hayy al-qayyum. Tamsa Allahu ala ayunihim
// version: x
// ======================================================
// - App Name: bismel1.com
// - Gusgraph LLC -
// - Author: Gus Kazem
// - https://Gusgraph.com
// - File Path: tests/Feature/Customer/CustomerAutomationRuntimeTest.php
// ======================================================

namespace Tests\Feature\Customer;

use App\Models\AlpacaAccount;
use App\Models\ApiLicense;
use App\Models\AutomationSetting;
use App\Models\BotRun;
use App\Models\BrokerConnection;
use App\Models\BrokerCredential;
use App\Models\StrategyProfile;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Concerns\CreatesAccessContext;
use Tests\Concerns\CreatesBismel1Entitlements;
use Tests\TestCase;

class CustomerAutomationRuntimeTest extends TestCase
{
    use CreatesAccessContext;
    use CreatesBismel1Entitlements;
    use RefreshDatabase;

    public function test_customer_automation_page_renders_real_runtime_state_and_safe_visibility(): void
    {
        [$user, $account] = $this->createAccessContext();
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
            'execution_enabled' => false,
            'run_health' => 'customer_runtime_ready',
            'settings' => [
                'bismel1_scheduler' => [
                    'next_intended_run' => now()->addHour()->toIso8601String(),
                    'scheduler_status_summary' => 'waiting for next bar close',
                ],
                'bismel1_runtime' => [
                    'last_runtime_status' => 'active',
                    'last_runtime_summary' => 'AI active',
                ],
            ],
        ]);

        $connection = BrokerConnection::query()->create([
            'account_id' => $account->getKey(),
            'name' => 'Primary Alpaca',
            'broker' => 'alpaca',
            'status' => 'connected',
        ]);

        BrokerCredential::query()->create([
            'broker_connection_id' => $connection->getKey(),
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

        AlpacaAccount::query()->create([
            'account_id' => $account->getKey(),
            'broker_connection_id' => $connection->getKey(),
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
            'metadata' => [
                'positions_sync_result' => 'verified',
                'orders_sync_result' => 'verified',
            ],
        ]);

        ApiLicense::query()->create([
            'account_id' => $account->getKey(),
            'name' => 'Primary API',
            'provider' => 'openai',
            'status' => 'active',
            'masked_key' => '****1234',
        ]);

        BotRun::query()->create([
            'account_id' => $account->getKey(),
            'strategy_profile_id' => $strategyProfile->getKey(),
            'automation_setting_id' => $automationSetting->getKey(),
            'run_type' => 'scan',
            'status' => 'completed',
            'risk_level' => 'balanced',
            'started_at' => now()->subMinutes(30),
            'finished_at' => now()->subMinutes(29),
            'summary' => [
                'safe_summary' => 'recent run completed',
            ],
        ]);

        $response = $this->actingAs($user)->get(route('customer.automation.index'));

        $response->assertOk();
        $response->assertSeeText('AI active');
        $response->assertSeeText('waiting for next bar close');
        $response->assertSeeText('Broker Support');
        $response->assertSeeText('Strategy Mapping');
        $response->assertSeeText('Ready');
        $response->assertDontSeeText('EMA');
    }

    public function test_customer_can_start_and_stop_ai_with_relational_runtime_state_updates(): void
    {
        [$user, $account] = $this->createAccessContext();
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

        $connection = BrokerConnection::query()->create([
            'account_id' => $account->getKey(),
            'name' => 'Primary Alpaca',
            'broker' => 'alpaca',
            'status' => 'connected',
        ]);

        BrokerCredential::query()->create([
            'broker_connection_id' => $connection->getKey(),
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

        AlpacaAccount::query()->create([
            'account_id' => $account->getKey(),
            'broker_connection_id' => $connection->getKey(),
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
            'metadata' => [
                'positions_sync_result' => 'verified',
                'orders_sync_result' => 'verified',
            ],
        ]);

        $startResponse = $this->actingAs($user)->put(route('customer.automation.update'), [
            'name' => 'Primary Automation',
            'status' => 'review',
            'risk_level' => 'balanced',
            'ai_enabled' => '1',
            'action_mode' => 'start',
        ]);

        $startResponse->assertRedirect(route('customer.automation.index'));
        $startResponse->assertSessionHas('status', 'Automation runtime was started for the current workspace.');

        $automationSetting = AutomationSetting::query()->firstOrFail();
        $this->assertTrue($automationSetting->ai_enabled);
        $this->assertTrue($automationSetting->scanner_enabled);
        $this->assertSame('armed', $automationSetting->status);
        $this->assertSame('active', data_get($automationSetting->settings, 'bismel1_runtime.last_runtime_status'));

        $stopResponse = $this->actingAs($user)->put(route('customer.automation.update'), [
            'name' => 'Primary Automation',
            'status' => 'armed',
            'risk_level' => 'balanced',
            'ai_enabled' => '0',
            'action_mode' => 'stop',
        ]);

        $stopResponse->assertRedirect(route('customer.automation.index'));
        $stopResponse->assertSessionHas('status', 'Automation runtime was stopped for the current workspace.');

        $this->assertFalse($automationSetting->fresh()->ai_enabled);
        $this->assertFalse($automationSetting->fresh()->scanner_enabled);
        $this->assertSame('draft', $automationSetting->fresh()->status);
        $this->assertSame('stopped', data_get($automationSetting->fresh()->settings, 'bismel1_runtime.last_runtime_status'));
        $this->assertSame('AI stopped', data_get($automationSetting->fresh()->settings, 'bismel1_runtime.last_runtime_summary'));
    }
}
