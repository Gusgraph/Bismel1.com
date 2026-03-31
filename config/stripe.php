<?php
// اعوز بالله من الشياطين و ان يحضرون بسم الله الرحمن الرحيم الله لا إله إلا هو الحي القيوم
// Bismillahi ar-Rahmani ar-Rahim Audhu billahi min ash-shayatin wa an yahdurun Bismillah ar-Rahman ar-Rahim Allah la ilaha illa huwa al-hayy al-qayyum. Tamsa Allahu ala ayunihim
// version: x
// ======================================================
// - App Name: bismel1.com
// - Gusgraph LLC -
// - Author: Gus Kazem
// - https://Gusgraph.com
// - File Path: config/stripe.php
// ======================================================

return [
    'key' => env('STRIPE_KEY'),
    'secret' => env('STRIPE_SECRET'),
    'webhook_secret' => env('STRIPE_WEBHOOK_SECRET'),
    'webhook_tolerance' => (int) env('STRIPE_WEBHOOK_TOLERANCE', 300),
    'checkout_success_path' => env('STRIPE_CHECKOUT_SUCCESS_PATH', '/customer/billing/checkout/success'),
    'checkout_cancel_path' => env('STRIPE_CHECKOUT_CANCEL_PATH', '/customer/billing/checkout/cancel'),
    'test_speed_execute' => [
        'enabled' => filter_var(env('STRIPE_TEST_SPEED_EXECUTE_ENABLED', false), FILTER_VALIDATE_BOOL),
        'key' => env('STRIPE_TEST_SPEED_EXECUTE_KEY'),
        'secret' => env('STRIPE_TEST_SPEED_EXECUTE_SECRET'),
        'price_id' => env('STRIPE_TEST_SPEED_EXECUTE_PRICE_ID'),
        'webhook_url' => env('STRIPE_TEST_SPEED_EXECUTE_WEBHOOK_URL'),
    ],
    'referral' => [
        'query_parameter' => env('REFERRAL_QUERY_PARAMETER', 'ref'),
        'cookie_name' => env('REFERRAL_COOKIE_NAME', 'gusgraph_referral_code'),
        'cookie_minutes' => (int) env('REFERRAL_COOKIE_MINUTES', 43200),
        'first_payment_commission_rate' => (float) env('AFFILIATE_FIRST_PAYMENT_COMMISSION_RATE', 0.73),
    ],
    'price_ids' => [
        'BISMILLAH_AI_SCANNER' => env('STRIPE_PRICE_BISMILLAH_AI_SCANNER'),
        'BISMILLAH1_BOT_OVERNIGHT_EQUITIES' => env('STRIPE_PRICE_BISMILLAH1_BOT_OVERNIGHT_EQUITIES'),
        'BISMILLAH1_BOT_OPTIONS' => env('STRIPE_PRICE_BISMILLAH1_BOT_OPTIONS'),
        'BISMILLAH1_BOT_CRYPTO' => env('STRIPE_PRICE_BISMILLAH1_BOT_CRYPTO'),
        'BISMILLAH1_BOT_PRIME' => env('STRIPE_PRICE_BISMILLAH1_BOT_PRIME'),
        'BISMILLAH1_BOT_EXECUTE_BASIC' => env('STRIPE_PRICE_BISMILLAH1_BOT_EXECUTE_BASIC'),
        'BISMILLAH1_BOT_CUSTOM_STRATEGY_ADDON' => env('STRIPE_PRICE_BISMILLAH1_BOT_CUSTOM_STRATEGY_ADDON'),
        'BISMILLAH1_BOT_ADDITIONAL_ACCOUNT_ADDON' => env('STRIPE_PRICE_BISMILLAH1_BOT_ADDITIONAL_ACCOUNT_ADDON'),
        'BISMILLAH1_BOT_SPEED_EXECUTE' => env('STRIPE_PRICE_BISMILLAH1_BOT_SPEED_EXECUTE'),
    ],
    'affiliate_price_ids' => [
        'BISMILLAH_AI_SCANNER' => env('STRIPE_AFFILIATE_PRICE_BISMILLAH_AI_SCANNER'),
        'BISMILLAH1_BOT_OVERNIGHT_EQUITIES' => env('STRIPE_AFFILIATE_PRICE_BISMILLAH1_BOT_OVERNIGHT_EQUITIES'),
        'BISMILLAH1_BOT_OPTIONS' => env('STRIPE_AFFILIATE_PRICE_BISMILLAH1_BOT_OPTIONS'),
        'BISMILLAH1_BOT_CRYPTO' => env('STRIPE_AFFILIATE_PRICE_BISMILLAH1_BOT_CRYPTO'),
        'BISMILLAH1_BOT_PRIME' => env('STRIPE_AFFILIATE_PRICE_BISMILLAH1_BOT_PRIME'),
        'BISMILLAH1_BOT_EXECUTE_BASIC' => env('STRIPE_AFFILIATE_PRICE_BISMILLAH1_BOT_EXECUTE_BASIC'),
    ],
    'affiliate_display_prices' => [
        'BISMILLAH_AI_SCANNER' => env('AFFILIATE_DISPLAY_PRICE_BISMILLAH_AI_SCANNER'),
        'BISMILLAH1_BOT_OVERNIGHT_EQUITIES' => env('AFFILIATE_DISPLAY_PRICE_BISMILLAH1_BOT_OVERNIGHT_EQUITIES'),
        'BISMILLAH1_BOT_OPTIONS' => env('AFFILIATE_DISPLAY_PRICE_BISMILLAH1_BOT_OPTIONS'),
        'BISMILLAH1_BOT_CRYPTO' => env('AFFILIATE_DISPLAY_PRICE_BISMILLAH1_BOT_CRYPTO'),
        'BISMILLAH1_BOT_PRIME' => env('AFFILIATE_DISPLAY_PRICE_BISMILLAH1_BOT_PRIME'),
        'BISMILLAH1_BOT_EXECUTE_BASIC' => env('AFFILIATE_DISPLAY_PRICE_BISMILLAH1_BOT_EXECUTE_BASIC'),
    ],
    'events' => [
        'checkout_completed' => 'checkout.session.completed',
        'invoice_paid' => 'invoice.paid',
        'invoice_payment_failed' => 'invoice.payment_failed',
        'subscription_updated' => 'customer.subscription.updated',
        'subscription_deleted' => 'customer.subscription.deleted',
    ],
];
