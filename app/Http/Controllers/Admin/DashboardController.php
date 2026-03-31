<?php
// اعوز بالله من الشياطين و ان يحضرون بسم الله الرحمن الرحيم الله لا إله إلا هو الحي القيوم
// Bismillahi ar-Rahmani ar-Rahim Audhu billahi min ash-shayatin wa an yahdurun Bismillah ar-Rahman ar-Rahim Allah la ilaha illa huwa al-hayy al-qayyum. Tamsa Allahu ala ayunihim
// version: x
// ======================================================
// - App Name: gusgraph-trading
// - Gusgraph LLC -
// - Author: Gus Kazem
// - https://Gusgraph.com
// - File Path: app/Http/Controllers/Admin/DashboardController.php
// ======================================================

namespace App\Http\Controllers\Admin;

use App\Domain\Account\Enums\AccountStatus;
use App\Domain\Audit\Enums\AuditEventType;
use App\Domain\Dashboard\Services\DashboardService;
use App\Http\Controllers\Controller;
use App\Support\Admin\Bismel1AdminOperationsService;
use App\Support\Labels\AdminSectionLabels;
use App\Support\Navigation\AdminNavigation;
use App\Support\ViewData\AdminAlertData;
use App\Support\ViewData\AdminPageData;

class DashboardController extends Controller
{
    public function index(
        DashboardService $dashboardService,
        Bismel1AdminOperationsService $bismel1AdminOperationsService
    )
    {
        $labels = AdminSectionLabels::make();
        $dashboard = $dashboardService->getAdminDashboardData();
        $operationsOverview = $bismel1AdminOperationsService->platformOverview();
        $alertData = AdminAlertData::make();

        return view('admin.dashboard', [
            'dashboard' => $dashboard,
            'operationsOverview' => $operationsOverview,
            'navItems' => AdminNavigation::items(),
            'page' => AdminPageData::make(
                'Admin Dashboard',
                'The admin control surface for workspace coverage, billing posture, broker readiness, license oversight, audits, and platform configuration.',
                $dashboard['sections'] ?: $labels['dashboard'],
                $dashboard['stats']
            ),
            'managementSummary' => $dashboard['managementSummary'] ?? [],
            'auditOverview' => $dashboard['auditOverview'] ?? [],
            'healthSummary' => $dashboard['healthSummary'] ?? [],
            'statusLabels' => AccountStatus::labels(),
            'auditLabels' => AuditEventType::labels(),
            'alerts' => $alertData['alerts'],
            'notices' => $alertData['notices'],
            'hasDashboardData' => $dashboard['hasDashboardData'] ?? false,
            'hasOperationsData' => $operationsOverview['has_operations_data'] ?? false,
        ]);
    }
}
