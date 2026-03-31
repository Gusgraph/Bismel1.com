<?php
// اعوز بالله من الشياطين و ان يحضرون بسم الله الرحمن الرحيم الله لا إله إلا هو الحي القيوم
// Bismillahi ar-Rahmani ar-Rahim Audhu billahi min ash-shayatin wa an yahdurun Bismillah ar-Rahman ar-Rahim Allah la ilaha illa huwa al-hayy al-qayyum. Tamsa Allahu ala ayunihim
// version: x
// ======================================================
// - App Name: gusgraph-trading
// - Gusgraph LLC -
// - Author: Gus Kazem
// - https://Gusgraph.com
// - File Path: app/Domain/License/Enums/ApiKeyStatus.php
// ======================================================

namespace App\Domain\License\Enums;

enum ApiKeyStatus: string
{
    case Ready = 'ready';
    case Placeholder = 'placeholder';
    case Attention = 'attention';

    public static function labels(): array
    {
        return [
            self::Ready->value => 'Ready',
            self::Placeholder->value => 'Placeholder',
            self::Attention->value => 'Needs Attention',
        ];
    }
}
