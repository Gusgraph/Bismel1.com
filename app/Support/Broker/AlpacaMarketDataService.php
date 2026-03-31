<?php
// اعوز بالله من الشياطين و ان يحضرون بسم الله الرحمن الرحيم الله لا إله إلا هو الحي القيوم
// Bismillahi ar-Rahmani ar-Rahim Audhu billahi min ash-shayatin wa an yahdurun Bismillah ar-Rahman ar-Rahim Allah la ilaha illa huwa al-hayy al-qayyum. Tamsa Allahu ala ayunihim
// version: x
// ======================================================
// - App Name: gusgraph-trading
// - Gusgraph LLC -
// - Author: Gus Kazem
// - https://Gusgraph.com
// - File Path: app/Support/Broker/AlpacaMarketDataService.php
// ======================================================

namespace App\Support\Broker;

use App\Models\Account;
use App\Models\AlpacaAccount;
use App\Models\AlpacaBar;
use App\Models\BrokerConnection;
use Carbon\CarbonImmutable;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;

class AlpacaMarketDataService
{
    public function __construct(
        protected AlpacaClient $client,
    ) {}

    public function syncLatestForAccount(
        Account $account,
        array $symbols,
        array $timeframes = ['1H', '4H'],
        ?int $warmupBarsPerTimeframe = null,
    ): array {
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

        if (! $alpacaAccount) {
            return [
                'status' => 'missing_alpaca_account',
                'message' => 'No linked Alpaca account record is stored for market-data reads yet.',
            ];
        }

        $credential = $connection->brokerCredentials()->latest('id')->first();

        if (! $credential) {
            return [
                'status' => 'missing_credentials',
                'message' => 'No saved Alpaca credentials are available for market-data reads.',
            ];
        }

        $feed = strtolower((string) ($alpacaAccount->data_feed ?: data_get($credential->credential_payload, 'market_data_feed', 'iex')));

        if ($feed !== (string) config('alpaca.market_data.feed', 'iex')) {
            $this->recordFailure($alpacaAccount, 'unsupported_feed', 'Only the IEX market-data feed is allowed for the current bars path.');

            return [
                'status' => 'unsupported_feed',
                'message' => 'Only the IEX market-data feed is allowed for the current bars path.',
            ];
        }

        $normalizedSymbols = $this->normalizeSymbols($symbols);
        $normalizedTimeframes = $this->normalizeTimeframes($timeframes);
        $warmupBars = max(1, $warmupBarsPerTimeframe ?? (int) config('alpaca.market_data.warmup_bars_per_timeframe', 120));
        $oneHourBarsRequired = in_array('4H', $normalizedTimeframes, true)
            ? max($warmupBars, $warmupBars * 4)
            : $warmupBars;
        $window = $this->barWindow($oneHourBarsRequired);

        $fetchResults = [];

        foreach ($normalizedSymbols as $symbol) {
            $barsResult = $this->client->fetchStockBars(
                $credential,
                $symbol,
                '1Hour',
                $window['start'],
                $window['end'],
                $feed,
                $oneHourBarsRequired
            );

            if (($barsResult['status'] ?? null) !== 'verified') {
                $message = $barsResult['message'] ?? 'Alpaca market-data bars fetch failed.';
                $this->recordFailure($alpacaAccount, $barsResult['status'] ?? 'request_failed', $message);

                return [
                    'status' => $barsResult['status'] ?? 'request_failed',
                    'message' => $message,
                ];
            }

            $fetchResults[$symbol] = $this->barsForSymbol($barsResult['bars'] ?? [], $symbol);
        }

        $this->persistBars($alpacaAccount, $connection, $normalizedTimeframes, $fetchResults, $warmupBars, $feed);

        return [
            'status' => 'synced',
            'message' => 'Alpaca IEX market-data bars were synced for 1H and 4H strategy warmup use.',
        ];
    }

    protected function normalizeSymbols(array $symbols): array
    {
        $normalized = collect($symbols)
            ->filter(fn ($symbol) => is_string($symbol))
            ->map(fn (string $symbol) => strtoupper(trim($symbol)))
            ->filter(fn (string $symbol) => $symbol !== '' && preg_match('/^[A-Z0-9.\-]+$/', $symbol) === 1)
            ->unique()
            ->values()
            ->all();

        if ($normalized === []) {
            throw new InvalidArgumentException('At least one symbol is required for Alpaca market-data reads.');
        }

        $maxSymbols = max(1, (int) config('alpaca.market_data.max_symbols', 8));

        return array_slice($normalized, 0, $maxSymbols);
    }

