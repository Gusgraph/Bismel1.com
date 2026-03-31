<?php
// اعوز بالله من الشياطين و ان يحضرون بسم الله الرحمن الرحيم الله لا إله إلا هو الحي القيوم
// Bismillahi ar-Rahmani ar-Rahim Audhu billahi min ash-shayatin wa an yahdurun Bismillah ar-Rahman ar-Rahim Allah la ilaha illa huwa al-hayy al-qayyum. Tamsa Allahu ala ayunihim
// version: x
// ======================================================
// - App Name: bismel1.com
// - Gusgraph LLC -
// - Author: Gus Kazem
// - https://Gusgraph.com
// - File Path: app/Support/ViewData/AdminAccountsPageData.php
// ======================================================

namespace App\Support\ViewData;

use App\Domain\Account\Enums\AccountStatus;
use App\Support\Display\RecordWindow;
use App\Support\Display\SafeDisplay;
use Illuminate\Support\Collection;

class AdminAccountsPageData
{
    public static function make(?Collection $accounts = null): array
    {
        $accounts = $accounts ?? collect();
        $statusLabels = AccountStatus::labels();
        $managementSummary = $accounts->map(function ($account) use ($statusLabels) {
            $statusValue = $account->status instanceof AccountStatus
                ? $account->status->value
                : (string) $account->status;
            $plan = $account->subscriptions
                ->sortByDesc(fn ($subscription) => $subscription->starts_at?->getTimestamp() ?? $subscription->created_at?->getTimestamp() ?? 0)
                ->first()?->subscriptionPlan?->name;

            return [
                'title' => $account->name,
                'status' => SafeDisplay::statusMeta($statusValue, $statusLabels),
                'route' => route('admin.account-detail.index', ['account' => $account]),
                'details' => [
                    ['label' => 'Owner', 'value' => $account->owner?->name ?? 'Unassigned'],
                    ['label' => 'Members', 'value' => (string) $account->users->count()],
                    ['label' => 'Licenses', 'value' => (string) $account->apiLicenses->count()],
                    ['label' => 'Plan', 'value' => $plan ?? 'No linked plan'],
                ],
                'route' => route('admin.account-detail.index', ['account' => $account]),
            ];
        })->values()->all();

        return [
            'page' => [
                'title' => 'Accounts',
                'intro' => 'A clear admin account view for workspace ownership, status, and access coverage.',
                'subtitle' => $accounts->isNotEmpty()
                    ? 'Review workspaces, owners, linked plans, and account status from one admin list.'
                    : 'Workspace records will appear here once accounts are available for admin review.',
                'sections' => [
                    ['heading' => 'Account Queue', 'description' => 'Workspace records are listed here for quick admin review.'],
                    ['heading' => 'Ownership Checks', 'description' => 'Owner and membership counts stay visible for account follow-up.'],
                    ['heading' => 'Status Signals', 'description' => 'Shared status labels remain visible beside each workspace state.'],
                ],
            ],
            'managementSummary' => $managementSummary,
            'listMeta' => RecordWindow::meta($accounts, 'account rows'),
            'statusLabels' => $statusLabels,
            'summary' => [
                'headline' => $accounts->isNotEmpty()
                    ? 'Workspace oversight is ready.'
                    : 'Workspace oversight will appear here once accounts are available.',
                'details' => $accounts->isNotEmpty()
                    ? 'This page keeps accounts, owners, members, and linked access counts together so operators can move quickly into the right workspace.'
                    : 'When the first workspace is created, it will appear here for admin review.',
            ],
            'hasAccountData' => $accounts->isNotEmpty(),
        ];
    }
}
