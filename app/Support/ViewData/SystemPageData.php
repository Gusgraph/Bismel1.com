<?php
// اعوز بالله من الشياطين و ان يحضرون بسم الله الرحمن الرحيم الله لا إله إلا هو الحي القيوم
// Bismillahi ar-Rahmani ar-Rahim Audhu billahi min ash-shayatin wa an yahdurun Bismillah ar-Rahman ar-Rahim Allah la ilaha illa huwa al-hayy al-qayyum. Tamsa Allahu ala ayunihim
// version: x
// ======================================================
// - App Name: bismel1.com
// - Gusgraph LLC -
// - Author: Gus Kazem
// - https://Gusgraph.com
// - File Path: app/Support/ViewData/SystemPageData.php
// ======================================================

namespace App\Support\ViewData;

use App\Support\Settings\AppSections;
use App\Support\Settings\SystemStatus;

class SystemPageData
{
    public static function make(): array
    {
        return [
            'page' => [
                'title' => 'System',
                'intro' => 'An admin system view for configuration posture, runtime status, and operational review.',
                'subtitle' => 'A compact system overview for current platform posture and follow-up.',
                'sections' => [
                    ['heading' => 'Application Settings', 'description' => 'Configuration categories remain visible for quick admin review.'],
                    ['heading' => 'Service Signals', 'description' => 'Current system states stay grouped into a simple health view.'],
                    ['heading' => 'Review Queue', 'description' => 'Follow-up items remain visible for operational review.'],
                ],
            ],
            'sectionLabel' => AppSections::labels()[AppSections::ADMIN_SYSTEM],
            'systemStatuses' => [
                ['value' => SystemStatus::NOMINAL, 'label' => SystemStatus::labels()[SystemStatus::NOMINAL], 'note' => 'Signals stable platform posture.'],
                ['value' => SystemStatus::PLACEHOLDER, 'label' => SystemStatus::labels()[SystemStatus::PLACEHOLDER], 'note' => 'Marks areas still waiting for full operational coverage.'],
                ['value' => SystemStatus::REVIEW, 'label' => SystemStatus::labels()[SystemStatus::REVIEW], 'note' => 'Calls attention to follow-up during admin review.'],
            ],
            'summary' => [
                'headline' => 'System posture overview',
                'details' => 'This page keeps system status and review context readable without changing deployment or runtime behavior.',
            ],
        ];
    }
}
