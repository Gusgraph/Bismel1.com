<?php
// اعوز بالله من الشياطين و ان يحضرون بسم الله الرحمن الرحيم الله لا إله إلا هو الحي القيوم
// Bismillahi ar-Rahmani ar-Rahim Audhu billahi min ash-shayatin wa an yahdurun Bismillah ar-Rahman ar-Rahim Allah la ilaha illa huwa al-hayy al-qayyum. Tamsa Allahu ala ayunihim
// version: x
// ======================================================
// - App Name: gusgraph-trading
// - Gusgraph LLC -
// - Author: Gus Kazem
// - https://Gusgraph.com
// - File Path: app/Domain/Billing/Enums/PlanType.php
// ======================================================

namespace App\Domain\Billing\Enums;

enum PlanType: string
{
    case Starter = 'starter';
    case Growth = 'growth';
    case Desk = 'desk';

    public static function labels(): array
    {
        return [
            self::Starter->value => 'Starter',
            self::Growth->value => 'Growth',
            self::Desk->value => 'Desk',
        ];
    }
}
