<?php
// اعوز بالله من الشياطين و ان يحضرون بسم الله الرحمن الرحيم الله لا إله إلا هو الحي القيوم
// Bismillahi ar-Rahmani ar-Rahim Audhu billahi min ash-shayatin wa an yahdurun Bismillah ar-Rahman ar-Rahim Allah la ilaha illa huwa al-hayy al-qayyum. Tamsa Allahu ala ayunihim
// version: x
// ======================================================
// - App Name: bismel1.com
// - Gusgraph LLC -
// - Author: Gus Kazem
// - https://Gusgraph.com
// - File Path: app/Support/Broker/AlpacaAccountSyncService.php
// ======================================================

namespace App\Support\Broker;

use App\Domain\Broker\Enums\BrokerConnectionStatus;
use App\Models\Account;
use App\Models\AlpacaAccount;
use App\Models\AlpacaOrder;
use App\Models\AlpacaPosition;
use App\Models\BrokerConnection;
use App\Models\BrokerCredential;
use App\Support\Automation\Bismel1RuntimeGuardrails;
use Carbon\CarbonImmutable;
use Illuminate\Support\Facades\DB;

class AlpacaAccountSyncService
{
    public function __construct(
        protected AlpacaClient $client,
        protected Bismel1RuntimeGuardrails $bismel1RuntimeGuardrails,
    ) {}

    public function syncLatestForAccount(Account $account): array
    {
        $connection = $account->brokerConnections()
            ->where('broker', 'alpaca')
            ->latest('id')
            ->first();

        if (! $connection) {
            return [
                'status' => 'missing_connection',
                'message' => 'No Alpaca broker connection is stored for this workspace yet.',
            ];
        }

        $alpacaAccount = $account->alpacaAccounts()
            ->where('broker_connection_id', $connection->getKey())
            ->latest('id')
            ->first();

        if (! $this->shouldSync($alpacaAccount)) {
            return [
                'status' => 'fresh',
                'message' => 'Existing Alpaca account readiness data is still fresh.',
            ];
        }

        return $this->syncConnection($connection);
    }

    public function syncConnection(BrokerConnection $connection): array
    {
        $credential = $connection->brokerCredentials()->latest('id')->first();

        if (! $credential) {
            return $this->recordFailure(
                $connection,
                null,
                'missing_credentials',
                'No saved Alpaca credentials are available for sync.',
                null,
                null
            );
        }

        $result = $this->client->fetchAccount($credential);

        if ($result['status'] !== 'verified') {
            return $this->recordFailure(
                $connection,
                $credential,
                $result['status'],
                $result['message'],
                $result['environment'] ?? null,
                $result['http_status'] ?? null
            );
        }

        $positionsResult = $this->client->fetchPositions($credential);
        $ordersResult = $this->client->fetchRecentOrders($credential);

        return $this->recordSuccess(
            $connection,
            $credential,
            is_array($result['account']) ? $result['account'] : [],
            (string) ($result['environment'] ?? 'paper'),
            $result['http_status'] ?? 200,
            $positionsResult,
            $ordersResult,
        );
    }

