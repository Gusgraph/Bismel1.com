<?php
// اعوز بالله من الشياطين و ان يحضرون بسم الله الرحمن الرحيم الله لا إله إلا هو الحي القيوم
// Bismillahi ar-Rahmani ar-Rahim Audhu billahi min ash-shayatin wa an yahdurun Bismillah ar-Rahman ar-Rahim Allah la ilaha illa huwa al-hayy al-qayyum. Tamsa Allahu ala ayunihim
// version: x
// ======================================================
// - App Name: gusgraph-trading
// - Gusgraph LLC -
// - Author: Gus Kazem
// - https://Gusgraph.com
// - File Path: app/Support/Automation/Bismel1ExecutionEngine.php
// ======================================================

namespace App\Support\Automation;

use App\Models\Account;
use App\Models\ActivityLog;
use App\Models\AlpacaAccount;
use App\Models\AlpacaOrder;
use App\Models\AlpacaPosition;
use App\Models\AutomationSetting;
use App\Models\BotRun;
use App\Models\BrokerConnection;
use App\Models\BrokerCredential;
use App\Models\Signal;
use App\Models\StrategyProfile;
use App\Support\Billing\Bismel1EntitlementService;
use App\Support\Broker\AlpacaClient;
use Illuminate\Support\Facades\DB;

class Bismel1ExecutionEngine
{
    protected const STALE_RUNTIME_MINUTES = 30;

    public function __construct(
        protected AlpacaClient $alpacaClient,
        protected Bismel1EntitlementService $bismel1EntitlementService,
        protected Bismel1RuntimeGuardrails $bismel1RuntimeGuardrails,
    ) {}

    public function executeSignal(Account $account, Signal $signal, array $triggerContext = []): array
    {
        $strategyProfile = $signal->strategyProfile()->first()
            ?? $account->strategyProfiles()->where('engine', 'python')->where('is_active', true)->latest('id')->first();
        $automationSetting = $account->automationSettings()
            ->when($strategyProfile, fn ($query) => $query->where(function ($inner) use ($strategyProfile): void {
                $inner->whereNull('strategy_profile_id')->orWhere('strategy_profile_id', $strategyProfile->getKey());
            }))
            ->latest('id')
            ->first();
        $brokerConnection = $account->brokerConnections()->where('broker', 'alpaca')->latest('id')->first();
        $alpacaAccount = $account->alpacaAccounts()
            ->where('broker_connection_id', $brokerConnection?->getKey())
            ->latest('id')
            ->first();
        $credential = $brokerConnection?->brokerCredentials()->latest('id')->first();
        $startedAt = now();
        $this->cleanupStaleRuns($account, $signal);
        $runningRun = $this->existingRunningRun($account, $signal);

        if ($runningRun instanceof BotRun) {
            return [
                'status' => 'skipped',
                'bot_run_id' => $runningRun->getKey(),
                'message' => 'Execution skipped because another broker action is already in progress for this signal.',
            ];
        }

        $botRun = BotRun::query()->create([
            'account_id' => $account->getKey(),
            'strategy_profile_id' => $strategyProfile?->getKey(),
            'automation_setting_id' => $automationSetting?->getKey(),
            'alpaca_account_id' => $alpacaAccount?->getKey(),
            'run_type' => 'execution',
            'status' => 'running',
            'risk_level' => $automationSetting?->risk_level ?? 'balanced',
            'started_at' => $startedAt,
            'summary' => [
                'engine' => 'bismel1',
                'scope' => 'alpaca_execution',
                'signal_id' => $signal->getKey(),
                'symbol' => $signal->symbol,
                'visibility' => 'safe_summary_only',
                'trigger' => $triggerContext,
            ],
        ]);

        $preflight = $this->preflight($signal, $automationSetting, $alpacaAccount, $credential);

        if (! $preflight['allowed']) {
            return $this->recordSkipped($account, $automationSetting, $signal, $botRun, $preflight['reason_code'], $preflight['public_summary']);
        }

        $request = $this->buildOrderRequest($signal, $alpacaAccount);
        $submit = $this->alpacaClient->submitOrder($credential, $request['broker_payload']);

        if (($submit['status'] ?? null) !== 'verified' || ! is_array($submit['order'] ?? null)) {
            return $this->recordFailure(
                $account,
                $automationSetting,
                $signal,
                $botRun,
                $alpacaAccount,
                $request,
                $submit,
            );
        }

        return $this->recordSubmitted(
            $account,
            $automationSetting,
            $signal,
            $botRun,
            $alpacaAccount,
            $strategyProfile,
            $request,
            $submit,
        );
    }

