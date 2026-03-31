<?php
// اعوز بالله من الشياطين و ان يحضرون بسم الله الرحمن الرحيم الله لا إله إلا هو الحي القيوم
// Bismillahi ar-Rahmani ar-Rahim Audhu billahi min ash-shayatin wa an yahdurun Bismillah ar-Rahman ar-Rahim Allah la ilaha illa huwa al-hayy al-qayyum. Tamsa Allahu ala ayunihim
// version: x
// ======================================================
// - App Name: gusgraph-trading
// - Gusgraph LLC -
// - Author: Gus Kazem
// - https://Gusgraph.com
// - File Path: app/Domain/Dashboard/Services/DashboardService.php
// =====================================================

namespace App\Domain\Dashboard\Services;

use App\Models\Account;
use App\Models\ActivityLog;
use App\Models\ApiKey;
use App\Models\ApiLicense;
use App\Models\AuditLog;
use App\Models\BrokerConnection;
use App\Models\BrokerCredential;
use App\Models\Invoice;
use App\Models\Signal;
use App\Models\Subscription;
use App\Models\SystemSetting;
use App\Models\User;
use App\Models\BotRun;
use App\Support\Admin\PlatformSummaryService;
use Illuminate\Support\Collection;

class DashboardService
{
    public function __construct(
        protected PlatformSummaryService $platformSummaryService
    ) {
    }

