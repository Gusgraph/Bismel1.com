<?php
// اعوز بالله من الشياطين و ان يحضرون بسم الله الرحمن الرحيم الله لا إله إلا هو الحي القيوم
// Bismillahi ar-Rahmani ar-Rahim Audhu billahi min ash-shayatin wa an yahdurun Bismillah ar-Rahman ar-Rahim Allah la ilaha illa huwa al-hayy al-qayyum. Tamsa Allahu ala ayunihim
// version: x
// ======================================================
// - App Name: bismel1.com
// - Gusgraph LLC -
// - Author: Gus Kazem
// - https://Gusgraph.com
// - File Path: app/Support/Admin/Bismel1OperatorToolsService.php
// ======================================================

namespace App\Support\Admin;

use App\Models\Account;
use App\Models\ActivityLog;
use App\Models\AlpacaAccount;
use App\Models\AutomationSetting;
use App\Models\StrategyProfile;
use App\Models\User;
use App\Support\Automation\Bismel1PositionManager;
use App\Support\Automation\Bismel1RuntimeGuardrails;
use App\Support\Automation\Bismel1SmallSymbolScanner;
use App\Support\Billing\Bismel1EntitlementService;
use App\Support\Broker\AlpacaAccountSyncService;
use Illuminate\Support\Facades\DB;
use Throwable;

class Bismel1OperatorToolsService
{
    protected const OPERATOR_ACTION_LABELS = [
        'scan_now' => 'Run scanner now',
        'sync_broker_now' => 'Sync broker now',
        'reconcile_positions_now' => 'Reconcile positions now',
        'recheck_runtime_readiness_now' => 'Recheck runtime readiness now',
        'pause_automation' => 'Pause customer automation',
        'resume_automation' => 'Resume customer automation',
    ];

    public function __construct(
        protected Bismel1SmallSymbolScanner $scanner,
        protected AlpacaAccountSyncService $alpacaAccountSyncService,
        protected Bismel1PositionManager $positionManager,
        protected Bismel1EntitlementService $bismel1EntitlementService,
        protected Bismel1RuntimeGuardrails $bismel1RuntimeGuardrails,
    ) {}

    public function run(Account $account, User $operator, string $action): array
    {
        if (! $this->bismel1RuntimeGuardrails->acquireRuntimeLock('operator-action', $account->getKey(), [$action], 15)) {
            return $this->actionResult('blocked', 'operator action already in progress');
        }

        try {
        return match ($action) {
            'scan_now' => $this->scanNow($account, $operator),
            'sync_broker_now' => $this->syncBrokerNow($account, $operator),
            'reconcile_positions_now' => $this->reconcilePositionsNow($account, $operator),
            'recheck_runtime_readiness_now' => $this->recheckRuntimeReadinessNow($account, $operator),
            'pause_automation' => $this->pauseAutomation($account, $operator),
            'resume_automation' => $this->resumeAutomation($account, $operator),
            default => [
                'status' => 'failed',
                'summary' => 'Operator action failed',
                'heading' => 'Action failed',
                'tone' => 'amber',
            ],
        };
        } finally {
            $this->bismel1RuntimeGuardrails->releaseRuntimeLock('operator-action', $account->getKey(), [$action]);
        }
    }

    protected function scanNow(Account $account, User $operator): array
    {
        $entitlementSummary = $this->operatorActionEntitlementBlock($account, 'scan_now');

        if ($entitlementSummary !== null) {
            return $this->recordOperatorOutcome($account, $operator, 'scan_now', 'failed', $entitlementSummary);
        }

        try {
            $result = $this->scanner->scanAccount($account, null, [
                'run_type' => 'admin_operator_scan',
                'operator_action' => 'scan_now',
                'initiated_by' => 'admin_operator',
            ]);

            if (($result['status'] ?? null) !== 'completed') {
                return $this->recordOperatorOutcome($account, $operator, 'scan_now', 'failed', 'scanner trigger failed');
            }

            return $this->recordOperatorOutcome($account, $operator, 'scan_now', 'completed', 'scanner triggered');
        } catch (Throwable $exception) {
            return $this->recordOperatorOutcome($account, $operator, 'scan_now', 'failed', $this->safeFailureSummary('scan_now', $exception));
        }
    }