    protected function preflight(
        Signal $signal,
        ?AutomationSetting $automationSetting,
        ?AlpacaAccount $alpacaAccount,
        ?BrokerCredential $credential,
    ): array {
        $action = (string) $signal->status;
        $dedupeClientOrderId = $this->bismel1RuntimeGuardrails->duplicateOrderKey($signal, $action);
        $riskAllowed = (bool) data_get($signal->payload, 'risk_engine.allowed', false);
        $existingOrder = AlpacaOrder::query()
            ->where(function ($query) use ($signal, $dedupeClientOrderId): void {
                $query->where('signal_id', $signal->getKey())
                    ->orWhere('client_order_id', $dedupeClientOrderId);
            })
            ->whereIn('status', ['submitted', 'new', 'accepted', 'partially_filled', 'filled'])
            ->latest('id')
            ->first();
        $credentialPayload = is_array($credential?->credential_payload) ? $credential->credential_payload : [];
        $accessMode = strtolower((string) ($credential?->access_mode ?: ($credentialPayload['access_mode'] ?? 'read_only')));
        $executeAllowed = $this->bismel1EntitlementService->allowsExecute($signal->account);
        $environmentGuard = $this->bismel1RuntimeGuardrails->executionEnvironmentGuard(
            (string) ($alpacaAccount?->environment ?? ($credential?->environment ?? 'paper'))
        );

        $reasonCode = match (true) {
            ! in_array($action, ['open', 'add', 'close'], true) => 'unsupported_signal_action',
            ! $riskAllowed => 'risk_not_approved',
            ! $automationSetting instanceof AutomationSetting => 'automation_settings_missing',
            ! $executeAllowed => 'subscription_execute_not_allowed',
            ! ($automationSetting->ai_enabled ?? false) => 'automation_disabled',
            ! ($automationSetting->execution_enabled ?? false) => 'execution_disabled',
            (string) ($automationSetting->status ?? 'draft') !== 'armed' => 'automation_not_armed',
            ! $alpacaAccount instanceof AlpacaAccount => 'broker_account_missing',
            ! $environmentGuard['allowed'] => 'paper_trading_guardrail_blocked',
            strtolower((string) ($alpacaAccount->data_feed ?? 'iex')) !== 'iex' => 'broker_feed_unavailable',
            ! in_array((string) ($alpacaAccount->status ?? 'inactive'), ['active'], true) => 'broker_account_unready',
            $credential === null => 'missing_broker_credentials',
            $accessMode === 'read_only' => 'credential_trade_access_missing',
            $existingOrder instanceof AlpacaOrder => 'duplicate_execution_protection',
            default => null,
        };

        return [
            'allowed' => $reasonCode === null,
            'reason_code' => $reasonCode ?? 'execution_allowed',
            'public_summary' => $reasonCode === null
                ? $this->successSummary($action)
                : $this->blockedSummary($reasonCode, $environmentGuard['summary'] ?? null),
        ];
    }

