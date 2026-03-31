<?php
// اعوز بالله من الشياطين و ان يحضرون بسم الله الرحمن الرحيم الله لا إله إلا هو الحي القيوم
// Bismillahi ar-Rahmani ar-Rahim Audhu billahi min ash-shayatin wa an yahdurun Bismillah ar-Rahman ar-Rahim Allah la ilaha illa huwa al-hayy al-qayyum. Tamsa Allahu ala ayunihim
// version: x
// ======================================================
// - App Name: gusgraph-trading
// - Gusgraph LLC -
// - Author: Gus Kazem
// - https://Gusgraph.com
// - File Path: app/Support/Admin/PlatformSummaryService.php
// ======================================================

namespace App\Support\Admin;

use App\Domain\Billing\Enums\SubscriptionStatus;
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

class PlatformSummaryService
{
    public function snapshot(): array
    {
        $accountCount = Account::query()->count();
        $userCount = User::query()->count();
        $subscriptionCount = Subscription::query()->count();
        $activeSubscriptionCount = Subscription::query()->where('status', SubscriptionStatus::Active->value)->count();
        $invoiceCount = Invoice::query()->count();
        $paidInvoiceCount = Invoice::query()->where('status', 'paid')->count();
        $brokerConnectionCount = BrokerConnection::query()->count();
        $brokerCredentialCount = BrokerCredential::query()->count();
        $licenseCount = ApiLicense::query()->count();
        $apiKeyCount = ApiKey::query()->count();
        $signalCount = Signal::query()->count();
        $botRunCount = BotRun::query()->count();
        $executionAttemptCount = BotRun::query()->where('run_type', 'execution')->count();
        $activityCount = ActivityLog::query()->count();
        $auditCount = AuditLog::query()->count();
        $systemSetting = SystemSetting::query()->first();

        return [
            'accounts' => $accountCount,
            'users' => $userCount,
            'subscriptions' => $subscriptionCount,
            'active_subscriptions' => $activeSubscriptionCount,
            'invoices' => $invoiceCount,
            'paid_invoices' => $paidInvoiceCount,
            'broker_connections' => $brokerConnectionCount,
            'broker_credentials' => $brokerCredentialCount,
            'licenses' => $licenseCount,
            'api_keys' => $apiKeyCount,
            'signals' => $signalCount,
            'bot_runs' => $botRunCount,
            'execution_attempts' => $executionAttemptCount,
            'activity_logs' => $activityCount,
            'audit_logs' => $auditCount,
            'has_report_data' => collect([
                $accountCount,
                $userCount,
                $subscriptionCount,
                $invoiceCount,
                $brokerConnectionCount,
                $brokerCredentialCount,
                $licenseCount,
                $apiKeyCount,
                $signalCount,
                $botRunCount,
                $activityCount,
                $auditCount,
            ])->sum() > 0 || (bool) $systemSetting,
            'system_setting' => $systemSetting,
        ];
    }

    public function detailedSnapshot(): array
    {
        $snapshot = $this->snapshot();

        return array_merge($snapshot, [
            'recent_activity' => ActivityLog::query()->latest('id')->first(),
            'recent_signal' => Signal::query()
                ->with(['strategyProfile', 'watchlistSymbol'])
                ->orderByDesc('generated_at')
                ->orderByDesc('id')
                ->first(),
            'recent_bot_run' => BotRun::query()
                ->with(['strategyProfile', 'automationSetting', 'alpacaAccount'])
                ->orderByDesc('started_at')
                ->orderByDesc('id')
                ->first(),
            'recent_audit' => AuditLog::query()->latest('id')->first(),
            'recent_invoice' => Invoice::query()->orderByDesc('issued_at')->orderByDesc('id')->first(),
            'recent_broker_connection' => BrokerConnection::query()->latest('id')->first(),
            'recent_broker_credential' => BrokerCredential::query()
                ->with(['brokerConnection.account'])
                ->latest('id')
                ->first(),
            'recent_license' => ApiLicense::query()
                ->with('account')
                ->latest('id')
                ->first(),
            'recent_api_key' => ApiKey::query()
                ->with('apiLicense.account')
                ->latest('id')
                ->first(),
            'recent_accounts' => Account::query()
                ->with(['owner', 'users', 'subscriptions.subscriptionPlan', 'apiLicenses'])
                ->orderBy('name')
                ->limit(5)
                ->get(),
        ]);
    }

    public function systemPlatformState(?array $snapshot = null): array
    {
        $snapshot ??= $this->snapshot();

        return [
            'accounts' => $snapshot['accounts'],
            'subscriptions' => $snapshot['subscriptions'],
            'broker_connections' => $snapshot['broker_connections'],
            'api_licenses' => $snapshot['licenses'],
            'api_keys' => $snapshot['api_keys'],
            'signals' => $snapshot['signals'],
            'bot_runs' => $snapshot['bot_runs'],
            'audit_logs' => $snapshot['audit_logs'],
            'activity_logs' => $snapshot['activity_logs'],
        ];
    }
}
