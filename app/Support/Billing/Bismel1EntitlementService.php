<?php
// اعوز بالله من الشياطين و ان يحضرون بسم الله الرحمن الرحيم الله لا إله إلا هو الحي القيوم
// Bismillahi ar-Rahmani ar-Rahim Audhu billahi min ash-shayatin wa an yahdurun Bismillah ar-Rahman ar-Rahim Allah la ilaha illa huwa al-hayy al-qayyum. Tamsa Allahu ala ayunihim
// version: x
// ======================================================
// - App Name: bismel1.com
// - Gusgraph LLC -
// - Author: Gus Kazem
// - https://Gusgraph.com
// - File Path: app/Support/Billing/Bismel1EntitlementService.php
// ======================================================

namespace App\Support\Billing;

use App\Domain\Billing\Enums\SubscriptionStatus;
use App\Models\Account;
use App\Models\StrategyProfile;
use App\Models\Subscription;
use App\Models\SubscriptionItem;
use App\Models\SubscriptionPlan;
use Illuminate\Support\Collection;

class Bismel1EntitlementService
{
    protected const BASE_PLAN_SCANNER = 'bismel1_ai_scanner';

    protected const BASE_PLAN_OVERNIGHT_EQUITIES = 'bismel1_bot_overnight_equities';

    protected const BASE_PLAN_OPTIONS = 'bismel1_bot_options';

    protected const BASE_PLAN_CRYPTO = 'bismel1_bot_crypto';

    protected const BASE_PLAN_STOCKS_PRIME = 'bismel1_stocks_bot_ai_prime';

    protected const BASE_PLAN_STOCKS_EXECUTE = 'bismel1_stocks_bot_execute';

    protected const ADDON_CUSTOM_STRATEGY = 'bismel1_bot_custom_strategy';

    protected const ADDON_ADDITIONAL_ACCOUNT = 'bismel1_bot_additional_account';

    protected const TEST_PLAN_SPEED_EXECUTE = 'bismel1_bot_speed_execute';

    public function resolve(?Account $account): array
    {
        if (! $account instanceof Account) {
            return $this->emptyEntitlements('subscription inactive');
        }

        $account->loadMissing([
            'subscriptions.subscriptionPlan',
            'subscriptions.items.subscriptionPlan',
            'alpacaAccounts',
        ]);

        $subscription = $this->activeConfirmedSubscription($account);
        $basePlan = $subscription?->subscriptionPlan;
        $basePlanCode = $this->canonicalPlanCode($basePlan?->code);
        $subscriptionActive = $subscription instanceof Subscription;
        $activeAddOnItems = $subscriptionActive
            ? $this->activeAddOnItems($subscription->items ?? collect())
            : collect();
        $activeAddOns = $activeAddOnItems
            ->map(function (SubscriptionItem $item): array {
                $plan = $item->subscriptionPlan;
                $canonicalCode = $this->canonicalPlanCode($plan?->code);

                return [
                    'code' => $canonicalCode,
                    'legacy_code' => $plan?->code,
                    'label' => $plan?->name ?? 'Add-On',
                    'quantity' => max(1, (int) ($item->quantity ?? 1)),
                ];
            })
            ->values();

        $capabilities = $this->capabilitiesForBasePlan($basePlanCode, $subscriptionActive);
        $capabilities['can_use_custom_strategy'] = $subscriptionActive && $activeAddOns->contains(fn (array $item) => $item['code'] === self::ADDON_CUSTOM_STRATEGY);

        $additionalAccountQuantity = $subscriptionActive
            ? (int) $activeAddOnItems
                ->filter(fn (SubscriptionItem $item) => $this->canonicalPlanCode($item->subscriptionPlan?->code) === self::ADDON_ADDITIONAL_ACCOUNT)
                ->sum(fn (SubscriptionItem $item) => max(1, (int) ($item->quantity ?? 1)))
            : 0;

        $supportsAdditionalAccounts = $subscriptionActive && $this->supportsAdditionalAccounts($basePlan, $basePlanCode);
        $allowedLinkedAccountsCount = $supportsAdditionalAccounts ? 1 + $additionalAccountQuantity : 1;
        $linkedAccountCount = (int) $account->alpacaAccounts->count();

        $blockedSummary = $this->blockedSummary($subscriptionActive, $capabilities, $linkedAccountCount, $allowedLinkedAccountsCount);

        return [
            'subscription_active' => $subscriptionActive,
            'base_plan' => [
                'code' => $basePlanCode,
                'legacy_code' => $basePlan?->code,
                'label' => $basePlan?->name ?? 'No active base plan',
            ],
            'active_add_ons' => $activeAddOns->all(),
            'capabilities' => $capabilities + [
                'allowed_linked_accounts_count' => $allowedLinkedAccountsCount,
            ],
            'linked_account_limit' => [
                'allowed' => $allowedLinkedAccountsCount,
                'current' => $linkedAccountCount,
                'additional_account_quantity' => $additionalAccountQuantity,
            ],
            'blocked_summary' => $blockedSummary,
            'admin_blocked_summary' => $this->adminBlockedSummary($subscriptionActive, $capabilities, $linkedAccountCount, $allowedLinkedAccountsCount),
            'mismatch_summary' => $this->mismatchSummary($subscriptionActive, $capabilities, $linkedAccountCount, $allowedLinkedAccountsCount),
            'subscription' => $subscription,
        ];
    }

