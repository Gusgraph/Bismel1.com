<?php
// اعوز بالله من الشياطين و ان يحضرون بسم الله الرحمن الرحيم الله لا إله إلا هو الحي القيوم
// Bismillahi ar-Rahmani ar-Rahim Audhu billahi min ash-shayatin wa an yahdurun Bismillah ar-Rahman ar-Rahim Allah la ilaha illa huwa al-hayy al-qayyum. Tamsa Allahu ala ayunihim
// version: x
// ======================================================
// - App Name: bismel1.com
// - Gusgraph LLC -
// - Author: Gus Kazem
// - https://Gusgraph.com
// - File Path: app/Support/ViewData/StrategyPageData.php
// ======================================================

namespace App\Support\ViewData;

use App\Models\Account;
use App\Models\StrategyProfile;

class StrategyPageData
{
    public static function make(?Account $account = null, array $state = []): array
    {
        $currentSubscription = $state['current_subscription'] ?? null;
        $strategyProfile = $state['strategy_profile'] instanceof StrategyProfile ? $state['strategy_profile'] : null;
        $brokerConnections = $state['broker_connections'] ?? collect();
        $brokerCredentials = $state['broker_credentials'] ?? collect();
        $licenses = $state['licenses'] ?? collect();
        $activityLogs = $state['activity_logs'] ?? collect();
        $watchlists = $state['watchlists'] ?? collect();
        $entitlements = is_array($state['entitlements'] ?? null) ? $state['entitlements'] : [];
        $strategyAccess = is_array($state['strategy_access'] ?? null) ? $state['strategy_access'] : [];
        $watchlistSymbols = $watchlists->flatMap->symbols;
        $hasStrategyData = (bool) ($account || $strategyProfile);
        $mode = $strategyProfile?->mode ?? ($brokerCredentials->isNotEmpty() && $licenses->isNotEmpty()
            ? 'scanner_ready'
            : 'review_first');

        $planLabel = (string) data_get($entitlements, 'base_plan.label', 'No active base plan');
        $strategyAllowed = (bool) ($strategyAccess['allowed'] ?? false);
        $strategyBlockedSummary = (string) ($strategyAccess['blocked_summary'] ?? data_get($entitlements, 'blocked_summary', 'subscription inactive'));

        return [
            'page' => [
                'title' => 'Strategy',
                'intro' => 'Define how this workspace should behave before turning on automation.',
                'subtitle' => $account
                    ? 'This page keeps strategy choices clear through mode, timeframe, symbol coverage, and operating style.'
                    : 'No workspace is available yet, so strategy setup will begin here once account details are ready.',
                'sections' => [
                    ['heading' => 'Strategy Mode', 'description' => 'Choose whether this workspace stays review-first, assist-only, or scanner-ready.'],
                    ['heading' => 'Timeframe Concept', 'description' => 'Keep the preferred timeframe explicit so the strategy stays consistent with how you trade.'],
                    ['heading' => 'Enabled State', 'description' => 'Mark the current strategy profile as active when it should represent this workspace.'],
                    ['heading' => 'Watchlist Scope', 'description' => 'Use watchlists and symbol coverage to keep the strategy focused on the right market universe.'],
                    ['heading' => 'Style Profiles', 'description' => 'Use conservative, balanced, or aggressive posture to describe the trading style in plain language.'],
                ],
            ],
            'summary' => [
                'headline' => $strategyProfile
                    ? 'Your strategy settings are saved and ready to review.'
                    : ($account
                        ? 'This workspace is ready for its first saved strategy profile.'
                        : 'Strategy setup is waiting for the workspace to be ready.'),
                'details' => $strategyProfile
                    ? 'Review the saved strategy profile, adjust the operating style, and keep the workspace aligned before moving into automation.'
                    : ($account
                        ? 'Set the mode, timeframe, symbol scope, and style here before saving the first strategy profile for this workspace.'
                        : 'Complete workspace setup first, then return here to define the strategy profile.'),
            ],
            'form' => [
                'name' => old('name', $strategyProfile?->name ?? (($account?->name ? $account->name.' Strategy' : 'Primary Strategy'))),
                'mode' => old('mode', $strategyProfile?->mode ?? $mode),
                'timeframe' => old('timeframe', $strategyProfile?->timeframe ?? 'mixed'),
                'symbol_scope' => old('symbol_scope', $strategyProfile?->symbol_scope ?? ($watchlists->isNotEmpty() ? 'watchlist' : 'focused')),
                'style' => old('style', $strategyProfile?->style ?? 'balanced'),
                'is_active' => old('is_active', $strategyProfile?->is_active ?? true),
            ],
            'strategyFrame' => [
                ['label' => 'Workspace', 'value' => $account?->name ?? 'No current workspace', 'context' => $account?->slug ? 'Slug '.$account->slug : 'Waiting on account context'],
                ['label' => 'Plan Access', 'value' => $planLabel, 'context' => ($entitlements['subscription_active'] ?? false) ? 'Paid plan access is active' : 'subscription inactive'],
                ['label' => 'Profile Name', 'value' => $strategyProfile?->name ?? 'No saved profile', 'context' => $strategyProfile ? 'This is the current saved strategy profile for the workspace.' : 'Save the first profile to begin strategy setup.'],
                ['label' => 'Strategy Mode', 'value' => ucfirst(str_replace('_', ' ', (string) $mode)), 'context' => $currentSubscription ? 'This mode matches the current workspace context.' : 'Choose a plan first if strategy access is still incomplete.'],
                ['label' => 'Timeframe', 'value' => ucfirst(str_replace('_', ' ', (string) ($strategyProfile?->timeframe ?? 'mixed'))), 'context' => 'This helps describe how the strategy should operate over time.'],
                ['label' => 'Enabled State', 'value' => ($strategyProfile?->is_active ?? false) ? 'Enabled' : 'Disabled', 'context' => $strategyAllowed ? 'The profile is available for the workspace when needed.' : $strategyBlockedSummary],
                ['label' => 'Strategy Engine', 'value' => $strategyProfile?->engine ?? 'python', 'context' => 'This page keeps the customer-facing setup high level while execution details stay hidden.'],
            ],
            'watchlistSummary' => [
                ['label' => 'Watchlists', 'value' => (string) $watchlists->count(), 'context' => $watchlists->isNotEmpty() ? 'Linked watchlists are available for this workspace.' : 'No watchlists have been linked yet.'],
                ['label' => 'Tracked Symbols', 'value' => (string) $watchlistSymbols->count(), 'context' => $watchlistSymbols->isNotEmpty() ? 'Tracked symbols are available for review.' : 'Add watchlist symbols to expand coverage.'],
                ['label' => 'Symbol Scope', 'value' => ucfirst(str_replace('_', ' ', (string) ($strategyProfile?->symbol_scope ?? ($watchlists->isNotEmpty() ? 'watchlist' : 'focused')))), 'context' => 'This controls how broad or focused the strategy should be.'],
            ],
            'styleProfiles' => [
                ['label' => 'Conservative', 'value' => 'More selective coverage with a steadier operating posture', 'context' => 'Useful when you want a slower, more deliberate style.'],
                ['label' => 'Balanced', 'value' => 'A middle ground between selectivity and market coverage', 'context' => 'Suitable for most workspaces.'],
                ['label' => 'Aggressive', 'value' => 'Broader coverage and a faster operating style', 'context' => 'Best used when the workspace is already well prepared.'],
            ],
            'linkageItems' => [
                ['label' => 'Stored Profile', 'value' => $strategyProfile ? 'Present' : 'Missing', 'context' => $strategyProfile ? 'Updated '.$strategyProfile->updated_at?->diffForHumans() : 'No strategy profile has been saved yet.'],
                ['label' => 'Selected Mode Access', 'value' => $strategyAllowed ? 'Allowed' : 'Blocked', 'context' => $strategyAllowed ? 'The current Bismel1 strategy mode matches the paid entitlement.' : $strategyBlockedSummary],
                ['label' => 'Broker Readiness', 'value' => (string) $brokerCredentials->count().' saved credentials', 'context' => $brokerConnections->count().' broker connections are available for this workspace.'],
                ['label' => 'License Readiness', 'value' => (string) $licenses->count().' license records', 'context' => 'License and access details can be reviewed before moving forward.'],
                ['label' => 'Recent Workspace Signals', 'value' => (string) $activityLogs->count().' activity records', 'context' => 'Recent workspace activity can help guide strategy updates.'],
            ],
            'relatedLinks' => [
                ['route' => 'customer.broker.index', 'label' => 'Broker', 'description' => 'Confirm the broker connection before moving into automation.'],
                ['route' => 'customer.automation.index', 'label' => 'Automation', 'description' => 'Move from strategy setup into automation controls and readiness.'],
                ['route' => 'customer.reports.index', 'label' => 'Reports', 'description' => 'Review the broader workspace summary in one place.'],
            ],
            'hasStrategyData' => $hasStrategyData,
        ];
    }
}