    protected function recordSuccess(
        BrokerConnection $connection,
        BrokerCredential $credential,
        array $accountPayload,
        string $environment,
        ?int $httpStatus,
        array $positionsResult,
        array $ordersResult,
    ): array {
        $syncedAt = now();
        $marketDataFeed = (string) data_get($credential->credential_payload, 'market_data_feed', 'iex');
        $positionsPayload = $this->positionsPayload($positionsResult);
        $ordersPayload = $this->ordersPayload($ordersResult);
        $positionsSucceeded = $positionsResult['status'] === 'verified';
        $ordersSucceeded = $ordersResult['status'] === 'verified';
        $overallStatus = $positionsSucceeded && $ordersSucceeded ? 'verified' : 'partial_success';
        $overallMessage = $positionsSucceeded && $ordersSucceeded
            ? 'Alpaca account, positions, and recent orders were synced successfully.'
            : implode(' / ', array_filter([
                'Alpaca account verification succeeded.',
                $positionsSucceeded ? 'Positions synced.' : ($positionsResult['message'] ?? 'Positions sync failed.'),
                $ordersSucceeded ? 'Orders synced.' : ($ordersResult['message'] ?? 'Orders sync failed.'),
            ]));

        DB::transaction(function () use ($connection, $credential, $accountPayload, $environment, $httpStatus, $syncedAt, $marketDataFeed, $positionsPayload, $ordersPayload, $positionsSucceeded, $ordersSucceeded, $overallStatus, $overallMessage, $positionsResult, $ordersResult): void {
            $connection->forceFill([
                'status' => BrokerConnectionStatus::Connected->value,
                'connected_at' => $connection->connected_at ?? $syncedAt,
                'last_synced_at' => $syncedAt,
            ])->save();

            $credential->forceFill([
                'provider' => 'alpaca',
                'environment' => $environment,
                'status' => 'verified',
                'key_last_four' => $credential->key_last_four ?: substr((string) data_get($credential->credential_payload, 'access_key_id', ''), -4) ?: null,
                'secret_hint' => $credential->secret_hint ?: substr((string) data_get($credential->credential_payload, 'access_secret', ''), -2) ?: null,
                'last_used_at' => $syncedAt,
            ])->save();

            $existingAlpacaAccount = AlpacaAccount::query()
                ->where('account_id', $connection->account_id)
                ->where('broker_connection_id', $connection->getKey())
                ->first();

            $alpacaAccount = AlpacaAccount::query()->updateOrCreate(
                [
                    'account_id' => $connection->account_id,
                    'broker_connection_id' => $connection->getKey(),
                ],
                [
                    'broker_credential_id' => $credential->getKey(),
                    'name' => $connection->name,
                    'environment' => $environment,
                    'data_feed' => $marketDataFeed,
                    'status' => (string) ($accountPayload['status'] ?? 'verified'),
                    'sync_status' => $overallStatus === 'verified' ? 'success' : 'partial_success',
                    'trade_stream_status' => 'credentials_verified',
                    'alpaca_account_id' => $accountPayload['id'] ?? null,
                    'account_number' => $accountPayload['account_number'] ?? null,
                    'buying_power' => $this->decimalOrNull($accountPayload['buying_power'] ?? null),
                    'cash' => $this->decimalOrNull($accountPayload['cash'] ?? null),
                    'equity' => $this->decimalOrNull($accountPayload['equity'] ?? null),
                    'last_synced_at' => $syncedAt,
                    'last_account_sync_at' => $syncedAt,
                    'last_positions_sync_at' => $positionsSucceeded ? $syncedAt : $existingAlpacaAccount?->last_positions_sync_at,
                    'last_orders_sync_at' => $ordersSucceeded ? $syncedAt : $existingAlpacaAccount?->last_orders_sync_at,
                    'metadata' => [
                        'access_mode' => data_get($credential->credential_payload, 'access_mode', 'read_only'),
                        'market_data_feed' => $marketDataFeed,
                        'last_sync_result' => $overallStatus,
                        'last_sync_message' => $overallMessage,
                        'last_http_status' => $httpStatus,
                        'positions_sync_result' => $positionsResult['status'] ?? 'not_run',
                        'positions_sync_message' => $positionsResult['message'] ?? null,
                        'positions_count' => count($positionsPayload),
                        'positions_http_status' => $positionsResult['http_status'] ?? null,
                        'orders_sync_result' => $ordersResult['status'] ?? 'not_run',
                        'orders_sync_message' => $ordersResult['message'] ?? null,
                        'orders_count' => count($ordersPayload),
                        'orders_http_status' => $ordersResult['http_status'] ?? null,
                    ],
                ]
            );

            $alpacaAccount->forceFill([
                'metadata' => array_merge(is_array($alpacaAccount->metadata) ? $alpacaAccount->metadata : [], [
                    'runtime_guardrail_summary' => (string) data_get($this->bismel1RuntimeGuardrails->runtimeAccountGuard($alpacaAccount), 'summary', 'paper-trading broker runtime is ready'),
                ]),
            ])->save();

            if ($positionsSucceeded) {
                $this->syncPositions($alpacaAccount, $connection, $positionsPayload, $syncedAt);
            }

            if ($ordersSucceeded) {
                $this->syncOrders($alpacaAccount, $connection, $ordersPayload, $syncedAt);
            }
        });

        return [
            'status' => $overallStatus,
            'message' => $overallMessage,
        ];
    }