    public function allowsStocksAutomation(?Account $account): bool
    {
        return (bool) data_get($this->resolve($account), 'capabilities.can_use_stocks_automation', false);
    }

    public function allowsExecute(?Account $account): bool
    {
        return (bool) data_get($this->resolve($account), 'capabilities.can_use_execute', false);
    }

    public function automationBlockedSummary(?Account $account): string
    {
        $entitlements = $this->resolve($account);

        if (! ($entitlements['subscription_active'] ?? false)) {
            return 'subscription inactive';
        }

        if ($this->isTestingOnlyCapabilities(data_get($entitlements, 'capabilities', []))) {
            return 'demo plan is isolated from the production automation lineup';
        }

        return (bool) data_get($entitlements, 'capabilities.can_use_stocks_automation', false)
            ? 'Automation access is available for this paid plan.'
            : 'plan does not include this automation mode';
    }

    public function executeBlockedSummary(?Account $account): string
    {
        $entitlements = $this->resolve($account);

        if (! ($entitlements['subscription_active'] ?? false)) {
            return 'subscription inactive';
        }

        if ($this->isTestingOnlyCapabilities(data_get($entitlements, 'capabilities', []))) {
            return 'demo plan is isolated from the production automation lineup';
        }

        return (bool) data_get($entitlements, 'capabilities.can_use_execute', false)
            ? 'Execution access is available for this paid plan.'
            : 'plan does not include this automation mode';
    }

    public function scannerBlockedSummary(?Account $account): string
    {
        $entitlements = $this->resolve($account);

        if (! ($entitlements['subscription_active'] ?? false)) {
            return 'subscription inactive';
        }

        if ($this->isTestingOnlyCapabilities(data_get($entitlements, 'capabilities', []))) {
            return 'demo plan is isolated from the production automation lineup';
        }

        return (bool) data_get($entitlements, 'capabilities.can_use_scanner', false)
            ? 'Scanner access is available for this paid plan.'
            : 'plan does not include this automation mode';
    }

    public function brokerLinkingSummary(?Account $account): array
    {
        $entitlements = $this->resolve($account);
        $current = (int) data_get($entitlements, 'linked_account_limit.current', 0);
        $allowed = (int) data_get($entitlements, 'linked_account_limit.allowed', 1);

        if (! ($entitlements['subscription_active'] ?? false)) {
            return [
                'allowed' => false,
                'summary' => 'subscription inactive',
            ];
        }

        if ($this->isTestingOnlyCapabilities(data_get($entitlements, 'capabilities', []))) {
            return [
                'allowed' => false,
                'summary' => 'demo plan is isolated from the production automation lineup',
            ];
        }

        if ($current >= $allowed) {
            return [
                'allowed' => false,
                'summary' => 'additional account limit reached',
            ];
        }

        return [
            'allowed' => true,
            'summary' => 'Broker account linking is available within the current paid limit.',
        ];
    }

    public function strategyAccess(?Account $account, ?StrategyProfile $strategyProfile, ?array $input = null): array
    {
        $entitlements = $this->resolve($account);
        $mode = strtolower((string) ($input['mode'] ?? $strategyProfile?->mode ?? 'review_first'));
        $requiresCustomStrategy = (bool) ($input['requires_custom_strategy'] ?? data_get($strategyProfile?->settings, 'requires_custom_strategy', false));
        $requiredCapability = $this->requiredCapabilityForStrategyMode($mode);
        $allowed = (bool) ($entitlements['subscription_active'] ?? false);
        $blockedSummary = 'subscription inactive';

        if ($allowed && $requiredCapability !== null) {
            $allowed = (bool) data_get($entitlements, 'capabilities.'.$requiredCapability, false);
            $blockedSummary = $allowed ? 'Plan access is aligned with the selected Bismel1 strategy mode.' : 'plan does not include this automation mode';
        }

        if ($allowed && $requiresCustomStrategy && ! (bool) data_get($entitlements, 'capabilities.can_use_custom_strategy', false)) {
            $allowed = false;
            $blockedSummary = 'custom strategy add-on required';
        }

        return [
            'allowed' => $allowed,
            'required_capability' => $requiredCapability,
            'blocked_summary' => $allowed ? 'Selected Bismel1 strategy access is allowed for the paid plan.' : $blockedSummary,
            'mode' => $mode,
        ];
    }

