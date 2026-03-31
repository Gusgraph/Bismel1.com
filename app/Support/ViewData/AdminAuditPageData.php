<?php
// اعوز بالله من الشياطين و ان يحضرون بسم الله الرحمن الرحيم الله لا إله إلا هو الحي القيوم
// Bismillahi ar-Rahmani ar-Rahim Audhu billahi min ash-shayatin wa an yahdurun Bismillah ar-Rahman ar-Rahim Allah la ilaha illa huwa al-hayy al-qayyum. Tamsa Allahu ala ayunihim
// version: x
// ======================================================
// - App Name: bismel1.com
// - Gusgraph LLC -
// - Author: Gus Kazem
// - https://Gusgraph.com
// - File Path: app/Support/ViewData/AdminAuditPageData.php
// ======================================================

namespace App\Support\ViewData;

use App\Support\Display\RecordWindow;
use App\Support\Display\SafeDisplay;
use Illuminate\Support\Collection;

class AdminAuditPageData
{
    public static function make(?Collection $activityLogs = null, ?Collection $auditLogs = null): array
    {
        $activityLogs = $activityLogs ?? collect();
        $auditLogs = $auditLogs ?? collect();

        return [
            'page' => [
                'title' => 'Audit',
                'intro' => 'A focused audit view for recent operational activity and audit history.',
                'subtitle' => $activityLogs->isNotEmpty() || $auditLogs->isNotEmpty()
                    ? 'Review recent activity and audit entries without leaving the admin workspace.'
                    : 'Activity and audit history will appear here once the platform begins recording oversight events.',
                'sections' => [
                    ['heading' => 'Recent Activity', 'description' => 'Recent activity entries stay visible with type, actor, time, and safe detail.'],
                    ['heading' => 'Recent Audit Entries', 'description' => 'Audit entries remain visible with action, target, actor, time, and safe summary detail.'],
                    ['heading' => 'Operational Review', 'description' => 'Use this page for oversight and follow-up without exposing raw internal noise.'],
                ],
            ],
            'activitySummary' => $activityLogs->map(function ($log) {
                return [
                    'title' => ucfirst(str_replace('_', ' ', $log->type)),
                    'status' => SafeDisplay::statusMeta((string) ($log->level ?? 'info')),
                    'details' => [
                        ['label' => 'Account', 'value' => $log->account?->name ?? 'No account'],
                        ['label' => 'Actor', 'value' => $log->user?->name ?? 'System'],
                        ['label' => 'Time', 'value' => SafeDisplay::dateTime($log->created_at)],
                        ['label' => 'Detail', 'value' => self::sanitizeDisplayText($log->message ?: 'No message recorded')],
                    ],
                ];
            })->values()->all(),
            'auditSummary' => $auditLogs->map(function ($log) {
                return [
                    'title' => ucfirst(str_replace('_', ' ', $log->action)),
                    'status' => SafeDisplay::statusMeta('review'),
                    'details' => [
                        ['label' => 'Account', 'value' => $log->account?->name ?? 'No account'],
                        ['label' => 'Actor', 'value' => $log->user?->name ?? 'System'],
                        ['label' => 'Time', 'value' => SafeDisplay::dateTime($log->created_at)],
                        ['label' => 'Target', 'value' => self::formatTarget($log->target_type, $log->target_id)],
                        ['label' => 'Detail', 'value' => self::sanitizeDisplayText($log->summary ?: 'No summary recorded')],
                    ],
                ];
            })->values()->all(),
            'activitySummaryMeta' => RecordWindow::meta($activityLogs, 'activity entries'),
            'auditSummaryMeta' => RecordWindow::meta($auditLogs, 'audit entries'),
            'summary' => [
                'headline' => $activityLogs->isNotEmpty() || $auditLogs->isNotEmpty()
                    ? 'Audit visibility is ready.'
                    : 'Audit visibility will appear here as platform activity is recorded.',
                'details' => $activityLogs->isNotEmpty() || $auditLogs->isNotEmpty()
                    ? 'This page keeps recent activity and audit history readable so operators can review what happened and what needs follow-up.'
                    : 'Once platform activity and audit events are recorded, they will appear here for operational review.',
            ],
            'hasAuditData' => $activityLogs->isNotEmpty() || $auditLogs->isNotEmpty(),
        ];
    }

    protected static function formatTarget(?string $targetType, $targetId): string
    {
        if (blank($targetType) && blank($targetId)) {
            return 'No target recorded';
        }

        $targetLabel = blank($targetType)
            ? 'Target'
            : class_basename((string) $targetType);

        return trim($targetLabel.' #'.($targetId ?? 'unknown'));
    }

    protected static function sanitizeDisplayText(?string $value): string
    {
        return SafeDisplay::sanitizedText($value, 'No detail recorded');
    }
}
