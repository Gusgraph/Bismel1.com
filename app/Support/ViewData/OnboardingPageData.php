<?php
// اعوز بالله من الشياطين و ان يحضرون بسم الله الرحمن الرحيم الله لا إله إلا هو الحي القيوم
// Bismillahi ar-Rahmani ar-Rahim Audhu billahi min ash-shayatin wa an yahdurun Bismillah ar-Rahman ar-Rahim Allah la ilaha illa huwa al-hayy al-qayyum. Tamsa Allahu ala ayunihim
// version: x
// ======================================================
// - App Name: bismel1.com
// - Gusgraph LLC -
// - Author: Gus Kazem
// - https://Gusgraph.com
// - File Path: app/Support/ViewData/OnboardingPageData.php
// ======================================================

namespace App\Support\ViewData;

use App\Models\Account;
use App\Models\User;
use App\Support\Display\SafeDisplay;

class OnboardingPageData
{
    public static function make(?Account $account = null, ?User $user = null, array $state = []): array
    {
        $subscription = $state['current_subscription'] ?? null;
        $brokerConnection = $state['current_broker_connection'] ?? null;
        $brokerCredential = $state['current_broker_credential'] ?? null;
        $license = $state['current_license'] ?? null;
        $apiKey = $state['current_api_key'] ?? null;
        $recentInvoice = $state['recent_invoice'] ?? null;
        $recentActivity = $state['recent_activity'] ?? null;
        $brokerEnvironment = $brokerCredential?->environment ?? data_get($brokerCredential?->credential_payload, 'environment');
        $hasOnboardingData = (bool) ($account || $subscription || $brokerConnection || $brokerCredential || $license || $apiKey || $recentInvoice || $user);
        $readinessCount = collect([
            $account ? 1 : 0,
            $subscription ? 1 : 0,
            $brokerConnection ? 1 : 0,
            $brokerCredential ? 1 : 0,
            $license ? 1 : 0,
            $apiKey ? 1 : 0,
            $user ? 1 : 0,
            $recentInvoice ? 1 : 0,
        ])->sum();

        return [
            'page' => [
                'title' => 'Onboarding',
                'intro' => 'Move from signup to a trusted Bismel1 setup by confirming billing, Alpaca connection, account mode, and readiness in order.',
                'subtitle' => $account
                    ? 'Setup progress is summarized from the current workspace, including billing, broker connection, account mode, license state, and recent readiness signals.'
                    : 'No workspace is available yet, so onboarding stays focused on the first setup steps still needed.',
                'sections' => [
                    ['heading' => 'Workspace Readiness', 'description' => 'Confirm the workspace and signed-in profile first so the rest of setup is attached to the right account.'],
                    ['heading' => 'Connection Readiness', 'description' => 'Connect Alpaca, confirm the account mode, and verify that saved credentials remain masked while readiness moves forward.'],
                    ['heading' => 'Billing Readiness', 'description' => 'Confirm the active package and invoice posture so product access and account capacity stay aligned.'],
                ],
            ],
            'onboardingChecklist' => [
                [
                    'label' => 'Workspace',
                    'value' => $account
                        ? 'Ready: '.$account->name.' / '.$account->slug
                        : 'Not ready yet: no accessible workspace record found',
                ],
                [
                    'label' => 'Profile',
                    'value' => $user
                        ? 'Ready: '.$user->name.' / '.$user->email
                        : 'Not ready yet: no authenticated profile record found',
                ],
                [
                    'label' => 'Subscription',
                    'value' => $subscription
                        ? 'Ready: '.($subscription->subscriptionPlan?->name ?? 'Linked plan missing').' / '.SafeDisplay::status($subscription->status->value ?? $subscription->status)
                        : 'Still needed: choose and activate a plan',
                ],
                [
                    'label' => 'Alpaca Connection',
                    'value' => $brokerConnection
                        ? 'Ready: '.$brokerConnection->name.' / '.ucwords(str_replace(['_', '-'], ' ', $brokerConnection->broker))
                        : 'Still needed: connect Alpaca',
                ],
                [
                    'label' => 'Saved Broker Access',
                    'value' => $brokerCredential
                        ? 'Ready: '.$brokerCredential->maskedSummary()
                        : 'Still needed: save broker access',
                ],
                [
                    'label' => 'Account Mode',
                    'value' => $brokerEnvironment
                        ? 'Ready: '.strtoupper((string) $brokerEnvironment)
                        : 'Still needed: confirm paper or live mode',
                ],
                [
                    'label' => 'License',
                    'value' => $license
                        ? 'Ready: '.$license->name.' / '.SafeDisplay::status($license->status->value ?? $license->status)
                        : 'Still needed: save license details',
                ],
                [
                    'label' => 'API Key',
                    'value' => $apiKey
                        ? 'Ready: '.$apiKey->maskedTokenSummary()
                        : 'Still needed: save API access',
                ],
                [
                    'label' => 'Invoices',
                    'value' => $recentInvoice
                        ? 'Ready: '.($recentInvoice->number ?? 'Invoice record').' / '.SafeDisplay::status((string) $recentInvoice->status)
                        : 'Waiting: no invoice activity recorded yet',
                ],
                [
                    'label' => 'Recent Activity',
                    'value' => $recentActivity
                        ? 'Ready: '.($recentActivity->type ?? 'activity').' / '.($recentActivity->message ?? 'Current-account activity available')
                        : 'Waiting: no recent workspace activity recorded yet',
                ],
            ],
            'brokerCredentialChecklist' => $brokerCredential
                ? [[
                    'label' => $brokerCredential->label,
                    'value' => $brokerCredential->maskedSummary(),
                ]]
                : [],
            'summary' => [
                'headline' => $hasOnboardingData
                    ? 'Setup progress is visible from the current workspace state.'
                    : 'Setup has not started yet.',
                'details' => $hasOnboardingData
                    ? 'Move through the checklist in order: confirm the workspace, activate the plan, connect Alpaca, confirm paper or live mode, and finish the remaining setup items. '.$readinessCount.' of 9 primary markers are currently present.'
                    : 'Start by creating the workspace records, then add billing, broker access, and the remaining setup details from this page.',
            ],
            'hasOnboardingData' => $hasOnboardingData,
        ];
    }
}