    protected function recordFailure(
        BrokerConnection $connection,
        ?BrokerCredential $credential,
        string $syncStatus,
        string $message,
        ?string $environment,
        ?int $httpStatus,
    ): array {
        $syncedAt = now();
        $marketDataFeed = (string) data_get($credential?->credential_payload, 'market_data_feed', 'iex');

        DB::transaction(function () use ($connection, $credential, $syncStatus, $message, $environment, $httpStatus, $syncedAt, $marketDataFeed): void {
            $connection->forceFill([
                'status' => BrokerConnectionStatus::Error->value,
                'last_synced_at' => $syncedAt,
            ])->save();

            if ($credential) {
                $credential->forceFill([
                    'provider' => 'alpaca',
                    'environment' => $environment ?: $credential->environment ?: 'paper',
                    'status' => 'error',
                    'key_last_four' => $credential->key_last_four ?: substr((string) data_get($credential->credential_payload, 'access_key_id', ''), -4) ?: null,
                    'secret_hint' => $credential->secret_hint ?: substr((string) data_get($credential->credential_payload, 'access_secret', ''), -2) ?: null,
                    'last_used_at' => $syncedAt,
                ])->save();
            }

            AlpacaAccount::query()->updateOrCreate(
                [
                    'account_id' => $connection->account_id,
                    'broker_connection_id' => $connection->getKey(),
                ],
                [
                    'broker_credential_id' => $credential?->getKey(),
                    'name' => $connection->name,
                    'environment' => $environment ?: ($credential?->environment ?: 'paper'),
                    'data_feed' => $marketDataFeed,
                    'status' => 'sync_failed',
                    'sync_status' => $syncStatus,
                    'trade_stream_status' => 'not_ready',
                    'last_synced_at' => $syncedAt,
                    'last_account_sync_at' => $syncedAt,
                    'metadata' => [
                        'access_mode' => data_get($credential?->credential_payload, 'access_mode', 'read_only'),
                        'market_data_feed' => $marketDataFeed,
                        'last_sync_result' => $syncStatus,
                        'last_sync_message' => $message,
                        'last_http_status' => $httpStatus,
                        'runtime_guardrail_summary' => $message,
                    ],
                ]
            );
        });

        return [
            'status' => $syncStatus,
            'message' => $message,
        ];
    }

    protected function shouldSync(?AlpacaAccount $alpacaAccount): bool
    {
        if (
            ! $alpacaAccount
            || ! $alpacaAccount->last_account_sync_at
            || ! $alpacaAccount->last_positions_sync_at
            || ! $alpacaAccount->last_orders_sync_at
        ) {
            return true;
        }

        $ttlMinutes = (int) config('alpaca.sync_ttl_minutes', 15);
        $staleThreshold = now()->subMinutes($ttlMinutes);

        return $alpacaAccount->last_account_sync_at->lte($staleThreshold)
            || $alpacaAccount->last_positions_sync_at->lte($staleThreshold)
            || $alpacaAccount->last_orders_sync_at->lte($staleThreshold);
    }

    protected function decimalOrNull(mixed $value): ?string
    {
        if (! is_numeric($value)) {
            return null;
        }

        return number_format((float) $value, 2, '.', '');
    }

    protected function decimalStringOrNull(mixed $value, int $scale = 6): ?string
    {
        if (! is_numeric($value)) {
            return null;
        }

        return number_format((float) $value, $scale, '.', '');
    }

    protected function positionsPayload(array $result): array
    {
        $payload = $result['positions'] ?? [];

        return is_array($payload) ? array_values(array_filter($payload, 'is_array')) : [];
    }

    protected function ordersPayload(array $result): array
    {
        $payload = $result['orders'] ?? [];

        return is_array($payload) ? array_values(array_filter($payload, 'is_array')) : [];
    }

