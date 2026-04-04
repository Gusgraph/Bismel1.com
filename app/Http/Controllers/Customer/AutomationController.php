<?php
// اعوز بالله من الشياطين و ان يحضرون بسم الله الرحمن الرحيم الله لا إله إلا هو الحي القيوم
// Bismillahi ar-Rahmani ar-Rahim Audhu billahi min ash-shayatin wa an yahdurun Bismillah ar-Rahman ar-Rahim Allah la ilaha illa huwa al-hayy al-qayyum. Tamsa Allahu ala ayunihim
// version: x
// ======================================================
// - App Name: bismel1.com
// - Gusgraph LLC -
// - Author: Gus Kazem
// - https://Gusgraph.com
// - File Path: app/Http/Controllers/Customer/AutomationController.php
// ======================================================

namespace App\Http\Controllers\Customer;

use App\Http\Controllers\Controller;
use App\Http\Requests\Customer\UpdateAutomationSettingsRequest;
use App\Models\AlpacaAccount;
use App\Models\AutomationSetting;
use App\Support\Automation\Bismel1RuntimeGuardrails;
use App\Support\Billing\Bismel1EntitlementService;
use App\Support\Customer\CurrentCustomerAccountResolver;
use App\Support\Navigation\CustomerNavigation;
use App\Support\ViewData\AutomationPageData;

class AutomationController extends Controller
{
    public function index(
        CurrentCustomerAccountResolver $currentCustomerAccountResolver,
        Bismel1EntitlementService $bismel1EntitlementService,
        Bismel1RuntimeGuardrails $bismel1RuntimeGuardrails,
    )
    {
        $account = $currentCustomerAccountResolver->resolveForPreset(request()->user(), 'automation');
        $brokerConnections = $account?->brokerConnections ?? collect();
        $brokerCredentials = $brokerConnections->flatMap->brokerCredentials;
        $licenses = $account?->apiLicenses ?? collect();
        $apiKeys = $licenses->flatMap->apiKeys;
        $activityLogs = $account?->activityLogs?->sortByDesc(fn ($item) => $item->created_at?->getTimestamp() ?? 0)->values() ?? collect();
        $automationSetting = $account?->automationSettings
            ?->sortByDesc(fn ($item) => ($item->updated_at?->getTimestamp() ?? 0))
            ->first();
        $strategyProfile = $automationSetting?->strategyProfile
            ?? $account?->strategyProfiles?->sortByDesc(fn ($item) => ($item->is_active ? 1 : 0) + (($item->updated_at?->getTimestamp() ?? 0) / 1000000000000))->first();
        $botRuns = $account?->botRuns
            ?->sortByDesc(fn ($item) => $item->started_at?->getTimestamp() ?? $item->created_at?->getTimestamp() ?? 0)
            ->values() ?? collect();
        $alpacaAccounts = $account?->alpacaAccounts
            ?->sortByDesc(fn ($item) => $item->last_synced_at?->getTimestamp() ?? $item->created_at?->getTimestamp() ?? 0)
            ->values() ?? collect();
        $alpacaAccount = $alpacaAccounts->first(fn ($item) => $item instanceof AlpacaAccount && $item->is_active) ?? $alpacaAccounts->first();
        $signals = $account?->signals
            ?->sortByDesc(fn ($item) => $item->generated_at?->getTimestamp() ?? $item->created_at?->getTimestamp() ?? 0)
            ->values() ?? collect();
        $positions = $account?->alpacaPositions
            ?->sortByDesc(fn ($item) => $item->last_managed_at?->getTimestamp() ?? $item->updated_at?->getTimestamp() ?? 0)
            ->values() ?? collect();
        $orders = $account?->alpacaOrders
            ?->sortByDesc(fn ($item) => $item->submitted_at?->getTimestamp() ?? $item->created_at?->getTimestamp() ?? 0)
            ->values() ?? collect();

        $entitlements = $bismel1EntitlementService->resolve($account);
        $brokerGuard = $bismel1RuntimeGuardrails->runtimeAccountGuard($alpacaAccount);
        $data = AutomationPageData::make($account, [
            'automation_setting' => $automationSetting,
            'strategy_profile' => $strategyProfile,
            'bot_runs' => $botRuns,
            'broker_connections' => $brokerConnections,
            'broker_credentials' => $brokerCredentials,
            'alpaca_account' => $alpacaAccount,
            'alpaca_accounts' => $alpacaAccounts,
            'signals' => $signals,
            'positions' => $positions,
            'orders' => $orders,
            'licenses' => $licenses,
            'api_keys' => $apiKeys,
            'activity_logs' => $activityLogs,
            'entitlements' => $entitlements,
            'broker_guard' => $brokerGuard,
        ]);

        return view('customer.automation.index', [
            'navItems' => CustomerNavigation::items(),
            'page' => $data['page'],
            'summary' => $data['summary'],
            'form' => $data['form'],
            'primeStocksProduct' => $data['primeStocksProduct'],
            'primeStocksStatusItems' => $data['primeStocksStatusItems'],
            'primeStocksConceptItems' => $data['primeStocksConceptItems'],
            'automationState' => $data['automationState'],
            'runWindow' => $data['runWindow'],
            'healthItems' => $data['healthItems'],
            'runtimeItems' => $data['runtimeItems'],
            'recentActivityItems' => $data['recentActivityItems'],
            'linkageItems' => $data['linkageItems'],
            'relatedLinks' => $data['relatedLinks'],
            'hasAutomationData' => $data['hasAutomationData'],
        ]);
    }