    protected function buildOrderRequest(Signal $signal, AlpacaAccount $alpacaAccount): array
    {
        $action = (string) $signal->status;
        $side = $action === 'close' ? 'sell' : 'buy';
        $position = AlpacaPosition::query()
            ->where('account_id', $signal->account_id)
            ->where('alpaca_account_id', $alpacaAccount->getKey())
            ->where('symbol', $signal->symbol)
            ->first();

        $qty = match ($action) {
            'close' => max(0.0, (float) ($position?->qty ?? 0.0)),
            'add' => 1.0,
            default => 1.0,
        };
        $qty = $qty > 0 ? $qty : 1.0;
        $clientOrderId = $this->clientOrderId($signal, $action);

        return [
            'client_order_id' => $clientOrderId,
            'action' => $action,
            'side' => $side,
            'qty' => number_format($qty, 6, '.', ''),
            'broker_payload' => [
                'symbol' => $signal->symbol,
                'side' => $side,
                'type' => 'market',
                'time_in_force' => 'day',
                'qty' => number_format($qty, 6, '.', ''),
                'client_order_id' => $clientOrderId,
            ],
        ];
    }

    protected function recordSkipped(
        Account $account,
        ?AutomationSetting $automationSetting,
        Signal $signal,
        BotRun $botRun,
        string $reasonCode,
        string $publicSummary,
    ): array {
        $finishedAt = now();

        DB::transaction(function () use ($account, $automationSetting, $signal, $botRun, $reasonCode, $publicSummary, $finishedAt): void {
            $botRun->forceFill([
                'status' => 'skipped',
                'finished_at' => $finishedAt,
                'runtime_seconds' => max(0, $botRun->started_at?->diffInSeconds($finishedAt) ?? 0),
                'summary' => array_merge(is_array($botRun->summary) ? $botRun->summary : [], [
                    'execution_status' => 'skipped',
                    'safe_summary' => $publicSummary,
                    'reason_code' => $reasonCode,
                ]),
                'error_message' => null,
            ])->save();

            $this->recordActivity($account, 'bismel1_execution_skipped', $publicSummary, [
                'signal_id' => $signal->getKey(),
                'symbol' => $signal->symbol,
                'reason_code' => $reasonCode,
            ]);

            if ($automationSetting instanceof AutomationSetting) {
                $automationSetting->forceFill([
                    'last_checked_at' => $finishedAt,
                    'run_health' => 'execution_skipped',
                    'settings' => $this->mergedExecutionSettings($automationSetting, $finishedAt, 'skipped', $publicSummary),
                ])->save();
            }
        });

        return [
            'status' => 'skipped',
            'bot_run_id' => $botRun->getKey(),
            'message' => $publicSummary,
        ];
    }

    protected function recordFailure(
        Account $account,
        ?AutomationSetting $automationSetting,
        Signal $signal,
        BotRun $botRun,
        AlpacaAccount $alpacaAccount,
        array $request,
        array $submit,
    ): array {
        $finishedAt = now();
        $summary = 'Broker action failed before an order could be accepted.';

        DB::transaction(function () use ($account, $automationSetting, $signal, $botRun, $alpacaAccount, $request, $submit, $finishedAt, $summary): void {
            AlpacaOrder::query()->create([
                'account_id' => $account->getKey(),
                'alpaca_account_id' => $alpacaAccount->getKey(),
                'broker_connection_id' => $alpacaAccount->broker_connection_id,
                'strategy_profile_id' => $signal->strategy_profile_id,
                'signal_id' => $signal->getKey(),
                'bot_run_id' => $botRun->getKey(),
                'request_action' => $request['action'],
                'alpaca_order_id' => 'local-failed-'.$request['client_order_id'],
                'client_order_id' => $request['client_order_id'],
                'symbol' => $signal->symbol,
                'asset_class' => 'equity',
                'side' => $request['side'],
                'order_type' => 'market',
                'time_in_force' => 'day',
                'status' => 'failed',
                'status_summary' => $summary,
                'broker_message' => (string) ($submit['message'] ?? 'Broker request failed.'),
                'qty' => $request['qty'],
                'failed_at' => $finishedAt,
                'synced_at' => $finishedAt,
            ]);

            $botRun->forceFill([
                'status' => 'failed',
                'finished_at' => $finishedAt,
                'runtime_seconds' => max(0, $botRun->started_at?->diffInSeconds($finishedAt) ?? 0),
                'summary' => array_merge(is_array($botRun->summary) ? $botRun->summary : [], [
                    'execution_status' => 'failed',
                    'safe_summary' => $summary,
                    'broker_action_summary' => $this->safeBrokerActionSummary($signal->status, $signal->symbol, 'failed'),
                ]),
                'error_message' => (string) ($submit['message'] ?? 'Broker request failed.'),
            ])->save();

            $this->recordActivity($account, 'bismel1_execution_failed', $summary, [
                'signal_id' => $signal->getKey(),
                'symbol' => $signal->symbol,
                'broker_status' => $submit['status'] ?? 'request_failed',
            ]);

            if ($automationSetting instanceof AutomationSetting) {
                $automationSetting->forceFill([
                    'last_checked_at' => $finishedAt,
                    'run_health' => 'execution_failed',
                    'settings' => $this->mergedExecutionSettings($automationSetting, $finishedAt, 'failed', $summary),
                ])->save();
            }
        });

        return [
            'status' => 'failed',
            'bot_run_id' => $botRun->getKey(),
            'message' => $summary,
        ];
    }

