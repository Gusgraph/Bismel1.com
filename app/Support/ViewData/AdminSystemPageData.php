<?php
// اعوز بالله من الشياطين و ان يحضرون بسم الله الرحمن الرحيم الله لا إله إلا هو الحي القيوم
// Bismillahi ar-Rahmani ar-Rahim Audhu billahi min ash-shayatin wa an yahdurun Bismillah ar-Rahman ar-Rahim Allah la ilaha illa huwa al-hayy al-qayyum. Tamsa Allahu ala ayunihim
// version: x
// ======================================================
// - App Name: bismel1.com
// - Gusgraph LLC -
// - Author: Gus Kazem
// - https://Gusgraph.com
// - File Path: app/Support/ViewData/AdminSystemPageData.php
// ======================================================

namespace App\Support\ViewData;

use App\Domain\Audit\Enums\SystemStatusLevel;
use App\Models\SystemSetting;
use App\Support\Admin\AuditOverview;
use App\Support\Admin\SystemHealthSummary;

class AdminSystemPageData
{
    public static function make(?SystemSetting $systemSetting = null, array $platformStateCounts = []): array
    {
        $statusLabels = SystemStatusLevel::labels();
        $currentStatusLevel = $systemSetting?->status_level ?? SystemStatusLevel::Medium->value;
        $healthSummary = SystemHealthSummary::items();
        $auditOverview = AuditOverview::items();
        $platformState = [
            ['label' => 'Accounts', 'value' => self::formatPlatformSignal($platformStateCounts['accounts'] ?? 0)],
            ['label' => 'Subscriptions', 'value' => self::formatPlatformSignal($platformStateCounts['subscriptions'] ?? 0)],
            ['label' => 'Broker Connections', 'value' => self::formatPlatformSignal($platformStateCounts['broker_connections'] ?? 0)],
            ['label' => 'API Licenses', 'value' => self::formatPlatformSignal($platformStateCounts['api_licenses'] ?? 0)],
            ['label' => 'API Keys', 'value' => self::formatPlatformSignal($platformStateCounts['api_keys'] ?? 0)],
            ['label' => 'Audit Logs', 'value' => self::formatPlatformSignal($platformStateCounts['audit_logs'] ?? 0)],
            ['label' => 'Activity Logs', 'value' => self::formatPlatformSignal($platformStateCounts['activity_logs'] ?? 0)],
        ];

        if ($systemSetting) {
            array_unshift($healthSummary, [
                'label' => 'Runtime Mode',
                'value' => $systemSetting->runtime_mode,
            ]);
            array_splice($healthSummary, 1, 0, [[
                'label' => 'Review Channel',
                'value' => $systemSetting->review_channel,
            ]]);
            array_unshift($auditOverview, [
                'event' => 'Persisted Status Level',
                'note' => $statusLabels[$currentStatusLevel] ?? ucfirst(str_replace('_', ' ', $currentStatusLevel)),
            ]);
        }

        array_unshift($healthSummary, [
            'label' => 'Persisted Settings Record',
            'value' => $systemSetting ? 'Present' : 'Missing',
        ]);
        array_splice($auditOverview, 0, 0, [[
            'event' => 'Platform Record Coverage',
            'note' => collect($platformState)
                ->map(fn (array $item) => $item['label'].': '.$item['value'])
                ->implode(' | '),
        ]]);

        return [
            'page' => [
                'title' => 'System',
                'intro' => 'A focused admin system area for health posture, audit coverage, and current platform settings.',
                'subtitle' => $systemSetting
                    ? 'Review the current runtime mode, review channel, and status level from the saved system settings record.'
                    : 'System settings have not been saved yet, so the page is showing the current platform view with safe defaults.',
                'sections' => [
                    ['heading' => 'Health Snapshot', 'description' => 'Runtime mode and review channel stay visible alongside current platform health signals.'],
                    ['heading' => 'Platform State', 'description' => 'Core account, subscription, broker, license, key, audit, and activity counts stay grouped in one system view.'],
                    ['heading' => 'Audit Visibility', 'description' => 'Operational oversight remains visible without forcing a deeper audit drill-down.'],
                    ['heading' => 'Status Levels', 'description' => 'Status labels remain available beside the currently selected system posture.'],
                ],
            ],
            'healthSummary' => $healthSummary,
            'auditOverview' => $auditOverview,
            'platformState' => $platformState,
            'statusLevels' => $statusLabels,
            'summary' => [
                'headline' => $systemSetting
                    ? 'System settings and platform state are ready.'
                    : 'System settings are waiting for the first saved record.',
                'details' => $systemSetting
                    ? 'This page keeps current system settings, platform counts, and audit posture together in one operational view.'
                    : 'Save the system settings form to establish the first recorded runtime posture while continuing to review the current platform state.',
            ],
            'currentSettings' => [
                ['label' => 'Runtime Mode', 'value' => $systemSetting?->runtime_mode ?? 'Not set'],
                ['label' => 'Review Channel', 'value' => $systemSetting?->review_channel ?? 'Not set'],
                ['label' => 'Status Level', 'value' => $statusLabels[$currentStatusLevel] ?? ucfirst(str_replace('_', ' ', $currentStatusLevel))],
            ],
        ];
    }

    protected static function formatPlatformSignal(int $count): string
    {
        if ($count < 1) {
            return 'No records yet';
        }

        return (string) $count;
    }
}