    public function getCustomerDashboardData(?Account $account = null): array
    {
        $subscription = $account?->subscriptions
            ->sortByDesc(fn ($item) => $item->starts_at?->getTimestamp() ?? $item->created_at?->getTimestamp() ?? 0)
            ->first();
        $invoices = $account?->invoices ?? collect();
        $brokerConnections = $account?->brokerConnections ?? collect();
        $brokerCredentials = $brokerConnections->flatMap->brokerCredentials;
        $alpacaAccounts = $account?->alpacaAccounts ?? collect();
        $primaryAlpacaAccount = $alpacaAccounts->sortByDesc(fn ($item) => $item->last_synced_at?->getTimestamp() ?? $item->created_at?->getTimestamp() ?? 0)->first();
        $licenses = $account?->apiLicenses ?? collect();
        $apiKeys = $licenses->flatMap->apiKeys;
        $signals = $account?->signals
            ?->sortByDesc(fn ($item) => $item->generated_at?->getTimestamp() ?? $item->created_at?->getTimestamp() ?? 0)
            ->values() ?? collect();
        $botRuns = $account?->botRuns
            ?->sortByDesc(fn ($item) => $item->started_at?->getTimestamp() ?? $item->created_at?->getTimestamp() ?? 0)
            ->values() ?? collect();
        $activityLogs = $account?->activityLogs
            ?->sortByDesc(fn ($item) => $item->created_at?->getTimestamp() ?? 0)
            ->values() ?? collect();
        $paidInvoices = $invoices->filter(fn ($invoice) => $invoice->status === 'paid');
        $recentInvoice = $invoices
            ->sortByDesc(fn ($invoice) => $invoice->issued_at?->getTimestamp() ?? $invoice->created_at?->getTimestamp() ?? 0)
            ->first();
        $recentActivity = $activityLogs->first();
        $recentSignal = $signals->first();
        $recentBotRun = $botRuns->first();
        $executionAttempts = $botRuns->filter(fn ($run) => $run->run_type === 'execution');
        $readinessCount = collect([
            $account ? 1 : 0,
            $subscription ? 1 : 0,
            $invoices->isNotEmpty() ? 1 : 0,
            $brokerCredentials->isNotEmpty() ? 1 : 0,
            $apiKeys->isNotEmpty() ? 1 : 0,
        ])->sum();
        $hasDashboardData = (bool) ($account || $subscription || $invoices->isNotEmpty() || $brokerConnections->isNotEmpty() || $licenses->isNotEmpty() || $activityLogs->isNotEmpty() || $signals->isNotEmpty() || $botRuns->isNotEmpty());

        return [
            'account' => [
                'title' => 'Account Summary',
                'message' => $account
                    ? 'Workspace '.$account->name.' is available locally with owner '.($account->owner?->name ?? 'unassigned').' and current-account linkage.'
                    : 'No accessible local workspace record is available yet.',
                'items' => $account ? [
                    ['label' => 'Workspace', 'value' => $account->name],
                    ['label' => 'Slug', 'value' => $account->slug],
                    ['label' => 'Owner', 'value' => $account->owner?->name ?? 'Unassigned'],
                    ['label' => 'Owner Contact', 'value' => $account->owner?->email ?? 'No owner email'],
                    ['label' => 'Status', 'value' => ucfirst((string) ($account->status->value ?? $account->status))],
                    ['label' => 'Members', 'value' => (string) $account->users->count()],
                ] : [],
            ],
            'billing' => [
                'title' => 'Billing Summary',
                'message' => $subscription
                    ? 'Current billing posture is derived from the latest local subscription, plan, and invoice records.'
                    : 'No local subscription record is available yet for billing summary.',
                'items' => [
                    ['label' => 'Current Plan', 'value' => $subscription?->subscriptionPlan?->name ?? 'No linked plan'],
                    ['label' => 'Subscription Status', 'value' => $subscription ? ucfirst((string) ($subscription->status->value ?? $subscription->status)) : 'Not started'],
                    ['label' => 'Plan Price', 'value' => $subscription?->subscriptionPlan ? strtoupper($subscription->subscriptionPlan->currency).' '.number_format((float) $subscription->subscriptionPlan->price, 2).' / '.$subscription->subscriptionPlan->interval : 'No linked plan pricing'],
                    ['label' => 'Subscription Start', 'value' => $subscription?->starts_at?->toDateString() ?? 'Not started'],
                    ['label' => 'Invoices', 'value' => (string) $invoices->count()],
                    ['label' => 'Paid Invoices', 'value' => (string) $paidInvoices->count()],
                ],
            ],
            'broker' => [
                'title' => 'Alpaca Summary',
                'message' => $brokerConnections->isNotEmpty()
                    ? 'Alpaca readiness is derived from saved local connection, account-state, and masked credential records.'
                    : 'No local Alpaca connection record is available yet.',
                'items' => [
                    ['label' => 'Connections', 'value' => (string) $brokerConnections->count()],
                    ['label' => 'Alpaca Accounts', 'value' => (string) $alpacaAccounts->count()],
                    ['label' => 'Saved Credentials', 'value' => (string) $brokerCredentials->count()],
                    ['label' => 'Primary Provider', 'value' => $brokerConnections->isNotEmpty() ? 'Alpaca' : 'Not set'],
                    ['label' => 'Environment', 'value' => $primaryAlpacaAccount?->environment ? strtoupper((string) $primaryAlpacaAccount->environment) : 'Not set'],
                    ['label' => 'Primary Connection', 'value' => $brokerConnections->first()?->name ?? 'No saved connection'],
                    ['label' => 'Account State', 'value' => $primaryAlpacaAccount?->status ?? 'No linked Alpaca account'],
                    ['label' => 'Primary Credential', 'value' => $brokerCredentials->first()?->maskedSummary() ?? 'No saved credential'],
                ],
            ],
            'license' => [
                'title' => 'License Summary',
                'message' => $licenses->isNotEmpty()
                    ? 'License readiness is derived from saved local license and API key records.'
                    : 'No local license record is available yet.',
                'items' => [
                    ['label' => 'Licenses', 'value' => (string) $licenses->count()],
                    ['label' => 'API Keys', 'value' => (string) $apiKeys->count()],
                    ['label' => 'Primary License', 'value' => $licenses->first()?->name ?? 'Not set'],
                    ['label' => 'License Status', 'value' => $licenses->first() ? ucfirst((string) ($licenses->first()->status->value ?? $licenses->first()->status)) : 'Not set'],
                    ['label' => 'Primary Key', 'value' => $apiKeys->first()?->maskedTokenSummary() ?? 'No saved API key'],
                ],
            ],
            'readiness' => [
                'title' => 'Onboarding Readiness',
                'message' => $account
                    ? 'Current-account readiness is derived from workspace, billing, broker, license, and invoice records already saved locally.'
                    : 'No current-account readiness records are available yet.',
                'items' => [
                    ['label' => 'Workspace', 'value' => $account ? 'Ready' : 'Missing'],
                    ['label' => 'Billing', 'value' => $subscription ? 'Ready' : 'Missing'],
                    ['label' => 'Invoices', 'value' => $invoices->isNotEmpty() ? 'Ready' : 'Missing'],
                    ['label' => 'Broker', 'value' => $brokerCredentials->isNotEmpty() ? 'Ready' : 'Missing'],
                    ['label' => 'License', 'value' => $apiKeys->isNotEmpty() ? 'Ready' : 'Missing'],
                    ['label' => 'Readiness Score', 'value' => $readinessCount.'/5'],
                ],
            ],
            'activity' => [
                'title' => 'Control-Room Activity',
                'message' => ($recentActivity || $recentSignal || $recentBotRun)
                    ? 'Current-account AI activity, latest signals, and bot-run health now come from saved relational rows.'
                    : 'No current-account AI activity, signal, or bot-run rows are available yet.',
                'items' => [
                    ['label' => 'Activity Rows', 'value' => (string) $activityLogs->count()],
                    ['label' => 'Latest Activity', 'value' => $recentActivity?->type ?? 'No recent activity'],
                    ['label' => 'Activity Level', 'value' => $recentActivity ? ucfirst((string) ($recentActivity->level ?? 'info')) : 'Not available'],
                    ['label' => 'Latest Signal', 'value' => $recentSignal ? ($recentSignal->symbol.' / '.strtoupper((string) $recentSignal->direction)) : 'No recent signal'],
                    ['label' => 'Signal Status', 'value' => $recentSignal?->status ?? 'No signal status'],
                    ['label' => 'Latest Bot Run', 'value' => $recentBotRun ? ucfirst((string) $recentBotRun->run_type).' / '.ucfirst((string) $recentBotRun->status) : 'No bot run yet'],
                    ['label' => 'Execution Attempts', 'value' => (string) $executionAttempts->count()],
                    ['label' => 'Latest Note', 'value' => $recentActivity?->message ?? 'No current-account activity message'],
                    ['label' => 'Latest Invoice', 'value' => $recentInvoice?->number ?? 'No current-account invoice'],
                ],
            ],
            'sections' => [
                [
                    'title' => 'Account Readiness',
                    'description' => $account
                        ? 'Workspace, owner, and member counts are read from the current local account.'
                        : 'No workspace record is available yet for customer dashboard summary.',
                ],
                [
                    'title' => 'Billing Readiness',
                    'description' => $subscription
                        ? 'Subscription, plan, and invoice counts now come from saved local billing records.'
                        : 'Billing stays in a clean empty state until the first subscription record exists.',
                ],
                [
                    'title' => 'Connection Readiness',
                    'description' => $brokerCredentials->isNotEmpty()
                        ? 'Saved broker credentials and API keys indicate onboarding readiness locally.'
                        : 'Broker and license readiness remain incomplete until saved local credentials exist.',
                ],
                [
                    'title' => 'Recent Activity',
                    'description' => ($recentActivity || $recentSignal || $recentBotRun)
                        ? 'Recent AI activity, latest signals, and bot-run summaries now stay visible without requiring runtime services.'
                        : 'Recent control-room visibility stays in a clean empty state until activity, signal, or bot-run rows exist.',
                ],
            ],
            'stats' => [
                [
                    'label' => 'Workspace',
                    'value' => $account ? 'Ready' : 'Missing',
                    'description' => $account ? $account->name.' / '.($account->slug ?? 'no-slug') : 'No accessible workspace',
                ],
                [
                    'label' => 'Billing',
                    'value' => $subscription ? ucfirst((string) ($subscription->status->value ?? $subscription->status)) : 'Missing',
                    'description' => $subscription?->subscriptionPlan?->name ?? 'No subscription plan linked',
                ],
                [
                    'label' => 'Broker',
                    'value' => $brokerCredentials->isNotEmpty() ? 'Ready' : 'Missing',
                    'description' => $brokerConnections->count().' Alpaca connections / '.$brokerCredentials->count().' credentials',
                ],
                [
                    'label' => 'License',
                    'value' => $apiKeys->isNotEmpty() ? 'Ready' : 'Missing',
                    'description' => $licenses->count().' licenses / '.$apiKeys->count().' keys',
                ],
                [
                    'label' => 'Activity',
                    'value' => ($activityLogs->isNotEmpty() || $signals->isNotEmpty() || $botRuns->isNotEmpty()) ? 'Available' : 'Empty',
                    'description' => $activityLogs->count().' activity / '.$signals->count().' signals / '.$botRuns->count().' runs',
                ],
            ],
            'hasDashboardData' => $hasDashboardData,
        ];
    }