    protected function recordSubmitted(
        Account $account,
        ?AutomationSetting $automationSetting,
        Signal $signal,
        BotRun $botRun,
        AlpacaAccount $alpacaAccount,
        ?StrategyProfile $strategyProfile,
        array $request,
        array $submit,
    ): array {
        $finishedAt = now();
        $orderPayload = $submit['order'];
        $orderStatus = strtolower((string) ($orderPayload['status'] ?? 'submitted'));
        $localStatus = in_array($orderStatus, ['accepted', 'pending_new', 'new', 'partially_filled', 'filled'], true)
            ? 'submitted'
            : 'rejected';
        $safeSummary = $localStatus === 'submitted'
            ? $this->safeBrokerActionSummary($signal->status, $signal->symbol, 'submitted')
            : 'Broker rejected the requested action before execution confirmation.';

        DB::transaction(function () use ($account, $automationSetting, $signal, $botRun, $alpacaAccount, $strategyProfile, $request, $orderPayload, $submit, $finishedAt, $localStatus, $safeSummary): void {
            AlpacaOrder::query()->updateOrCreate(
                ['alpaca_order_id' => (string) ($orderPayload['id'] ?? $request['client_order_id'])],
                [
                    'account_id' => $account->getKey(),
                    'alpaca_account_id' => $alpacaAccount->getKey(),
                    'broker_connection_id' => $alpacaAccount->broker_connection_id,
                    'strategy_profile_id' => $strategyProfile?->getKey(),
                    'signal_id' => $signal->getKey(),
                    'bot_run_id' => $botRun->getKey(),
                    'request_action' => $request['action'],
                    'client_order_id' => $orderPayload['client_order_id'] ?? $request['client_order_id'],
                    'alpaca_asset_id' => $orderPayload['asset_id'] ?? null,
                    'symbol' => $signal->symbol,
                    'asset_class' => strtolower((string) ($orderPayload['asset_class'] ?? 'equity')),
                    'side' => $orderPayload['side'] ?? $request['side'],
                    'order_type' => $orderPayload['type'] ?? 'market',
                    'time_in_force' => $orderPayload['time_in_force'] ?? 'day',
                    'status' => $localStatus,
                    'status_summary' => $safeSummary,
                    'broker_message' => (string) ($submit['message'] ?? 'Broker accepted the order request.'),
                    'qty' => $orderPayload['qty'] ?? $request['qty'],
                    'filled_qty' => $orderPayload['filled_qty'] ?? null,
                    'notional' => $orderPayload['notional'] ?? null,
                    'limit_price' => $orderPayload['limit_price'] ?? null,
                    'stop_price' => $orderPayload['stop_price'] ?? null,
                    'filled_avg_price' => $orderPayload['filled_avg_price'] ?? null,
                    'submitted_at' => $orderPayload['submitted_at'] ?? $finishedAt,
                    'filled_at' => $orderPayload['filled_at'] ?? null,
                    'canceled_at' => $orderPayload['canceled_at'] ?? null,
                    'expired_at' => $orderPayload['expired_at'] ?? null,
                    'failed_at' => $localStatus === 'rejected' ? $finishedAt : null,
                    'synced_at' => $finishedAt,
                ]
            );

            $botRun->forceFill([
                'status' => $localStatus === 'submitted' ? 'completed' : 'failed',
                'finished_at' => $finishedAt,
                'runtime_seconds' => max(0, $botRun->started_at?->diffInSeconds($finishedAt) ?? 0),
                'summary' => array_merge(is_array($botRun->summary) ? $botRun->summary : [], [
                    'execution_status' => $localStatus,
                    'safe_summary' => $safeSummary,
                    'broker_action_summary' => $safeSummary,
                    'order_reference' => $orderPayload['client_order_id'] ?? $request['client_order_id'],
                ]),
                'error_message' => $localStatus === 'submitted' ? null : (string) ($submit['message'] ?? 'Broker rejected the order request.'),
            ])->save();

            $this->recordActivity($account, 'bismel1_execution_'.$localStatus, $safeSummary, [
                'signal_id' => $signal->getKey(),
                'symbol' => $signal->symbol,
                'action' => $request['action'],
                'client_order_id' => $orderPayload['client_order_id'] ?? $request['client_order_id'],
            ]);

            if ($automationSetting instanceof AutomationSetting) {
                $automationSetting->forceFill([
                    'last_checked_at' => $finishedAt,
                    'run_health' => $localStatus === 'submitted' ? 'execution_submitted' : 'execution_failed',
                    'settings' => $this->mergedExecutionSettings($automationSetting, $finishedAt, $localStatus, $safeSummary),
                ])->save();
            }
        });

        return [
            'status' => $localStatus,
            'bot_run_id' => $botRun->getKey(),
            'message' => $safeSummary,
        ];
    }

