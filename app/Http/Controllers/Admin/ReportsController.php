<?php
// اعوز بالله من الشياطين و ان يحضرون بسم الله الرحمن الرحيم الله لا إله إلا هو الحي القيوم
// Bismillahi ar-Rahmani ar-Rahim Audhu billahi min ash-shayatin wa an yahdurun Bismillah ar-Rahman ar-Rahim Allah la ilaha illa huwa al-hayy al-qayyum. Tamsa Allahu ala ayunihim
// version: x
// ======================================================
// - App Name: gusgraph-trading
// - Gusgraph LLC -
// - Author: Gus Kazem
// - https://Gusgraph.com
// - File Path: app/Http/Controllers/Admin/ReportsController.php
// ======================================================

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Support\Admin\Bismel1AdminOperationsService;
use App\Support\Admin\PlatformSummaryService;
use App\Support\Customer\CurrentCustomerAccountResolver;
use App\Support\Firestore\FirestoreBridge;
use App\Support\Navigation\AdminNavigation;
use App\Support\ViewData\AdminReportsPageData;

class ReportsController extends Controller
{
    public function index(
        PlatformSummaryService $platformSummaryService,
        Bismel1AdminOperationsService $bismel1AdminOperationsService,
        CurrentCustomerAccountResolver $currentCustomerAccountResolver,
        FirestoreBridge $firestoreBridge
    )
    {
        $snapshot = $platformSummaryService->detailedSnapshot();
        $operationsOverview = $bismel1AdminOperationsService->platformOverview();
        $user = request()->user();
        $account = $currentCustomerAccountResolver->resolveForPreset($user, 'summary');
        $firestoreAdminSummary = $firestoreBridge->readMappedUsersSummary($user, $account);
        $data = AdminReportsPageData::make([
            'accounts' => $snapshot['accounts'],
            'users' => $snapshot['users'],
            'subscriptions' => $snapshot['subscriptions'],
            'active_subscriptions' => $snapshot['active_subscriptions'],
            'invoices' => $snapshot['invoices'],
            'paid_invoices' => $snapshot['paid_invoices'],
            'broker_connections' => $snapshot['broker_connections'],
            'broker_credentials' => $snapshot['broker_credentials'],
            'licenses' => $snapshot['licenses'],
            'api_keys' => $snapshot['api_keys'],
            'signals' => $snapshot['signals'],
            'bot_runs' => $snapshot['bot_runs'],
            'execution_attempts' => $snapshot['execution_attempts'],
            'activity_logs' => $snapshot['activity_logs'],
            'audit_logs' => $snapshot['audit_logs'],
            'has_report_data' => $snapshot['has_report_data'],
            'recent_signal' => $snapshot['recent_signal'],
            'recent_bot_run' => $snapshot['recent_bot_run'],
        ], $snapshot['system_setting']);

        return view('admin.reports.index', [
            'navItems' => AdminNavigation::items(),
            'page' => $data['page'],
            'summary' => $data['summary'],
            'operationsOverview' => $operationsOverview,
            'firestoreAdminSummary' => $firestoreAdminSummary,
            'metrics' => $data['metrics'],
            'trendColumns' => $data['trendColumns'],
            'trendRows' => $data['trendRows'],
            'relatedLinks' => $data['relatedLinks'],
            'hasReportData' => $data['hasReportData'],
            'hasOperationsData' => $operationsOverview['has_operations_data'] ?? false,
        ]);
    }
}
