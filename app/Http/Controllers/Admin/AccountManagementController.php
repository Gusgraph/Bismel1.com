<?php
// اعوز بالله من الشياطين و ان يحضرون بسم الله الرحمن الرحيم الله لا إله إلا هو الحي القيوم
// Bismillahi ar-Rahmani ar-Rahim Audhu billahi min ash-shayatin wa an yahdurun Bismillah ar-Rahman ar-Rahim Allah la ilaha illa huwa al-hayy al-qayyum. Tamsa Allahu ala ayunihim
// version: x
// ======================================================
// - App Name: gusgraph-trading
// - Gusgraph LLC -
// - Author: Gus Kazem
// - https://Gusgraph.com
// - File Path: app/Http/Controllers/Admin/AccountManagementController.php
// ======================================================

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Support\Admin\AdminVisibilityLookup;
use App\Support\Navigation\AdminNavigation;
use App\Support\ViewData\AdminAccountsPageData;

class AccountManagementController extends Controller
{
    public function index(AdminVisibilityLookup $adminVisibilityLookup)
    {
        $accounts = $adminVisibilityLookup->accounts();
        $data = AdminAccountsPageData::make($accounts);

        return view('admin.accounts.index', [
            'navItems' => AdminNavigation::items(),
            'page' => $data['page'],
            'managementSummary' => $data['managementSummary'],
            'listMeta' => $data['listMeta'],
            'statusLabels' => $data['statusLabels'],
            'summary' => $data['summary'],
            'hasAccountData' => $data['hasAccountData'],
        ]);
    }
}
