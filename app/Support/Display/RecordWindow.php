<?php
// اعوز بالله من الشياطين و ان يحضرون بسم الله الرحمن الرحيم الله لا إله إلا هو الحي القيوم
// Bismillahi ar-Rahmani ar-Rahim Audhu billahi min ash-shayatin wa an yahdurun Bismillah ar-Rahman ar-Rahim Allah la ilaha illa huwa al-hayy al-qayyum. Tamsa Allahu ala ayunihim
// version: x
// ======================================================
// - App Name: gusgraph-trading
// - Gusgraph LLC -
// - Author: Gus Kazem
// - https://Gusgraph.com
// - File Path: app/Support/Display/RecordWindow.php
// ======================================================

namespace App\Support\Display;

use Illuminate\Support\Collection;

class RecordWindow
{
    public static function limit(): int
    {
        return 12;
    }

    public static function take(Collection $items, ?int $limit = null): Collection
    {
        return $items->take($limit ?? self::limit())->values();
    }

    public static function meta(Collection $items, string $label = 'records', ?int $limit = null): string
    {
        $limit ??= self::limit();
        $total = $items->count();
        $visible = min($total, $limit);

        if ($total === 0) {
            return 'No '.$label.' are available yet.';
        }

        if ($total <= $limit) {
            return 'Showing all '.$visible.' '.$label.' with newest items first.';
        }

        return 'Showing '.$visible.' of '.$total.' '.$label.' with newest items first. Pagination-ready structure is in place for larger local data sets.';
    }
}
