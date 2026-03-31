<?php
// اعوز بالله من الشياطين و ان يحضرون بسم الله الرحمن الرحيم الله لا إله إلا هو الحي القيوم
// Bismillahi ar-Rahmani ar-Rahim Audhu billahi min ash-shayatin wa an yahdurun Bismillah ar-Rahman ar-Rahim Allah la ilaha illa huwa al-hayy al-qayyum. Tamsa Allahu ala ayunihim
// version: x
// ======================================================
// - App Name: gusgraph-trading
// - Gusgraph LLC -
// - Author: Gus Kazem
// - https://Gusgraph.com
// - File Path: app/Http/Controllers/Customer/ReportsController.php
// ======================================================

namespace App\Http\Controllers\Customer;

use App\Domain\Billing\Enums\SubscriptionStatus;
use App\Http\Controllers\Controller;
use App\Support\Customer\CurrentCustomerAccountResolver;
use App\Support\Firestore\FirestoreBridge;
use App\Support\Navigation\CustomerNavigation;
use App\Support\ViewData\CustomerReportsPageData;

class ReportsController extends Controller
{
    public function index(CurrentCustomerAccountResolver $currentCustomerAccountResolver, FirestoreBridge $firestoreBridge)
    {
        $user = request()->user();
        $account = $currentCustomerAccountResolver->resolveForPreset($user, 'summary');
        $subscription = $account?->subscriptions
            ->sortByDesc(fn ($item) => $item->starts_at?->getTimestamp() ?? $item->created_at?->getTimestamp() ?? 0)
            ->first();
        $invoices = $account?->invoices ?? collect();
        $brokerConnections = $account?->brokerConnections ?? collect();
        $brokerCredentials = $brokerConnections->flatMap->brokerCredentials;
        $licenses = $account?->apiLicenses ?? collect();
        $apiKeys = $licenses->flatMap->apiKeys;
        $signals = $account?->signals ?? collect();
        $botRuns = $account?->botRuns ?? collect();
        $activityLogs = $account?->activityLogs ?? collect();
        $paidInvoices = $invoices->filter(fn ($invoice) => $invoice->status === 'paid');
        $hasReportData = (bool) ($account || $subscription || $invoices->isNotEmpty() || $brokerConnections->isNotEmpty() || $licenses->isNotEmpty() || $activityLogs->isNotEmpty() || $signals->isNotEmpty() || $botRuns->isNotEmpty());
        $firestoreReadSummary = $user
            ? $firestoreBridge->readUserIntegrationSummary($user, $account)
            : ['status' => 'not_mapped', 'headline' => 'This user is not mapped to Firestore yet.', 'details' => 'No signed-in user is available for Firestore summary.', 'items' => []];
        $data = CustomerReportsPageData::make(
            $account,
            [
                'subscriptions' => $account?->subscriptions ?? collect(),
                'current_subscription' => $subscription,
                'invoices' => $invoices,
                'paid_invoices' => $paidInvoices,
                'broker_connections' => $brokerConnections,
                'broker_credentials' => $brokerCredentials,
                'licenses' => $licenses,
                'api_keys' => $apiKeys,
                'signals' => $signals,
                'bot_runs' => $botRuns,
                'activity_logs' => $activityLogs,
                'has_report_data' => $hasReportData,
                'active_subscriptions' => ($account?->subscriptions ?? collect())->filter(fn ($item) => ($item->status->value ?? $item->status) === SubscriptionStatus::Active->value),
            ]
        );

        return view('customer.reports.index', [
            'navItems' => CustomerNavigation::items(),
            'page' => $data['page'],
            'summary' => $data['summary'],
            'firestoreReadSummary' => $firestoreReadSummary,
            'metrics' => $data['metrics'],
            'trendColumns' => $data['trendColumns'],
            'trendRows' => $data['trendRows'],
            'relatedLinks' => $data['relatedLinks'],
            'hasReportData' => $data['hasReportData'],
        ]);
    }
}
