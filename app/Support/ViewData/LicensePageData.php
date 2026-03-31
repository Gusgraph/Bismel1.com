<?php
// اعوز بالله من الشياطين و ان يحضرون بسم الله الرحمن الرحيم الله لا إله إلا هو الحي القيوم
// Bismillahi ar-Rahmani ar-Rahim Audhu billahi min ash-shayatin wa an yahdurun Bismillah ar-Rahman ar-Rahim Allah la ilaha illa huwa al-hayy al-qayyum. Tamsa Allahu ala ayunihim
// version: x
// ======================================================
// - App Name: bismel1.com
// - Gusgraph LLC -
// - Author: Gus Kazem
// - https://Gusgraph.com
// - File Path: app/Support/ViewData/LicensePageData.php
// ======================================================

namespace App\Support\ViewData;

use App\Domain\License\Enums\ApiKeyStatus;
use App\Domain\License\Enums\ApiLicenseStatus;
use App\Models\Account;
use App\Support\Display\SafeDisplay;
use App\Support\Settings\AppSections;

class LicensePageData
{
    public static function make(?Account $account = null): array
    {
        $licenses = $account?->apiLicenses
            ?->sortByDesc(fn ($license) => $license->starts_at?->getTimestamp() ?? $license->created_at?->getTimestamp() ?? 0)
            ->values() ?? collect();
        $license = $licenses->first();
        $keys = $licenses->flatMap->apiKeys->sortByDesc(fn ($key) => $key->created_at?->getTimestamp() ?? 0)->values();
        $licenseInventory = $licenses->map(function ($license) use ($account) {
            $statusValue = $license->status instanceof ApiLicenseStatus
                ? $license->status->value
                : (string) $license->status;

            return [
                'label' => $license->name,
                'value' => implode(' / ', array_filter([
                    SafeDisplay::status($statusValue, ApiLicenseStatus::labels()),
                    $account?->name ? 'Account '.$account->name : null,
                    $account?->slug ? 'Slug '.$account->slug : null,
                    'Keys '.$license->apiKeys->count(),
                    SafeDisplay::prefixedDate($license->starts_at, 'Starts ', 'No start date'),
                    SafeDisplay::prefixedDate($license->expires_at, 'Expires ', 'No expiry recorded'),
                ])),
            ];
        })->all();
        $keyInventory = $keys->map(function ($key) use ($account) {
            $statusValue = $key->status instanceof ApiKeyStatus ? $key->status->value : (string) $key->status;

            return [
                'label' => $key->name,
                'value' => implode(' / ', array_filter([
                    SafeDisplay::status($statusValue, ApiKeyStatus::labels()),
                    $key->maskedTokenSummary(),
                    $account?->name ? 'Account '.$account->name : null,
                    $key->apiLicense?->name ? 'License '.$key->apiLicense->name : null,
                    SafeDisplay::prefixedDateTime($key->last_used_at, 'Last used ', 'Never used'),
                    SafeDisplay::prefixedDate($key->expires_at, 'Expires ', 'No expiry recorded'),
                    SafeDisplay::prefixedDateTime($key->created_at, 'Created ', ''),
                ])),
            ];
        })->all();

        return [
            'page' => [
                'title' => 'License',
                'intro' => 'Review license access and masked API key status for this workspace.',
                'subtitle' => $account
                    ? 'License access, masked key status, and renewal dates stay visible here for the current workspace.'
                    : 'No license access has been added yet, so this page stays focused on the next setup step.',
                'sections' => [
                    ['heading' => 'License Visibility', 'description' => 'See current license records with status, related access, and renewal timing.'],
                    ['heading' => 'Key Handling', 'description' => 'API keys stay masked while still showing status and recent activity.'],
                    ['heading' => 'Renewal Notes', 'description' => 'Review renewal timing and access posture from one place.'],
                ],
            ],
            'sectionLabel' => AppSections::labels()[AppSections::CUSTOMER_LICENSE],
            'keyStatuses' => $keys->isNotEmpty()
                ? $keys->map(function ($key) {
                    $statusValue = $key->status instanceof ApiKeyStatus ? $key->status->value : (string) $key->status;

                    return [
                        'value' => $statusValue,
                        'label' => SafeDisplay::status($statusValue, ApiKeyStatus::labels()),
                        'note' => $key->maskedTokenSummary().' | '.SafeDisplay::prefixedDate($key->expires_at, 'Expires ', 'No expiry recorded'),
                    ];
                })->values()->all()
                : [
                    ['value' => ApiKeyStatus::Ready->value, 'label' => ApiKeyStatus::labels()[ApiKeyStatus::Ready->value], 'note' => 'Use this status when a saved key is ready to use.'],
                    ['value' => ApiKeyStatus::Placeholder->value, 'label' => ApiKeyStatus::labels()[ApiKeyStatus::Placeholder->value], 'note' => 'Use this status for access that has not been fully set up yet.'],
                    ['value' => ApiKeyStatus::Attention->value, 'label' => ApiKeyStatus::labels()[ApiKeyStatus::Attention->value], 'note' => 'Use this status when the key needs review.'],
                ],
            'licenseDetails' => $license ? [
                ['label' => 'Workspace Name', 'value' => $account->name],
                ['label' => 'Workspace Slug', 'value' => $account->slug],
                ['label' => 'Workspace Owner', 'value' => $account->owner?->name ?? 'No owner assigned'],
                ['label' => 'License Name', 'value' => $license->name],
                ['label' => 'License Status', 'value' => SafeDisplay::status($license->status instanceof ApiLicenseStatus ? $license->status->value : $license->status)],
                ['label' => 'Starts At', 'value' => SafeDisplay::prefixedDateTime($license->starts_at, '', 'Not scheduled')],
                ['label' => 'Expires At', 'value' => SafeDisplay::prefixedDateTime($license->expires_at, '', 'No expiry recorded')],
                ['label' => 'API Key Count', 'value' => (string) $keys->count()],
            ] : [],
            'licenseInventory' => $licenseInventory,
            'keyInventory' => $keyInventory,
            'summary' => [
                'headline' => $license
                    ? 'Your license access and key status are visible in one place.'
                    : 'No license access has been added yet.',
                'details' => $license
                    ? 'Review current license records, masked API key details, and renewal timing without exposing any raw token values.'
                    : 'Add a license and API key to begin tracking access for this workspace.',
            ],
            'hasLicenseData' => (bool) $license,
        ];
    }
}
