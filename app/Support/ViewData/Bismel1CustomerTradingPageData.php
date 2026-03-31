<?php
// اعوز بالله من الشياطين و ان يحضرون بسم الله الرحمن الرحيم الله لا إله إلا هو الحي القيوم
// Bismillahi ar-Rahmani ar-Rahim Audhu billahi min ash-shayatin wa an yahdurun Bismillah ar-Rahman ar-Rahim Allah la ilaha illa huwa al-hayy al-qayyum. Tamsa Allahu ala ayunihim
// version: x
// ======================================================
// - App Name: bismel1.com
// - Gusgraph LLC -
// - Author: Gus Kazem
// - https://Gusgraph.com
// - File Path: app/Support/ViewData/Bismel1CustomerTradingPageData.php
// ======================================================

namespace App\Support\ViewData;

use App\Models\Account;
use App\Models\ActivityLog;
use App\Support\Display\SafeDisplay;
use Illuminate\Support\Collection;

class Bismel1CustomerTradingPageData
{
    public static function make(string $pageType, ?Account $account = null, array $state = []): array
    {
        return match ($pageType) {
            'positions' => self::positions($account, $state),
            'orders' => self::orders($account, $state),
            'activity' => self::activity($account, $state),
            default => self::positions($account, $state),
        };
    }

    protected static function positions(?Account $account, array $state): array
    {
        $positions = $state['positions'] ?? collect();
        $activityLogs = $state['activity_logs'] ?? collect();
        $alpacaAccount = $state['alpaca_account'] ?? null;
        $entitlements = is_array($state['entitlements'] ?? null) ? $state['entitlements'] : [];
        $openPositions = $positions->filter(fn ($position) => (float) ($position->qty ?? 0) > 0)->values();
        $recentClosed = $activityLogs
            ->filter(fn ($item) => str_contains((string) ($item->type ?? ''), 'bismel1_position_close'))
            ->take(5)
            ->count();
        $reconciled = $positions
            ->filter(fn ($position) => str_contains(strtolower((string) ($position->management_state ?? '')), 'reconcile')
                || str_contains(strtolower((string) ($position->status_summary ?? '')), 'reconcile'))
            ->count();

        return [
            'page' => [
                'title' => 'Customer Positions',
                'intro' => 'Review current positions for this workspace with clear, high-level management updates.',
                'subtitle' => $account
                    ? 'Open positions, management updates, and reconciliation status stay visible here without exposing internal strategy details.'
                    : 'No workspace is available yet, so positions will appear here after setup is complete and activity begins.',
                'sections' => [
                    ['heading' => 'Open Position State', 'description' => 'See symbol, side, quantity, and current value references for this workspace.'],
                    ['heading' => 'Management Posture', 'description' => 'Position updates stay readable through hold, add, closed, or reconciled summaries.'],
                    ['heading' => 'Broker Sync Context', 'description' => 'Recent sync timing helps explain how fresh the position view is.'],
                ],
            ],
            'summary' => [
                'headline' => $openPositions->isNotEmpty() ? 'Your positions are visible with clear management updates.' : 'No open positions are showing for this workspace yet.',
                'details' => $openPositions->isNotEmpty()
                    ? 'This page shows current positions, monitored value references, and recent management posture in one place.'
                    : 'Positions will appear here after the broker connection is active and the first position is opened or synced.',
            ],
            'summaryItems' => [
                ['label' => 'Open Positions', 'value' => (string) $openPositions->count(), 'context' => 'Open positions currently linked to this workspace'],
                ['label' => 'Recently Closed', 'value' => (string) $recentClosed, 'context' => 'Recent position closures recorded in this workspace'],
                ['label' => 'Reconciled Positions', 'value' => (string) $reconciled, 'context' => 'Positions that were recently brought back into alignment'],
                ['label' => 'Subscription Access', 'value' => ($entitlements['subscription_active'] ?? false) ? data_get($entitlements, 'base_plan.label', 'Active plan') : 'subscription inactive', 'context' => ($entitlements['subscription_active'] ?? false) ? 'This trading view is active for the current workspace.' : 'Historical monitoring remains visible while subscription access is inactive.'],
                ['label' => 'Last Positions Sync', 'value' => SafeDisplay::dateTime($alpacaAccount?->last_positions_sync_at, 'No positions sync recorded'), 'context' => $alpacaAccount ? 'Latest broker position update for this workspace' : 'Connect a broker account to begin position updates'],
                ['label' => 'Last Position Update', 'value' => self::runtimeSummary($state, 'bismel1_position_manager.last_management_summary', 'No position updates yet'), 'context' => 'Most recent high-level position management summary'],
            ],
            'primaryTitle' => 'Open Positions',
            'primaryItems' => $positions
                ->take(20)
                ->map(fn ($position) => self::mapPositionItem($position))
                ->values()
                ->all(),
            'primaryEmptyMessage' => 'No open positions are showing yet. They will appear here after trading activity begins.',
            'secondaryTitle' => 'Recent Position Activity',
            'secondaryItems' => $activityLogs
                ->filter(fn ($item) => str_contains((string) ($item->type ?? ''), 'bismel1_position_'))
                ->take(8)
                ->map(fn ($item) => self::mapActivityLogItem($item))
                ->values()
                ->all(),
            'secondaryEmptyMessage' => 'No recent position updates are showing yet.',
            'relatedLinks' => [
                ['route' => 'customer.orders.index', 'label' => 'Orders', 'description' => 'Review recent broker orders tied to this workspace.'],
                ['route' => 'customer.activity.index', 'label' => 'Activity', 'description' => 'Review safe scanner, risk, execution, and management activity.'],
                ['route' => 'customer.automation.index', 'label' => 'Automation', 'description' => 'Review runtime readiness and current automation posture.'],
            ],
            'hasTradingData' => (bool) ($account || $positions->isNotEmpty() || $activityLogs->isNotEmpty()),
            'emptyStateTitle' => 'No Positions Yet',
            'emptyStateMessage' => 'Positions will appear here after the broker connection is active and the workspace begins recording trades.',
        ];
    }

