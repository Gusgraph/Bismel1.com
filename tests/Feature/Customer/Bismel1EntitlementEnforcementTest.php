<?php
// اعوز بالله من الشياطين و ان يحضرون بسم الله الرحمن الرحيم الله لا إله إلا هو الحي القيوم
// Bismillahi ar-Rahmani ar-Rahim Audhu billahi min ash-shayatin wa an yahdurun Bismillah ar-Rahman ar-Rahim Allah la ilaha illa huwa al-hayy al-qayyum. Tamsa Allahu ala ayunihim
// version: x
// ======================================================
// - App Name: gusgraph-trading
// - Gusgraph LLC -
// - Author: Gus Kazem
// - https://Gusgraph.com
// - File Path: tests/Feature/Customer/Bismel1EntitlementEnforcementTest.php
// ======================================================

namespace Tests\Feature\Customer;

use App\Models\AlpacaAccount;
use App\Models\AutomationSetting;
use App\Models\BrokerConnection;
use App\Models\BrokerCredential;
use App\Models\StrategyProfile;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Concerns\CreatesAccessContext;
use Tests\Concerns\CreatesBismel1Entitlements;
use Tests\TestCase;

class Bismel1EntitlementEnforcementTest extends TestCase
{
    use CreatesAccessContext;
    use CreatesBismel1Entitlements;
    use RefreshDatabase;

    public function test_customer_automation_start_is_blocked_when_the_plan_is_scanner_only(): void
    {
        [$user, $account] = $this->createAccessContext();
        $this->seedConfirmedBismel1Subscription($account, 'BISMILLAH_AI_SCANNER');

        StrategyProfile::query()->create([
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
            'last_synced_at' => now()->subMinutes(5),
            'last_account_sync_at' => now()->subMinutes(5),
        ]);

        $response = $this->actingAs($user)->put(route('customer.automation.update'), [
            'name' => 'Primary Automation',
            'status' => 'review',
            'risk_level' => 'balanced',
            'ai_enabled' => '1',
            'action_mode' => 'start',
        ]);

        $response->assertRedirect(route('customer.automation.index'));
        $response->assertSessionHas('status', 'Automation runtime was not started because plan does not include this automation mode.');

        $automationSetting = AutomationSetting::query()->firstOrFail();
        $this->assertFalse($automationSetting->ai_enabled);
        $this->assertFalse($automationSetting->scanner_enabled);
        $this->assertSame('blocked', data_get($automationSetting->settings, 'bismel1_runtime.last_runtime_status'));
        $this->assertSame('plan does not include this automation mode', data_get($automationSetting->settings, 'bismel1_runtime.last_runtime_summary'));
    }

    public function test_customer_strategy_page_shows_when_the_selected_strategy_mode_is_not_paid_for(): void
    {
        [$user, $account] = $this->createAccessContext();
        $this->seedConfirmedBismel1Subscription($account, 'BISMILLAH_AI_SCANNER');

        StrategyProfile::query()->create([
            'account_id' => $account->getKey(),
            'name' => 'Bismel1 Swing',
            'mode' => 'stocks_swing',
            'timeframe' => '4h_1d',
            'symbol_scope' => 'watchlist',
            'style' => 'balanced',
            'engine' => 'python',
            'is_active' => true,
        ]);

        $response = $this->actingAs($user)->get(route('customer.strategy.index'));

        $response->assertOk();
        $response->assertSeeText('Selected Mode Access');
        $response->assertSeeText('Blocked');
        $response->assertSeeText('plan does not include this automation mode');
        $response->assertDontSeeText('EMA');
    }

    public function test_customer_broker_store_blocks_a_second_linked_account_without_the_additional_account_add_on(): void
    {
        [$user, $account] = $this->createAccessContext();
        $this->seedConfirmedBismel1Subscription($account, 'BISMILLAH1_BOT_EXECUTE_BASIC');

        $existingConnection = BrokerConnection::query()->create([
            'account_id' => $account->getKey(),
            'name' => 'Existing Alpaca',
            'broker' => 'alpaca',
            'status' => 'connected',
        ]);

        BrokerCredential::query()->create([
            'broker_connection_id' => $existingConnection->getKey(),
            'label' => 'Existing Credential',
            'provider' => 'alpaca',
            'status' => 'verified',
            'environment' => 'paper',
            'access_mode' => 'trade_disabled',
            'credential_payload' => [
                'provider' => 'alpaca',
                'environment' => 'paper',
                'access_mode' => 'trade_disabled',
                'market_data_feed' => 'iex',
                'access_key_id' => 'PKTEST1234',
                'access_secret' => 'SECRET99',
            ],
            'is_encrypted' => true,
        ]);

        AlpacaAccount::query()->create([
            'account_id' => $account->getKey(),
            'broker_connection_id' => $existingConnection->getKey(),
            'name' => 'Existing Alpaca',
            'environment' => 'paper',
            'data_feed' => 'iex',
            'status' => 'active',
            'sync_status' => 'success',
            'trade_stream_status' => 'credentials_verified',
            'is_primary' => true,
            'is_active' => true,
        ]);

        $response = $this->actingAs($user)->post(route('customer.broker.store'), [
            'provider' => 'alpaca',
            'account_label' => 'Second Alpaca',
            'access_mode' => 'trade_disabled',
            'environment' => 'paper',
            'market_data_feed' => 'iex',
            'access_key_id' => 'PKTEST5678',
            'access_secret' => 'SECRET88',
        ]);

        $response->assertRedirect(route('customer.broker.create'));
        $response->assertSessionHas('status', 'Alpaca access was not saved because additional account limit reached.');
        $this->assertSame(1, BrokerConnection::query()->count());
    }
}
