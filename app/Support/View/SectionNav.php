<?php
// اعوز بالله من الشياطين و ان يحضرون بسم الله الرحمن الرحيم الله لا إله إلا هو الحي القيوم
// Bismillahi ar-Rahmani ar-Rahim Audhu billahi min ash-shayatin wa an yahdurun Bismillah ar-Rahman ar-Rahim Allah la ilaha illa huwa al-hayy al-qayyum. Tamsa Allahu ala ayunihim
// version: x
// ======================================================
// - App Name: gusgraph-trading
// - Gusgraph LLC -
// - Author: Gus Kazem
// - https://Gusgraph.com
// - File Path: app/Support/View/SectionNav.php
// ======================================================

namespace App\Support\View;

class SectionNav
{
    public static function make(array $items = []): array
    {
        return array_values(array_map(
            static fn (array $item): array => [
                'label' => $item['label'] ?? 'Section',
                'route' => $item['route'] ?? null,
                'description' => $item['description'] ?? null,
            ],
            $items
        ));
    }
}