    protected function syncBrokerNow(Account $account, User $operator): array
    {
        $entitlementSummary = $this->operatorActionEntitlementBlock($account, 'sync_broker_now');

        if ($entitlementSummary !== null) {
            return $this->recordOperatorOutcome($account, $operator, 'sync_broker_now', 'failed', $entitlementSummary);
        }

        try {
            $result = $this->alpacaAccountSyncService->syncLatestForAccount($account);
            $success = in_array((string) ($result['status'] ?? 'failed'), ['verified', 'partial_success', 'fresh'], true);

            return $this->recordOperatorOutcome(
                $account,
                $operator,
                'sync_broker_now',
                $success ? 'completed' : 'failed',
                $success ? 'broker sync completed' : 'broker sync failed'
            );
        } catch (Throwable $exception) {
            return $this->recordOperatorOutcome($account, $operator, 'sync_broker_now', 'failed', $this->safeFailureSummary('sync_broker_now', $exception));
        }
    }

    protected function reconcilePositionsNow(Account $account, User $operator): array
    {
        $entitlementSummary = $this->operatorActionEntitlementBlock($account, 'reconcile_positions_now');

        if ($entitlementSummary !== null) {
            return $this->recordOperatorOutcome($account, $operator, 'reconcile_positions_now', 'failed', $entitlementSummary);
        }

        try {
            $result = $this->positionManager->manageAccount($account, null, [
                'run_type' => 'admin_operator_reconcile',
                'operator_action' => 'reconcile_positions_now',
                'initiated_by' => 'admin_operator',
            ]);

            if (($result['status'] ?? null) !== 'completed') {
                return $this->recordOperatorOutcome($account, $operator, 'reconcile_positions_now', 'failed', 'reconciliation failed');
            }

            return $this->recordOperatorOutcome($account, $operator, 'reconcile_positions_now', 'completed', 'reconciliation completed');
        } catch (Throwable $exception) {
            return $this->recordOperatorOutcome($account, $operator, 'reconcile_positions_now', 'failed', $this->safeFailureSummary('reconcile_positions_now', $exception));
        }
    }

    protected function recheckRuntimeReadinessNow(Account $account, User $operator): array
    {
        $automationSetting = $this->resolveAutomationSetting($account);
        $readiness = $this->readinessState($account, $automationSetting);

        $this->persistRuntimeState($account, $automationSetting, $readiness, 'recheck_runtime_readiness_now', $operator);

        return $this->recordOperatorOutcome(
            $account,
            $operator,
            'recheck_runtime_readiness_now',
            $readiness['result'],
            $readiness['result'] === 'completed' ? 'runtime readiness checked' : 'readiness check failed'
        );
    }

    protected function pauseAutomation(Account $account, User $operator): array
    {
        $entitlementSummary = $this->operatorActionEntitlementBlock($account, 'pause_automation');

        if ($entitlementSummary !== null) {
            return $this->recordOperatorOutcome($account, $operator, 'pause_automation', 'failed', $entitlementSummary);
        }

        $automationSetting = $this->resolveAutomationSetting($account, true);
        $checkedAt = now();
        $existingSettings = is_array($automationSetting->settings) ? $automationSetting->settings : [];
        $runtimeState = is_array($existingSettings['bismel1_runtime'] ?? null) ? $existingSettings['bismel1_runtime'] : [];

        $automationSetting->forceFill([
            'ai_enabled' => false,
            'scanner_enabled' => false,
            'execution_enabled' => false,
            'status' => 'draft',
            'run_health' => 'operator_paused',
            'last_checked_at' => $checkedAt,
            'settings' => array_merge($existingSettings, [
                'bismel1_runtime' => array_merge($runtimeState, [
                    'last_runtime_status' => 'stopped',
                    'last_runtime_summary' => 'automation paused',
                    'last_runtime_updated_at' => $checkedAt->toIso8601String(),
                    'last_stage' => 'operator_tools',
                    'last_stage_result' => 'paused',
                    'last_stage_summary' => 'automation paused',
                ]),
            ]),
        ])->save();

        return $this->recordOperatorOutcome($account, $operator, 'pause_automation', 'completed', 'automation paused');
    }

