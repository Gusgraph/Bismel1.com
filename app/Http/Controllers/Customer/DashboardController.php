<?php
// اعوز بالله من الشياطين و ان يحضرون بسم الله الرحمن الرحيم الله لا إله إلا هو الحي القيوم
// Bismillahi ar-Rahmani ar-Rahim Audhu billahi min ash-shayatin wa an yahdurun Bismillah ar-Rahman ar-Rahim Allah la ilaha illa huwa al-hayy al-qayyum. Tamsa Allahu ala ayunihim
// version: x
// ======================================================
// - App Name: gusgraph-trading
// - Gusgraph LLC -
// - Author: Gus Kazem
// - https://Gusgraph.com
// - File Path: app/Http/Controllers/Customer/DashboardController.php
// =====================================================

namespace App\Http\Controllers\Customer;

use App\Domain\Account\Enums\AccountStatus;
use App\Domain\Billing\Enums\SubscriptionStatus;
use App\Domain\Broker\Enums\BrokerConnectionStatus;
use App\Domain\Dashboard\Services\DashboardService;
use App\Domain\License\Enums\ApiLicenseStatus;
use App\Http\Controllers\Controller;
use App\Support\Customer\CurrentCustomerAccountResolver;
use App\Support\Firestore\FirestoreBridge;
use App\Support\Labels\CustomerSectionLabels;
use App\Support\Navigation\CustomerNavigation;
use App\Support\ViewData\CustomerAlertData;
use App\Support\ViewData\CustomerPageData;

class DashboardController extends Controller
{
    public function index(
        DashboardService $dashboardService,
        CurrentCustomerAccountResolver $currentCustomerAccountResolver,
        FirestoreBridge $firestoreBridge
    )
    {
        $labels = CustomerSectionLabels::make();
        $account = $currentCustomerAccountResolver->resolveForPreset(request()->user(), 'dashboard');
        $dashboard = $dashboardService->getCustomerDashboardData($account);
        $alertData = CustomerAlertData::make();
        $firestoreReadSummary = request()->user()
            ? $firestoreBridge->readUserIntegrationSummary(request()->user(), $account)
            : ['status' => 'not_mapped', 'headline' => 'This user is not mapped to Firestore yet.', 'details' => 'No signed-in user is available for Firestore summary.', 'items' => []];

        return view('customer.dashboard', [
            'dashboard' => $dashboard,
            'navItems' => CustomerNavigation::items(),
            'page' => CustomerPageData::make(
                'Customer Dashboard',
                'The customer home for current workspace readiness, billing posture, broker setup, license coverage, and recent local activity.',
                $dashboard['sections'] ?: $labels['dashboard'],
                $dashboard['stats']
            ),
            'statusGroups' => [
                'Account' => AccountStatus::labels(),
                'Billing' => SubscriptionStatus::labels(),
                'Broker' => BrokerConnectionStatus::labels(),
                'License' => ApiLicenseStatus::labels(),
            ],
            'alerts' => $alertData['alerts'],
            'notices' => $alertData['notices'],
            'hasDashboardData' => $dashboard['hasDashboardData'] ?? false,
            'firestoreReadSummary' => $firestoreReadSummary,
        ]);
    }
}