    protected function activeConfirmedSubscription(Account $account): ?Subscription
    {
        return $account->subscriptions
            ->filter(function (Subscription $subscription): bool {
                $status = $subscription->status instanceof SubscriptionStatus
                    ? $subscription->status->value
                    : (string) $subscription->status;

                return $status === SubscriptionStatus::Active->value
                    && $subscription->stripe_confirmed_at !== null;
            })
            ->sortByDesc(fn (Subscription $subscription) => $subscription->stripe_confirmed_at?->getTimestamp() ?? $subscription->updated_at?->getTimestamp() ?? 0)
            ->first();
    }

    protected function activeAddOnItems(Collection $items): Collection
    {
        return $items
            ->filter(function (SubscriptionItem $item): bool {
                $status = strtolower((string) ($item->status ?? 'active'));
                $planType = strtolower((string) ($item->plan_type ?? 'base'));

                return $planType === 'addon'
                    && in_array($status, ['active', 'trial', 'pending'], true)
                    && ($item->ends_at === null || $item->ends_at->isFuture());
            })
            ->values();
    }

    protected function capabilitiesForBasePlan(?string $basePlanCode, bool $subscriptionActive): array
    {
        $capabilities = [
            'can_use_scanner' => false,
            'can_use_stocks_automation' => false,
            'can_use_options' => false,
            'can_use_crypto' => false,
            'can_use_prime' => false,
            'can_use_execute' => false,
            'can_use_custom_strategy' => false,
            'can_use_speed_execute' => false,
        ];

        if (! $subscriptionActive) {
            return $capabilities;
        }

        return match ($basePlanCode) {
            self::BASE_PLAN_SCANNER => array_merge($capabilities, [
                'can_use_scanner' => true,
            ]),
            self::BASE_PLAN_OVERNIGHT_EQUITIES => array_merge($capabilities, [
                'can_use_scanner' => true,
                'can_use_stocks_automation' => true,
            ]),
            self::BASE_PLAN_OPTIONS => array_merge($capabilities, [
                'can_use_scanner' => true,
                'can_use_options' => true,
            ]),
            self::BASE_PLAN_CRYPTO => array_merge($capabilities, [
                'can_use_scanner' => true,
                'can_use_crypto' => true,
            ]),
            self::BASE_PLAN_STOCKS_PRIME => array_merge($capabilities, [
                'can_use_scanner' => true,
                'can_use_stocks_automation' => true,
                'can_use_prime' => true,
            ]),
            self::BASE_PLAN_STOCKS_EXECUTE => array_merge($capabilities, [
                'can_use_scanner' => true,
                'can_use_stocks_automation' => true,
                'can_use_execute' => true,
            ]),
            self::TEST_PLAN_SPEED_EXECUTE => array_merge($capabilities, [
                'can_use_speed_execute' => true,
            ]),
            default => $capabilities,
        };
    }

    protected function supportsAdditionalAccounts(?SubscriptionPlan $basePlan, ?string $basePlanCode): bool
    {
        $metadataSupport = data_get($basePlan?->metadata, 'supports_additional_accounts');

        if ($metadataSupport !== null) {
            return (bool) $metadataSupport;
        }

        return in_array($basePlanCode, [
            self::BASE_PLAN_OVERNIGHT_EQUITIES,
            self::BASE_PLAN_OPTIONS,
            self::BASE_PLAN_CRYPTO,
            self::BASE_PLAN_STOCKS_PRIME,
        ], true);
    }

    protected function blockedSummary(bool $subscriptionActive, array $capabilities, int $linkedAccountCount, int $allowedLinkedAccountsCount): string
    {
        if (! $subscriptionActive) {
            return 'subscription inactive';
        }

        if ($this->isTestingOnlyCapabilities($capabilities)) {
            return 'demo plan is isolated from the production automation lineup';
        }

        if (! ($capabilities['can_use_stocks_automation'] ?? false) && ($capabilities['can_use_scanner'] ?? false)) {
            return 'plan does not include this automation mode';
        }

        if ($linkedAccountCount > $allowedLinkedAccountsCount) {
            return 'additional account limit reached';
        }

        return 'Paid entitlements are aligned with current Bismel1 runtime access.';
    }

    protected function adminBlockedSummary(bool $subscriptionActive, array $capabilities, int $linkedAccountCount, int $allowedLinkedAccountsCount): string
    {
        if (! $subscriptionActive) {
            return 'subscription inactive';
        }

        if ($this->isTestingOnlyCapabilities($capabilities)) {
            return 'demo plan is isolated from the production automation lineup';
        }

        if (! ($capabilities['can_use_stocks_automation'] ?? false)) {
            return 'plan does not include this automation mode';
        }

        if ($linkedAccountCount > $allowedLinkedAccountsCount) {
            return 'additional account limit reached';
        }

        return 'Entitlement posture is aligned.';
    }