    protected static function orders(?Account $account, array $state): array
    {
        $orders = $state['orders'] ?? collect();
        $activityLogs = $state['activity_logs'] ?? collect();
        $alpacaAccount = $state['alpaca_account'] ?? null;
        $filledCount = $orders->filter(fn ($order) => in_array((string) ($order->status ?? ''), ['filled', 'partially_filled'], true))->count();
        $failedCount = $orders->filter(fn ($order) => in_array((string) ($order->status ?? ''), ['failed', 'rejected', 'canceled', 'expired'], true))->count();
        $submittedCount = $orders->filter(fn ($order) => in_array((string) ($order->status ?? ''), ['submitted', 'new', 'accepted'], true))->count();

        return [
            'page' => [
                'title' => 'Customer Orders',
                'intro' => 'Review recent orders for this workspace with clear, current status updates.',
                'subtitle' => $account
                    ? 'Recent order actions, times, statuses, and outcome summaries stay visible here without exposing raw broker details.'
                    : 'No workspace is available yet, so orders will appear here after setup is complete and activity begins.',
                'sections' => [
                    ['heading' => 'Recent Orders', 'description' => 'See symbol, action, time, and current status for recent orders.'],
                    ['heading' => 'Execution Outcomes', 'description' => 'Order status stays clear through submitted, filled, failed, rejected, or skipped wording.'],
                    ['heading' => 'Broker Visibility', 'description' => 'Recent sync timing helps explain how fresh the order view is.'],
                ],
            ],
            'summary' => [
                'headline' => $orders->isNotEmpty() ? 'Your recent orders are visible with clear outcome summaries.' : 'No recent orders are showing for this workspace yet.',
                'details' => $orders->isNotEmpty()
                    ? 'This page keeps recent orders clear through action, status, timing, and high-level outcome summaries.'
                    : 'Orders will appear here after the broker connection is active and the first order is recorded.',
            ],
            'summaryItems' => [
                ['label' => 'Recent Orders', 'value' => (string) $orders->count(), 'context' => 'Most recent account-scoped broker orders only'],
                ['label' => 'Submitted', 'value' => (string) $submittedCount, 'context' => 'Orders waiting on fill or completion'],
                ['label' => 'Filled', 'value' => (string) $filledCount, 'context' => 'Filled or partially filled order states'],
                ['label' => 'Failed or Rejected', 'value' => (string) $failedCount, 'context' => 'Orders that still need attention'],
                ['label' => 'Last Orders Sync', 'value' => SafeDisplay::dateTime($alpacaAccount?->last_orders_sync_at, 'No orders sync recorded'), 'context' => $alpacaAccount ? 'Latest broker order update for this workspace' : 'Connect a broker account to begin order updates'],
                ['label' => 'Last Execution Update', 'value' => self::runtimeSummary($state, 'bismel1_execution.last_execution_summary', 'No execution updates yet'), 'context' => 'Most recent high-level execution summary'],
            ],
            'primaryTitle' => 'Recent Orders',
            'primaryItems' => $orders
                ->take(20)
                ->map(fn ($order) => self::mapOrderItem($order))
                ->values()
                ->all(),
            'primaryEmptyMessage' => 'No recent orders are showing yet. They will appear here after trading activity begins.',
            'secondaryTitle' => 'Recent Execution Activity',
            'secondaryItems' => $activityLogs
                ->filter(fn ($item) => str_contains((string) ($item->type ?? ''), 'bismel1_execution_'))
                ->take(8)
                ->map(fn ($item) => self::mapActivityLogItem($item))
                ->values()
                ->all(),
            'secondaryEmptyMessage' => 'No recent execution updates are showing yet.',
            'relatedLinks' => [
                ['route' => 'customer.positions.index', 'label' => 'Positions', 'description' => 'Review current position state after recent order activity.'],
                ['route' => 'customer.activity.index', 'label' => 'Activity', 'description' => 'Review the full safe runtime feed across scanner, risk, execution, and management.'],
                ['route' => 'customer.broker.index', 'label' => 'Broker', 'description' => 'Review broker sync posture and masked connection state.'],
            ],
            'hasTradingData' => (bool) ($account || $orders->isNotEmpty() || $activityLogs->isNotEmpty()),
            'emptyStateTitle' => 'No Orders Yet',
            'emptyStateMessage' => 'Orders will appear here after the broker connection is active and the workspace begins recording order flow.',
        ];
    }

