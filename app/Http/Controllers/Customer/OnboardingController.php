<?php
// اعوز بالله من الشياطين و ان يحضرون بسم الله الرحمن الرحيم الله لا إله إلا هو الحي القيوم
// Bismillahi ar-Rahmani ar-Rahim Audhu billahi min ash-shayatin wa an yahdurun Bismillah ar-Rahman ar-Rahim Allah la ilaha illa huwa al-hayy al-qayyum. Tamsa Allahu ala ayunihim
// version: x
// ======================================================
// - App Name: gusgraph-trading
// - Gusgraph LLC -
// - Author: Gus Kazem
// - https://Gusgraph.com
// - File Path: app/Http/Controllers/Customer/OnboardingController.php
// ======================================================

namespace App\Http\Controllers\Customer;

use App\Http\Controllers\Controller;
use App\Support\Customer\CurrentCustomerAccountResolver;
use App\Support\Firestore\FirestoreBridge;
use App\Support\Navigation\CustomerNavigation;
use App\Support\ViewData\CustomerAlertData;
use App\Support\ViewData\OnboardingPageData;

class OnboardingController extends Controller
{
    public function index(CurrentCustomerAccountResolver $currentCustomerAccountResolver, FirestoreBridge $firestoreBridge)
    {
        $user = request()->user();
        $account = $currentCustomerAccountResolver->resolveForPreset($user, 'summary');
        $subscription = $account?->subscriptions
            ->sortByDesc(fn ($item) => $item->starts_at?->getTimestamp() ?? $item->created_at?->getTimestamp() ?? 0)
            ->first();
        $brokerConnection = $account?->brokerConnections->first();
        $brokerCredential = $brokerConnection?->brokerCredentials?->sortByDesc('id')->first();
        $license = $account?->apiLicenses->first();
        $apiKey = $license?->apiKeys?->sortByDesc('id')->first();
        $alertData = CustomerAlertData::make();
        $firestoreReadSummary = $user
            ? $firestoreBridge->readUserIntegrationSummary($user, $account)
            : ['status' => 'not_mapped', 'headline' => 'This user is not mapped to Firestore yet.', 'details' => 'No signed-in user is available for Firestore summary.', 'items' => []];
        $data = OnboardingPageData::make($account, $user, [
            'current_subscription' => $subscription,
            'current_broker_connection' => $brokerConnection,
            'current_broker_credential' => $brokerCredential,
            'current_license' => $license,
            'current_api_key' => $apiKey,
            'recent_invoice' => $account?->invoices?->sortByDesc(fn ($invoice) => $invoice->issued_at?->getTimestamp() ?? $invoice->created_at?->getTimestamp() ?? 0)->first(),
            'recent_activity' => $account?->activityLogs?->sortByDesc('created_at')->first(),
        ]);

        return view('customer.onboarding.index', [
            'navItems' => CustomerNavigation::items(),
            'page' => $data['page'],
            'onboardingChecklist' => $data['onboardingChecklist'],
            'brokerCredentialChecklist' => $data['brokerCredentialChecklist'],
            'summary' => $data['summary'],
            'firestoreReadSummary' => $firestoreReadSummary,
            'alerts' => $alertData['alerts'],
            'notices' => $alertData['notices'],
            'hasOnboardingData' => $data['hasOnboardingData'],
        ]);
    }
}
