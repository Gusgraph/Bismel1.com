<?php
// اعوز بالله من الشياطين و ان يحضرون بسم الله الرحمن الرحيم الله لا إله إلا هو الحي القيوم
// Bismillahi ar-Rahmani ar-Rahim Audhu billahi min ash-shayatin wa an yahdurun Bismillah ar-Rahman ar-Rahim Allah la ilaha illa huwa al-hayy al-qayyum. Tamsa Allahu ala ayunihim
// version: x
// ======================================================
// - App Name: gusgraph-trading
// - Gusgraph LLC -
// - Author: Gus Kazem
// - https://Gusgraph.com
// - File Path: app/Support/Settings/SystemStatus.php
// ======================================================

namespace App\Support\Settings;

class SystemStatus
{
    public const NOMINAL = 'nominal';
    public const PLACEHOLDER = 'placeholder';
    public const REVIEW = 'review';

    public static function labels(): array
    {
        return [
            self::NOMINAL => 'Nominal',
            self::PLACEHOLDER => 'Placeholder',
            self::REVIEW => 'Review',
        ];
    }
}