    protected static function activity(?Account $account, array $state): array
    {
        $activityLogs = $state['activity_logs'] ?? collect();
        $botRuns = $state['bot_runs'] ?? collect();
        $signals = $state['signals'] ?? collect();
        $feedItems = self::activityFeedItems($activityLogs, $botRuns, $signals);

        return [
            'page' => [
                'title' => 'Customer Activity',
                'intro' => 'Review recent activity across scanning, execution, risk decisions, and position management.',
                'subtitle' => $account
                    ? 'This page keeps recent Bismel1 activity readable from one place with clear product wording and no internal strategy detail.'
                    : 'No workspace is available yet, so activity will appear here after setup is complete and automation begins recording updates.',
                'sections' => [
                    ['heading' => 'Activity Feed', 'description' => 'See recent activity across scanning, risk decisions, execution, and position updates.'],
                    ['heading' => 'Signal and Run Outcomes', 'description' => 'Review high-level signal and run outcomes from the latest automation work.'],
                    ['heading' => 'Blocked and Recovery Context', 'description' => 'Risk blocks, skipped actions, and reconciliation events remain visible in clear language.'],
                ],
            ],
            'summary' => [
                'headline' => $feedItems->isNotEmpty() ? 'Recent Bismel1 activity is visible in one clear feed.' : 'No recent Bismel1 activity is showing for this workspace yet.',
                'details' => $feedItems->isNotEmpty()
                    ? 'The activity feed combines high-level runtime messages, run summaries, and signal outcomes so you can follow automation clearly.'
                    : 'Activity will appear here after scanning, execution, risk decisions, or position updates are recorded for this workspace.',
            ],
            'summaryItems' => [
                ['label' => 'Recent Feed Items', 'value' => (string) $feedItems->count(), 'context' => 'Recent activity, runs, and signal outcomes combined'],
                ['label' => 'Scanner Events', 'value' => (string) $botRuns->filter(fn ($run) => str_contains((string) ($run->run_type ?? ''), 'scan'))->count(), 'context' => 'Recent scan outcomes'],
                ['label' => 'Risk Blocks', 'value' => (string) $signals->filter(fn ($signal) => self::signalRepresentsRiskBlock($signal))->count(), 'context' => 'Recent times the system chose not to proceed'],
                ['label' => 'Execution Events', 'value' => (string) $activityLogs->filter(fn ($item) => str_contains((string) ($item->type ?? ''), 'bismel1_execution_'))->count(), 'context' => 'Recent execution-related updates'],
                ['label' => 'Position Updates', 'value' => (string) $activityLogs->filter(fn ($item) => str_contains((string) ($item->type ?? ''), 'bismel1_position_'))->count(), 'context' => 'Recent position and reconciliation updates'],
                ['label' => 'Latest Workspace Summary', 'value' => self::runtimeSummary($state, 'bismel1_runtime.last_runtime_summary', 'No workspace summary yet'), 'context' => 'Most recent high-level automation summary'],
            ],
            'primaryTitle' => 'Recent Runtime Feed',
            'primaryItems' => $feedItems->take(20)->values()->all(),
            'primaryEmptyMessage' => 'No recent activity is showing yet. It will appear here after the first automation updates are recorded.',
            'secondaryTitle' => 'Recent Signal and Run Outcomes',
            'secondaryItems' => $botRuns
                ->take(5)
                ->map(fn ($run) => self::mapBotRunItem($run))
                ->merge($signals->take(5)->map(fn ($signal) => self::mapSignalItem($signal)))
                ->sortByDesc(fn ($item) => $item['sort_timestamp'] ?? 0)
                ->take(10)
                ->map(function (array $item): array {
                    unset($item['sort_timestamp']);

                    return $item;
                })
                ->values()
                ->all(),
            'secondaryEmptyMessage' => 'No recent signal or run outcomes are showing yet.',
            'relatedLinks' => [
                ['route' => 'customer.positions.index', 'label' => 'Positions', 'description' => 'Review current position state and management summaries.'],
                ['route' => 'customer.orders.index', 'label' => 'Orders', 'description' => 'Review recent orders and broker outcome summaries.'],
                ['route' => 'customer.reports.index', 'label' => 'Reports', 'description' => 'Review the broader account-scoped operating snapshot.'],
            ],
            'hasTradingData' => (bool) ($account || $activityLogs->isNotEmpty() || $botRuns->isNotEmpty() || $signals->isNotEmpty()),
            'emptyStateTitle' => 'No Activity Yet',
            'emptyStateMessage' => 'Activity will appear here after the first automation cycle or broker update is recorded for this workspace.',
        ];
    }