    public function getAdminDashboardData(): array
    {
        $snapshot = $this->platformSummaryService->detailedSnapshot();
        $accountCount = $snapshot['accounts'];
        $userCount = $snapshot['users'];
        $subscriptionCount = $snapshot['subscriptions'];
        $activeSubscriptionCount = $snapshot['active_subscriptions'];
        $invoiceCount = $snapshot['invoices'];
        $paidInvoiceCount = $snapshot['paid_invoices'];
        $brokerConnectionCount = $snapshot['broker_connections'];
        $brokerCredentialCount = $snapshot['broker_credentials'];
        $licenseCount = $snapshot['licenses'];
        $apiKeyCount = $snapshot['api_keys'];
        $signalCount = $snapshot['signals'];
        $botRunCount = $snapshot['bot_runs'];
        $executionAttemptCount = $snapshot['execution_attempts'];
        $activityCount = $snapshot['activity_logs'];
        $auditCount = $snapshot['audit_logs'];
        $systemSetting = $snapshot['system_setting'];
        $recentActivity = $snapshot['recent_activity'];
        $recentSignal = $snapshot['recent_signal'];
        $recentBotRun = $snapshot['recent_bot_run'];
        $recentAudit = $snapshot['recent_audit'];
        $recentInvoice = $snapshot['recent_invoice'];
        $recentBrokerConnection = $snapshot['recent_broker_connection'];
        $recentBrokerCredential = $snapshot['recent_broker_credential'];
        $recentLicense = $snapshot['recent_license'];
        $recentApiKey = $snapshot['recent_api_key'];
        $readinessCount = collect([
            $accountCount > 0 ? 1 : 0,
            $subscriptionCount > 0 ? 1 : 0,
            $invoiceCount > 0 ? 1 : 0,
            $brokerCredentialCount > 0 ? 1 : 0,
            $apiKeyCount > 0 ? 1 : 0,
            $systemSetting ? 1 : 0,
        ])->sum();
        $recentAccounts = $snapshot['recent_accounts'];
        $hasDashboardData = (bool) $snapshot['has_report_data'];

        return [
            'account' => [
                'title' => 'Accounts Summary',
                'message' => $accountCount > 0
                    ? 'Account oversight is derived from current local workspace and membership records.'
                    : 'No local account records are available yet.',
                'items' => [
                    ['label' => 'Accounts', 'value' => (string) $accountCount],
                    ['label' => 'Users', 'value' => (string) $userCount],
                    ['label' => 'Recent Workspace', 'value' => $recentAccounts->first()?->name ?? 'No local account'],
                    ['label' => 'Recent Slug', 'value' => $recentAccounts->first()?->slug ?? 'No local slug'],
                    ['label' => 'Recent Owner', 'value' => $recentAccounts->first()?->owner?->name ?? 'Unassigned'],
                    ['label' => 'Subscriptions', 'value' => (string) $subscriptionCount],
                    ['label' => 'Invoices', 'value' => (string) $invoiceCount],
                ],
            ],
            'billing' => [
                'title' => 'Billing Summary',
                'message' => $invoiceCount > 0 || $subscriptionCount > 0
                    ? 'Billing coverage is derived from local subscription, plan, and invoice rows.'
                    : 'No local billing rows are available yet.',
                'items' => [
                    ['label' => 'Subscriptions', 'value' => (string) $subscriptionCount],
                    ['label' => 'Active Subscriptions', 'value' => (string) $activeSubscriptionCount],
                    ['label' => 'Invoices', 'value' => (string) $invoiceCount],
                    ['label' => 'Paid Invoices', 'value' => (string) $paidInvoiceCount],
                    ['label' => 'Unpaid Invoices', 'value' => (string) max($invoiceCount - $paidInvoiceCount, 0)],
                    ['label' => 'Latest Invoice', 'value' => $recentInvoice?->number ?? 'No local invoice'],
                    ['label' => 'Latest Invoice Status', 'value' => $recentInvoice ? ucfirst((string) ($recentInvoice->status->value ?? $recentInvoice->status)) : 'Not available'],
                ],
            ],
            'broker' => [
                'title' => 'Broker Summary',
                'message' => $brokerConnectionCount > 0 || $brokerCredentialCount > 0
                    ? 'Broker readiness is derived from local connection and credential rows.'
                    : 'No local broker rows are available yet.',
                'items' => [
                    ['label' => 'Connections', 'value' => (string) $brokerConnectionCount],
                    ['label' => 'Credentials', 'value' => (string) $brokerCredentialCount],
                    ['label' => 'Credential Presence', 'value' => $brokerCredentialCount > 0 ? 'Available' : 'Missing'],
                    ['label' => 'Latest Connection', 'value' => $recentBrokerConnection?->name ?? 'No local broker connection'],
                    ['label' => 'Connection Status', 'value' => $recentBrokerConnection ? ucfirst((string) ($recentBrokerConnection->status->value ?? $recentBrokerConnection->status ?? 'unknown')) : 'Not available'],
                    ['label' => 'Latest Credential', 'value' => $recentBrokerCredential?->label ?? 'No local credential'],
                    ['label' => 'Masked Credential', 'value' => $recentBrokerCredential?->maskedSummary() ?? 'No saved credential'],
                ],
            ],
            'license' => [
                'title' => 'License Summary',
                'message' => $licenseCount > 0 || $apiKeyCount > 0
                    ? 'License readiness is derived from local license and API key rows.'
                    : 'No local license rows are available yet.',
                'items' => [
                    ['label' => 'Licenses', 'value' => (string) $licenseCount],
                    ['label' => 'API Keys', 'value' => (string) $apiKeyCount],
                    ['label' => 'Key Presence', 'value' => $apiKeyCount > 0 ? 'Available' : 'Missing'],
                    ['label' => 'Latest License', 'value' => $recentLicense?->name ?? 'No local license'],
                    ['label' => 'License Status', 'value' => $recentLicense ? ucfirst((string) ($recentLicense->status->value ?? $recentLicense->status)) : 'Not available'],
                    ['label' => 'Latest API Key', 'value' => $recentApiKey?->name ?? 'No local API key'],
                    ['label' => 'Masked Key', 'value' => $recentApiKey?->maskedTokenSummary() ?? 'No saved API key'],
                ],
            ],
            'system' => [
                'title' => 'System Summary',
                'message' => $systemSetting
                    ? 'System settings are present locally with runtime mode '.$systemSetting->runtime_mode.' and review channel '.$systemSetting->review_channel.'.'
                    : 'No persisted local system settings record is available yet.',
                'items' => [
                    ['label' => 'Settings Record', 'value' => $systemSetting ? 'Present' : 'Missing'],
                    ['label' => 'Runtime Mode', 'value' => $systemSetting?->runtime_mode ?? 'Not set'],
                    ['label' => 'Review Channel', 'value' => $systemSetting?->review_channel ?? 'Not set'],
                    ['label' => 'Status Level', 'value' => $systemSetting?->status_level ?? 'Not set'],
                    ['label' => 'Platform Readiness', 'value' => $readinessCount.'/6'],
                ],
            ],
            'activity' => [
                'title' => 'Recent Platform Activity',
                'message' => $recentActivity || $recentAudit || $recentSignal || $recentBotRun
                    ? 'Recent platform indicators are derived from local AI activity, signal, bot-run, and audit rows.'
                    : 'No recent platform activity rows are available yet.',
                'items' => [
                    ['label' => 'Activity Logs', 'value' => (string) $activityCount],
                    ['label' => 'Signals', 'value' => (string) $signalCount],
                    ['label' => 'Bot Runs', 'value' => (string) $botRunCount],
                    ['label' => 'Execution Attempts', 'value' => (string) $executionAttemptCount],
                    ['label' => 'Audit Logs', 'value' => (string) $auditCount],
                    ['label' => 'Latest Activity', 'value' => $recentActivity?->type ?? 'No recent activity'],
                    ['label' => 'Latest Signal', 'value' => $recentSignal ? ($recentSignal->symbol.' / '.strtoupper((string) $recentSignal->direction)) : 'No recent signal'],
                    ['label' => 'Latest Bot Run', 'value' => $recentBotRun ? ucfirst((string) $recentBotRun->run_type).' / '.ucfirst((string) $recentBotRun->status) : 'No recent bot run'],
                    ['label' => 'Latest Activity Note', 'value' => $recentActivity?->message ?? 'No local activity message'],
                    ['label' => 'Latest Audit', 'value' => $recentAudit?->action ?? 'No recent audit'],
                    ['label' => 'Latest Audit Summary', 'value' => $recentAudit?->summary ?? 'No local audit summary'],
                    ['label' => 'Latest Workspace', 'value' => $recentAccounts->first()?->name ?? 'No local workspace'],
                ],
            ],
            'sections' => [
                [
                    'title' => 'Workspace Coverage',
                    'description' => $accountCount > 0
                        ? 'Account, user, subscription, and invoice totals now come directly from saved local records.'
                        : 'Workspace coverage stays in a clean empty state until local records exist.',
                ],
                [
                    'title' => 'Connection Coverage',
                    'description' => ($brokerConnectionCount + $licenseCount) > 0
                        ? 'Broker connection, credential, license, and API key totals now come from saved local records.'
                        : 'Connection coverage remains empty until broker or license rows are saved.',
                ],
                [
                    'title' => 'Platform Oversight',
                    'description' => ($activityCount + $auditCount + $signalCount + $botRunCount) > 0 || $systemSetting
                        ? 'Audit, AI activity, signal, bot-run, invoice, and system-setting presence now come from saved local records.'
                        : 'Oversight coverage stays empty until audit or system rows exist.',
                ],
            ],
            'stats' => [
                [
                    'label' => 'Accounts',
                    'value' => (string) $accountCount,
                    'description' => $userCount.' users / '.$subscriptionCount.' subscriptions',
                ],
                [
                    'label' => 'Broker',
                    'value' => (string) $brokerConnectionCount,
                    'description' => $brokerCredentialCount.' saved credentials / '.($recentBrokerConnection?->name ?? 'no recent connection'),
                ],
                [
                    'label' => 'License',
                    'value' => (string) $licenseCount,
                    'description' => $apiKeyCount.' saved API keys / '.($recentLicense?->name ?? 'no recent license'),
                ],
                [
                    'label' => 'Audit',
                    'value' => (string) ($activityCount + $auditCount + $signalCount + $botRunCount),
                    'description' => $activityCount.' activity / '.$signalCount.' signals / '.$botRunCount.' runs / '.$auditCount.' audit',
                ],
                [
                    'label' => 'System',
                    'value' => $systemSetting ? 'Configured' : 'Missing',
                    'description' => 'Readiness '.$readinessCount.'/6',
                ],
            ],
            'managementSummary' => $recentAccounts->map(function ($account) {
                $plan = $account->subscriptions
                    ->sortByDesc(fn ($subscription) => $subscription->starts_at?->getTimestamp() ?? $subscription->created_at?->getTimestamp() ?? 0)
                    ->first()?->subscriptionPlan?->name;

                return [
                    'label' => $account->name,
                    'value' => ucfirst((string) ($account->status->value ?? $account->status)),
                    'note' => 'Owner: '.($account->owner?->name ?? 'Unassigned')
                        .' | Members: '.$account->users->count()
                        .' | Licenses: '.$account->apiLicenses->count()
                        .' | Plan: '.($plan ?? 'No linked plan'),
                    'route' => route('admin.account-detail.index', ['account' => $account]),
                ];
            })->values()->all(),
            'healthSummary' => [
                ['label' => 'Accounts', 'value' => (string) $accountCount],
                ['label' => 'Subscriptions', 'value' => (string) $subscriptionCount],
                ['label' => 'Invoices', 'value' => (string) $invoiceCount],
                ['label' => 'System Settings', 'value' => $systemSetting ? 'Present' : 'Missing'],
            ],
            'auditOverview' => [
                ['event' => 'Activity Logs', 'note' => (string) $activityCount.' local rows'],
                ['event' => 'Signals', 'note' => (string) $signalCount.' local rows'],
                ['event' => 'Bot Runs', 'note' => (string) $botRunCount.' local rows'],
                ['event' => 'Audit Logs', 'note' => (string) $auditCount.' local rows'],
                ['event' => 'Execution Attempts', 'note' => (string) $executionAttemptCount.' local runs'],
            ],
            'hasDashboardData' => $hasDashboardData,
        ];
    }
}
