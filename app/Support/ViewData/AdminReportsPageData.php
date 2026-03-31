<?php
// اعوز بالله من الشياطين و ان يحضرون بسم الله الرحمن الرحيم الله لا إله إلا هو الحي القيوم
// Bismillahi ar-Rahmani ar-Rahim Audhu billahi min ash-shayatin wa an yahdurun Bismillah ar-Rahman ar-Rahim Allah la ilaha illa huwa al-hayy al-qayyum. Tamsa Allahu ala ayunihim
// version: x
// ======================================================
// - App Name: bismel1.com
// - Gusgraph LLC -
// - Author: Gus Kazem
// - https://Gusgraph.com
// - File Path: app/Support/ViewData/AdminReportsPageData.php
// ======================================================

namespace App\Support\ViewData;

use App\Models\SystemSetting;
use App\Models\BotRun;
use App\Models\Signal;

class AdminReportsPageData
{
    public static function make(array $counts = [], ?SystemSetting $systemSetting = null): array
    {
        $hasReportData = (bool) ($counts['has_report_data'] ?? false);
        $accountCount = (int) ($counts['accounts'] ?? 0);
        $userCount = (int) ($counts['users'] ?? 0);
        $subscriptionCount = (int) ($counts['subscriptions'] ?? 0);
        $activeSubscriptionCount = (int) ($counts['active_subscriptions'] ?? 0);
        $invoiceCount = (int) ($counts['invoices'] ?? 0);
        $paidInvoiceCount = (int) ($counts['paid_invoices'] ?? 0);
        $brokerConnectionCount = (int) ($counts['broker_connections'] ?? 0);
        $brokerCredentialCount = (int) ($counts['broker_credentials'] ?? 0);
        $licenseCount = (int) ($counts['licenses'] ?? 0);
        $apiKeyCount = (int) ($counts['api_keys'] ?? 0);
        $signalCount = (int) ($counts['signals'] ?? 0);
        $botRunCount = (int) ($counts['bot_runs'] ?? 0);
        $executionAttemptCount = (int) ($counts['execution_attempts'] ?? 0);
        $activityCount = (int) ($counts['activity_logs'] ?? 0);
        $auditCount = (int) ($counts['audit_logs'] ?? 0);
        $recentSignal = $counts['recent_signal'] instanceof Signal ? $counts['recent_signal'] : null;
        $recentBotRun = $counts['recent_bot_run'] instanceof BotRun ? $counts['recent_bot_run'] : null;

        return [
            'page' => [
                'title' => 'Admin Reports',
                'intro' => 'A clear platform report that brings workspace coverage, operational activity, and system posture into one admin view.',
                'subtitle' => $hasReportData
                    ? 'Use this page to review workspace volume, billing posture, automation coverage, and current system state in one place.'
                    : 'Platform reporting will fill in here as workspaces, billing, broker access, and system activity accumulate.',
                'sections' => [
                    ['heading' => 'Workspace Coverage', 'description' => 'Accounts and users show how much of the platform is currently under active oversight.'],
                    ['heading' => 'Operational Coverage', 'description' => 'Billing, broker, license, signal, bot-run, and oversight summaries stay grouped into clear operational lanes.'],
                    ['heading' => 'System Coverage', 'description' => 'System posture stays visible beside current runtime and review settings.'],
                ],
            ],
            'summary' => [
                'headline' => $hasReportData
                    ? 'Platform reporting is ready.'
                    : 'Platform reporting will appear here as operational activity begins.',
                'details' => $hasReportData
                    ? 'This page summarizes workspace volume, operational coverage, automation visibility, and system posture without forcing operators into raw records.'
                    : 'As accounts, billing, broker access, automation, and oversight activity come online, this page will turn into a useful admin summary.',
                'items' => [
                    ['label' => 'Workspace Footprint', 'value' => (string) $accountCount.' accounts', 'context' => $userCount.' users linked'],
                    ['label' => 'Commercial Coverage', 'value' => (string) $subscriptionCount.' subscriptions', 'context' => $activeSubscriptionCount.' active'],
                    ['label' => 'Billing Follow-through', 'value' => (string) $paidInvoiceCount.' paid / '.max($invoiceCount - $paidInvoiceCount, 0).' open', 'context' => $invoiceCount.' total invoices'],
                    ['label' => 'Automation Visibility', 'value' => (string) $signalCount.' signals / '.(string) $botRunCount.' runs', 'context' => $executionAttemptCount.' execution attempts'],
                    ['label' => 'System Posture', 'value' => $systemSetting ? 'Configured' : 'Waiting', 'context' => $systemSetting ? 'Runtime '.$systemSetting->runtime_mode.' / Review '.$systemSetting->review_channel : 'System settings have not been saved yet'],
                ],
            ],
            'metrics' => [
                ['label' => 'Broker Coverage', 'value' => (string) $brokerConnectionCount, 'context' => $brokerCredentialCount.' saved credentials'],
                ['label' => 'License Coverage', 'value' => (string) $licenseCount, 'context' => $apiKeyCount.' masked API keys'],
                ['label' => 'Signal Coverage', 'value' => (string) $signalCount, 'context' => $executionAttemptCount.' execution attempts'],
                ['label' => 'Bot Run Coverage', 'value' => (string) $botRunCount, 'context' => 'Latest automation visibility'],
                ['label' => 'Billing Coverage', 'value' => (string) $invoiceCount, 'context' => $paidInvoiceCount.' paid invoices'],
                ['label' => 'Oversight Coverage', 'value' => (string) $activityCount, 'context' => $auditCount.' audit logs'],
                ['label' => 'Saved Settings', 'value' => $systemSetting ? '1' : '0', 'context' => $systemSetting?->status_level ?? 'No status level selected'],
            ],
            'trendColumns' => ['area' => 'Area', 'value' => 'Value', 'status' => 'Status', 'note' => 'Note'],
            'trendRows' => [
                ['area' => 'Workspace Footprint', 'value' => (string) $accountCount.' accounts', 'status' => $accountCount > 0 ? 'Available' : 'Empty', 'note' => $userCount.' users linked'],
                ['area' => 'Commercial Coverage', 'value' => (string) $subscriptionCount.' subscriptions', 'status' => $subscriptionCount > 0 ? 'Available' : 'Empty', 'note' => $activeSubscriptionCount.' active subscriptions'],
                ['area' => 'Billing Follow-through', 'value' => (string) $invoiceCount.' invoices', 'status' => $invoiceCount > 0 ? 'Available' : 'Empty', 'note' => $paidInvoiceCount.' paid invoices'],
                ['area' => 'Broker Readiness', 'value' => (string) $brokerConnectionCount.' connections', 'status' => $brokerConnectionCount > 0 ? 'Available' : 'Empty', 'note' => $brokerCredentialCount.' saved access pairs'],
                ['area' => 'License Readiness', 'value' => (string) $licenseCount.' licenses', 'status' => $licenseCount > 0 ? 'Available' : 'Empty', 'note' => $apiKeyCount.' masked API keys'],
                ['area' => 'Signal Visibility', 'value' => (string) $signalCount.' signals', 'status' => $signalCount > 0 ? 'Available' : 'Empty', 'note' => data_get($recentSignal?->payload, 'admin_summary', $executionAttemptCount.' execution attempts')],
                ['area' => 'Bot Run Health', 'value' => (string) $botRunCount.' runs', 'status' => $botRunCount > 0 ? 'Available' : 'Empty', 'note' => data_get($recentBotRun?->summary, 'safe_summary', 'Recent automation summary is available here when runs have been recorded')],
                ['area' => 'Oversight Signals', 'value' => (string) $activityCount.' activity / '.(string) $auditCount.' audit', 'status' => ($activityCount + $auditCount) > 0 ? 'Available' : 'Empty', 'note' => 'Latest operational oversight coverage'],
                ['area' => 'System Posture', 'value' => $systemSetting?->runtime_mode ?? 'Not set', 'status' => $systemSetting ? 'Configured' : 'Empty', 'note' => $systemSetting ? 'Review '.$systemSetting->review_channel : 'System settings have not been saved yet'],
            ],
            'relatedLinks' => [
                ['route' => 'admin.dashboard', 'label' => 'Dashboard', 'description' => 'Return to the main admin overview.'],
                ['route' => 'admin.audit.index', 'label' => 'Audit', 'description' => 'Review operational activity and audit history.'],
                ['route' => 'admin.system.index', 'label' => 'System', 'description' => 'Review current system health and settings context.'],
            ],
            'hasReportData' => $hasReportData,
        ];
    }
}