    protected function normalizeTimeframes(array $timeframes): array
    {
        $supported = config('alpaca.market_data.supported_timeframes', ['1H', '4H']);
        $normalized = collect($timeframes)
            ->filter(fn ($timeframe) => is_string($timeframe))
            ->map(fn (string $timeframe) => strtoupper(trim($timeframe)))
            ->unique()
            ->values()
            ->all();

        if ($normalized === []) {
            return ['1H', '4H'];
        }

        foreach ($normalized as $timeframe) {
            if (! in_array($timeframe, $supported, true)) {
                throw new InvalidArgumentException('Unsupported Alpaca market-data timeframe ['.$timeframe.'].');
            }
        }

        return $normalized;
    }

    protected function barWindow(int $requiredOneHourBars): array
    {
        $end = now('UTC')->startOfHour();
        $lookbackHours = max(
            $requiredOneHourBars + 12,
            (int) ceil($requiredOneHourBars * 4.5)
        );
        $start = $end->subHours($lookbackHours);

        return [
            'start' => CarbonImmutable::parse($start),
            'end' => CarbonImmutable::parse($end),
        ];
    }

    protected function barsForSymbol(mixed $payload, string $symbol): array
    {
        if (! is_array($payload)) {
            return [];
        }

        $barsPayload = is_array($payload['bars'] ?? null) ? $payload['bars'] : $payload;
        $bars = $barsPayload[$symbol] ?? $barsPayload[strtoupper($symbol)] ?? [];

        if (! is_array($bars)) {
            return [];
        }

        return array_values(array_filter($bars, 'is_array'));
    }

    protected function persistBars(
        AlpacaAccount $alpacaAccount,
        BrokerConnection $connection,
        array $timeframes,
        array $barsBySymbol,
        int $warmupBars,
        string $feed,
    ): void {
        $fetchedAt = now();
        $storedCounts = [
            '1H' => 0,
            '4H' => 0,
        ];

        DB::transaction(function () use ($alpacaAccount, $connection, $timeframes, $barsBySymbol, $warmupBars, $feed, $fetchedAt, &$storedCounts): void {
            foreach ($barsBySymbol as $symbol => $oneHourPayload) {
                $oneHourBars = $this->normalizeOneHourBars($oneHourPayload);

                if (in_array('1H', $timeframes, true)) {
                    $storedCounts['1H'] += $this->upsertBars(
                        $alpacaAccount,
                        $connection,
                        $symbol,
                        '1H',
                        $feed,
                        array_slice($oneHourBars, -$warmupBars),
                        $fetchedAt
                    );
                }

                if (in_array('4H', $timeframes, true)) {
                    $fourHourBars = $this->aggregateFourHourBars($oneHourBars);
                    $storedCounts['4H'] += $this->upsertBars(
                        $alpacaAccount,
                        $connection,
                        $symbol,
                        '4H',
                        $feed,
                        array_slice($fourHourBars, -$warmupBars),
                        $fetchedAt
                    );
                }
            }

            $metadata = is_array($alpacaAccount->metadata) ? $alpacaAccount->metadata : [];
            $alpacaAccount->forceFill([
                'metadata' => array_merge($metadata, [
                    'bars_sync_result' => 'synced',
                    'bars_sync_message' => 'Alpaca IEX 1H and 4H market-data bars are ready for warmup reads.',
                    'bars_feed' => strtoupper($feed),
                    'bars_symbol_count' => count($barsBySymbol),
                    'bars_1h_count' => $storedCounts['1H'],
                    'bars_4h_count' => $storedCounts['4H'],
                    'bars_timeframes' => $timeframes,
                    'last_bars_sync_at' => $fetchedAt->toIso8601String(),
                ]),
            ])->save();
        });
    }

    protected function normalizeOneHourBars(array $bars): array
    {
        return collect($bars)
            ->map(function (array $bar) {
                $startsAt = $this->timestampOrNull($bar['t'] ?? null);

                if (! $startsAt) {
                    return null;
                }

                return [
                    'starts_at' => $startsAt,
                    'ends_at' => $startsAt->addHour(),
                    'open' => $this->decimalStringOrNull($bar['o'] ?? null),
                    'high' => $this->decimalStringOrNull($bar['h'] ?? null),
                    'low' => $this->decimalStringOrNull($bar['l'] ?? null),
                    'close' => $this->decimalStringOrNull($bar['c'] ?? null),
                    'volume' => $this->integerOrNull($bar['v'] ?? null),
                    'trade_count' => $this->integerOrNull($bar['n'] ?? null),
                    'vwap' => $this->decimalStringOrNull($bar['vw'] ?? null),
                ];
            })
            ->filter(function (?array $bar) {
                return $bar !== null
                    && $bar['open'] !== null
                    && $bar['high'] !== null
                    && $bar['low'] !== null
                    && $bar['close'] !== null;
            })
            ->sortBy(fn (array $bar) => $bar['starts_at']->getTimestamp())
            ->values()
            ->all();
    }