    protected function resumeAutomation(Account $account, User $operator): array
    {
        $entitlementSummary = $this->operatorActionEntitlementBlock($account, 'resume_automation');

        if ($entitlementSummary !== null) {
            return $this->recordOperatorOutcome($account, $operator, 'resume_automation', 'failed', $entitlementSummary);
        }

        $automationSetting = $this->resolveAutomationSetting($account, true);
        $readiness = $this->readinessState($account, $automationSetting);

        if ($readiness['result'] !== 'completed') {
            $this->persistRuntimeState($account, $automationSetting, $readiness, 'resume_automation', $operator);

            return $this->recordOperatorOutcome($account, $operator, 'resume_automation', 'failed', 'readiness check failed');
        }

        $existingSettings = is_array($automationSetting->settings) ? $automationSetting->settings : [];
        $runtimeState = is_array($existingSettings['bismel1_runtime'] ?? null) ? $existingSettings['bismel1_runtime'] : [];
        $checkedAt = now();

        $automationSetting->forceFill([
            'ai_enabled' => true,
            'scanner_enabled' => true,
            'execution_enabled' => (bool) data_get($this->bismel1EntitlementService->resolve($account), 'capabilities.can_use_execute', false),
            'status' => 'armed',
            'run_health' => 'operator_resumed',
            'last_checked_at' => $checkedAt,
            'settings' => array_merge($existingSettings, [
                'bismel1_runtime' => array_merge($runtimeState, [
                    'last_runtime_status' => 'active',
                    'last_runtime_summary' => 'waiting for next bar close',
                    'last_runtime_updated_at' => $checkedAt->toIso8601String(),
                    'last_stage' => 'operator_tools',
                    'last_stage_result' => 'resumed',
                    'last_stage_summary' => 'automation resumed',
                ]),
            ]),
        ])->save();

        return $this->recordOperatorOutcome($account, $operator, 'resume_automation', 'completed', 'automation resumed');
    }

    protected function resolveAutomationSetting(Account $account, bool $createIfMissing = false): ?AutomationSetting
    {
        $strategyProfile = $this->resolveStrategyProfile($account);

        $automationSetting = $account->automationSettings()
            ->when($strategyProfile, fn ($query) => $query->where(function ($inner) use ($strategyProfile): void {
                $inner->whereNull('strategy_profile_id')->orWhere('strategy_profile_id', $strategyProfile->getKey());
            }))
            ->latest('id')
            ->first();

        if ($automationSetting instanceof AutomationSetting || ! $createIfMissing) {
            return $automationSetting;
        }

        $automationSetting = new AutomationSetting([
            'account_id' => $account->getKey(),
            'name' => 'Primary Automation',
            'status' => 'draft',
            'risk_level' => 'balanced',
            'ai_enabled' => false,
            'scanner_enabled' => false,
            'execution_enabled' => false,
            'run_health' => 'operator_created',
            'settings' => [],
        ]);
        $automationSetting->account()->associate($account);
        $automationSetting->strategyProfile()->associate($strategyProfile);
        $automationSetting->save();

        return $automationSetting;
    }

    protected function resolveStrategyProfile(Account $account): ?StrategyProfile
    {
        return $account->strategyProfiles()
            ->where('engine', 'python')
            ->where('is_active', true)
            ->latest('id')
            ->first();
    }

    protected function resolveActiveAlpacaAccount(Account $account): ?AlpacaAccount
    {
        return $account->alpacaAccounts()
            ->where('is_active', true)
            ->latest('id')
            ->first();
    }