    protected function clientOrderId(Signal $signal, string $action): string
    {
        return $this->bismel1RuntimeGuardrails->duplicateOrderKey($signal, $action);
    }

    protected function successSummary(string $action): string
    {
        return match ($action) {
            'close' => 'Execution prepared a safe broker close request.',
            'add' => 'Execution prepared a safe broker add request.',
            default => 'Execution prepared a safe broker open request.',
        };
    }

    protected function blockedSummary(string $reasonCode, ?string $environmentGuardSummary = null): string
    {
        return match ($reasonCode) {
            'execution_disabled', 'automation_not_armed', 'automation_disabled' => 'Execution skipped because automation is not armed for live broker action.',
            'subscription_execute_not_allowed' => 'Execution skipped because plan does not include this automation mode.',
            'duplicate_execution_protection' => 'Execution skipped because a recent broker action already exists for this setup.',
            'credential_trade_access_missing', 'missing_broker_credentials' => 'Execution skipped because broker trade access is not ready.',
            'paper_trading_guardrail_blocked' => $environmentGuardSummary ?: 'Execution skipped because paper-trading guardrails blocked the broker environment.',
            'risk_not_approved' => 'Execution skipped because the action was not risk-approved.',
            default => 'Execution skipped because broker readiness checks were not satisfied.',
        };
    }

    protected function safeBrokerActionSummary(string $action, string $symbol, string $status): string
    {
        $verb = match ($action) {
            'close' => 'close',
            'add' => 'add',
            default => 'open',
        };

        return 'Broker '.$verb.' request for '.$symbol.' was '.$status.' with safe execution handling.';
    }