    protected function mismatchSummary(bool $subscriptionActive, array $capabilities, int $linkedAccountCount, int $allowedLinkedAccountsCount): ?string
    {
        if (! $subscriptionActive) {
            return 'Subscription is not Stripe-confirmed active.';
        }

        if ($this->isTestingOnlyCapabilities($capabilities)) {
            return 'Demo access is active, but production automation and broker-linking entitlements stay disabled.';
        }

        if (! ($capabilities['can_use_stocks_automation'] ?? false) && ($capabilities['can_use_scanner'] ?? false)) {
            return 'Scanner access is active, but full Bismel1 stocks automation is not included.';
        }

        if ($linkedAccountCount > $allowedLinkedAccountsCount) {
            return 'Linked Alpaca accounts exceed the paid allowance.';
        }

        return null;
    }

    protected function requiredCapabilityForStrategyMode(string $mode): ?string
    {
        if (str_contains($mode, 'option')) {
            return 'can_use_options';
        }

        if (str_contains($mode, 'crypto')) {
            return 'can_use_crypto';
        }

        if (str_contains($mode, 'scanner')) {
            return 'can_use_scanner';
        }

        if (in_array($mode, ['review_first', 'assist_only'], true)) {
            return 'can_use_scanner';
        }

        return 'can_use_stocks_automation';
    }

    protected function canonicalPlanCode(?string $legacyCode): ?string
    {
        return match (strtoupper(trim((string) $legacyCode))) {
            'BISMILLAH_AI_SCANNER', 'BISMEL1_AI_SCANNER' => self::BASE_PLAN_SCANNER,
            'BISMILLAH1_BOT_OVERNIGHT_EQUITIES', 'BISMEL1_BOT_OVERNIGHT_EQUITIES' => self::BASE_PLAN_OVERNIGHT_EQUITIES,
            'BISMILLAH1_BOT_OPTIONS', 'BISMEL1_BOT_OPTIONS' => self::BASE_PLAN_OPTIONS,
            'BISMILLAH1_BOT_CRYPTO', 'BISMEL1_BOT_CRYPTO' => self::BASE_PLAN_CRYPTO,
            'BISMILLAH1_BOT_PRIME', 'BISMEL1_STOCKS_BOT_AI_PRIME' => self::BASE_PLAN_STOCKS_PRIME,
            'BISMILLAH1_BOT_EXECUTE_BASIC', 'BISMEL1_STOCKS_BOT_EXECUTE' => self::BASE_PLAN_STOCKS_EXECUTE,
            'BISMILLAH1_BOT_CUSTOM_STRATEGY_ADDON', 'BISMEL1_BOT_CUSTOM_STRATEGY' => self::ADDON_CUSTOM_STRATEGY,
            'BISMILLAH1_BOT_ADDITIONAL_ACCOUNT_ADDON', 'BISMEL1_BOT_ADDITIONAL_ACCOUNT' => self::ADDON_ADDITIONAL_ACCOUNT,
            'BISMEL1_BOT_SPEED_EXECUTE', 'BISMILLAH1_BOT_SPEED_EXECUTE' => self::TEST_PLAN_SPEED_EXECUTE,
            default => null,
        };
    }

    protected function isTestingOnlyCapabilities(array $capabilities): bool
    {
        return (bool) ($capabilities['can_use_speed_execute'] ?? false)
            && ! ($capabilities['can_use_stocks_automation'] ?? false)
            && ! ($capabilities['can_use_scanner'] ?? false);
    }

    protected function emptyEntitlements(string $blockedSummary): array
    {
        return [
            'subscription_active' => false,
            'base_plan' => [
                'code' => null,
                'legacy_code' => null,
                'label' => 'No active base plan',
            ],
            'active_add_ons' => [],
            'capabilities' => [
                'can_use_scanner' => false,
                'can_use_stocks_automation' => false,
                'can_use_options' => false,
                'can_use_crypto' => false,
                'can_use_prime' => false,
                'can_use_execute' => false,
                'can_use_custom_strategy' => false,
                'can_use_speed_execute' => false,
                'allowed_linked_accounts_count' => 1,
            ],
            'linked_account_limit' => [
                'allowed' => 1,
                'current' => 0,
                'additional_account_quantity' => 0,
            ],
            'blocked_summary' => $blockedSummary,
            'admin_blocked_summary' => $blockedSummary,
            'mismatch_summary' => 'Subscription is not Stripe-confirmed active.',
            'subscription' => null,
        ];
    }
}
