<?php
// اعوز بالله من الشياطين و ان يحضرون بسم الله الرحمن الرحيم الله لا إله إلا هو الحي القيوم
// Bismillahi ar-Rahmani ar-Rahim Audhu billahi min ash-shayatin wa an yahdurun Bismillah ar-Rahman ar-Rahim Allah la ilaha illa huwa al-hayy al-qayyum. Tamsa Allahu ala ayunihim
// version: x
// ======================================================
// - App Name: gusgraph-trading
// - Gusgraph LLC -
// - Author: Gus Kazem
// - https://Gusgraph.com
// - File Path: app/Http/Controllers/Customer/AccountController.php
// ======================================================

namespace App\Http\Controllers\Customer;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Support\Customer\CurrentCustomerAccountResolver;
use App\Support\Navigation\CustomerNavigation;
use App\Support\ViewData\AccountPageData;

class AccountController extends Controller
{
    public function index(CurrentCustomerAccountResolver $currentCustomerAccountResolver)
    {
        $user = request()->user();
        $account = $currentCustomerAccountResolver->resolveForPreset($user, 'account');

        $data = AccountPageData::make($account, $user instanceof User ? $user : null);

        return view('customer.account.index', [
            'navItems' => CustomerNavigation::items(),
            'page' => $data['page'],
            'membership' => $data['membership'],
            'accountContext' => $data['accountContext'],
            'teamMembers' => $data['teamMembers'],
            'statusLabels' => $data['statusLabels'],
            'summary' => $data['summary'],
            'hasAccountData' => $data['hasAccountData'],
        ]);
    }
}