    protected function readinessState(Account $account, ?AutomationSetting $automationSetting): array
    {
        $entitlements = $this->bismel1EntitlementService->resolve($account);
        $strategyProfile = $this->resolveStrategyProfile($account);
        $alpacaAccount = $this->resolveActiveAlpacaAccount($account);
        $accountGuard = $this->bismel1RuntimeGuardrails->runtimeAccountGuard($alpacaAccount);
        $brokerReady = $accountGuard['allowed'];
        $strategyReady = $strategyProfile instanceof StrategyProfile;

        if (! ($entitlements['subscription_active'] ?? false)) {
            return [
                'result' => 'failed',
                'runtime_status' => 'blocked',
                'summary' => 'subscription inactive',
                'run_health' => 'customer_runtime_blocked',
            ];
        }

        if (! (bool) data_get($entitlements, 'capabilities.can_use_stocks_automation', false)) {
            return [
                'result' => 'failed',
                'runtime_status' => 'blocked',
                'summary' => 'plan does not include this automation mode',
                'run_health' => 'customer_runtime_blocked',
            ];
        }

        if (! $strategyReady) {
            return [
                'result' => 'failed',
                'runtime_status' => 'blocked',
                'summary' => 'strategy not mapped',
                'run_health' => 'customer_runtime_blocked',
            ];
        }

        if (! $brokerReady) {
            return [
                'result' => 'failed',
                'runtime_status' => 'blocked',
                'summary' => (string) ($accountGuard['summary'] ?? 'broker not ready'),
                'run_health' => 'customer_runtime_blocked',
            ];
        }

        return [
            'result' => 'completed',
            'runtime_status' => 'active',
            'summary' => 'waiting for next bar close',
            'run_health' => 'customer_runtime_ready',
        ];
    }

    protected function persistRuntimeState(Account $account, ?AutomationSetting $automationSetting, array $readiness, string $action, User $operator): void
    {
        $automationSetting = $automationSetting ?? $this->resolveAutomationSetting($account, true);

        if (! $automationSetting instanceof AutomationSetting) {
            return;
        }

        $checkedAt = now();
        $existingSettings = is_array($automationSetting->settings) ? $automationSetting->settings : [];
        $runtimeState = is_array($existingSettings['bismel1_runtime'] ?? null) ? $existingSettings['bismel1_runtime'] : [];

        $automationSetting->forceFill([
            'last_checked_at' => $checkedAt,
            'run_health' => $readiness['run_health'] ?? 'customer_runtime_blocked',
            'settings' => array_merge($existingSettings, [
                'bismel1_runtime' => array_merge($runtimeState, [
                    'last_runtime_status' => $readiness['runtime_status'] ?? 'blocked',
                    'last_runtime_summary' => $readiness['summary'] ?? 'readiness check failed',
                    'last_runtime_updated_at' => $checkedAt->toIso8601String(),
                    'last_stage' => 'operator_tools',
                    'last_stage_result' => $readiness['result'] ?? 'failed',
                    'last_stage_summary' => $readiness['summary'] ?? 'readiness check failed',
                ]),
            ]),
        ])->save();
    }

    protected function recordOperatorOutcome(Account $account, User $operator, string $action, string $result, string $summary): array
    {
        $checkedAt = now();
        $automationSetting = $this->resolveAutomationSetting($account, $action !== 'sync_broker_now');

        DB::transaction(function () use ($account, $operator, $action, $result, $summary, $checkedAt, $automationSetting): void {
            if ($automationSetting instanceof AutomationSetting) {
                $existingSettings = is_array($automationSetting->settings) ? $automationSetting->settings : [];
                $operatorState = is_array($existingSettings['bismel1_operator'] ?? null) ? $existingSettings['bismel1_operator'] : [];

                $automationSetting->forceFill([
                    'settings' => array_merge($existingSettings, [
                        'bismel1_operator' => array_merge($operatorState, [
                            'last_action' => $action,
                            'last_action_result' => $result,
                            'last_action_summary' => $summary,
                            'last_action_at' => $checkedAt->toIso8601String(),
                            'last_action_user_id' => $operator->getKey(),
                        ]),
                    ]),
                ])->save();
            }

            ActivityLog::query()->create([
                'account_id' => $account->getKey(),
                'user_id' => $operator->getKey(),
                'type' => 'bismel1_operator_'.$action,
                'level' => $result === 'completed' ? 'info' : 'warning',
                'message' => ucfirst(str_replace('_', ' ', $action)).': '.$summary,
                'context' => [
                    'action' => $action,
                    'result' => $result,
                    'safe_summary' => $summary,
                ],
            ]);
        });

        return $this->actionResult($result, $summary);
    }

