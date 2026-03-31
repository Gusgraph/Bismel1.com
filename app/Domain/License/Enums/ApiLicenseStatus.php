<?php
// اعوز بالله من الشياطين و ان يحضرون بسم الله الرحمن الرحيم الله لا إله إلا هو الحي القيوم
// Bismillahi ar-Rahmani ar-Rahim Audhu billahi min ash-shayatin wa an yahdurun Bismillah ar-Rahman ar-Rahim Allah la ilaha illa huwa al-hayy al-qayyum. Tamsa Allahu ala ayunihim
// version: x
// ======================================================
// - App Name: gusgraph-trading
// - Gusgraph LLC -
// - Author: Gus Kazem
// - https://Gusgraph.com
// - File Path: app/Domain/License/Enums/ApiLicenseStatus.php
// =====================================================

namespace App\Domain\License\Enums;

enum ApiLicenseStatus: string
{
    case Active = 'active';
    case Expiring = 'expiring';
    case Expired = 'expired';

    public static function labels(): array
    {
        return [
            self::Active->value => 'Active',
            self::Expiring->value => 'Expiring',
            self::Expired->value => 'Expired',
        ];
    }
}
