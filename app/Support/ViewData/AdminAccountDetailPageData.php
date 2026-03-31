<?php
// اعوز بالله من الشياطين و ان يحضرون بسم الله الرحمن الرحيم الله لا إله إلا هو الحي القيوم
// Bismillahi ar-Rahmani ar-Rahim Audhu billahi min ash-shayatin wa an yahdurun Bismillah ar-Rahman ar-Rahim Allah la ilaha illa huwa al-hayy al-qayyum. Tamsa Allahu ala ayunihim
// version: x
// ======================================================
// - App Name: bismel1.com
// - Gusgraph LLC -
// - Author: Gus Kazem
// - https://Gusgraph.com
// - File Path: app/Support/ViewData/AdminAccountDetailPageData.php
// ======================================================

namespace App\Support\ViewData;

use App\Domain\Account\Enums\AccountStatus;
use App\Domain\Broker\Enums\BrokerConnectionStatus;
use App\Models\Account;
use App\Models\BrokerConnection;
use App\Models\BrokerCredential;
use App\Support\Display\RecordWindow;
use App\Support\Display\SafeDisplay;

class AdminAccountDetailPageData
{
    public static function make(?Account $account = null): array
    {
        $statusLabels = AccountStatus::labels();
        $brokerStatusLabels = BrokerConnectionStatus::labels();
        $statusValue = $account?->status instanceof AccountStatus
            ? $account->status->value
            : (string) ($account?->status ?? 'pending');
        $latestSubscription = $account?->subscriptions
            ?->sortByDesc(fn ($subscription) => $subscription->starts_at?->getTimestamp() ?? $subscription->created_at?->getTimestamp() ?? 0)
            ->first();
        $brokerConnections = $account?->brokerConnections
            ?->sortByDesc(fn (BrokerConnection $connection) => $connection->connected_at?->getTimestamp() ?? $connection->created_at?->getTimestamp() ?? 0)
            ->values();
        $brokerCredentials = $brokerConnections
            ?->flatMap(function (BrokerConnection $connection) {
                return $connection->brokerCredentials->map(function (BrokerCredential $credential) use ($connection) {
                    return [
                        'label' => $credential->label ?: 'Broker credential',
                        'value' => implode(' / ', array_filter([
                            'Connection: '.$connection->name,
                            $credential->maskedSummary(),
                            SafeDisplay::prefixedDateTime($credential->last_used_at, 'Last used ', 'Not used yet'),
                        ])),
                    ];
                });
            })
            ->values();

        return [
            'page' => [
                'title' => 'Account Detail',
                'intro' => 'A focused admin detail view for one workspace and its related operational signals.',
                'subtitle' => $account
                    ? 'Review the selected workspace, linked broker access, recent automation posture, and related account details in one place.'
                    : 'Choose an available workspace to review account detail, broker state, and automation posture.',
                'sections' => [
                    ['heading' => 'Account Detail', 'description' => 'Workspace identity, ownership, and status remain visible in one summary.'],
                    ['heading' => 'Tenant Overview', 'description' => 'Membership, broker, license, and activity counts stay grouped for quick review.'],
                    ['heading' => 'Broker State', 'description' => 'Broker connections and saved credential metadata are shown with masked values only.'],
                    ['heading' => 'Manual Operator Tools', 'description' => 'Admin-only scanner, broker sync, readiness, reconciliation, and pause or resume controls stay limited to safe runtime actions.'],
                ],
            ],
            'accountDetails' => $account ? [
                ['label' => 'Workspace Name', 'value' => $account->name],
                ['label' => 'Workspace Slug', 'value' => $account->slug],
                ['label' => 'Workspace Owner', 'value' => $account->owner?->name ?? 'No owner assigned'],
                ['label' => 'Workspace Status', 'value' => SafeDisplay::status($statusValue, $statusLabels)],
                ['label' => 'Latest Plan', 'value' => $latestSubscription?->subscriptionPlan?->name ?? 'No linked plan'],
            ] : [],
            'tenantOverview' => $account ? [
                ['label' => 'Member Count', 'value' => (string) $account->users->count()],
                ['label' => 'Broker Connections', 'value' => (string) $account->brokerConnections->count()],
                ['label' => 'Broker Credentials', 'value' => (string) ($brokerCredentials?->count() ?? 0)],
                ['label' => 'API Licenses', 'value' => (string) $account->apiLicenses->count()],
                ['label' => 'Activity Log Entries', 'value' => (string) $account->activityLogs->count()],
                ['label' => 'Audit Log Entries', 'value' => (string) $account->auditLogs->count()],
            ] : [],
            'brokerConnections' => $brokerConnections
                ?->map(function (BrokerConnection $connection) use ($brokerStatusLabels) {
                    return [
                        'title' => $connection->name ?: 'Broker connection',
                        'status' => SafeDisplay::statusMeta($connection->status->value ?? (string) $connection->status, $brokerStatusLabels),
                        'details' => [
                            ['label' => 'Provider', 'value' => ucwords(str_replace(['_', '-'], ' ', $connection->broker))],
                            ['label' => 'Connected', 'value' => SafeDisplay::prefixedDateTime($connection->connected_at, '', 'Not connected yet')],
                            ['label' => 'Synced', 'value' => SafeDisplay::prefixedDateTime($connection->last_synced_at, '', 'No sync recorded')],
                            ['label' => 'Account', 'value' => $connection->account?->name ?? 'Unassigned'],
                            ['label' => 'Credentials', 'value' => (string) $connection->brokerCredentials->count()],
                        ],
                    ];
                })
                ->all() ?? [],
            'brokerCredentials' => $brokerCredentials?->all() ?? [],
            'brokerConnectionsMeta' => RecordWindow::meta($brokerConnections ?? collect(), 'broker connections'),
            'brokerCredentialsMeta' => RecordWindow::meta($brokerCredentials ?? collect(), 'broker credentials'),
            'summary' => [
                'headline' => $account
                    ? 'Account detail is ready.'
                    : 'Account detail will appear here after a workspace is selected.',
                'details' => $account
                    ? 'This page keeps one workspace, its related memberships, billing context, broker access, automation visibility, and operator controls together in one operational view.'
                    : 'Choose a workspace from the accounts page to review its detail, broker state, and recent operational activity.',
            ],
            'hasAccountData' => (bool) $account,
        ];
    }
}
