<?php
// اعوز بالله من الشياطين و ان يحضرون بسم الله الرحمن الرحيم الله لا إله إلا هو الحي القيوم
// Bismillahi ar-Rahmani ar-Rahim Audhu billahi min ash-shayatin wa an yahdurun Bismillah ar-Rahman ar-Rahim Allah la ilaha illa huwa al-hayy al-qayyum. Tamsa Allahu ala ayunihim
// version: x
// ======================================================
// - App Name: bismel1.com
// - Gusgraph LLC -
// - Author: Gus Kazem
// - https://Gusgraph.com
// - File Path: app/Support/ViewData/AccountPageData.php
// ======================================================

namespace App\Support\ViewData;

use App\Domain\Account\Enums\AccountStatus;
use App\Models\Account;
use App\Models\User;

class AccountPageData
{
    public static function make(?Account $account = null, ?User $user = null): array
    {
        $statusLabels = AccountStatus::labels();
        $statusValue = $account?->status instanceof AccountStatus
            ? $account->status->value
            : ($account?->status ?? 'inactive');
        $statusLabel = $statusLabels[$statusValue] ?? ucfirst(str_replace('_', ' ', (string) $statusValue));
        $currentMember = $account && $user
            ? $account->users->firstWhere('id', $user->id)
            : null;
        $teamMembers = $account
            ? $account->users->map(function ($user) {
                return [
                    'name' => $user->name,
                    'role' => ucfirst(str_replace('_', ' ', $user->pivot->role ?? 'member')),
                    'status' => ucfirst(str_replace('_', ' ', $user->pivot->status ?? 'active')),
                ];
            })->values()->all()
            : [];

        if ($account && $account->owner && empty($teamMembers)) {
            $teamMembers[] = [
                'name' => $account->owner->name,
                'role' => 'Owner',
                'status' => 'Active',
            ];
        }

        return [
            'page' => [
                'title' => 'Account',
                'intro' => 'Review workspace identity, membership, and account access from one place.',
                'subtitle' => $account
                    ? 'Workspace identity, membership, and team visibility stay available here for the current account.'
                    : 'No workspace is available yet, so account details will appear here after setup is complete.',
                'sections' => [
                    ['heading' => 'Profile Snapshot', 'description' => 'Review the current workspace name, slug, and status.'],
                    ['heading' => 'Current Access Context', 'description' => 'See how the signed-in user is connected to this workspace.'],
                    ['heading' => 'Membership Summary', 'description' => 'Owner and member details stay visible in one clear summary.'],
                    ['heading' => 'Account Status', 'description' => 'Shared status labels stay visible alongside the current workspace state.'],
                ],
            ],
            'membership' => $account ? [
                ['label' => 'Workspace Name', 'value' => $account->name],
                ['label' => 'Workspace Slug', 'value' => $account->slug],
                ['label' => 'Workspace Owner', 'value' => $account->owner?->name ?? 'No owner assigned'],
                ['label' => 'Workspace Status', 'value' => $statusLabel],
            ] : [],
            'accountContext' => [
                ['label' => 'Current User', 'value' => $user?->name ?? 'No authenticated user'],
                ['label' => 'Current Email', 'value' => $user?->email ?? 'No email available'],
                ['label' => 'Access Role', 'value' => $currentMember ? ucfirst(str_replace('_', ' ', $currentMember->pivot->role ?? 'member')) : ($account && $account->owner_user_id === $user?->id ? 'Owner' : 'No active membership')],
                ['label' => 'Membership Status', 'value' => $currentMember ? ucfirst(str_replace('_', ' ', $currentMember->pivot->status ?? 'active')) : ($account ? 'Owner linked' : 'No account context')],
                ['label' => 'Joined Workspace', 'value' => $currentMember?->pivot?->joined_at ? (string) $currentMember->pivot->joined_at : ($account && $account->owner_user_id === $user?->id ? 'Owner record present' : 'Not linked')],
            ],
            'teamMembers' => $teamMembers,
            'statusLabels' => $statusLabels,
            'summary' => [
                'headline' => $account
                    ? 'Your account details and membership context are visible in one place.'
                    : 'No account details are available yet.',
                'details' => $account
                    ? 'Review workspace identity, current access, and team visibility without leaving the account view.'
                    : 'Complete workspace setup first, then return here to review account details.',
            ],
            'hasAccountData' => (bool) $account,
        ];
    }
}
