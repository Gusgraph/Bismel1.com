<?php
// اعوز بالله من الشياطين و ان يحضرون بسم الله الرحمن الرحيم الله لا إله إلا هو الحي القيوم
// Bismillahi ar-Rahmani ar-Rahim Audhu billahi min ash-shayatin wa an yahdurun Bismillah ar-Rahman ar-Rahim Allah la ilaha illa huwa al-hayy al-qayyum. Tamsa Allahu ala ayunihim
// version: x
// ======================================================
// - App Name: gusgraph-trading
// - Gusgraph LLC -
// - Author: Gus Kazem
// - https://Gusgraph.com
// - File Path: app/Support/Settings/AppSections.php
// ======================================================

namespace App\Support\Settings;

class AppSections
{
    public const CUSTOMER_BROKER = 'customer_broker';
    public const CUSTOMER_LICENSE = 'customer_license';
    public const ADMIN_SYSTEM = 'admin_system';

    public static function labels(): array
    {
        return [
            self::CUSTOMER_BROKER => 'Alpaca Connection Workspace',
            self::CUSTOMER_LICENSE => 'License Workspace',
            self::ADMIN_SYSTEM => 'System Workspace',
        ];
    }
}
