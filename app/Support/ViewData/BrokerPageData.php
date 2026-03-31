<?php
// اعوز بالله من الشياطين و ان يحضرون بسم الله الرحمن الرحيم الله لا إله إلا هو الحي القيوم
// Bismillahi ar-Rahmani ar-Rahim Audhu billahi min ash-shayatin wa an yahdurun Bismillah ar-Rahman ar-Rahim Allah la ilaha illa huwa al-hayy al-qayyum. Tamsa Allahu ala ayunihim
// version: x
// ======================================================
// - App Name: bismel1.com
// - Gusgraph LLC -
// - Author: Gus Kazem
// - https://Gusgraph.com
// - File Path: app/Support/ViewData/BrokerPageData.php
// ======================================================

namespace App\Support\ViewData;

use App\Domain\Broker\Enums\BrokerConnectionStatus;
use App\Models\Account;
use App\Support\Display\SafeDisplay;
use App\Support\Settings\AppSections;

class BrokerPageData
{
    public static function make(?Account $account = null, array $state = []): array
    {
        $entitlements = is_array($state['entitlements'] ?? null) ? $state['entitlements'] : [];
        $connectionStatusLabels = BrokerConnectionStatus::labels();
        $connections = $account?->brokerConnections
            ?->sortByDesc(fn ($connection) => $connection->connected_at?->getTimestamp() ?? $connection->created_at?->getTimestamp() ?? 0)
            ->values() ?? collect();
        $connection = $connections->first();
        $credentials = $connections->flatMap->brokerCredentials->sortByDesc(fn ($credential) => $credential->created_at?->getTimestamp() ?? 0)->values();
        $alpacaAccounts = $account?->alpacaAccounts
            ?->sortByDesc(fn ($alpacaAccount) => $alpacaAccount->last_synced_at?->getTimestamp() ?? $alpacaAccount->created_at?->getTimestamp() ?? 0)
            ->values() ?? collect();
        $alpacaAccount = $alpacaAccounts->firstWhere('is_primary', true) ?? $alpacaAccounts->first();
        $primaryCredential = $credentials->first();
        $providerEnvironment = $alpacaAccount?->environment ?? $primaryCredential?->environment ?? data_get($primaryCredential?->credential_payload, 'environment');
        $dataFeed = $alpacaAccount?->data_feed ?? data_get($primaryCredential?->credential_payload, 'market_data_feed');
        $lastSyncResult = data_get($alpacaAccount?->metadata, 'last_sync_result');
        $lastSyncMessage = data_get($alpacaAccount?->metadata, 'last_sync_message');
        $positionsCount = (int) data_get($alpacaAccount?->metadata, 'positions_count', 0);
        $ordersCount = (int) data_get($alpacaAccount?->metadata, 'orders_count', 0);
        $positionsSyncResult = data_get($alpacaAccount?->metadata, 'positions_sync_result');
        $ordersSyncResult = data_get($alpacaAccount?->metadata, 'orders_sync_result');
        $barsSyncResult = data_get($alpacaAccount?->metadata, 'bars_sync_result');
        $barsSyncMessage = data_get($alpacaAccount?->metadata, 'bars_sync_message');
        $barsFeed = data_get($alpacaAccount?->metadata, 'bars_feed');
        $bars1hCount = (int) data_get($alpacaAccount?->metadata, 'bars_1h_count', 0);
        $bars4hCount = (int) data_get($alpacaAccount?->metadata, 'bars_4h_count', 0);
        $lastBarsSyncAt = data_get($alpacaAccount?->metadata, 'last_bars_sync_at');
        $allowedLinkedAccountsCount = (int) data_get($entitlements, 'capabilities.allowed_linked_accounts_count', 1);
        $linkedAccountCount = (int) data_get($entitlements, 'linked_account_limit.current', $alpacaAccounts->count());
        $remainingLinkedAccountCapacity = max($allowedLinkedAccountsCount - $linkedAccountCount, 0);
        $brokerEntitlementSummary = (string) data_get($entitlements, 'blocked_summary', 'subscription inactive');
        $brokerLinkingAllowed = (bool) ($entitlements['subscription_active'] ?? false) && $linkedAccountCount < $allowedLinkedAccountsCount;
        $providers = [
            [
                'value' => implode(' / ', array_filter([
                    'ALPACA',
                    $providerEnvironment ? strtoupper((string) $providerEnvironment) : null,
                    $dataFeed ? strtoupper((string) $dataFeed) : null,
                ])),
                'label' => 'Alpaca',
                'status' => $connection
                    ? SafeDisplay::status($connection->status->value ?? (string) $connection->status, $connectionStatusLabels)
                    : 'Not connected',
            ],
        ];
        $connectionInventory = $connections->map(function ($connection) use ($account, $connectionStatusLabels) {
            $statusValue = $connection->status->value ?? (string) $connection->status;
            $primaryCredential = $connection->brokerCredentials->sortByDesc('id')->first();
            $linkedAlpacaAccount = $connection->alpacaAccounts->sortByDesc('id')->first();
            $environment = $linkedAlpacaAccount?->environment ?? $primaryCredential?->environment ?? data_get($primaryCredential?->credential_payload, 'environment');
            $feed = $linkedAlpacaAccount?->data_feed ?? data_get($primaryCredential?->credential_payload, 'market_data_feed');

            return [
                'label' => $connection->name,
                'value' => implode(' / ', array_filter([
                    'Alpaca',
                    $environment ? strtoupper((string) $environment) : null,
                    $feed ? 'Feed '.strtoupper((string) $feed) : null,
                    SafeDisplay::status($statusValue, $connectionStatusLabels),
                    $account?->name ? 'Account '.$account->name : null,
                    $connection->managedBy?->name ? 'Managed by '.$connection->managedBy->name : null,
                    $linkedAlpacaAccount?->is_primary ? 'Primary account' : null,
                    'Credentials '.$connection->brokerCredentials->count(),
                    $linkedAlpacaAccount?->sync_status ? 'Sync '.SafeDisplay::status((string) $linkedAlpacaAccount->sync_status) : null,
                    SafeDisplay::prefixedDateTime($connection->connected_at, 'Connected ', 'Not connected yet'),
                    SafeDisplay::prefixedDateTime($connection->last_synced_at, 'Last sync ', 'No sync recorded'),
                ])),
            ];
        })->all();
        $credentialInventory = $credentials->map(function ($credential) use ($account) {
            $environment = $credential->environment ?: data_get($credential->credential_payload, 'environment');
            $accessMode = $credential->access_mode ?: data_get($credential->credential_payload, 'access_mode');

            return [
                'label' => $credential->label,
                'value' => implode(' / ', array_filter([
                    strtoupper((string) ($credential->provider ?: 'alpaca')),
                    $environment ? strtoupper((string) $environment) : null,
                    $accessMode ? SafeDisplay::status((string) $accessMode) : null,
                    $credential->is_encrypted ? 'Stored securely' : 'Needs secure refresh',
                    $credential->maskedSummary(),
                    $account?->name ? 'Account '.$account->name : null,
                    $credential->brokerConnection?->name ? 'Connection '.$credential->brokerConnection->name : null,
                    SafeDisplay::prefixedDateTime($credential->last_used_at, 'Last used ', 'Never used'),
                    SafeDisplay::prefixedDateTime($credential->created_at, 'Created ', ''),
                ])),
            ];
        })->all();

        return [
            'page' => [
                'title' => 'Broker',
                'intro' => 'Connect Alpaca, confirm the linked account, verify paper or live mode, and see when the workspace is ready for automation.',
                'subtitle' => $account
                    ? 'This workspace shows its saved Alpaca connection, current account mode, feed choice, readiness state, and masked credential details in one place.'
                    : 'No Alpaca connection has been saved yet, so this page stays focused on the first setup step.',
                'sections' => [
                    ['heading' => 'Alpaca Connection', 'description' => 'Confirm that Alpaca is linked to the current workspace and that the saved account label is the one you expect.'],
                    ['heading' => 'Paper or Live Mode', 'description' => 'See clearly whether the linked account is in paper or live mode so automation never feels accidentally pointed at the wrong lane.'],
                    ['heading' => 'Market Data Feed', 'description' => 'The current feed choice stays visible so the market-data lane matches the broker connection you expect.'],
                    ['heading' => 'Readiness and Sync', 'description' => 'Account, positions, orders, and market-data sync checks show whether the connection is ready for automation or still needs attention.'],
                    ['heading' => 'Market Readiness', 'description' => 'The current market-data path stays aligned to the supported Bismel1 read cycle for broker-aware operation.'],
                    ['heading' => 'Masked Credentials', 'description' => 'Saved broker secrets stay hidden after save, while safe masked identifiers confirm which connection is stored.'],
                ],
            ],
            'sectionLabel' => AppSections::labels()[AppSections::CUSTOMER_BROKER],
            'providers' => $providers,
            'connectionDetails' => $connection ? [
                ['label' => 'Workspace Name', 'value' => $account->name],
                ['label' => 'Workspace Slug', 'value' => $account->slug],
                ['label' => 'Workspace Owner', 'value' => $account->owner?->name ?? 'No owner assigned'],
                ['label' => 'Connection Manager', 'value' => $connection->managedBy?->name ?? 'Not recorded'],
                ['label' => 'Connection Name', 'value' => $connection->name],
                ['label' => 'Broker Provider', 'value' => 'Alpaca'],
                ['label' => 'Environment', 'value' => $providerEnvironment ? strtoupper((string) $providerEnvironment) : 'Not configured', 'context' => match ((string) $providerEnvironment) {
                    'paper' => 'Paper mode is connected.',
                    'live' => 'Live mode is connected. Review this carefully before enabling automation.',
                    default => 'Choose paper or live mode explicitly when reconnecting.',
                }],
                ['label' => 'Market Data Feed', 'value' => $dataFeed ? strtoupper((string) $dataFeed) : 'Not configured'],
                ['label' => 'Connection Status', 'value' => SafeDisplay::status($connection->status->value ?? (string) $connection->status, $connectionStatusLabels)],
                ['label' => 'Alpaca Account State', 'value' => $alpacaAccount ? SafeDisplay::status((string) $alpacaAccount->status) : 'No linked Alpaca account'],
                ['label' => 'Account Readiness', 'value' => $alpacaAccount ? SafeDisplay::status((string) $alpacaAccount->sync_status) : 'Not configured', 'context' => $alpacaAccount ? 'Readiness depends on recent account, positions, and orders sync.' : 'Link Alpaca first to begin readiness checks.'],
                ['label' => 'Last Connection Check', 'value' => $lastSyncResult ? SafeDisplay::status((string) $lastSyncResult) : 'Not recorded'],
                ['label' => 'Readiness Note', 'value' => $lastSyncMessage ?: 'No readiness note recorded yet'],
                ['label' => 'Trade Stream Readiness', 'value' => $alpacaAccount ? SafeDisplay::status((string) $alpacaAccount->trade_stream_status) : 'Not configured'],
                ['label' => 'Stored Credentials', 'value' => (string) $credentials->count()],
                ['label' => 'Linked Alpaca Accounts', 'value' => (string) $alpacaAccounts->count()],
                ['label' => 'Linked Account Capacity', 'value' => (string) $allowedLinkedAccountsCount, 'context' => $brokerLinkingAllowed ? ($remainingLinkedAccountCapacity === 1 ? 'You can add 1 more linked account.' : 'You can add '.$remainingLinkedAccountCapacity.' more linked accounts.') : $brokerEntitlementSummary],
                ['label' => 'Primary Linked Account', 'value' => $alpacaAccount?->name ?? 'No primary account set'],
                ['label' => 'Connected At', 'value' => SafeDisplay::prefixedDateTime($connection->connected_at, '', 'Not connected yet')],
                ['label' => 'Last Sync', 'value' => SafeDisplay::prefixedDateTime($alpacaAccount?->last_synced_at ?? $connection->last_synced_at, '', 'No sync recorded')],
                ['label' => 'Last Account Sync', 'value' => SafeDisplay::prefixedDateTime($alpacaAccount?->last_account_sync_at, '', 'No account sync recorded')],
                ['label' => 'Last Positions Sync', 'value' => SafeDisplay::prefixedDateTime($alpacaAccount?->last_positions_sync_at, '', 'No positions sync recorded')],
                ['label' => 'Last Orders Sync', 'value' => SafeDisplay::prefixedDateTime($alpacaAccount?->last_orders_sync_at, '', 'No orders sync recorded')],
                ['label' => 'Stored Positions', 'value' => (string) $positionsCount],
                ['label' => 'Recent Orders Stored', 'value' => (string) $ordersCount],
                ['label' => 'Positions Sync Result', 'value' => $positionsSyncResult ? SafeDisplay::status((string) $positionsSyncResult) : 'Not recorded'],
                ['label' => 'Orders Sync Result', 'value' => $ordersSyncResult ? SafeDisplay::status((string) $ordersSyncResult) : 'Not recorded'],
                ['label' => 'Bars Feed', 'value' => $barsFeed ?: 'IEX only'],
                ['label' => 'Stored 1H Bars', 'value' => (string) $bars1hCount],
                ['label' => 'Stored 4H Bars', 'value' => (string) $bars4hCount],
                ['label' => 'Bars Sync Result', 'value' => $barsSyncResult ? SafeDisplay::status((string) $barsSyncResult) : 'Not recorded'],
                ['label' => 'Bars Sync Message', 'value' => $barsSyncMessage ?: 'No bars sync message recorded'],
                ['label' => 'Last Bars Sync', 'value' => $lastBarsSyncAt ?: 'No bars sync recorded'],
            ] : [],
            'brokerCredentialChecklist' => $credentials->map(function ($credential) {
                return [
                    'label' => $credential->label,
                    'value' => implode(' / ', array_filter([
                        $credential->maskedSummary(),
                        $credential->environment ? strtoupper((string) $credential->environment) : null,
                        $credential->access_mode ? SafeDisplay::status((string) $credential->access_mode) : null,
                        SafeDisplay::prefixedDateTime($credential->last_used_at, 'last used ', 'not used yet'),
                    ])),
                ];
            })->values()->all(),
            'connectionInventory' => $connectionInventory,
            'credentialInventory' => $credentialInventory,
            'summary' => [
                'headline' => $connection
                    ? 'Alpaca is linked here, and this page shows whether the workspace is ready to move forward.'
                    : 'No Alpaca account is linked yet.',
                'details' => $connection
                    ? 'Review the linked account label, confirm paper or live mode, check recent sync health, and verify that saved credentials stay masked before turning on automation.'
                    : 'Connect Alpaca first, then return here to confirm the account mode, sync health, and readiness for automation.',
            ],
            'connectionActionDescription' => $brokerLinkingAllowed
                ? 'Add another linked Alpaca account if you need it for this workspace.'
                : 'Linked-account capacity is currently limited by the active plan or current account usage.',
            'hasBrokerData' => (bool) ($connection || $alpacaAccount || $credentials->isNotEmpty()),
        ];
    }
}