    protected static function mapPositionItem($position): array
    {
        $statusSummary = SafeDisplay::sanitizedText((string) ($position->status_summary ?? ''), self::defaultPositionSummary($position));
        $managementState = strtolower(trim((string) ($position->management_state ?? 'open')));
        $status = $managementState !== '' ? $managementState : (((float) ($position->qty ?? 0) > 0) ? 'open' : 'closed');

        return [
            'title' => strtoupper((string) ($position->symbol ?? 'Position')),
            'summary' => $statusSummary,
            'status' => $status,
            'details' => array_values(array_filter([
                ['label' => 'Side', 'value' => ucfirst((string) ($position->side ?? 'long'))],
                ['label' => 'Qty', 'value' => self::quantity($position->qty ?? null)],
                ['label' => 'Entry', 'value' => self::money($position->avg_entry_price ?? null)],
                ['label' => 'Current', 'value' => self::money($position->current_price ?? null)],
                ['label' => 'Unrealized', 'value' => self::signedMoney($position->unrealized_pl ?? null)],
                ['label' => 'Managed', 'value' => SafeDisplay::dateTime($position->last_managed_at, 'Not managed yet')],
            ], fn ($item) => $item['value'] !== 'Not available')),
        ];
    }

    protected static function mapOrderItem($order): array
    {
        $summary = SafeDisplay::sanitizedText(
            (string) ($order->status_summary ?: $order->broker_message ?: self::defaultOrderSummary($order)),
            'Order state recorded'
        );

        return [
            'title' => strtoupper((string) ($order->symbol ?? 'Order')),
            'summary' => $summary,
            'status' => strtolower((string) ($order->status ?? 'submitted')),
            'details' => array_values(array_filter([
                ['label' => 'Action', 'value' => ucfirst((string) ($order->request_action ?? 'review'))],
                ['label' => 'Side', 'value' => ucfirst((string) ($order->side ?? 'buy'))],
                ['label' => 'Qty', 'value' => self::quantity($order->qty ?? null)],
                ['label' => 'Filled', 'value' => self::quantity($order->filled_qty ?? null)],
                ['label' => 'Submitted', 'value' => SafeDisplay::dateTime($order->submitted_at, 'Not submitted yet')],
                ['label' => 'Filled At', 'value' => SafeDisplay::dateTime($order->filled_at, 'Not filled yet')],
            ], fn ($item) => $item['value'] !== 'Not available')),
        ];
    }

