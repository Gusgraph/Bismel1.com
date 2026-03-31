<?php
// اعوز بالله من الشياطين و ان يحضرون بسم الله الرحمن الرحيم الله لا إله إلا هو الحي القيوم
// Bismillahi ar-Rahmani ar-Rahim Audhu billahi min ash-shayatin wa an yahdurun Bismillah ar-Rahman ar-Rahim Allah la ilaha illa huwa al-hayy al-qayyum. Tamsa Allahu ala ayunihim
// version: x
// ======================================================
// - App Name: gusgraph-trading
// - Gusgraph LLC -
// - Author: Gus Kazem
// - https://Gusgraph.com
// - File Path: app/Support/Broker/AlpacaClient.php
// ======================================================

namespace App\Support\Broker;

use App\Models\BrokerCredential;
use App\Support\Automation\Bismel1RuntimeGuardrails;
use Carbon\CarbonInterface;
use Illuminate\Http\Client\Response;
use Illuminate\Support\Facades\Http;

class AlpacaClient
{
    public function __construct(
        protected Bismel1RuntimeGuardrails $bismel1RuntimeGuardrails,
    ) {}

    public function fetchAccount(BrokerCredential $credential): array
    {
        return $this->request($credential, '/v2/account', 'account verification', 'account');
    }

    public function fetchPositions(BrokerCredential $credential): array
    {
        return $this->request($credential, '/v2/positions', 'positions sync', 'positions');
    }

    public function fetchRecentOrders(BrokerCredential $credential): array
    {
        return $this->request(
            $credential,
            '/v2/orders',
            'orders sync',
            'orders',
            [
                'status' => 'all',
                'direction' => 'desc',
                'limit' => (int) config('alpaca.recent_orders_limit', 50),
                'nested' => 'false',
            ]
        );
    }

    public function fetchStockBars(
        BrokerCredential $credential,
        string $symbol,
        string $timeframe,
        CarbonInterface $start,
        CarbonInterface $end,
        string $feed = 'iex',
        int $limit = 500,
    ): array {
        return $this->request(
            $credential,
            '/v2/stocks/bars',
            'market-data bars fetch',
            'bars',
            [
                'symbols' => strtoupper(trim($symbol)),
                'timeframe' => $timeframe,
                'start' => $start->toIso8601String(),
                'end' => $end->toIso8601String(),
                'feed' => strtolower(trim($feed)),
                'limit' => $limit,
                'sort' => 'asc',
                'adjustment' => 'raw',
            ]
        );
    }

    public function submitOrder(BrokerCredential $credential, array $payload): array
    {
        return $this->postRequest($credential, '/v2/orders', 'order submission', 'order', $payload);
    }

    protected function request(
        BrokerCredential $credential,
        string $path,
        string $operation,
        string $payloadKey,
        array $query = [],
    ): array {
        $configGuard = $this->bismel1RuntimeGuardrails->configGuard();

        if (! $configGuard['allowed']) {
            return [
                'status' => 'config_blocked',
                'message' => $configGuard['summary'],
                'http_status' => null,
                'environment' => 'paper',
                $payloadKey => null,
            ];
        }

        $authentication = $this->credentialContext($credential);

        if ($authentication['status'] !== 'ok') {
            return [
                'status' => 'missing_credentials',
                'message' => 'Stored Alpaca credentials are incomplete for '.$operation.'.',
                'http_status' => null,
                'environment' => $authentication['environment'],
                $payloadKey => null,
            ];
        }

        $response = Http::timeout((int) config('alpaca.timeout', 10))
            ->acceptJson()
            ->withHeaders([
                'APCA-API-KEY-ID' => $authentication['key_id'],
                'APCA-API-SECRET-KEY' => $authentication['secret'],
            ])
            ->get($this->resourceUrl($authentication['environment'], $path), $query);

        return $this->mapResponse($response, $authentication['environment'], $operation, $payloadKey);
    }

    protected function postRequest(
        BrokerCredential $credential,
        string $path,
        string $operation,
        string $payloadKey,
        array $payload = [],
    ): array {
        $configGuard = $this->bismel1RuntimeGuardrails->configGuard();

        if (! $configGuard['allowed']) {
            return [
                'status' => 'config_blocked',
                'message' => $configGuard['summary'],
                'http_status' => null,
                'environment' => 'paper',
                $payloadKey => null,
            ];
        }

        $authentication = $this->credentialContext($credential);

        if ($authentication['status'] !== 'ok') {
            return [
                'status' => 'missing_credentials',
                'message' => 'Stored Alpaca credentials are incomplete for '.$operation.'.',
                'http_status' => null,
                'environment' => $authentication['environment'],
                $payloadKey => null,
            ];
        }

        $environmentGuard = $this->bismel1RuntimeGuardrails->executionEnvironmentGuard($authentication['environment']);

        if (! $environmentGuard['allowed']) {
            return [
                'status' => 'environment_blocked',
                'message' => $environmentGuard['summary'],
                'http_status' => null,
                'environment' => $authentication['environment'],
                $payloadKey => null,
            ];
        }

        $response = Http::timeout((int) config('alpaca.timeout', 10))
            ->acceptJson()
            ->withHeaders([
                'APCA-API-KEY-ID' => $authentication['key_id'],
                'APCA-API-SECRET-KEY' => $authentication['secret'],
            ])
            ->post($this->resourceUrl($authentication['environment'], $path), $payload);

        return $this->mapResponse($response, $authentication['environment'], $operation, $payloadKey);
    }

    protected function resourceUrl(string $environment, string $path): string
    {
        $baseUrl = rtrim((string) config('alpaca.environments.'.$environment.'.base_url'), '/');

        return $baseUrl.'/'.ltrim($path, '/');
    }

    protected function mapResponse(Response $response, string $environment, string $operation, string $payloadKey): array
    {
        if ($response->successful()) {
            return [
                'status' => 'verified',
                'message' => 'Alpaca '.$operation.' succeeded.',
                'http_status' => $response->status(),
                'environment' => $environment,
                $payloadKey => $response->json(),
            ];
        }

        if (in_array($response->status(), [401, 403], true)) {
            return [
                'status' => 'auth_failed',
                'message' => 'Alpaca '.$operation.' failed because the saved credentials were rejected.',
                'http_status' => $response->status(),
                'environment' => $environment,
                $payloadKey => null,
            ];
        }

        return [
            'status' => 'request_failed',
            'message' => 'Alpaca '.$operation.' failed during the broker API request.',
            'http_status' => $response->status(),
            'environment' => $environment,
            $payloadKey => null,
        ];
    }

    protected function credentialContext(BrokerCredential $credential): array
    {
        $payload = is_array($credential->credential_payload) ? $credential->credential_payload : [];
        $keyId = $payload['access_key_id'] ?? null;
        $secret = $payload['access_secret'] ?? null;
        $environment = $this->normalizeEnvironment($credential->environment ?: ($payload['environment'] ?? 'paper'));

        if (! is_string($keyId) || trim($keyId) === '' || ! is_string($secret) || trim($secret) === '') {
            return [
                'status' => 'missing_credentials',
                'environment' => $environment,
            ];
        }

        return [
            'status' => 'ok',
            'environment' => $environment,
            'key_id' => trim($keyId),
            'secret' => trim($secret),
        ];
    }

    protected function normalizeEnvironment(string $environment): string
    {
        return match (strtolower(trim($environment))) {
            'live' => 'live',
            'sandbox', 'paper' => 'paper',
            default => 'paper',
        };
    }
}
