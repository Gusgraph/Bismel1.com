<?php
// اعوز بالله من الشياطين و ان يحضرون بسم الله الرحمن الرحيم الله لا إله إلا هو الحي القيوم
// Bismillahi ar-Rahmani ar-Rahim Audhu billahi min ash-shayatin wa an yahdurun Bismillah ar-Rahman ar-Rahim Allah la ilaha illa huwa al-hayy al-qayyum. Tamsa Allahu ala ayunihim
// version: x
// ======================================================
// - App Name: gusgraph-trading
// - Gusgraph LLC -
// - Author: Gus Kazem
// - https://Gusgraph.com
// - File Path: tests/Feature/Broker/AlpacaMarketDataServiceTest.php
// ======================================================

namespace Tests\Feature\Broker;

use App\Models\Account;
use App\Models\AlpacaAccount;
use App\Models\BrokerConnection;
use App\Models\BrokerCredential;
use App\Support\Broker\AlpacaMarketDataService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class AlpacaMarketDataServiceTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_syncs_iex_1h_and_4h_bars_into_relational_storage(): void
    {
        Http::fake([
            'https://paper-api.alpaca.markets/v2/stocks/bars*' => Http::response([
                'bars' => [
                    'AAPL' => [
                        ['t' => '2026-03-28T12:00:00Z', 'o' => 100, 'h' => 103, 'l' => 99, 'c' => 102, 'v' => 1000, 'n' => 10, 'vw' => 101.5],
                        ['t' => '2026-03-28T13:00:00Z', 'o' => 102, 'h' => 104, 'l' => 101, 'c' => 103, 'v' => 1100, 'n' => 11, 'vw' => 102.8],
                        ['t' => '2026-03-28T14:00:00Z', 'o' => 103, 'h' => 105, 'l' => 102, 'c' => 104, 'v' => 1200, 'n' => 12, 'vw' => 103.7],
                        ['t' => '2026-03-28T15:00:00Z', 'o' => 104, 'h' => 106, 'l' => 103, 'c' => 105, 'v' => 1300, 'n' => 13, 'vw' => 104.8],
                        ['t' => '2026-03-28T16:00:00Z', 'o' => 105, 'h' => 107, 'l' => 104, 'c' => 106, 'v' => 1400, 'n' => 14, 'vw' => 105.7],
                        ['t' => '2026-03-28T17:00:00Z', 'o' => 106, 'h' => 108, 'l' => 105, 'c' => 107, 'v' => 1500, 'n' => 15, 'vw' => 106.7],
                        ['t' => '2026-03-28T18:00:00Z', 'o' => 107, 'h' => 109, 'l' => 106, 'c' => 108, 'v' => 1600, 'n' => 16, 'vw' => 107.6],
                        ['t' => '2026-03-28T19:00:00Z', 'o' => 108, 'h' => 110, 'l' => 107, 'c' => 109, 'v' => 1700, 'n' => 17, 'vw' => 108.7],
                    ],
                ],
            ], 200),
        ]);

        $account = Account::factory()->create();
        $connection = BrokerConnection::query()->create([
            'account_id' => $account->getKey(),
            'name' => 'Primary Alpaca',
            'broker' => 'alpaca',
            'status' => 'connected',
        ]);

        $credential = BrokerCredential::query()->create([
            'broker_connection_id' => $connection->getKey(),
            'label' => 'Primary Credential',
            'provider' => 'alpaca',
            'status' => 'verified',
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
            'key_last_four' => '1234',
            'secret_hint' => '99',
            'is_encrypted' => true,
        ]);

        $alpacaAccount = AlpacaAccount::query()->create([
            'account_id' => $account->getKey(),
            'broker_connection_id' => $connection->getKey(),
            'broker_credential_id' => $credential->getKey(),
            'name' => 'Primary Alpaca',
            'environment' => 'paper',
            'data_feed' => 'iex',
            'status' => 'active',
            'sync_status' => 'success',
            'trade_stream_status' => 'credentials_verified',
            'is_primary' => true,
            'is_active' => true,
            'metadata' => [],
        ]);

        $result = app(AlpacaMarketDataService::class)->syncLatestForAccount($account, ['AAPL'], ['1H', '4H'], 2);

        $this->assertSame('synced', $result['status']);

        $alpacaAccount->refresh();

        $this->assertDatabaseCount('alpaca_bars', 4);
        $this->assertDatabaseHas('alpaca_bars', [
            'account_id' => $account->getKey(),
            'alpaca_account_id' => $alpacaAccount->getKey(),
            'symbol' => 'AAPL',
            'timeframe' => '1H',
            'feed' => 'iex',
        ]);
        $this->assertDatabaseHas('alpaca_bars', [
            'account_id' => $account->getKey(),
            'alpaca_account_id' => $alpacaAccount->getKey(),
            'symbol' => 'AAPL',
            'timeframe' => '4H',
            'feed' => 'iex',
        ]);
        $this->assertSame('synced', data_get($alpacaAccount->metadata, 'bars_sync_result'));
        $this->assertSame(2, (int) data_get($alpacaAccount->metadata, 'bars_1h_count'));
        $this->assertSame(2, (int) data_get($alpacaAccount->metadata, 'bars_4h_count'));
        $this->assertSame('IEX', data_get($alpacaAccount->metadata, 'bars_feed'));
    }
}
