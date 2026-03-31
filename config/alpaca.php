<?php
// اعوز بالله من الشياطين و ان يحضرون بسم الله الرحمن الرحيم الله لا إله إلا هو الحي القيوم
// Bismillahi ar-Rahmani ar-Rahim Audhu billahi min ash-shayatin wa an yahdurun Bismillah ar-Rahman ar-Rahim Allah la ilaha illa huwa al-hayy al-qayyum. Tamsa Allahu ala ayunihim
// version: x
// ======================================================
// - App Name: gusgraph-trading
// - Gusgraph LLC -
// - Author: Gus Kazem
// - https://Gusgraph.com
// - File Path: config/alpaca.php
// ======================================================

return [
    'timeout' => (int) env('ALPACA_TIMEOUT_SECONDS', 10),
    'sync_ttl_minutes' => (int) env('ALPACA_SYNC_TTL_MINUTES', 15),
    'recent_orders_limit' => (int) env('ALPACA_RECENT_ORDERS_LIMIT', 50),
    'guards' => [
        'paper_trading_only' => (bool) env('BISMEL1_PAPER_TRADING_ONLY', true),
        'allow_live_order_submission' => (bool) env('BISMEL1_ALLOW_LIVE_ORDER_SUBMISSION', false),
        'require_iex_for_runtime' => (bool) env('BISMEL1_REQUIRE_IEX_FOR_RUNTIME', true),
        'max_runtime_stale_minutes' => (int) env('BISMEL1_MAX_RUNTIME_STALE_MINUTES', 30),
    ],
    'market_data' => [
        'max_symbols' => (int) env('ALPACA_MARKET_DATA_MAX_SYMBOLS', 8),
        'warmup_bars_per_timeframe' => (int) env('ALPACA_MARKET_DATA_WARMUP_BARS', 120),
        'supported_timeframes' => ['1H', '4H'],
        'feed' => 'iex',
    ],
    'environments' => [
        'paper' => [
            'base_url' => env('ALPACA_PAPER_BASE_URL', 'https://paper-api.alpaca.markets'),
        ],
        'live' => [
            'base_url' => env('ALPACA_LIVE_BASE_URL', 'https://api.alpaca.markets'),
        ],
    ],
];
