<?php
// اعوز بالله من الشياطين و ان يحضرون بسم الله الرحمن الرحيم الله لا إله إلا هو الحي القيوم
// Bismillahi ar-Rahmani ar-Rahim Audhu billahi min ash-shayatin wa an yahdurun Bismillah ar-Rahman ar-Rahim Allah la ilaha illa huwa al-hayy al-qayyum. Tamsa Allahu ala ayunihim
// version: x
// ======================================================
// - App Name: gusgraph-trading
// - Gusgraph LLC -
// - Author: Gus Kazem
// - https://Gusgraph.com
// - File Path: app/Support/Customer/CurrentCustomerAccountResolver.php
// ======================================================

namespace App\Support\Customer;

use App\Models\Account;
use App\Models\User;
use InvalidArgumentException;

class CurrentCustomerAccountResolver
{
    public function resolveCurrent(?User $user): ?Account
    {
        return $this->resolve($user);
    }

    public function resolveForPreset(?User $user, string $preset): ?Account
    {
        return $this->resolve($user, $this->relationsForPreset($preset));
    }

    public function resolve(?User $user, array $relations = []): ?Account
    {
        if (! $user) {
            return null;
        }

        $ownedAccount = $user->ownedAccounts()
            ->with($relations)
            ->orderBy('name')
            ->first();

        if ($ownedAccount) {
            return $ownedAccount;
        }

        return $user->accounts()
            ->wherePivot('status', 'active')
            ->with($relations)
            ->orderBy('accounts.name')
            ->first();
    }

    public function relationsForPreset(string $preset): array
    {
        return match ($preset) {
            'account' => ['owner', 'users'],
            'billing' => ['owner', 'subscriptions.subscriptionPlan', 'subscriptions.items.subscriptionPlan', 'subscriptions.referralAttribution'],
            'broker' => ['owner', 'brokerConnections.brokerCredentials', 'alpacaAccounts.brokerConnection'],
            'strategy' => ['owner', 'strategyProfiles.watchlists.symbols', 'watchlists.symbols', 'brokerConnections.brokerCredentials', 'apiLicenses.apiKeys', 'activityLogs'],
            'automation' => ['owner', 'automationSettings.strategyProfile', 'strategyProfiles', 'botRuns', 'brokerConnections.brokerCredentials', 'alpacaAccounts.brokerConnection', 'alpacaPositions', 'alpacaOrders', 'signals', 'apiLicenses.apiKeys', 'activityLogs'],
            'trading' => ['owner', 'subscriptions.subscriptionPlan', 'automationSettings.strategyProfile', 'brokerConnections.brokerCredentials', 'alpacaAccounts.brokerConnection', 'alpacaPositions', 'alpacaOrders', 'signals', 'botRuns', 'activityLogs'],
            'license' => ['owner', 'apiLicenses.apiKeys'],
            'invoice' => ['owner', 'subscriptions.subscriptionPlan', 'invoices.subscription'],
            'summary' => ['owner', 'subscriptions.subscriptionPlan', 'invoices', 'brokerConnections.brokerCredentials', 'alpacaAccounts.brokerConnection', 'strategyProfiles', 'automationSettings', 'signals.watchlistSymbol', 'signals.strategyProfile', 'botRuns.strategyProfile', 'botRuns.automationSetting', 'apiLicenses.apiKeys', 'activityLogs'],
            'dashboard' => ['owner', 'users', 'subscriptions.subscriptionPlan', 'invoices', 'brokerConnections.brokerCredentials', 'alpacaAccounts.brokerConnection', 'strategyProfiles', 'automationSettings', 'signals.watchlistSymbol', 'signals.strategyProfile', 'botRuns.strategyProfile', 'botRuns.automationSetting', 'apiLicenses.apiKeys', 'activityLogs'],
            default => throw new InvalidArgumentException('Unsupported current customer account preset ['.$preset.'].'),
        };
    }
}