    protected function aggregateFourHourBars(array $oneHourBars): array
    {
        return collect($oneHourBars)
            ->groupBy(function (array $bar) {
                $start = $bar['starts_at'];
                $alignedHour = (int) floor($start->hour / 4) * 4;

                return $start->copy()->setTime($alignedHour, 0)->toIso8601String();
            })
            ->map(function (Collection $bars, string $blockStart) {
                $ordered = $bars->sortBy(fn (array $bar) => $bar['starts_at']->getTimestamp())->values();

                if ($ordered->count() !== 4) {
                    return null;
                }

                for ($index = 1; $index < $ordered->count(); $index++) {
                    if (! $ordered[$index - 1]['ends_at']->equalTo($ordered[$index]['starts_at'])) {
                        return null;
                    }
                }

                $first = $ordered->first();
                $last = $ordered->last();
                $volume = $ordered->sum(fn (array $bar) => (int) ($bar['volume'] ?? 0));
                $tradeCount = $ordered->sum(fn (array $bar) => (int) ($bar['trade_count'] ?? 0));
                $weightedVwap = $volume > 0
                    ? $ordered->sum(fn (array $bar) => ((float) ($bar['vwap'] ?? $bar['close'])) * (int) ($bar['volume'] ?? 0)) / $volume
                    : null;

                return [
                    'starts_at' => CarbonImmutable::parse($blockStart),
                    'ends_at' => $last['ends_at'],
                    'open' => $first['open'],
                    'high' => $this->decimalStringOrNull($ordered->max(fn (array $bar) => (float) $bar['high'])),
                    'low' => $this->decimalStringOrNull($ordered->min(fn (array $bar) => (float) $bar['low'])),
                    'close' => $last['close'],
                    'volume' => $volume,
                    'trade_count' => $tradeCount > 0 ? $tradeCount : null,
                    'vwap' => $this->decimalStringOrNull($weightedVwap),
                ];
            })
            ->filter()
            ->sortBy(fn (array $bar) => $bar['starts_at']->getTimestamp())
            ->values()
            ->all();
    }

    protected function upsertBars(
        AlpacaAccount $alpacaAccount,
        BrokerConnection $connection,
        string $symbol,
        string $timeframe,
        string $feed,
        array $bars,
        $fetchedAt,
    ): int {
        foreach ($bars as $bar) {
            AlpacaBar::query()->updateOrCreate(
                [
                    'alpaca_account_id' => $alpacaAccount->getKey(),
                    'symbol' => $symbol,
                    'timeframe' => $timeframe,
                    'feed' => strtolower($feed),
                    'starts_at' => $bar['starts_at'],
                ],
                [
                    'account_id' => $alpacaAccount->account_id,
                    'broker_connection_id' => $connection->getKey(),
                    'ends_at' => $bar['ends_at'],
                    'open' => $bar['open'],
                    'high' => $bar['high'],
                    'low' => $bar['low'],
                    'close' => $bar['close'],
                    'volume' => $bar['volume'],
                    'trade_count' => $bar['trade_count'],
                    'vwap' => $bar['vwap'],
                    'fetched_at' => $fetchedAt,
                ]
            );
        }

        return count($bars);
    }

    protected function recordFailure(AlpacaAccount $alpacaAccount, string $status, string $message): void
    {
        $metadata = is_array($alpacaAccount->metadata) ? $alpacaAccount->metadata : [];

        $alpacaAccount->forceFill([
            'metadata' => array_merge($metadata, [
                'bars_sync_result' => $status,
                'bars_sync_message' => $message,
            ]),
        ])->save();
    }

    protected function timestampOrNull(mixed $value): ?CarbonImmutable
    {
        if (! is_string($value) || trim($value) === '') {
            return null;
        }

        try {
            return CarbonImmutable::parse($value)->setTimezone('UTC');
        } catch (\Throwable) {
            return null;
        }
    }

    protected function decimalStringOrNull(mixed $value): ?string
    {
        if (! is_numeric($value)) {
            return null;
        }

        return number_format((float) $value, 6, '.', '');
    }

    protected function integerOrNull(mixed $value): ?int
    {
        if (! is_numeric($value)) {
            return null;
        }

        return (int) $value;
    }
}
