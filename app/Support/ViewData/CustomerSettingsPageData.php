<?php
// اعوز بالله من الشياطين و ان يحضرون بسم الله الرحمن الرحيم الله لا إله إلا هو الحي القيوم
// Bismillahi ar-Rahmani ar-Rahim Audhu billahi min ash-shayatin wa an yahdurun Bismillah ar-Rahman ar-Rahim Allah la ilaha illa huwa al-hayy al-qayyum. Tamsa Allahu ala ayunihim
// version: x
// ======================================================
// - App Name: bismel1.com
// - Gusgraph LLC -
// - Author: Gus Kazem
// - https://Gusgraph.com
// - File Path: app/Support/ViewData/CustomerSettingsPageData.php
// ======================================================

namespace App\Support\ViewData;

use App\Models\Account;
use App\Models\User;

class CustomerSettingsPageData
{
    public static function make(?Account $account = null, ?User $user = null): array
    {
        $currentMember = $account && $user
            ? $account->users->firstWhere('id', $user->id)
            : null;

        return [
            'page' => [
                'title' => 'Settings',
                'intro' => 'Manage the profile, workspace context, and team access details that shape your customer experience.',
                'subtitle' => $user
                    ? 'Review the profile and workspace details tied to your current login, then update what should stay current across the product.'
                    : 'Sign in again to review and update your account settings.',
                'sections' => [
                    ['heading' => 'Profile Details', 'description' => 'Keep your visible name and email current across the customer workspace.'],
                    ['heading' => 'Workspace Context', 'description' => 'Review the current workspace and access role connected to your login.'],
                    ['heading' => 'Team Access', 'description' => 'See who has access to this workspace and how each member is listed.'],
                ],
            ],
            'membership' => $account ? [
                ['label' => 'Workspace Name', 'value' => $account->name],
                ['label' => 'Workspace Slug', 'value' => $account->slug],
                ['label' => 'Workspace Owner', 'value' => $account->owner?->name ?? 'No owner assigned'],
                ['label' => 'Access Role', 'value' => $currentMember ? ucfirst(str_replace('_', ' ', $currentMember->pivot->role ?? 'member')) : ($account->owner_user_id === $user?->id ? 'Owner' : 'No active membership')],
            ] : [],
            'teamMembers' => $account
                ? $account->users->map(fn ($member) => [
                    'name' => $member->name,
                    'role' => ucfirst(str_replace('_', ' ', $member->pivot->role ?? 'member')),
                    'status' => ucfirst(str_replace('_', ' ', $member->pivot->status ?? 'active')),
                ])->values()->all()
                : [],
            'preferences' => $user ? [
                ['label' => 'Display Name', 'value' => $user->name],
                ['label' => 'Email Address', 'value' => $user->email],
                ['label' => 'Current Workspace', 'value' => $account?->name ?? 'No workspace selected yet'],
                ['label' => 'Membership Status', 'value' => $currentMember ? ucfirst(str_replace('_', ' ', $currentMember->pivot->status ?? 'active')) : ($account ? 'Owner linked' : 'No workspace membership yet')],
            ] : [],
            'summary' => [
                'headline' => $user
                    ? 'Your account settings are ready.'
                    : 'Settings are waiting for an active signed-in account.',
                'details' => $user
                    ? 'Use this page to keep profile details accurate and review the workspace context connected to your account.'
                    : 'Sign in to review your profile, workspace context, and team access.',
            ],
            'hasSettingsData' => (bool) $user,
        ];
    }
}
