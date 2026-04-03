<?php
// اعوز بالله من الشياطين و ان يحضرون بسم الله الرحمن الرحيم الله لا إله إلا هو الحي القيوم
// Bismillahi ar-Rahmani ar-Rahim Audhu billahi min ash-shayatin wa an yahdurun Bismillah ar-Rahman ar-Rahim Allah la ilaha illa huwa al-hayy al-qayyum. Tamsa Allahu ala ayunihim
// version: x
// ======================================================
// - App Name: gusgraph-trading
// - Gusgraph LLC -
// - Author: Gus Kazem
// - https://Gusgraph.com
// - File Path: app/Support/Dashboard/CustomerStats.php
// =====================================================

namespace App\Support\Dashboard;

use App\Support\Display\SafeDisplay;

class CustomerStats
{
    public static function items(array $state = []): array
    {
        $alpacaAccount = $state['alpacaAccount'] ?? null;
        $automationSetting = $state['automationSetting'] ?? null;
        $runtimeState = is_array($state['runtimeState'] ?? null) ? $state['runtimeState'] : [];
        $runtimeStatus = trim((string) ($runtimeState['last_runtime_status'] ?? ''));
        $runtimeSummary = SafeDisplay::sanitizedText((string) ($runtimeState['last_runtime_summary'] ?? 'Runtime has not reported yet.'), 'Runtime has not reported yet.');
        $automationEnabled = (bool) ($automationSetting->ai_enabled ?? false);
        $automationStatus = trim((string) ($automationSetting->status ?? ''));

        return [
            [
                'label' => 'Equity',
                'value' => self::money($alpacaAccount?->equity),
                'description' => $alpacaAccount
                    ? 'Last equity sync '.SafeDisplay::dateTime($alpacaAccount->last_account_sync_at, 'not recorded')
                    : 'Waiting on the first broker sync.',
                'icon' => 'fa-solid fa-wallet',
                'tone' => 'emerald',
            ],
            [
                'label' => 'Buying Power',
                'value' => self::money($alpacaAccount?->buying_power),
                'description' => $alpacaAccount
                    ? 'Cash '.self::money($alpacaAccount->cash)
                    : 'Buying power appears after broker data lands.',
                'icon' => 'fa-solid fa-bolt',
                'tone' => 'sky',
            ],
            [
                'label' => 'Runtime State',
                'value' => $runtimeStatus !== '' ? SafeDisplay::status($runtimeStatus) : 'Standby',
                'description' => $runtimeSummary,
                'icon' => 'fa-solid fa-wave-square',
                'tone' => 'rose',
            ],
            [
                'label' => 'Broker Sync',
                'value' => $alpacaAccount ? SafeDisplay::status($alpacaAccount->sync_status ?? 'pending') : 'Disconnected',
                'description' => $alpacaAccount
                    ? 'Last broker sync '.SafeDisplay::dateTime($alpacaAccount->last_synced_at, 'not recorded')
                    : 'No linked Alpaca account yet.',
                'icon' => 'fa-solid fa-plug-circle-bolt',
                'tone' => 'blue',
            ],
            [
                'label' => 'Automation State',
                'value' => $automationEnabled
                    ? ($automationStatus !== '' ? SafeDisplay::status($automationStatus) : 'Armed')
                    : 'Parked',
                'description' => $automationEnabled
                    ? 'Saved automation controls are ready for review.'
                    : 'Automation remains parked until you arm it.',
                'icon' => 'fa-solid fa-robot',
                'tone' => 'violet',
            ],
        ];
    }

    protected static function money($value): string
    {
        if ($value === null || $value === '') {
            return 'Awaiting sync';
        }

        return '$'.number_format((float) $value, 2);
    }
}
