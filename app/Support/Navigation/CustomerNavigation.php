<?php
// اعوز بالله من الشياطين و ان يحضرون بسم الله الرحمن الرحيم الله لا إله إلا هو الحي القيوم
// Bismillahi ar-Rahmani ar-Rahim Audhu billahi min ash-shayatin wa an yahdurun Bismillah ar-Rahman ar-Rahim Allah la ilaha illa huwa al-hayy al-qayyum. Tamsa Allahu ala ayunihim
// version: x
// ======================================================
// - App Name: gusgraph-trading
// - Gusgraph LLC -
// - Author: Gus Kazem
// - https://Gusgraph.com
// - File Path: app/Support/Navigation/CustomerNavigation.php
// ======================================================

namespace App\Support\Navigation;

class CustomerNavigation
{
    public static function items(): array
    {
        return [
            [
                'label' => 'Dashboard',
                'route' => 'customer.dashboard',
                'description' => 'Review the current workspace overview and setup posture.',
            ],
            [
                'label' => 'Automation',
                'route' => 'customer.automation.index',
                'description' => 'Review AI control, automation health, scheduler posture, and runtime guardrails.',
            ],
            [
                'label' => 'Broker',
                'route' => 'customer.broker.index',
                'description' => 'Review broker connections and masked credential state.',
            ],
            [
                'label' => 'Positions',
                'route' => 'customer.positions.index',
                'description' => 'Review account-scoped open positions, management state, and safe reconciliation visibility.',
            ],
            [
                'label' => 'Orders',
                'route' => 'customer.orders.index',
                'description' => 'Review recent broker orders, execution status, and safe outcome summaries.',
            ],
            [
                'label' => 'Activity',
                'route' => 'customer.activity.index',
                'description' => 'Review safe scanner, risk, execution, and position-manager activity across the current workspace.',
            ],
            [
                'label' => 'Plans & Billing',
                'route' => 'customer.billing.index',
                'description' => 'Review the current subscription, plan, and billing posture.',
            ],
            [
                'label' => 'Settings',
                'route' => 'customer.settings.index',
                'description' => 'Review profile settings and current workspace context.',
            ],
        ];
    }
}
