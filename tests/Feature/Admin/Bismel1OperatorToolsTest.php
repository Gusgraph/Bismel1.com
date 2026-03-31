<?php
// اعوز بالله من الشياطين و ان يحضرون بسم الله الرحمن الرحيم الله لا إله إلا هو الحي القيوم
// Bismillahi ar-Rahmani ar-Rahim Audhu billahi min ash-shayatin wa an yahdurun Bismillah ar-Rahman ar-Rahim Allah la ilaha illa huwa al-hayy al-qayyum. Tamsa Allahu ala ayunihim
// version: x
// ======================================================
// - App Name: bismel1.com
// - Gusgraph LLC -
// - Author: Gus Kazem
// - https://Gusgraph.com
// - File Path: tests/Feature/Admin/Bismel1OperatorToolsTest.php
// ======================================================

namespace Tests\Feature\Admin;

use App\Models\ActivityLog;
use App\Models\AlpacaAccount;
use App\Models\AutomationSetting;
use App\Models\BrokerConnection;
use App\Models\BrokerCredential;
use App\Models\StrategyProfile;
use App\Support\Automation\Bismel1PositionManager;
use App\Support\Automation\Bismel1SmallSymbolScanner;
use App\Support\Broker\AlpacaAccountSyncService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Tests\Concerns\CreatesAccessContext;
use Tests\Concerns\CreatesBismel1Entitlements;
use Tests\TestCase;

class Bismel1OperatorToolsTest extends TestCase
{
    use CreatesAccessContext;
    use CreatesBismel1Entitlements;
    use RefreshDatabase;

    public function test_admin_account_detail_page_renders_manual_operator_tools_safely(): void
    {
        [$user, $account, $automationSetting] = $this->createReadyOperatorAccount();

        $settings = is_array($automationSetting->settings) ? $automationSetting->settings : [];
        $automationSetting->forceFill([
            'settings' => array_merge($settings, [
                'bismel1_operator' => [
                    'last_action' => 'pause_automation',
                    'last_action_result' => 'completed',
                    'last_action_summary' => 'automation paused',
                    'last_action_at' => now()->subMinute()->toIso8601String(),
                    'last_action_user_id' => $user->getKey(),
                ],
            ]),
        ])->save();

        ActivityLog::query()->create([
            'account_id' => $account->getKey(),
            'user_id' => $user->getKey(),
            'type' => 'bismel1_operator_pause_automation',
            'level' => 'info',
            'message' => 'Pause automation: automation paused',
            'context' => [
                'action' => 'pause_automation',
                'result' => 'completed',
                'safe_summary' => 'automation paused',
            ],
        ]);

        $response = $this->actingAs($user)->get(route('admin.account-detail.index', ['account' => $account]));

        $response->assertOk();
        $response->assertSeeText('Manual Operator Tools');
        $response->assertSeeText('Run scanner now');
        $response->assertSeeText('Sync broker now');
        $response->assertSeeText('Reconcile positions now');
        $response->assertSeeText('Pause customer automation');
        $response->assertSeeText('Resume customer automation');
        $response->assertSeeText('Operator Action Summary');
        $response->assertSeeText('Recent Operator Actions');
        $response->assertSeeText('automation paused');
        $response->assertDontSeeText('EMA');
    }

    public function test_admin_can_trigger_scan_now_action(): void
    {
        [$user, $account] = $this->createReadyOperatorAccount();

        $this->mock(Bismel1SmallSymbolScanner::class, function ($mock) use ($account): void {
            $mock->shouldReceive('scanAccount')
                ->once()
                ->withArgs(fn ($resolvedAccount, $symbols, $context) => $resolvedAccount->is($account)
                    && $symbols === null
                    && data_get($context, 'operator_action') === 'scan_now')
                ->andReturn(['status' => 'completed']);
        });

        $response = $this->actingAs($user)->post(route('admin.account-detail.operator-action', ['account' => $account]), [
            'action' => 'scan_now',
        ]);

        $response->assertRedirect(route('admin.account-detail.index', ['account' => $account]));
        $response->assertSessionHas('status', 'scanner triggered');
        $response->assertSessionHas('status_meta.heading', 'Action completed');
        $response->assertSessionHas('status_meta.tone', 'emerald');
        $this->assertDatabaseHas('activity_logs', [
            'account_id' => $account->getKey(),
            'user_id' => $user->getKey(),
            'type' => 'bismel1_operator_scan_now',
            'message' => 'Scan now: scanner triggered',
        ]);
    }

