<?php
// اعوز بالله من الشياطين و ان يحضرون بسم الله الرحمن الرحيم الله لا إله إلا هو الحي القيوم
// Bismillahi ar-Rahmani ar-Rahim Audhu billahi min ash-shayatin wa an yahdurun Bismillah ar-Rahman ar-Rahim Allah la ilaha illa huwa al-hayy al-qayyum. Tamsa Allahu ala ayunihim
// version: x
// ======================================================
// - App Name: gusgraph-trading
// - Gusgraph LLC -
// - Author: Gus Kazem
// - https://Gusgraph.com
// - File Path: app/Http/Controllers/Admin/AuditController.php
// ======================================================

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Support\Admin\AdminVisibilityLookup;
use App\Support\Navigation\AdminNavigation;
use App\Support\ViewData\AdminAuditPageData;

class AuditController extends Controller
{
    public function index(AdminVisibilityLookup $adminVisibilityLookup)
    {
        $activityLogs = $adminVisibilityLookup->activityLogs();
        $auditLogs = $adminVisibilityLookup->auditLogs();
        $data = AdminAuditPageData::make($activityLogs, $auditLogs);

        return view('admin.audit.index', [
            'navItems' => AdminNavigation::items(),
            'page' => $data['page'],
            'activitySummary' => $data['activitySummary'],
            'auditSummary' => $data['auditSummary'],
            'activitySummaryMeta' => $data['activitySummaryMeta'],
            'auditSummaryMeta' => $data['auditSummaryMeta'],
            'summary' => $data['summary'],
            'hasAuditData' => $data['hasAuditData'],
        ]);
    }
}