    protected function syncPositions(AlpacaAccount $alpacaAccount, BrokerConnection $connection, array $positionsPayload, $syncedAt): void
    {
        $symbols = [];

        foreach ($positionsPayload as $positionPayload) {
            $symbol = strtoupper(trim((string) ($positionPayload['symbol'] ?? '')));

            if ($symbol === '') {
                continue;
            }

            $symbols[] = $symbol;

            AlpacaPosition::query()->updateOrCreate(
                [
                    'alpaca_account_id' => $alpacaAccount->getKey(),
                    'symbol' => $symbol,
                ],
                [
                    'account_id' => $alpacaAccount->account_id,
                    'broker_connection_id' => $connection->getKey(),
                    'alpaca_asset_id' => $positionPayload['asset_id'] ?? null,
                    'asset_class' => strtolower((string) ($positionPayload['asset_class'] ?? 'equity')),
                    'exchange' => $positionPayload['exchange'] ?? null,
                    'side' => $positionPayload['side'] ?? null,
                    'qty' => $this->decimalStringOrNull($positionPayload['qty'] ?? null),
                    'qty_available' => $this->decimalStringOrNull($positionPayload['qty_available'] ?? null),
                    'market_value' => $this->decimalOrNull($positionPayload['market_value'] ?? null),
                    'cost_basis' => $this->decimalOrNull($positionPayload['cost_basis'] ?? null),
                    'current_price' => $this->decimalStringOrNull($positionPayload['current_price'] ?? null),
                    'avg_entry_price' => $this->decimalStringOrNull($positionPayload['avg_entry_price'] ?? null),
                    'unrealized_pl' => $this->decimalOrNull($positionPayload['unrealized_pl'] ?? null),
                    'unrealized_plpc' => $this->decimalStringOrNull($positionPayload['unrealized_plpc'] ?? null),
                    'change_today' => $this->decimalStringOrNull($positionPayload['change_today'] ?? null),
                    'synced_at' => $syncedAt,
                ]
            );
        }

        $query = AlpacaPosition::query()->where('alpaca_account_id', $alpacaAccount->getKey());

        if ($symbols === []) {
            $query->delete();

            return;
        }

        $query->whereNotIn('symbol', $symbols)->delete();
    }

    protected function syncOrders(AlpacaAccount $alpacaAccount, BrokerConnection $connection, array $ordersPayload, $syncedAt): void
    {
        foreach ($ordersPayload as $orderPayload) {
            $alpacaOrderId = trim((string) ($orderPayload['id'] ?? ''));
            $symbol = strtoupper(trim((string) ($orderPayload['symbol'] ?? '')));

            if ($alpacaOrderId === '' || $symbol === '') {
                continue;
            }

            AlpacaOrder::query()->updateOrCreate(
                [
                    'alpaca_order_id' => $alpacaOrderId,
                ],
                [
                    'account_id' => $alpacaAccount->account_id,
                    'alpaca_account_id' => $alpacaAccount->getKey(),
                    'broker_connection_id' => $connection->getKey(),
                    'client_order_id' => $orderPayload['client_order_id'] ?? null,
                    'alpaca_asset_id' => $orderPayload['asset_id'] ?? null,
                    'symbol' => $symbol,
                    'asset_class' => strtolower((string) ($orderPayload['asset_class'] ?? 'equity')),
                    'side' => $orderPayload['side'] ?? null,
                    'order_type' => $orderPayload['type'] ?? null,
                    'time_in_force' => $orderPayload['time_in_force'] ?? null,
                    'status' => $orderPayload['status'] ?? null,
                    'status_summary' => $this->bismel1RuntimeGuardrails->orderStatusSummary($orderPayload['status'] ?? null),
                    'qty' => $this->decimalStringOrNull($orderPayload['qty'] ?? null),
                    'filled_qty' => $this->decimalStringOrNull($orderPayload['filled_qty'] ?? null),
                    'notional' => $this->decimalOrNull($orderPayload['notional'] ?? null),
                    'limit_price' => $this->decimalStringOrNull($orderPayload['limit_price'] ?? null),
                    'stop_price' => $this->decimalStringOrNull($orderPayload['stop_price'] ?? null),
                    'filled_avg_price' => $this->decimalStringOrNull($orderPayload['filled_avg_price'] ?? null),
                    'submitted_at' => $this->timestampOrNull($orderPayload['submitted_at'] ?? null),
                    'filled_at' => $this->timestampOrNull($orderPayload['filled_at'] ?? null),
                    'canceled_at' => $this->timestampOrNull($orderPayload['canceled_at'] ?? null),
                    'expired_at' => $this->timestampOrNull($orderPayload['expired_at'] ?? null),
                    'failed_at' => $this->timestampOrNull($orderPayload['failed_at'] ?? null),
                    'synced_at' => $syncedAt,
                ]
            );
        }
    }

    protected function timestampOrNull(mixed $value): ?CarbonImmutable
    {
        if (! is_string($value) || trim($value) === '') {
            return null;
        }

        try {
            return CarbonImmutable::parse($value);
        } catch (\Throwable) {
            return null;
        }
    }
}
