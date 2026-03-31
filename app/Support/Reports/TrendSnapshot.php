<?php
// اعوز بالله من الشياطين و ان يحضرون بسم الله الرحمن الرحيم الله لا إله إلا هو الحي القيوم
// Bismillahi ar-Rahmani ar-Rahim Audhu billahi min ash-shayatin wa an yahdurun Bismillah ar-Rahman ar-Rahim Allah la ilaha illa huwa al-hayy al-qayyum. Tamsa Allahu ala ayunihim
// version: x
// ======================================================
// - App Name: gusgraph-trading
// - Gusgraph LLC -
// - Author: Gus Kazem
// - https://Gusgraph.com
// - File Path: app/Support/Reports/TrendSnapshot.php
// ======================================================

namespace App\Support\Reports;

class TrendSnapshot
{
    public static function make(): array
    {
        return [
            ['period' => 'Week 1', 'metric' => 'Activity Window', 'status' => 'Stable placeholder', 'note' => 'No live events connected'],
            ['period' => 'Week 2', 'metric' => 'Revenue Trend', 'status' => 'Illustrative rise', 'note' => 'Review-only placeholder'],
            ['period' => 'Week 3', 'metric' => 'Usage Health', 'status' => 'Steady placeholder', 'note' => 'No persisted telemetry'],
        ];
    }
}