    public function test_admin_can_trigger_sync_and_reconcile_actions(): void
    {
        [$user, $account] = $this->createReadyOperatorAccount();

        $this->mock(AlpacaAccountSyncService::class, function ($mock) use ($account): void {
            $mock->shouldReceive('syncLatestForAccount')
                ->once()
                ->withArgs(fn ($resolvedAccount) => $resolvedAccount->is($account))
                ->andReturn(['status' => 'verified']);
        });

        $syncResponse = $this->actingAs($user)->post(route('admin.account-detail.operator-action', ['account' => $account]), [
            'action' => 'sync_broker_now',
        ]);

        $syncResponse->assertRedirect(route('admin.account-detail.index', ['account' => $account]));
        $syncResponse->assertSessionHas('status', 'broker sync completed');
        $syncResponse->assertSessionHas('status_meta.heading', 'Action completed');

        $this->mock(Bismel1PositionManager::class, function ($mock) use ($account): void {
            $mock->shouldReceive('manageAccount')
                ->once()
                ->withArgs(fn ($resolvedAccount, $symbols, $context) => $resolvedAccount->is($account)
                    && $symbols === null
                    && data_get($context, 'operator_action') === 'reconcile_positions_now')
                ->andReturn(['status' => 'completed']);
        });

        $reconcileResponse = $this->actingAs($user)->post(route('admin.account-detail.operator-action', ['account' => $account]), [
            'action' => 'reconcile_positions_now',
        ]);

        $reconcileResponse->assertRedirect(route('admin.account-detail.index', ['account' => $account]));
        $reconcileResponse->assertSessionHas('status', 'reconciliation completed');
        $reconcileResponse->assertSessionHas('status_meta.heading', 'Action completed');
        $this->assertDatabaseHas('activity_logs', [
            'account_id' => $account->getKey(),
            'type' => 'bismel1_operator_sync_broker_now',
            'message' => 'Sync broker now: broker sync completed',
        ]);
        $this->assertDatabaseHas('activity_logs', [
            'account_id' => $account->getKey(),
            'type' => 'bismel1_operator_reconcile_positions_now',
            'message' => 'Reconcile positions now: reconciliation completed',
        ]);
    }

    public function test_admin_can_pause_and_resume_customer_automation(): void
    {
        [$user, $account, $automationSetting] = $this->createReadyOperatorAccount();

        $pauseResponse = $this->actingAs($user)->post(route('admin.account-detail.operator-action', ['account' => $account]), [
            'action' => 'pause_automation',
        ]);

        $pauseResponse->assertRedirect(route('admin.account-detail.index', ['account' => $account]));
        $pauseResponse->assertSessionHas('status', 'automation paused');
        $pauseResponse->assertSessionHas('status_meta.heading', 'Action completed');

        $automationSetting->refresh();
        $this->assertFalse((bool) $automationSetting->ai_enabled);
        $this->assertFalse((bool) $automationSetting->scanner_enabled);
        $this->assertFalse((bool) $automationSetting->execution_enabled);
        $this->assertSame('draft', $automationSetting->status);

        $resumeResponse = $this->actingAs($user)->post(route('admin.account-detail.operator-action', ['account' => $account]), [
            'action' => 'resume_automation',
        ]);

        $resumeResponse->assertRedirect(route('admin.account-detail.index', ['account' => $account]));
        $resumeResponse->assertSessionHas('status', 'automation resumed');
        $resumeResponse->assertSessionHas('status_meta.heading', 'Action completed');

        $automationSetting->refresh();
        $this->assertTrue((bool) $automationSetting->ai_enabled);
        $this->assertTrue((bool) $automationSetting->scanner_enabled);
        $this->assertTrue((bool) $automationSetting->execution_enabled);
        $this->assertSame('armed', $automationSetting->status);
        $this->assertDatabaseHas('activity_logs', [
            'account_id' => $account->getKey(),
            'type' => 'bismel1_operator_resume_automation',
            'message' => 'Resume automation: automation resumed',
        ]);
    }

    public function test_admin_resume_action_is_blocked_when_plan_does_not_include_automation_mode(): void
    {
        [$user, $account, $automationSetting] = $this->createReadyOperatorAccount('BISMILLAH_AI_SCANNER');

        $response = $this->actingAs($user)->post(route('admin.account-detail.operator-action', ['account' => $account]), [
            'action' => 'resume_automation',
        ]);

        $response->assertRedirect(route('admin.account-detail.index', ['account' => $account]));
        $response->assertSessionHas('status', 'plan does not include this automation mode');
        $response->assertSessionHas('status_meta.heading', 'Action failed');

        $automationSetting->refresh();
        $this->assertDatabaseHas('activity_logs', [
            'account_id' => $account->getKey(),
            'type' => 'bismel1_operator_resume_automation',
            'message' => 'Resume automation: plan does not include this automation mode',
        ]);
    }

