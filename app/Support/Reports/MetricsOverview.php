<?php
// اعوز بالله من الشياطين و ان يحضرون بسم الله الرحمن الرحيم الله لا إله إلا هو الحي القيوم
// Bismillahi ar-Rahmani ar-Rahim Audhu billahi min ash-shayatin wa an yahdurun Bismillah ar-Rahman ar-Rahim Allah la ilaha illa huwa al-hayy al-qayyum. Tamsa Allahu ala ayunihim
// version: x
// ======================================================
// - App Name: gusgraph-trading
// - Gusgraph LLC -
// - Author: Gus Kazem
// - https://Gusgraph.com
// - File Path: app/Support/Reports/MetricsOverview.php
// ======================================================

namespace App\Support\Reports;

class MetricsOverview
{
    public static function make(): array
    {
        return [
            ['label' => 'Tracked Metrics', 'value' => '4 placeholder measures', 'context' => 'Overview only'],
            ['label' => 'Refresh Mode', 'value' => 'Static placeholder arrays', 'context' => 'No sync jobs'],
            ['label' => 'Trend Signals', 'value' => 'Narrative placeholder status', 'context' => 'No chart engine'],
            ['label' => 'Review Format', 'value' => 'Summary cards and tables', 'context' => 'Shared partials'],
        ];
    }
}
