<?php
// اعوز بالله من الشياطين و ان يحضرون بسم الله الرحمن الرحيم الله لا إله إلا هو الحي القيوم
// Bismillahi ar-Rahmani ar-Rahim Audhu billahi min ash-shayatin wa an yahdurun Bismillah ar-Rahman ar-Rahim Allah la ilaha illa huwa al-hayy al-qayyum. Tamsa Allahu ala ayunihim
// version: x
// ======================================================
// - App Name: bismel1.com
// - Gusgraph LLC -
// - Author: Gus Kazem
// - https://Gusgraph.com
// - File Path: app/Support/ViewData/CustomerReportsPageData.php
// ======================================================

namespace App\Support\ViewData;

use App\Models\Account;
use Illuminate\Support\Collection;

class CustomerReportsPageData
{
    public static function make(?Account $account = null, array $state = []): array
    {
        $subscriptions = $state['subscriptions'] ?? collect();
        $currentSubscription = $state['current_subscription'] ?? null;
        $activeSubscriptions = $state['active_subscriptions'] ?? collect();
        $invoices = $state['invoices'] ?? collect();
        $paidInvoices = $state['paid_invoices'] ?? collect();
        $brokerConnections = $state['broker_connections'] ?? collect();
        $brokerCredentials = $state['broker_credentials'] ?? collect();
        $licenses = $state['licenses'] ?? collect();
        $apiKeys = $state['api_keys'] ?? collect();
        $signals = $state['signals'] ?? collect();
        $botRuns = $state['bot_runs'] ?? collect();
        $activityLogs = $state['activity_logs'] ?? collect();
        $hasReportData = (bool) ($state['has_report_data'] ?? false);
        $onboardingReadyCount = collect([
            $currentSubscription ? 1 : 0,
            $invoices->isNotEmpty() ? 1 : 0,
            $brokerCredentials->isNotEmpty() ? 1 : 0,
            $apiKeys->isNotEmpty() ? 1 : 0,
        ])->sum();
        $recentActivity = $activityLogs instanceof Collection
            ? $activityLogs->sortByDesc('created_at')->take(1)->first()
            : null;
        $recentSignal = $signals instanceof Collection
            ? $signals->sortByDesc(fn ($item) => $item->generated_at?->getTimestamp() ?? $item->created_at?->getTimestamp() ?? 0)->take(1)->first()
            : null;
        $recentBotRun = $botRuns instanceof Collection
            ? $botRuns->sortByDesc(fn ($item) => $item->started_at?->getTimestamp() ?? $item->created_at?->getTimestamp() ?? 0)->take(1)->first()
            : null;
        $executionAttempts = $botRuns->filter(fn ($run) => $run->run_type === 'execution');

        return [
            'page' => [
                'title' => 'Customer Reports',
                'intro' => 'A clear workspace summary that brings billing, access, connection, and recent account activity into one calm view.',
                'subtitle' => $account
                    ? 'Use this page to review the current workspace, recent account history, and setup progress without leaving the customer area.'
                    : 'Choose or create a workspace first, then return here to review billing, connection, and account progress.',
                'sections' => [
                    ['heading' => 'Plan and Billing', 'description' => 'Review the active package, invoice history, and overall billing posture for this workspace.'],
                    ['heading' => 'Setup Progress', 'description' => 'See whether broker access, API access, and key setup steps are in place for this workspace.'],
                    ['heading' => 'Recent Product Activity', 'description' => 'Check the latest account activity, summaries, and automation visibility in one place.'],
                ],
            ],
            'summary' => [
                'headline' => $hasReportData
                    ? 'Your workspace summary is ready.'
                    : 'Your workspace summary will appear here as setup and activity begin.',
                'details' => $hasReportData
                    ? 'This page brings together the most useful billing, setup, and activity signals for the current workspace in a clear customer view.'
                    : 'Once billing, connection, or account activity begins, this page will turn into a useful workspace summary.',
                'items' => $account ? [
                    ['label' => 'Workspace', 'value' => $account->name, 'context' => 'Slug '.$account->slug],
                    ['label' => 'Current Plan', 'value' => $currentSubscription?->subscriptionPlan?->name ?? 'No active plan yet', 'context' => ucfirst((string) ($currentSubscription?->status->value ?? $currentSubscription?->status ?? 'not_started'))],
                    ['label' => 'Invoices', 'value' => $paidInvoices->count().' paid / '.max($invoices->count() - $paidInvoices->count(), 0).' open', 'context' => $invoices->count().' total invoices'],
                    ['label' => 'Setup Progress', 'value' => (string) $onboardingReadyCount.' of 4 complete', 'context' => 'Plan, billing, broker, and API access'],
                    ['label' => 'Activity Overview', 'value' => (string) $signals->count().' signals / '.(string) $botRuns->count().' runs', 'context' => (string) $activityLogs->count().' recent activity updates'],
                ] : [],
            ],
            'metrics' => [
                ['label' => 'Subscriptions', 'value' => (string) $subscriptions->count(), 'context' => $activeSubscriptions->count().' active'],
                ['label' => 'Broker Connections', 'value' => (string) $brokerConnections->count(), 'context' => $brokerCredentials->count().' saved access pairs'],
                ['label' => 'API Access', 'value' => (string) $licenses->count(), 'context' => $apiKeys->count().' masked keys'],
                ['label' => 'Invoices', 'value' => (string) $invoices->count(), 'context' => $paidInvoices->count().' paid'],
                ['label' => 'Activity', 'value' => (string) $activityLogs->count(), 'context' => $recentActivity?->type ?? 'No recent activity yet'],
                ['label' => 'Signals', 'value' => (string) $signals->count(), 'context' => $recentSignal?->symbol ?? 'No recent signal yet'],
                ['label' => 'Runs', 'value' => (string) $botRuns->count(), 'context' => $recentBotRun?->run_type ?? 'No recent run yet'],
            ],
            'trendColumns' => ['area' => 'Area', 'value' => 'Value', 'status' => 'Status', 'note' => 'Note'],
            'trendRows' => $account ? [
                [
                    'area' => 'Workspace',
                    'value' => $account->name,
                    'status' => 'Available',
                    'note' => 'Owner: '.($account->owner?->name ?? 'Unassigned'),
                ],
                [
                    'area' => 'Current Plan',
                    'value' => $currentSubscription?->subscriptionPlan?->name ?? 'No active plan yet',
                    'status' => $currentSubscription ? ucfirst((string) ($currentSubscription->status->value ?? $currentSubscription->status)) : 'Missing',
                    'note' => $currentSubscription?->starts_at?->toDateString() ? 'Started '.$currentSubscription->starts_at->toDateString() : 'No plan start recorded',
                ],
                [
                    'area' => 'Billing',
                    'value' => (string) $invoices->count().' invoices',
                    'status' => $invoices->isNotEmpty() ? 'Available' : 'Waiting',
                    'note' => $paidInvoices->count().' paid invoices',
                ],
                [
                    'area' => 'Broker Access',
                    'value' => (string) $brokerConnections->count().' connections',
                    'status' => $brokerCredentials->isNotEmpty() ? 'Ready' : 'Waiting',
                    'note' => $brokerCredentials->isNotEmpty() ? 'Broker access is saved for this workspace' : 'Connect Alpaca to complete this step',
                ],
                [
                    'area' => 'API Access',
                    'value' => (string) $licenses->count().' licenses / '.(string) $apiKeys->count().' keys',
                    'status' => $apiKeys->isNotEmpty() ? 'Ready' : 'Waiting',
                    'note' => $apiKeys->isNotEmpty() ? 'Saved API access is ready for this workspace' : 'Add API access to complete this step',
                ],
                [
                    'area' => 'Recent Activity',
                    'value' => $recentActivity?->type ?? 'No recent activity yet',
                    'status' => $recentActivity ? ucfirst((string) ($recentActivity->level ?? 'info')) : 'Waiting',
                    'note' => $recentActivity?->message ?? 'Activity updates will appear here as the workspace becomes active',
                ],
                [
                    'area' => 'Latest Signal',
                    'value' => $recentSignal ? ($recentSignal->symbol.' / '.strtoupper((string) $recentSignal->direction)) : 'No recent signal yet',
                    'status' => $recentSignal ? ucfirst((string) $recentSignal->status) : 'Waiting',
                    'note' => data_get($recentSignal?->payload, 'public_summary', 'Signals will appear here after the first recorded setup cycle'),
                ],
                [
                    'area' => 'Automation Summary',
                    'value' => (string) $botRuns->count().' runs',
                    'status' => $recentBotRun ? ucfirst((string) $recentBotRun->status) : 'Waiting',
                    'note' => $recentBotRun ? data_get($recentBotRun->summary, 'safe_summary', ucfirst((string) $recentBotRun->run_type).' / '.$executionAttempts->count().' execution attempts') : 'Automation summaries will appear here after the first recorded run',
                ],
            ] : [],
            'relatedLinks' => [
                ['route' => 'customer.dashboard', 'label' => 'Dashboard', 'description' => 'Return to your main workspace overview.'],
                ['route' => 'customer.invoices.index', 'label' => 'Invoices', 'description' => 'Review plan and invoice history for this workspace.'],
                ['route' => 'customer.onboarding.index', 'label' => 'Onboarding', 'description' => 'Continue setup and readiness steps.'],
                ['route' => 'customer.settings.index', 'label' => 'Settings', 'description' => 'Review profile, workspace, and team details.'],
            ],
            'hasReportData' => $hasReportData,
        ];
    }
}
