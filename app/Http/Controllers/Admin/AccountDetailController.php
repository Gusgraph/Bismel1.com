<?php
// اعوز بالله من الشياطين و ان يحضرون بسم الله الرحمن الرحيم الله لا إله إلا هو الحي القيوم
// Bismillahi ar-Rahmani ar-Rahim Audhu billahi min ash-shayatin wa an yahdurun Bismillah ar-Rahman ar-Rahim Allah la ilaha illa huwa al-hayy al-qayyum. Tamsa Allahu ala ayunihim
// version: x
// ======================================================
// - App Name: bismel1.com
// - Gusgraph LLC -
// - Author: Gus Kazem
// - https://Gusgraph.com
// - File Path: app/Http/Controllers/Admin/AccountDetailController.php
// ======================================================

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\RunBismel1OperatorActionRequest;
use App\Models\Account;
use App\Support\Admin\Bismel1AdminOperationsService;
use App\Support\Admin\AdminVisibilityLookup;
use App\Support\Admin\Bismel1OperatorToolsService;
use App\Support\Navigation\AdminNavigation;
use App\Support\ViewData\AdminAccountDetailPageData;

class AccountDetailController extends Controller
{
    public function index(
        Account $account,
        AdminVisibilityLookup $adminVisibilityLookup,
        Bismel1AdminOperationsService $bismel1AdminOperationsService
    )
    {
        $account = $adminVisibilityLookup->accountDetail($account);
        $accountOperations = $bismel1AdminOperationsService->accountOverview($account);

        $data = AdminAccountDetailPageData::make($account);

        return view('admin.account-detail.index', [
            'navItems' => AdminNavigation::items(),
            'page' => $data['page'],
            'accountDetails' => $data['accountDetails'],
            'tenantOverview' => $data['tenantOverview'],
            'brokerConnections' => $data['brokerConnections'],
            'brokerCredentials' => $data['brokerCredentials'],
            'accountOperations' => $accountOperations,
            'brokerConnectionsMeta' => $data['brokerConnectionsMeta'],
            'brokerCredentialsMeta' => $data['brokerCredentialsMeta'],
            'summary' => $data['summary'],
            'hasAccountData' => $data['hasAccountData'],
            'hasOperationsData' => $accountOperations['has_operations_data'] ?? false,
        ]);
    }

    public function operatorAction(
        Account $account,
        RunBismel1OperatorActionRequest $request,
        AdminVisibilityLookup $adminVisibilityLookup,
        Bismel1OperatorToolsService $bismel1OperatorToolsService,
    )
    {
        $account = $adminVisibilityLookup->accountDetail($account);
        $result = $bismel1OperatorToolsService->run(
            $account,
            $request->user(),
            (string) $request->validated('action')
        );

        return redirect()
            ->route('admin.account-detail.index', ['account' => $account])
            ->with('status', (string) ($result['summary'] ?? 'Operator action failed'))
            ->with('status_meta', [
                'heading' => (string) ($result['heading'] ?? 'Action failed'),
                'tone' => (string) ($result['tone'] ?? 'rose'),
            ]);
    }
}
