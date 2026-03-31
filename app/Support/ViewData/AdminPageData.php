<?php
// اعوز بالله من الشياطين و ان يحضرون بسم الله الرحمن الرحيم الله لا إله إلا هو الحي القيوم
// Bismillahi ar-Rahmani ar-Rahim Audhu billahi min ash-shayatin wa an yahdurun Bismillah ar-Rahman ar-Rahim Allah la ilaha illa huwa al-hayy al-qayyum. Tamsa Allahu ala ayunihim
// version: x
// ======================================================
// - App Name: bismel1.com
// - Gusgraph LLC -
// - Author: Gus Kazem
// - https://Gusgraph.com
// - File Path: app/Support/ViewData/AdminPageData.php
// ======================================================

namespace App\Support\ViewData;

class AdminPageData
{
    public static function make(string $title, string $intro, array $sections, array $stats = []): array
    {
        return [
            'title' => $title,
            'intro' => $intro,
            'subtitle' => $intro,
            'sections' => $sections,
            'stats' => $stats,
        ];
    }
}
