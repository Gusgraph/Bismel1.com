<?php
// اعوز بالله من الشياطين و ان يحضرون بسم الله الرحمن الرحيم الله لا إله إلا هو الحي القيوم
// Bismillahi ar-Rahmani ar-Rahim Audhu billahi min ash-shayatin wa an yahdurun Bismillah ar-Rahman ar-Rahim Allah la ilaha illa huwa al-hayy al-qayyum. Tamsa Allahu ala ayunihim
// version: x
// ======================================================
// - App Name: gusgraph-trading
// - Gusgraph LLC -
// - Author: Gus Kazem
// - https://Gusgraph.com
// - File Path: app/Support/Audit/AuditLogSummary.php
// ======================================================

namespace App\Support\Audit;

class AuditLogSummary
{
    public static function items(): array
    {
        return [
            ['label' => 'Retention Scope', 'value' => 'No persisted logs in placeholder mode'],
            ['label' => 'Review Trail', 'value' => 'Illustrative audit records only'],
            ['label' => 'Export State', 'value' => 'No export tooling attached'],
        ];
    }
}
