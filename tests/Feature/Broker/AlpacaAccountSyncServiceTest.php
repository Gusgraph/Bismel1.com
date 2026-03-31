<?php
// اعوز بالله من الشياطين و ان يحضرون بسم الله الرحمن الرحيم الله لا إله إلا هو الحي القيوم
// Bismillahi ar-Rahmani ar-Rahim Audhu billahi min ash-shayatin wa an yahdurun Bismillah ar-Rahman ar-Rahim Allah la ilaha illa huwa al-hayy al-qayyum. Tamsa Allahu ala ayunihim
// version: x
// ======================================================
// - App Name: gusgraph-trading
// - Gusgraph LLC -
// - Author: Gus Kazem
// - https://Gusgraph.com
// - File Path: tests/Feature/Broker/AlpacaAccountSyncServiceTest.php
// ======================================================

namespace Tests\Feature\Broker;

use App\Models\Account;
use App\Models\BrokerConnection;
use App\Models\BrokerCredential;
use App\Support\Broker\AlpacaAccountSyncService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class AlpacaAccountSyncServiceTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_syncs_safe_alpaca_account_readiness_state(): void
    {
        Http::fake([
            'https://paper-api.alpaca.markets/v2/account' => Http::response([
                'id' => 'alpaca-account-123',
                'account_number' => 'PA1234567',
                'status' => 'ACTIVE',
                'buying_power' => '12000.55',
                'cash' => '4500.10',
                'equity' => '13000.75',
            ], 200),
            'https://paper-api.alpaca.markets/v2/positions' => Http::response([], 200),
            'https://paper-api.alpaca.markets/v2/orders*' => Http::response([], 200),
        ]);

        $account = Account::factory()->create();
        $connection = BrokerConnection::query()->create([
            'account_id' => $account->getKey(),
            'name' => 'Primary Alpaca',
            'broker' => 'alpaca',
            'status' => 'pending',
        ]);

        BrokerCredential::query()->create([
            'broker_connection_id' => $connection->getKey(),
            'label' => 'Primary Credential',
            'provider' => 'alpaca',
            'status' => 'saved',
            'environment' => 'paper',
            'access_mode' => 'read_only',
            'credential_payload' => [
                'provider' => 'alpaca',
                'environment' => 'paper',
                'access_mode' => 'read_only',
                'market_data_feed' => 'iex',
                'access_key_id' => 'PKTEST1234',
                'access_secret' => 'SECRET99',
            ],
            'is_encrypted' => true,
        ]);

        $result = app(AlpacaAccountSyncService::class)->syncLatestForAccount($account);

        $this->assertSame('verified', $result['status']);

        $connection->refresh();
        $alpacaAccount = $account->alpacaAccounts()->first();

        $this->assertSame('connected', $connection->status->value);
        $this->assertNotNull($connection->last_synced_at);
        $this->assertNotNull($alpacaAccount);
        $this->assertSame('success', $alpacaAccount->sync_status);
        $this->assertSame('credentials_verified', $alpacaAccount->trade_stream_status);
        $this->assertSame('alpaca-account-123', $alpacaAccount->alpaca_account_id);
        $this->assertSame('iex', $alpacaAccount->data_feed);
        $this->assertSame('verified', data_get($alpacaAccount->metadata, 'last_sync_result'));
        $this->assertNotNull($alpacaAccount->last_positions_sync_at);
        $this->assertNotNull($alpacaAccount->last_orders_sync_at);
        $this->assertSame(0, (int) data_get($alpacaAccount->metadata, 'positions_count'));
        $this->assertSame(0, (int) data_get($alpacaAccount->metadata, 'orders_count'));
    }

    public function test_it_blocks_sync_when_runtime_config_is_invalid(): void
    {
        Http::fake();
        config(['alpaca.timeout' => 0]);

        $account = Account::factory()->create();
        $connection = BrokerConnection::query()->create([
            'account_id' => $account->getKey(),
            'name' => 'Primary Alpaca',
            'broker' => 'alpaca',
            'status' => 'pending',
        ]);

        BrokerCredential::query()->create([
            'broker_connection_id' => $connection->getKey(),
            'label' => 'Primary Credential',
            'provider' => 'alpaca',
            'status' => 'saved',
            'environment' => 'paper',
            'access_mode' => 'read_only',
            'credential_payload' => [
                'provider' => 'alpaca',
                'environment' => 'paper',
                'access_mode' => 'read_only',
                'market_data_feed' => 'iex',
                'access_key_id' => 'PKTEST1234',
                'access_secret' => 'SECRET99',
            ],
            'is_encrypted' => true,
        ]);

        $result = app(AlpacaAccountSyncService::class)->syncLatestForAccount($account);

        $this->assertSame('config_blocked', $result['status']);
        $this->assertSame('runtime config blocked broker requests', $result['message']);
    }
}
