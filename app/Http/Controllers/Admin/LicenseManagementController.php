<?php
// اعوز بالله من الشياطين و ان يحضرون بسم الله الرحمن الرحيم الله لا إله إلا هو الحي القيوم
// Bismillahi ar-Rahmani ar-Rahim Audhu billahi min ash-shayatin wa an yahdurun Bismillah ar-Rahman ar-Rahim Allah la ilaha illa huwa al-hayy al-qayyum. Tamsa Allahu ala ayunihim
// version: x
// ======================================================
// - App Name: gusgraph-trading
// - Gusgraph LLC -
// - Author: Gus Kazem
// - https://Gusgraph.com
// - File Path: app/Http/Controllers/Admin/LicenseManagementController.php
// ======================================================

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Support\Admin\AdminVisibilityLookup;
use App\Support\Navigation\AdminNavigation;
use App\Support\ViewData\AdminLicensePageData;

class LicenseManagementController extends Controller
{
    public function index(AdminVisibilityLookup $adminVisibilityLookup)
    {
        $licenses = $adminVisibilityLookup->licenses();
        $data = AdminLicensePageData::make($licenses);

        return view('admin.licenses.index', [
            'navItems' => AdminNavigation::items(),
            'page' => $data['page'],
            'licenseInventory' => $data['licenseInventory'],
            'keyInventory' => $data['keyInventory'],
            'licenseInventoryMeta' => $data['licenseInventoryMeta'],
            'keyInventoryMeta' => $data['keyInventoryMeta'],
            'summary' => $data['summary'],
            'hasLicenseData' => $data['hasLicenseData'],
        ]);
    }
}
