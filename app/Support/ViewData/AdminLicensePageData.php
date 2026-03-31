<?php
// اعوز بالله من الشياطين و ان يحضرون بسم الله الرحمن الرحيم الله لا إله إلا هو الحي القيوم
// Bismillahi ar-Rahmani ar-Rahim Audhu billahi min ash-shayatin wa an yahdurun Bismillah ar-Rahman ar-Rahim Allah la ilaha illa huwa al-hayy al-qayyum. Tamsa Allahu ala ayunihim
// version: x
// ======================================================
// - App Name: bismel1.com
// - Gusgraph LLC -
// - Author: Gus Kazem
// - https://Gusgraph.com
// - File Path: app/Support/ViewData/AdminLicensePageData.php
// ======================================================

namespace App\Support\ViewData;

use App\Domain\License\Enums\ApiLicenseStatus;
use App\Support\Display\RecordWindow;
use App\Support\Display\SafeDisplay;
use Illuminate\Support\Collection;

class AdminLicensePageData
{
    public static function make(?Collection $licenses = null): array
    {
        $licenses = $licenses ?? collect();
        $licenseInventory = $licenses->map(function ($license) {
            $statusValue = $license->status instanceof ApiLicenseStatus
                ? $license->status->value
                : (string) $license->status;
            $statusLabel = SafeDisplay::status($statusValue, ApiLicenseStatus::labels());

            return [
                'title' => $license->name,
                'status' => SafeDisplay::statusMeta($statusValue, ApiLicenseStatus::labels()),
                'details' => [
                    ['label' => 'Account', 'value' => $license->account?->name ?? 'Unassigned'],
                    ['label' => 'Keys', 'value' => (string) $license->apiKeys->count()],
                    ['label' => 'Starts', 'value' => SafeDisplay::prefixedDate($license->starts_at, '', 'Not scheduled')],
                    ['label' => 'Expires', 'value' => SafeDisplay::prefixedDate($license->expires_at, '', 'No expiry recorded')],
                    ['label' => 'Created', 'value' => SafeDisplay::prefixedDateTime($license->created_at, '', 'No timestamp')],
                ],
            ];
        })->values()->all();
        $keyInventory = $licenses
            ->flatMap(function ($license) {
                return $license->apiKeys->map(function ($key) use ($license) {
                    return [
                        'title' => $key->name,
                        'status' => SafeDisplay::statusMeta((string) $key->status),
                        'details' => [
                            ['label' => 'License', 'value' => $license->name],
                            ['label' => 'Account', 'value' => $license->account?->name ?? 'Unassigned'],
                            ['label' => 'Token', 'value' => $key->maskedTokenSummary()],
                            ['label' => 'Expires', 'value' => SafeDisplay::prefixedDate($key->expires_at, '', 'No expiry recorded')],
                            ['label' => 'Last Used', 'value' => SafeDisplay::prefixedDateTime($key->last_used_at, '', 'Never')],
                            ['label' => 'Created', 'value' => SafeDisplay::prefixedDateTime($key->created_at, '', 'No timestamp')],
                        ],
                    ];
                });
            })
            ->values()
            ->all();

        return [
            'page' => [
                'title' => 'Licenses',
                'intro' => 'A focused admin license view for API access, masked key status, and workspace linkage.',
                'subtitle' => $licenses->isNotEmpty()
                    ? 'Review current API licenses, masked keys, and workspace linkage in one place.'
                    : 'License and key visibility will appear here once API access records are available.',
                'sections' => [
                    ['heading' => 'License Detail', 'description' => 'License rows stay visible with account linkage, status, dates, and key counts.'],
                    ['heading' => 'Key Detail', 'description' => 'Linked API keys stay visible with masked token indicators, timestamps, and related license context only.'],
                    ['heading' => 'Admin Notes', 'description' => 'Use this page to review API access posture without exposing sensitive values.'],
                ],
            ],
            'licenseInventory' => $licenseInventory,
            'keyInventory' => $keyInventory,
            'licenseInventoryMeta' => RecordWindow::meta($licenses, 'license records'),
            'keyInventoryMeta' => RecordWindow::meta($licenses->flatMap->apiKeys->values(), 'API key records'),
            'summary' => [
                'headline' => $licenses->isNotEmpty()
                    ? 'License oversight is ready.'
                    : 'License oversight will appear here once API access is configured.',
                'details' => $licenses->isNotEmpty()
                    ? 'This page keeps account-linked licenses and masked key metadata readable for operational review.'
                    : 'Once API licenses and keys are created, they will appear here for admin review.',
            ],
            'hasLicenseData' => $licenses->isNotEmpty(),
        ];
    }
}
