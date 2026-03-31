<?php
// اعوز بالله من الشياطين و ان يحضرون بسم الله الرحمن الرحيم الله لا إله إلا هو الحي القيوم
// Bismillahi ar-Rahmani ar-Rahim Audhu billahi min ash-shayatin wa an yahdurun Bismillah ar-Rahman ar-Rahim Allah la ilaha illa huwa al-hayy al-qayyum. Tamsa Allahu ala ayunihim
// version: x
// ======================================================
// - App Name: gusgraph-trading
// - Gusgraph LLC -
// - Author: Gus Kazem
// - https://Gusgraph.com
// - File Path: app/Support/Account/AccountMembershipSummary.php
// ======================================================

namespace App\Support\Account;

class AccountMembershipSummary
{
    public static function items(): array
    {
        return [
            ['label' => 'Workspace Role', 'value' => 'Primary owner placeholder'],
            ['label' => 'Seat Usage', 'value' => 'Two of five placeholder seats'],
            ['label' => 'Tenant Scope', 'value' => 'Single placeholder workspace'],
        ];
    }
}
