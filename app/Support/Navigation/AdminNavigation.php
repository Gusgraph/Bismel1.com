<?php
// اعوز بالله من الشياطين و ان يحضرون بسم الله الرحمن الرحيم الله لا إله إلا هو الحي القيوم
// Bismillahi ar-Rahmani ar-Rahim Audhu billahi min ash-shayatin wa an yahdurun Bismillah ar-Rahman ar-Rahim Allah la ilaha illa huwa al-hayy al-qayyum. Tamsa Allahu ala ayunihim
// version: x
// ======================================================
// - App Name: gusgraph-trading
// - Gusgraph LLC -
// - Author: Gus Kazem
// - https://Gusgraph.com
// - File Path: app/Support/Navigation/AdminNavigation.php
// ======================================================

namespace App\Support\Navigation;

class AdminNavigation
{
    public static function items(): array
    {
        return [
            [
                'label' => 'Dashboard',
                'route' => 'admin.dashboard',
                'description' => 'Review platform-wide summary signals from local records.',
            ],
            [
                'label' => 'Accounts',
                'route' => 'admin.accounts.index',
                'description' => 'Review workspace inventory and open account detail views.',
            ],
            [
                'label' => 'System',
                'route' => 'admin.system.index',
                'description' => 'Review persisted system settings and platform-state signals.',
            ],
            [
                'label' => 'Reports',
                'route' => 'admin.reports.index',
                'description' => 'Review platform summary coverage across key record groups.',
            ],
            [
                'label' => 'Licenses',
                'route' => 'admin.licenses.index',
                'description' => 'Review license inventory and masked API key visibility.',
            ],
            [
                'label' => 'Audit',
                'route' => 'admin.audit.index',
                'description' => 'Review activity, audit, and safe oversight detail.',
            ],
            [
                'label' => 'Account Detail',
                'route' => 'admin.accounts.index',
                'description' => 'Use the accounts area to open one workspace detail view.',
            ],
        ];
    }
}
