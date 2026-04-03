<?php
// اعوز بالله من الشياطين و ان يحضرون بسم الله الرحمن الرحيم الله لا إله إلا هو الحي القيوم
// Bismillahi ar-Rahmani ar-Rahim Audhu billahi min ash-shayatin wa an yahdurun Bismillah ar-Rahman ar-Rahim Allah la ilaha illa huwa al-hayy al-qayyum. Tamsa Allahu ala ayunihim
// version: x
// ======================================================
// - App Name: gusgraph-trading
// - Gusgraph LLC -
// - Author: Gus Kazem
// - https://Gusgraph.com
// - File Path: app/Support/Dashboard/CustomerDashboardSections.php
// =====================================================

namespace App\Support\Dashboard;

use App\Support\Display\SafeDisplay;

class CustomerDashboardSections
{
    public static function items(array $state = []): array
    {
        $positions = $state['positions'] ?? collect();
        $orders = $state['orders'] ?? collect();
        $activityLogs = $state['activityLogs'] ?? collect();
        $signals = $state['signals'] ?? collect();
        $actions = $state['actions'] ?? [];
        $openPositions = $positions
            ->filter(fn ($item) => (float) ($item->qty ?? 0) > 0)
            ->take(3)
            ->map(function ($item): array {
                return [
                    'title' => strtoupper((string) ($item->symbol ?? 'Position')),
                    'status' => self::signedMoney($item->unrealized_pl),
                    'summary' => trim(implode(' / ', array_filter([
                        ucfirst((string) ($item->side ?? 'long')),
                        self::quantity($item->qty),
                        SafeDisplay::sanitizedText((string) ($item->status_summary ?? '')),
                    ]))),
                ];
            })
            ->values()
            ->all();
        $openOrders = $orders
            ->filter(fn ($item) => ! in_array(strtolower((string) ($item->status ?? '')), ['filled', 'canceled', 'cancelled', 'expired', 'failed', 'rejected'], true))
            ->take(5)
            ->map(function ($item): array {
                return [
                    'title' => strtoupper((string) ($item->symbol ?? 'Order')),
                    'status' => SafeDisplay::status((string) ($item->status ?? 'submitted')),
                    'summary' => trim(implode(' / ', array_filter([
                        ucfirst((string) ($item->side ?? 'buy')),
                        self::quantity($item->qty),
                        SafeDisplay::dateTime($item->submitted_at, 'Submitted locally'),
                    ]))),
                ];
            })
            ->values()
            ->all();
        $latestSignals = $signals
            ->take(3)
            ->map(function ($item): array {
                return [
                    'title' => strtoupper((string) ($item->symbol ?? 'Signal')),
                    'status' => strtoupper((string) ($item->direction ?? 'watch')),
                    'summary' => trim(implode(' / ', array_filter([
                        'Status '.SafeDisplay::status((string) ($item->status ?? 'review')),
                        $item->strength !== null ? 'Strength '.number_format((float) $item->strength, 2) : null,
                        SafeDisplay::dateTime($item->generated_at, 'Generated locally'),
                    ]))),
                ];
            })
            ->values()
            ->all();
        $latestActivity = $activityLogs
            ->take(5)
            ->map(function ($item): array {
                return [
                    'title' => (string) ($item->type ?? 'activity'),
                    'status' => SafeDisplay::status((string) ($item->level ?? 'info')),
                    'summary' => SafeDisplay::sanitizedText((string) ($item->message ?? 'Runtime activity recorded')),
                    'meta' => SafeDisplay::dateTime($item->created_at, 'Recorded locally'),
                ];
            })
            ->values()
            ->all();

        return [
            [
                'kind' => 'positions',
                'title' => 'Positions Preview',
                'description' => 'Current live exposure stays visible from the main trading surface.',
                'items' => $openPositions,
                'empty' => 'No open positions are visible yet.',
                'route' => 'customer.positions.index',
                'routeLabel' => 'Open positions',
                'icon' => 'fa-solid fa-layer-group',
                'tone' => 'emerald',
            ],
            [
                'kind' => 'orders',
                'title' => 'Open Orders Preview',
                'description' => 'Active order flow stays close so you can check execution state fast.',
                'items' => $openOrders,
                'empty' => 'No open orders are visible yet.',
                'route' => 'customer.orders.index',
                'routeLabel' => 'Open orders',
                'icon' => 'fa-solid fa-list',
                'tone' => 'sky',
            ],
            [
                'kind' => 'activity',
                'title' => 'Latest Activity',
                'description' => 'Recent runtime, broker, and management events stay on the desk instead of hiding behind a deeper page.',
                'items' => $latestActivity,
                'empty' => 'No recent activity is available yet.',
                'route' => 'customer.activity.index',
                'routeLabel' => 'Open full activity',
                'icon' => 'fa-solid fa-wave-square',
                'tone' => 'blue',
            ],
            [
                'kind' => 'signals',
                'title' => 'Latest Signals',
                'description' => 'The newest local signals stay readable before you jump into deeper review.',
                'items' => $latestSignals,
                'empty' => 'No recent signals are visible yet.',
                'route' => 'customer.activity.index',
                'routeLabel' => 'Open signal activity',
                'icon' => 'fa-solid fa-signal',
                'tone' => 'violet',
            ],
            [
                'kind' => 'actions',
                'title' => 'Action Needed',
                'description' => 'Current blockers and next checks stay in one short queue.',
                'items' => $actions,
                'empty' => 'No immediate action is showing.',
                'route' => 'customer.dashboard',
                'routeLabel' => 'Refresh dashboard',
                'icon' => 'fa-solid fa-triangle-exclamation',
                'tone' => 'amber',
            ],
        ];
    }

    protected static function quantity($value): ?string
    {
        if ($value === null || $value === '') {
            return null;
        }

        return rtrim(rtrim(number_format((float) $value, 2, '.', ''), '0'), '.').' sh';
    }

    protected static function signedMoney($value): string
    {
        if ($value === null || $value === '') {
            return 'Flat';
        }

        $amount = (float) $value;

        return ($amount > 0 ? '+' : '').'$'.number_format($amount, 2);
    }
}