    protected static function activityFeedItems(Collection $activityLogs, Collection $botRuns, Collection $signals): Collection
    {
        return $activityLogs
            ->take(10)
            ->map(fn ($item) => self::mapActivityLogItem($item))
            ->merge($botRuns->take(8)->map(fn ($run) => self::mapBotRunItem($run)))
            ->merge($signals->take(8)->map(fn ($signal) => self::mapSignalItem($signal)))
            ->sortByDesc(fn ($item) => $item['sort_timestamp'] ?? 0)
            ->map(function (array $item): array {
                unset($item['sort_timestamp']);

                return $item;
            })
            ->values();
    }

    protected static function mapActivityLogItem(ActivityLog $item): array
    {
        $type = (string) ($item->type ?? 'activity');
        $summary = SafeDisplay::sanitizedText((string) data_get($item->context, 'safe_summary', $item->message), 'Runtime activity recorded');

        return [
            'title' => self::activityTitle($type),
            'summary' => self::customerFacingActivitySummary($type, $summary),
            'status' => (string) ($item->level ?? 'info'),
            'details' => [
                ['label' => 'When', 'value' => SafeDisplay::dateTime($item->created_at, 'Not available')],
            ],
            'sort_timestamp' => $item->created_at?->getTimestamp() ?? 0,
        ];
    }

    protected static function mapBotRunItem($run): array
    {
        return [
            'title' => self::botRunTitle((string) ($run->run_type ?? 'runtime')),
            'summary' => SafeDisplay::sanitizedText((string) data_get($run->summary, 'safe_summary', ucfirst((string) ($run->status ?? 'recorded'))), 'Runtime summary recorded'),
            'status' => (string) ($run->status ?? 'completed'),
            'details' => [
                ['label' => 'When', 'value' => SafeDisplay::dateTime($run->started_at, 'Not available')],
                ['label' => 'Stage', 'value' => ucfirst(str_replace('_', ' ', (string) ($run->run_type ?? 'runtime')))],
            ],
            'sort_timestamp' => $run->started_at?->getTimestamp() ?? $run->created_at?->getTimestamp() ?? 0,
        ];
    }

