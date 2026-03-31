<?php
// اعوز بالله من الشياطين و ان يحضرون بسم الله الرحمن الرحيم الله لا إله إلا هو الحي القيوم
// Bismillahi ar-Rahmani ar-Rahim Audhu billahi min ash-shayatin wa an yahdurun Bismillah ar-Rahman ar-Rahim Allah la ilaha illa huwa al-hayy al-qayyum. Tamsa Allahu ala ayunihim
// version: x
// ======================================================
// - App Name: gusgraph-trading
// - Gusgraph LLC -
// - Author: Gus Kazem
// - https://Gusgraph.com
// - File Path: app/Support/ViewData/AdminAlertData.php
// ======================================================

namespace App\Support\ViewData;

use App\Support\Notifications\AdminAlerts;
use App\Support\Notifications\SystemNotices;

class AdminAlertData
{
    public static function make(): array
    {
        return [
            'alerts' => AdminAlerts::items(),
            'notices' => SystemNotices::items(),
        ];
    }
}