    protected function mergedExecutionSettings(AutomationSetting $automationSetting, $checkedAt, string $result, string $summary): array
    {
        $settings = is_array($automationSetting->settings) ? $automationSetting->settings : [];
        $current = is_array($settings['bismel1_execution'] ?? null) ? $settings['bismel1_execution'] : [];
        $currentRuntime = is_array($settings['bismel1_runtime'] ?? null) ? $settings['bismel1_runtime'] : [];

        $settings['bismel1_execution'] = array_merge($current, [
            'last_execution_attempt_at' => $checkedAt?->toIso8601String(),
            'last_execution_attempt_result' => $result,
            'last_execution_attempt_summary' => $summary,
        ]);

        if ($result !== 'skipped') {
            $settings['bismel1_execution'] = array_merge($settings['bismel1_execution'], [
                'last_execution_at' => $checkedAt?->toIso8601String(),
                'last_execution_result' => $result,
                'last_execution_summary' => $summary,
            ]);
        }

        $runtimeStatus = match ($result) {
            'failed' => 'blocked',
            'submitted' => 'active',
            default => (string) ($currentRuntime['last_runtime_status'] ?? 'active'),
        };
        $runtimeSummary = match ($result) {
            'failed' => 'Execution requires review before the next paper-trading cycle.',
            'submitted' => 'Broker action submitted with safe paper-trading handling.',
            default => (string) ($currentRuntime['last_runtime_summary'] ?? $summary),
        };

        $settings['bismel1_runtime'] = array_merge($currentRuntime, [
            'last_runtime_status' => $runtimeStatus,
            'last_runtime_summary' => $runtimeSummary,
            'last_runtime_updated_at' => $checkedAt?->toIso8601String(),
            'last_run_at' => $checkedAt?->toIso8601String(),
            'last_stage' => 'execution',
            'last_stage_result' => $result,
            'last_stage_summary' => $summary,
        ]);

        return $settings;
    }

    protected function cleanupStaleRuns(Account $account, Signal $signal): void
    {
        $cutoff = now()->subMinutes(self::STALE_RUNTIME_MINUTES);

        BotRun::query()
            ->where('account_id', $account->getKey())
            ->where('run_type', 'execution')
            ->where('status', 'running')
            ->where('summary->signal_id', $signal->getKey())
            ->where(function ($query) use ($cutoff): void {
                $query->where('started_at', '<=', $cutoff)
                    ->orWhereNull('started_at');
            })
            ->get()
            ->each(function (BotRun $run) use ($account, $signal): void {
                $finishedAt = now();
                $run->forceFill([
                    'status' => 'failed',
                    'finished_at' => $finishedAt,
                    'runtime_seconds' => max(0, ($run->started_at ?? $finishedAt)->diffInSeconds($finishedAt)),
                    'summary' => array_merge(is_array($run->summary) ? $run->summary : [], [
                        'execution_status' => 'failed',
                        'safe_summary' => 'Execution validation closed a stale running broker action before retry.',
                    ]),
                    'error_message' => 'Stale running execution was closed before retry.',
                ])->save();

                $this->recordActivity($account, 'bismel1_execution_recovery', 'Execution validation closed a stale running broker action before retry.', [
                    'signal_id' => $signal->getKey(),
                    'stale_run_id' => $run->getKey(),
                    'safe_summary' => 'Execution validation closed a stale running broker action before retry.',
                ]);
            });
    }

    protected function existingRunningRun(Account $account, Signal $signal): ?BotRun
    {
        return BotRun::query()
            ->where('account_id', $account->getKey())
            ->where('run_type', 'execution')
            ->where('status', 'running')
            ->where('summary->signal_id', $signal->getKey())
            ->orderByDesc('started_at')
            ->orderByDesc('id')
            ->first();
    }

    protected function recordActivity(Account $account, string $type, string $message, array $context = []): void
    {
        ActivityLog::query()->create([
            'account_id' => $account->getKey(),
            'type' => $type,
            'level' => 'info',
            'message' => $message,
            'context' => $context,
        ]);
    }
}
