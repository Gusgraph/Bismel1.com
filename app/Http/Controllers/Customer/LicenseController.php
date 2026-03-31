<?php
// اعوز بالله من الشياطين و ان يحضرون بسم الله الرحمن الرحيم الله لا إله إلا هو الحي القيوم
// Bismillahi ar-Rahmani ar-Rahim Audhu billahi min ash-shayatin wa an yahdurun Bismillah ar-Rahman ar-Rahim Allah la ilaha illa huwa al-hayy al-qayyum. Tamsa Allahu ala ayunihim
// version: x
// ======================================================
// - App Name: gusgraph-trading
// - Gusgraph LLC -
// - Author: Gus Kazem
// - https://Gusgraph.com
// - File Path: app/Http/Controllers/Customer/LicenseController.php
// ======================================================

namespace App\Http\Controllers\Customer;

use App\Domain\License\Enums\ApiKeyStatus;
use App\Domain\License\Enums\ApiLicenseStatus;
use App\Http\Controllers\Controller;
use App\Http\Requests\Customer\StoreApiLicenseKeyRequest;
use App\Models\ApiKey;
use App\Support\Customer\CurrentCustomerAccountResolver;
use App\Support\Navigation\CustomerNavigation;
use App\Support\ViewData\LicensePageData;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\DB;
use Throwable;

class LicenseController extends Controller
{
    public function index(CurrentCustomerAccountResolver $currentCustomerAccountResolver)
    {
        $account = $currentCustomerAccountResolver->resolveForPreset(request()->user(), 'license');

        $data = LicensePageData::make($account);

        return view('customer.license.index', [
            'navItems' => CustomerNavigation::items(),
            'page' => $data['page'],
            'sectionLabel' => $data['sectionLabel'],
            'keyStatuses' => $data['keyStatuses'],
            'licenseDetails' => $data['licenseDetails'],
            'licenseInventory' => $data['licenseInventory'],
            'keyInventory' => $data['keyInventory'],
            'summary' => $data['summary'],
            'hasLicenseData' => $data['hasLicenseData'],
        ]);
    }

    public function create()
    {
        return view('customer.license.create', [
            'navItems' => CustomerNavigation::items(),
            'page' => [
                'title' => 'Add License Access',
                'subtitle' => 'Save one local API license and key pair for the current workspace with encrypted-at-rest handling and masked display.',
            ],
        ]);
    }

    public function store(StoreApiLicenseKeyRequest $request, CurrentCustomerAccountResolver $currentCustomerAccountResolver): RedirectResponse
    {
        $validated = $request->validated();
        $account = $currentCustomerAccountResolver->resolveCurrent($request->user());

        if (! $account) {
            return redirect()
                ->route('customer.license.create')
                ->with('status', 'License access was not saved because no current customer workspace record is available.');
        }

        try {
            DB::transaction(function () use ($account, $validated): void {
                $license = $account->apiLicenses()->firstOrCreate(
                    ['name' => $validated['license_name']],
                    [
                        'status' => ApiLicenseStatus::Active->value,
                        'starts_at' => now(),
                        'expires_at' => $validated['expires_at'] ?? null,
                    ]
                );

                if (! $license->starts_at) {
                    $license->forceFill(['starts_at' => now()])->save();
                }

                if (! $license->expires_at && ! empty($validated['expires_at'])) {
                    $license->forceFill(['expires_at' => $validated['expires_at']])->save();
                }

                $tokenValue = $validated['token_value'];

                $apiKey = new ApiKey([
                    'name' => $validated['key_name'],
                    'key_hash' => hash('sha256', $license->id.'|'.$tokenValue),
                    'secret_hint' => $tokenValue,
                    'status' => ApiKeyStatus::Ready->value,
                    'expires_at' => $validated['expires_at'] ?? null,
                    'last_used_at' => null,
                ]);

                $license->apiKeys()->save($apiKey);
            });
        } catch (Throwable) {
            return redirect()
                ->route('customer.license.create')
                ->with('status', 'License access could not be saved locally. Sensitive values were not displayed.');
        }

        return redirect()
            ->route('customer.license.index')
            ->with('status', 'License access was saved locally. Token values remain encrypted at rest and only masked license metadata is shown.');
    }
}
