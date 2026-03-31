<?php
// اعوز بالله من الشياطين و ان يحضرون بسم الله الرحمن الرحيم الله لا إله إلا هو الحي القيوم
// Bismillahi ar-Rahmani ar-Rahim Audhu billahi min ash-shayatin wa an yahdurun Bismillah ar-Rahman ar-Rahim Allah la ilaha illa huwa al-hayy al-qayyum. Tamsa Allahu ala ayunihim
// version: x
// ======================================================
// - App Name: gusgraph-trading
// - Gusgraph LLC -
// - Author: Gus Kazem
// - https://Gusgraph.com
// - File Path: tests/Feature/Broker/AlpacaBrokerStateSyncTest.php
// ======================================================

namespace Tests\Feature\Broker;

use App\Models\Account;
use App\Models\BrokerConnection;
use App\Models\BrokerCredential;
use App\Support\Broker\AlpacaAccountSyncService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class AlpacaBrokerStateSyncTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_syncs_safe_positions_and_recent_orders_into_relational_storage(): void
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
            'https://paper-api.alpaca.markets/v2/positions' => Http::response([
                [
                    'asset_id' => 'asset-aapl',
                    'symbol' => 'AAPL',
                    'asset_class' => 'us_equity',
                    'exchange' => 'NASDAQ',
                    'side' => 'long',
                    'qty' => '3',
                    'qty_available' => '3',
                    'market_value' => '540.00',
                    'cost_basis' => '500.00',
                    'current_price' => '180.00',
                    'avg_entry_price' => '166.666667',
                    'unrealized_pl' => '40.00',
                    'unrealized_plpc' => '0.080000',
                    'change_today' => '0.012500',
                ],
            ], 200),
            'https://paper-api.alpaca.markets/v2/orders*' => Http::response([
                [
                    'id' => 'order-1',
                    'client_order_id' => 'client-1',
                    'asset_id' => 'asset-aapl',
                    'symbol' => 'AAPL',
                    'asset_class' => 'us_equity',
                    'side' => 'buy',
                    'type' => 'limit',
                    'time_in_force' => 'day',
                    'status' => 'filled',
                    'qty' => '3',
                    'filled_qty' => '3',
                    'notional' => null,
                    'limit_price' => '166.67',
                    'stop_price' => null,
                    'filled_avg_price' => '166.67',
                    'submitted_at' => '2026-03-29T09:30:00Z',
                    'filled_at' => '2026-03-29T09:31:00Z',
                    'canceled_at' => null,
                    'expired_at' => null,
                    'failed_at' => null,
                ],
            ], 200),
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

        $alpacaAccount = $account->fresh()->alpacaAccounts()->first();

        $this->assertNotNull($alpacaAccount);
        $this->assertNotNull($alpacaAccount->last_positions_sync_at);
        $this->assertNotNull($alpacaAccount->last_orders_sync_at);
        $this->assertSame(1, (int) data_get($alpacaAccount->metadata, 'positions_count'));
        $this->assertSame(1, (int) data_get($alpacaAccount->metadata, 'orders_count'));

        $this->assertDatabaseHas('alpaca_positions', [
            'account_id' => $account->getKey(),
            'alpaca_account_id' => $alpacaAccount->getKey(),
            'symbol' => 'AAPL',
            'side' => 'long',
        ]);

        $this->assertDatabaseHas('alpaca_orders', [
            'account_id' => $account->getKey(),
            'alpaca_account_id' => $alpacaAccount->getKey(),
            'alpaca_order_id' => 'order-1',
            'symbol' => 'AAPL',
            'status' => 'filled',
        ]);
    }
}