    protected function operatorActionEntitlementBlock(Account $account, string $action): ?string
    {
        $entitlements = $this->bismel1EntitlementService->resolve($account);

        if (! ($entitlements['subscription_active'] ?? false)) {
            return 'subscription inactive';
        }

        $automationSetting = $this->resolveAutomationSetting($account);
        $paused = $automationSetting instanceof AutomationSetting
            && (! (bool) ($automationSetting->ai_enabled ?? false)
                || ! (bool) ($automationSetting->scanner_enabled ?? false)
                || (string) ($automationSetting->status ?? 'draft') === 'draft');
        $active = $automationSetting instanceof AutomationSetting
            && (bool) ($automationSetting->ai_enabled ?? false)
            && (bool) ($automationSetting->scanner_enabled ?? false)
            && (string) ($automationSetting->status ?? 'draft') === 'armed';

        return match ($action) {
            'scan_now' => (bool) data_get($entitlements, 'capabilities.can_use_scanner', false)
                ? ($paused ? 'automation paused' : null)
                : 'plan does not include this automation mode',
            'reconcile_positions_now',
            'resume_automation' => (bool) data_get($entitlements, 'capabilities.can_use_stocks_automation', false)
                ? match (true) {
                    $action === 'resume_automation' && $active => 'automation already active',
                    $action === 'reconcile_positions_now' && $paused => 'automation paused',
                    default => null,
                }
                : 'plan does not include this automation mode',
            'pause_automation' => $paused ? 'automation already paused' : null,
            'sync_broker_now',
            'recheck_runtime_readiness_now' => null,
            default => 'Operator action failed',
        };
    }

    public function allowedActionsFor(Account $account): array
    {
        $automationSetting = $this->resolveAutomationSetting($account);
        $alpacaAccount = $this->resolveActiveAlpacaAccount($account);

        return collect(self::OPERATOR_ACTION_LABELS)
            ->map(function (string $label, string $action) use ($account, $automationSetting, $alpacaAccount): array {
                $blockedSummary = $this->operatorActionEntitlementBlock($account, $action);
                $inProgress = $this->bismel1RuntimeGuardrails->runtimeLockActive('operator-action', $account->getKey(), [$action]);
                $state = $inProgress ? 'in_progress' : ($blockedSummary === null ? 'ready' : 'blocked');

                return [
                    'action' => $action,
                    'label' => $label,
                    'allowed' => $state === 'ready',
                    'state' => $state,
                    'state_label' => match ($state) {
                        'in_progress' => 'In progress',
                        'blocked' => 'Blocked',
                        default => 'Ready',
                    },
                    'state_status' => match ($state) {
                        'in_progress' => ['label' => 'In progress', 'tone' => 'amber', 'value' => 'review'],
                        'blocked' => ['label' => 'Blocked', 'tone' => 'rose', 'value' => 'blocked'],
                        default => ['label' => 'Ready', 'tone' => 'emerald', 'value' => 'ready'],
                    },
                    'blocked_summary' => $inProgress ? 'operator action already in progress' : $blockedSummary,
                    'guidance' => $this->actionGuidance($account, $action, $automationSetting, $alpacaAccount, $state, $blockedSummary),
                    'confirm_message' => $this->confirmMessage($action, $alpacaAccount),
                ];
            })
            ->values()
            ->all();
    }