    protected static function mapSignalItem($signal): array
    {
        return [
            'title' => 'AI scanned '.strtoupper((string) ($signal->symbol ?? 'symbol')),
            'summary' => SafeDisplay::sanitizedText((string) data_get($signal->payload, 'public_summary', 'AI skipped setup'), 'Signal summary recorded'),
            'status' => (string) ($signal->status ?? 'recorded'),
            'details' => [
                ['label' => 'Timeframe', 'value' => strtoupper((string) ($signal->timeframe ?? '4H'))],
                ['label' => 'When', 'value' => SafeDisplay::dateTime($signal->generated_at, 'Not available')],
            ],
            'sort_timestamp' => $signal->generated_at?->getTimestamp() ?? $signal->created_at?->getTimestamp() ?? 0,
        ];
    }

    protected static function activityTitle(string $type): string
    {
        return match (true) {
            str_contains($type, 'bismel1_execution_') => 'AI execution update',
            str_contains($type, 'bismel1_position_') => 'AI position manager update',
            str_contains($type, 'scanner') => 'AI scanner update',
            default => 'AI activity update',
        };
    }

    protected static function customerFacingActivitySummary(string $type, string $summary): string
    {
        return match (true) {
            str_contains($type, 'bismel1_position_close') => 'AI closed on protection',
            str_contains($type, 'bismel1_position_add') => 'AI added position',
            str_contains($type, 'bismel1_position_hold') => 'AI held position',
            str_contains($type, 'bismel1_position_reconcile') => 'AI reconciled broker state',
            str_contains($type, 'bismel1_execution_submitted') => 'AI opened position',
            str_contains($type, 'bismel1_execution_skipped') => 'AI skipped setup',
            default => $summary,
        };
    }

    protected static function botRunTitle(string $runType): string
    {
        return match (true) {
            str_contains($runType, 'scan') => 'AI scanner run',
            str_contains($runType, 'execution') => 'AI execution run',
            str_contains($runType, 'position') => 'AI position manager run',
            default => 'AI runtime run',
        };
    }

    protected static function signalRepresentsRiskBlock($signal): bool
    {
        return (bool) data_get($signal->payload, 'safe_flags.risk_blocked', false)
            || (bool) (data_get($signal->payload, 'risk_engine.allowed', true) === false)
            || (string) data_get($signal->payload, 'risk_engine.status', '') === 'block_action';
    }

    protected static function defaultPositionSummary($position): string
    {
        if (str_contains(strtolower((string) ($position->management_state ?? '')), 'close')) {
            return 'AI closed on protection';
        }

        if (str_contains(strtolower((string) ($position->management_state ?? '')), 'add')) {
            return 'AI added position';
        }

        if (str_contains(strtolower((string) ($position->management_state ?? '')), 'reconcile')) {
            return 'AI reconciled broker state';
        }

        return 'AI held position';
    }

    protected static function defaultOrderSummary($order): string
    {
        return match (strtolower((string) ($order->status ?? 'submitted'))) {
            'filled', 'partially_filled' => 'Order filled with safe execution handling.',
            'failed', 'rejected', 'canceled', 'expired' => 'Order did not complete and requires review.',
            default => 'Order submitted with safe execution handling.',
        };
    }

    protected static function runtimeSummary(array $state, string $path, string $fallback): string
    {
        $automationSetting = $state['automation_setting'] ?? null;
        $settings = is_array($automationSetting?->settings) ? $automationSetting->settings : [];

        return SafeDisplay::sanitizedText((string) data_get($settings, $path, $fallback), $fallback);
    }

    protected static function quantity($value): string
    {
        if ($value === null || $value === '') {
            return 'Not available';
        }

        $formatted = rtrim(rtrim(number_format((float) $value, 6, '.', ''), '0'), '.');

        return $formatted === '' ? '0' : $formatted;
    }

    protected static function money($value): string
    {
        if ($value === null || $value === '') {
            return 'Not available';
        }

        return 'USD '.number_format((float) $value, 2, '.', ',');
    }

    protected static function signedMoney($value): string
    {
        if ($value === null || $value === '') {
            return 'Not available';
        }

        $amount = (float) $value;

        return ($amount >= 0 ? '+' : '-').'USD '.number_format(abs($amount), 2, '.', ',');
    }
}
