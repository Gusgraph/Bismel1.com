<?php
// اعوز بالله من الشياطين و ان يحضرون بسم الله الرحمن الرحيم الله لا إله إلا هو الحي القيوم
// Bismillahi ar-Rahmani ar-Rahim Audhu billahi min ash-shayatin wa an yahdurun Bismillah ar-Rahman ar-Rahim Allah la ilaha illa huwa al-hayy al-qayyum. Tamsa Allahu ala ayunihim
// version: x
// ======================================================
// - App Name: gusgraph-trading
// - Gusgraph LLC -
// - Author: Gus Kazem
// - https://Gusgraph.com
// - File Path: app/Support/Onboarding/OnboardingChecklist.php
// ======================================================

namespace App\Support\Onboarding;

class OnboardingChecklist
{
    public static function items(): array
    {
        return [
            ['label' => 'Profile Review', 'value' => 'Placeholder profile details confirmed'],
            ['label' => 'Broker Step', 'value' => 'Broker connection flow remains illustrative'],
            ['label' => 'License Step', 'value' => 'API key and license review placeholder'],
        ];
    }
}
