<?php
// اعوز بالله من الشياطين و ان يحضرون بسم الله الرحمن الرحيم الله لا إله إلا هو الحي القيوم
// Bismillahi ar-Rahmani ar-Rahim Audhu billahi min ash-shayatin wa an yahdurun Bismillah ar-Rahman ar-Rahim Allah la ilaha illa huwa al-hayy al-qayyum. Tamsa Allahu ala ayunihim
// version: x
// ======================================================
// - App Name: gusgraph-trading
// - Gusgraph LLC -
// - Author: Gus Kazem
// - https://Gusgraph.com
// - File Path: app/Support/Labels/CustomerSectionLabels.php
// =====================================================

namespace App\Support\Labels;

class CustomerSectionLabels
{
    public static function make(): array
    {
        return [
            'dashboard' => [
                ['heading' => 'Account Summary', 'description' => 'Customer account placeholder summary.'],
                ['heading' => 'Billing Summary', 'description' => 'Customer billing placeholder summary.'],
                ['heading' => 'Broker Summary', 'description' => 'Customer broker placeholder summary.'],
                ['heading' => 'License Summary', 'description' => 'Customer license placeholder summary.'],
            ],
            'account' => [
                ['heading' => 'Profile Snapshot', 'description' => 'Basic customer account placeholder details.'],
                ['heading' => 'Team Access', 'description' => 'Team membership placeholder review.'],
                ['heading' => 'Account Status', 'description' => 'Shared status labels for the account area.'],
            ],
            'billing' => [
                ['heading' => 'Current Plan', 'description' => 'Subscription placeholder overview.'],
                ['heading' => 'Invoices', 'description' => 'Invoice placeholder history.'],
                ['heading' => 'Payment Status', 'description' => 'Shared billing status labels.'],
            ],
            'broker' => [
                ['heading' => 'Connection List', 'description' => 'Broker connection placeholder inventory.'],
                ['heading' => 'Sync Status', 'description' => 'Broker sync placeholder state.'],
                ['heading' => 'Credential State', 'description' => 'Credential placeholder readiness.'],
            ],
            'license' => [
                ['heading' => 'License Status', 'description' => 'Shared license state labels.'],
                ['heading' => 'Activation Window', 'description' => 'License period placeholder details.'],
                ['heading' => 'Key Readiness', 'description' => 'API key readiness placeholder notes.'],
            ],
        ];
    }
}
