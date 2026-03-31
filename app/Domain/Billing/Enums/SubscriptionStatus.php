<?php
// اعوز بالله من الشياطين و ان يحضرون بسم الله الرحمن الرحيم الله لا إله إلا هو الحي القيوم
// Bismillahi ar-Rahmani ar-Rahim Audhu billahi min ash-shayatin wa an yahdurun Bismillah ar-Rahman ar-Rahim Allah la ilaha illa huwa al-hayy al-qayyum. Tamsa Allahu ala ayunihim
// version: x
// ======================================================
// - App Name: gusgraph-trading
// - Gusgraph LLC -
// - Author: Gus Kazem
// - https://Gusgraph.com
// - File Path: app/Domain/Billing/Enums/SubscriptionStatus.php
// =====================================================

namespace App\Domain\Billing\Enums;

enum SubscriptionStatus: string
{
    case Trial = 'trial';
    case Active = 'active';
    case PastDue = 'past_due';
    case Cancelled = 'cancelled';

    public static function labels(): array
    {
        return [
            self::Trial->value => 'Trial',
            self::Active->value => 'Active',
            self::PastDue->value => 'Past Due',
            self::Cancelled->value => 'Cancelled',
        ];
    }
}