    public function update(
        UpdateAutomationSettingsRequest $request,
        CurrentCustomerAccountResolver $currentCustomerAccountResolver,
        Bismel1EntitlementService $bismel1EntitlementService,
        Bismel1RuntimeGuardrails $bismel1RuntimeGuardrails,
    )
    {
        $account = $currentCustomerAccountResolver->resolveCurrent($request->user());

        if (! $account) {
            return redirect()
                ->route('customer.automation.index')
                ->with('status', 'Automation settings were not saved because no current customer workspace record is available.');
        }

        $validated = $request->validated();
        $actionMode = (string) ($validated['action_mode'] ?? 'save');
        $strategyProfile = $account->strategyProfiles()
            ->where('is_active', true)
            ->latest('id')
            ->first();
        $brokerConnection = $account->brokerConnections()
            ->where('broker', 'alpaca')
            ->latest('id')
            ->first();
        $alpacaAccount = $account->alpacaAccounts()
            ->when($brokerConnection, fn ($query) => $query->where('broker_connection_id', $brokerConnection->getKey()))
            ->latest('id')
            ->first();

        $automationSetting = AutomationSetting::query()
            ->where('account_id', $account->getKey())
            ->latest('id')
            ->first() ?? new AutomationSetting([
                'account_id' => $account->getKey(),
            ]);
        $existingSettings = is_array($automationSetting->settings) ? $automationSetting->settings : [];
        $runtimeState = is_array($existingSettings['bismel1_runtime'] ?? null) ? $existingSettings['bismel1_runtime'] : [];
        $schedulerState = is_array($existingSettings['bismel1_scheduler'] ?? null) ? $existingSettings['bismel1_scheduler'] : [];
        $brokerGuard = $bismel1RuntimeGuardrails->runtimeAccountGuard($alpacaAccount);
        $brokerReady = (bool) ($brokerConnection && ($brokerGuard['allowed'] ?? false));
        $strategyReady = $strategyProfile !== null && (bool) ($strategyProfile->is_active ?? false);
        $entitlements = $bismel1EntitlementService->resolve($account);
        $automationEntitled = (bool) data_get($entitlements, 'capabilities.can_use_stocks_automation', false);
        $entitlementBlockedSummary = $bismel1EntitlementService->automationBlockedSummary($account);

        $aiEnabled = (bool) ($automationSetting->ai_enabled ?? false);
        $status = (string) ($validated['status'] ?? ($automationSetting->status ?? 'draft'));
        $scannerEnabled = (bool) ($automationSetting->scanner_enabled ?? false);
        $executionEnabled = (bool) ($automationSetting->execution_enabled ?? false);
        $runtimeStatus = (string) ($runtimeState['last_runtime_status'] ?? 'stopped');
        $runtimeSummary = (string) ($runtimeState['last_runtime_summary'] ?? 'AI stopped');
        $timestamp = now()->toIso8601String();

        if ($actionMode === 'start') {
            if ($automationEntitled) {
                $aiEnabled = true;
                $scannerEnabled = true;
                $status = $brokerReady && $strategyReady ? 'armed' : 'review';
                $runtimeStatus = $brokerReady && $strategyReady ? 'active' : 'blocked';
                $runtimeSummary = $brokerReady && $strategyReady
                    ? ((string) ($schedulerState['next_intended_run'] ?? '') !== '' ? 'waiting for next bar close' : 'AI active')
                    : (! $brokerReady ? (string) ($brokerGuard['summary'] ?? 'broker not ready') : 'strategy not mapped');
            } else {
                $aiEnabled = false;
                $scannerEnabled = false;
                $executionEnabled = false;
                $status = 'review';
                $runtimeStatus = 'blocked';
                $runtimeSummary = $entitlementBlockedSummary;
            }
            $runtimeState['automation_enabled_at'] = $timestamp;
        } elseif ($actionMode === 'stop') {
            $aiEnabled = false;
            $scannerEnabled = false;
            $executionEnabled = false;
            $status = 'draft';
            $runtimeStatus = 'stopped';
            $runtimeSummary = 'AI stopped';
            $runtimeState['automation_disabled_at'] = $timestamp;
        } else {
            if ($aiEnabled && $scannerEnabled && $automationEntitled) {
                $runtimeStatus = $brokerReady && $strategyReady ? 'active' : 'blocked';
                $runtimeSummary = $brokerReady && $strategyReady
                    ? ((string) ($schedulerState['next_intended_run'] ?? '') !== '' ? 'waiting for next bar close' : 'AI active')
                    : (! $brokerReady ? (string) ($brokerGuard['summary'] ?? 'broker not ready') : 'strategy not mapped');
            } elseif ($aiEnabled && $scannerEnabled) {
                $aiEnabled = false;
                $scannerEnabled = false;
                $executionEnabled = false;
                $status = 'review';
                $runtimeStatus = 'blocked';
                $runtimeSummary = $entitlementBlockedSummary;
            } else {
                $runtimeStatus = 'stopped';
                $runtimeSummary = 'AI stopped';
            }
        }

        $runtimeState = array_merge($runtimeState, [
            'last_runtime_status' => $runtimeStatus,
            'last_runtime_summary' => $runtimeSummary,
            'last_runtime_updated_at' => $timestamp,
            'last_run_at' => optional($account->botRuns()->latest('started_at')->first()?->started_at)->toIso8601String(),
            'next_intended_run_at' => $schedulerState['next_intended_run'] ?? null,
            'entitlement_summary' => $entitlementBlockedSummary,
        ]);

        $automationSetting->fill([
            'name' => $validated['name'],
            'ai_enabled' => $aiEnabled,
            'status' => $status,
            'risk_level' => $validated['risk_level'],
            'run_health' => $runtimeStatus === 'active' ? 'customer_runtime_ready' : ($runtimeStatus === 'blocked' ? 'customer_runtime_blocked' : 'customer_runtime_stopped'),
            'scanner_enabled' => $scannerEnabled,
            'execution_enabled' => $executionEnabled,
            'last_checked_at' => now(),
            'settings' => array_merge($existingSettings, [
                'managed_via' => 'customer_automation_update',
                'bismel1_runtime' => $runtimeState,
            ]),
        ]);
        $automationSetting->account()->associate($account);
        $automationSetting->strategyProfile()->associate($strategyProfile);
        $automationSetting->save();

        return redirect()
            ->route('customer.automation.index')
            ->with('status', $actionMode === 'start'
                ? ($automationEntitled
                    ? 'Automation runtime was started for the current workspace.'
                    : 'Automation runtime was not started because plan does not include this automation mode.')
                : ($actionMode === 'stop'
                    ? 'Automation runtime was stopped for the current workspace.'
                    : 'Automation settings were saved to the current workspace configuration.'));
    }
}
