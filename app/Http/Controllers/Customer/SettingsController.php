<?php
// اعوز بالله من الشياطين و ان يحضرون بسم الله الرحمن الرحيم الله لا إله إلا هو الحي القيوم
// Bismillahi ar-Rahmani ar-Rahim Audhu billahi min ash-shayatin wa an yahdurun Bismillah ar-Rahman ar-Rahim Allah la ilaha illa huwa al-hayy al-qayyum. Tamsa Allahu ala ayunihim
// version: x
// ======================================================
// - App Name: gusgraph-trading
// - Gusgraph LLC -
// - Author: Gus Kazem
// - https://Gusgraph.com
// - File Path: app/Http/Controllers/Customer/SettingsController.php
// ======================================================

namespace App\Http\Controllers\Customer;

use App\Http\Controllers\Controller;
use App\Http\Requests\Customer\UpdateSettingsRequest;
use App\Models\User;
use App\Support\Customer\CurrentCustomerAccountResolver;
use App\Support\Navigation\CustomerNavigation;
use App\Support\ViewData\CustomerSettingsPageData;

class SettingsController extends Controller
{
    public function index(CurrentCustomerAccountResolver $currentCustomerAccountResolver)
    {
        $user = request()->user();
        $account = $currentCustomerAccountResolver->resolveForPreset($user, 'account');
        $data = CustomerSettingsPageData::make($account, $user instanceof User ? $user : null);

        return view('customer.settings.index', [
            'navItems' => CustomerNavigation::items(),
            'page' => $data['page'],
            'membership' => $data['membership'],
            'teamMembers' => $data['teamMembers'],
            'preferences' => $data['preferences'],
            'summary' => $data['summary'],
            'hasSettingsData' => $data['hasSettingsData'],
        ]);
    }

    public function edit()
    {
        $user = request()->user();

        return view('customer.settings.edit', [
            'navItems' => CustomerNavigation::items(),
            'page' => [
                'title' => 'Edit Profile Settings',
                'subtitle' => 'Update the current local user profile with a narrow DB-backed settings flow.',
            ],
            'form' => [
                'name' => $user?->name ?? '',
                'email' => $user?->email ?? '',
            ],
        ]);
    }

    public function update(UpdateSettingsRequest $request)
    {
        $user = $request->user();
        $validated = $request->validated();

        if (! $user) {
            return redirect()
                ->route('customer.settings.edit')
                ->with('status', 'Profile settings were not saved because no authenticated user record was available.');
        }

        $user->forceFill([
            'name' => $validated['name'],
            'email' => $validated['email'],
        ])->save();

        return redirect()
            ->route('customer.settings.index')
            ->with('status', 'Profile settings were saved to the current local user profile.');
    }
}
