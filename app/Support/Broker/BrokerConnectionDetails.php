<?php
// اعوز بالله من الشياطين و ان يحضرون بسم الله الرحمن الرحيم الله لا إله إلا هو الحي القيوم
// Bismillahi ar-Rahmani ar-Rahim Audhu billahi min ash-shayatin wa an yahdurun Bismillah ar-Rahman ar-Rahim Allah la ilaha illa huwa al-hayy al-qayyum. Tamsa Allahu ala ayunihim
// version: x
// ======================================================
// - App Name: gusgraph-trading
// - Gusgraph LLC -
// - Author: Gus Kazem
// - https://Gusgraph.com
// - File Path: app/Support/Broker/BrokerConnectionDetails.php
// ======================================================

namespace App\Support\Broker;

class BrokerConnectionDetails
{
    public static function items(): array
    {
        return [
            ['label' => 'Connection Slot', 'value' => 'Primary placeholder workspace'],
            ['label' => 'Credential State', 'value' => 'Masked placeholder only'],
            ['label' => 'Sync Window', 'value' => 'Manual review placeholder'],
        ];
    }
}
