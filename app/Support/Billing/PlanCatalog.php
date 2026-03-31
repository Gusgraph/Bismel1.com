<?php
// اعوز بالله من الشياطين و ان يحضرون بسم الله الرحمن الرحيم الله لا إله إلا هو الحي القيوم
// Bismillahi ar-Rahmani ar-Rahim Audhu billahi min ash-shayatin wa an yahdurun Bismillah ar-Rahman ar-Rahim Allah la ilaha illa huwa al-hayy al-qayyum. Tamsa Allahu ala ayunihim
// version: x
// ======================================================
// - App Name: gusgraph-trading
// - Gusgraph LLC -
// - Author: Gus Kazem
// - https://Gusgraph.com
// - File Path: app/Support/Billing/PlanCatalog.php
// ======================================================

namespace App\Support\Billing;

use App\Domain\Billing\Enums\PlanType;

class PlanCatalog
{
    public static function items(): array
    {
        return [
            [
                'type' => PlanType::Starter->value,
                'label' => PlanType::labels()[PlanType::Starter->value],
                'summary' => 'Placeholder plan for single-user review.',
            ],
            [
                'type' => PlanType::Growth->value,
                'label' => PlanType::labels()[PlanType::Growth->value],
                'summary' => 'Placeholder plan for a small trading team.',
            ],
            [
                'type' => PlanType::Desk->value,
                'label' => PlanType::labels()[PlanType::Desk->value],
                'summary' => 'Placeholder plan for broader operational visibility.',
            ],
        ];
    }
}
