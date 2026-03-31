<?php
// اعوز بالله من الشياطين و ان يحضرون بسم الله الرحمن الرحيم الله لا إله إلا هو الحي القيوم
// Bismillahi ar-Rahmani ar-Rahim Audhu billahi min ash-shayatin wa an yahdurun Bismillah ar-Rahman ar-Rahim Allah la ilaha illa huwa al-hayy al-qayyum. Tamsa Allahu ala ayunihim
// version: x
// ======================================================
// - App Name: bismel1.com
// - Gusgraph LLC -
// - Author: Gus Kazem
// - https://Gusgraph.com
// - File Path: app/Support/Admin/AuditOverview.php
// ======================================================

namespace App\Support\Admin;

use App\Domain\Audit\Enums\AuditEventType;

class AuditOverview
{
    public static function items(): array
    {
        return [
            ['event' => AuditEventType::labels()[AuditEventType::AccountReviewed->value], 'note' => 'Track workspace follow-up and account review decisions here.'],
            ['event' => AuditEventType::labels()[AuditEventType::BrokerChecked->value], 'note' => 'Use broker review history to confirm sync and readiness follow-up.'],
            ['event' => AuditEventType::labels()[AuditEventType::LicenseChecked->value], 'note' => 'Use license review history to confirm access posture and follow-up.'],
        ];
    }
}