    public function test_admin_reconcile_action_is_blocked_when_automation_is_paused(): void
    {
        [$user, $account, $automationSetting] = $this->createReadyOperatorAccount();
        $automationSetting->forceFill([
            'ai_enabled' => false,
            'scanner_enabled' => false,
            'execution_enabled' => false,
            'status' => 'draft',
        ])->save();

        $response = $this->actingAs($user)->post(route('admin.account-detail.operator-action', ['account' => $account]), [
            'action' => 'reconcile_positions_now',
        ]);

        $response->assertRedirect(route('admin.account-detail.index', ['account' => $account]));
        $response->assertSessionHas('status', 'automation paused');
        $response->assertSessionHas('status_meta.heading', 'Action failed');
    }

    public function test_admin_operator_action_is_blocked_when_the_same_action_lock_is_held(): void
    {
        [$user, $account] = $this->createReadyOperatorAccount();
        Cache::put('bismel1:runtime:operator-action:'.$account->getKey().':scan-now', now()->toIso8601String(), now()->addMinutes(15));

        $response = $this->actingAs($user)->post(route('admin.account-detail.operator-action', ['account' => $account]), [
            'action' => 'scan_now',
        ]);

        $response->assertRedirect(route('admin.account-detail.index', ['account' => $account]));
        $response->assertSessionHas('status', 'operator action already in progress');
        $response->assertSessionHas('status_meta.heading', 'Action blocked');
        $response->assertSessionHas('status_meta.tone', 'amber');
    }

    protected function createReadyOperatorAccount(string $basePlanCode = 'BISMILLAH1_BOT_EXECUTE_BASIC'): array
    {
        [$user, $account] = $this->createAccessContext();
        $this->seedConfirmedBismel1Subscription($account, $basePlanCode);

        $connection = BrokerConnection::query()->create([
            'account_id' => $account->getKey(),
            'name' => 'Operator Broker',
            'broker' => 'alpaca',
            'status' => 'connected',
            'connected_at' => now()->subHour(),
            'last_synced_at' => now()->subMinutes(10),
        ]);

        $credential = BrokerCredential::query()->create([
            'broker_connection_id' => $connection->getKey(),
            'label' => 'Operator Credential',
            'status' => 'saved',
            'credential_payload' => [
                'access_key_id' => 'OPERATORKEY-9988',
                'access_secret' => 'operator-secret-safe',
                'environment' => 'paper',
            ],
            'last_used_at' => now()->subMinutes(5),
        ]);

        $strategyProfile = StrategyProfile::query()->create([
            'account_id' => $account->getKey(),
            'name' => 'Bismel1 Swing',
            'mode' => 'stocks_swing',
            'timeframe' => '4h_1d',
            'symbol_scope' => 'watchlist',
            'style' => 'balanced',
            'engine' => 'python',
            'is_active' => true,
        ]);

        $automationSetting = AutomationSetting::query()->create([
            'account_id' => $account->getKey(),
            'strategy_profile_id' => $strategyProfile->getKey(),
            'name' => 'Primary Automation',
            'status' => 'armed',
            'risk_level' => 'balanced',
            'ai_enabled' => true,
            'scanner_enabled' => true,
            'execution_enabled' => true,
            'run_health' => 'customer_runtime_ready',
            'settings' => [
                'bismel1_runtime' => [
                    'last_runtime_status' => 'active',
                    'last_runtime_summary' => 'waiting for next bar close',
                    'last_runtime_updated_at' => now()->subMinutes(2)->toIso8601String(),
                ],
            ],
        ]);

        AlpacaAccount::query()->create([
            'account_id' => $account->getKey(),
            'broker_connection_id' => $connection->getKey(),
            'broker_credential_id' => $credential->getKey(),
            'name' => 'Primary Paper Account',
            'environment' => 'paper',
            'data_feed' => 'iex',
            'status' => 'active',
            'sync_status' => 'fresh',
            'is_primary' => true,
            'is_active' => true,
            'alpaca_account_id' => 'paper-'.$account->getKey(),
            'account_number' => 'PAPER-'.$account->getKey(),
            'buying_power' => 10000,
            'cash' => 10000,
            'equity' => 10000,
            'last_synced_at' => now()->subMinutes(3),
            'last_account_sync_at' => now()->subMinutes(3),
            'last_positions_sync_at' => now()->subMinutes(3),
            'last_orders_sync_at' => now()->subMinutes(3),
            'metadata' => [
                'positions_sync_result' => 'verified',
                'orders_sync_result' => 'verified',
            ],
        ]);

        return [$user, $account->fresh(), $automationSetting->fresh()];
    }
}