    protected function safeFailureSummary(string $action, Throwable $exception): string
    {
        $message = strtolower(trim($exception->getMessage()));

        if (str_contains($message, 'subscription inactive')) {
            return 'subscription inactive';
        }

        if (str_contains($message, 'plan does not include this automation mode')) {
            return 'plan does not include this automation mode';
        }

        if (str_contains($message, 'strategy profile')) {
            return 'readiness check failed';
        }

        if (str_contains($message, 'alpaca account') || str_contains($message, 'broker')) {
            return $action === 'sync_broker_now' ? 'broker sync failed' : 'readiness check failed';
        }

        return match ($action) {
            'scan_now' => 'scanner trigger failed',
            'sync_broker_now' => 'broker sync failed',
            'reconcile_positions_now' => 'reconciliation failed',
            default => 'readiness check failed',
        };
    }

    protected function actionGuidance(Account $account, string $action, ?AutomationSetting $automationSetting, ?AlpacaAccount $alpacaAccount, string $state, ?string $blockedSummary): string
    {
        if ($state === 'in_progress') {
            return 'This action is already running for this workspace. Wait for the current run to finish before trying again.';
        }

        if ($state === 'blocked') {
            return match ($blockedSummary) {
                'automation paused' => 'Resume automation first if you want scanner or reconciliation work to continue from this workspace.',
                'automation already paused' => 'Automation is already paused for this workspace. Use resume when you are ready to restart monitoring.',
                'automation already active' => 'Automation is already active for this workspace. Review recent status before running another action.',
                'plan does not include this automation mode' => 'This workspace needs the right paid plan before this operator action can be used.',
                'subscription inactive' => 'Restore an active subscription before using this operator action.',
                default => 'Review the blocked reason, then resolve the workspace issue before trying again.',
            };
        }

        $environment = strtolower((string) ($alpacaAccount?->environment ?? 'paper'));

        return match ($action) {
            'scan_now' => 'Use this when setup is ready and you want an immediate scanner pass outside the normal scheduler window.',
            'sync_broker_now' => 'Use this first when broker status or positions look stale and you want a fresh broker snapshot.',
            'reconcile_positions_now' => 'Use this after broker sync when local positions or orders need to be brought back into alignment.',
            'recheck_runtime_readiness_now' => 'Use this to refresh readiness truth before deciding whether to resume automation or escalate an issue.',
            'pause_automation' => $environment === 'live'
                ? 'Use this to stop automated monitoring and execution on a live workspace while you review the account.'
                : 'Use this to stop automated monitoring and execution on this workspace while you review the account.',
            'resume_automation' => $environment === 'live'
                ? 'Live broker access is connected. Confirm broker readiness and recent status before resuming automation.'
                : 'Use this when broker readiness, plan access, and strategy mapping are all in place and you want automation to continue.',
            default => 'Review the current workspace status before running this action.',
        };
    }

    protected function confirmMessage(string $action, ?AlpacaAccount $alpacaAccount): ?string
    {
        $environment = strtolower((string) ($alpacaAccount?->environment ?? 'paper'));

        return match ($action) {
            'scan_now' => 'Run the scanner now for this workspace?',
            'sync_broker_now' => 'Sync broker data now for this workspace?',
            'reconcile_positions_now' => 'Reconcile broker and local positions now for this workspace?',
            'recheck_runtime_readiness_now' => 'Run a readiness check now for this workspace?',
            'pause_automation' => $environment === 'live'
                ? 'Pause automation for this live workspace?'
                : 'Pause automation for this workspace?',
            'resume_automation' => $environment === 'live'
                ? 'Resume automation for this live workspace? Confirm the broker is ready before continuing.'
                : 'Resume automation for this workspace?',
            default => null,
        };
    }

    protected function actionResult(string $status, string $summary): array
    {
        $normalized = strtolower($status);

        return [
            'status' => $status,
            'summary' => $summary,
            'heading' => match ($normalized) {
                'completed' => 'Action completed',
                'blocked' => 'Action blocked',
                default => 'Action failed',
            },
            'tone' => match ($normalized) {
                'completed' => 'emerald',
                'blocked' => 'amber',
                default => 'rose',
            },
        ];
    }
}
