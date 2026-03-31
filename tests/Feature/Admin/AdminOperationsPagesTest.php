<?php
// اعوز بالله من الشياطين و ان يحضرون بسم الله الرحمن الرحيم الله لا إله إلا هو الحي القيوم
// Bismillahi ar-Rahmani ar-Rahim Audhu billahi min ash-shayatin wa an yahdurun Bismillah ar-Rahman ar-Rahim Allah la ilaha illa huwa al-hayy al-qayyum. Tamsa Allahu ala ayunihim
// version: x
// ======================================================
// - App Name: gusgraph-trading
// - Gusgraph LLC -
// - Author: Gus Kazem
// - https://Gusgraph.com
// - File Path: tests/Feature/Admin/AdminOperationsPagesTest.php
// ======================================================

namespace Tests\Feature\Admin;

use App\Models\Account;
use App\Models\ActivityLog;
use App\Models\AlpacaAccount;
use App\Models\ApiKey;
use App\Models\ApiLicense;
use App\Models\AutomationSetting;
use App\Models\BotRun;
use App\Models\AuditLog;
use App\Models\BrokerConnection;
use App\Models\BrokerCredential;
use App\Models\Invoice;
use App\Models\Signal;
use App\Models\StrategyProfile;
use App\Models\Subscription;
use App\Models\SubscriptionPlan;
use App\Models\SystemSetting;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Concerns\CreatesAccessContext;
use Tests\Concerns\CreatesBismel1Entitlements;
use Tests\TestCase;

class AdminOperationsPagesTest extends TestCase
{
    use CreatesAccessContext;
    use CreatesBismel1Entitlements;
    use RefreshDatabase;

    public function test_admin_system_update_persists_the_single_settings_record(): void
    {
        [$user] = $this->createAccessContext();

        $response = $this->actingAs($user)->put(route('admin.system.update'), [
            'runtime_mode' => 'local',
            'review_channel' => 'manual',
            'status_level' => 'medium',
        ]);

        $response->assertRedirect(route('admin.system.index'));
        $response->assertSessionHas('status', 'Platform settings were saved to the local admin system record.');

        $this->assertDatabaseHas('system_settings', [
            'runtime_mode' => 'local',
            'review_channel' => 'manual',
            'status_level' => 'medium',
        ]);
        $this->assertSame(1, SystemSetting::query()->count());

        $pageResponse = $this->actingAs($user)->get(route('admin.system.index'));

        $pageResponse->assertOk();
        $pageResponse->assertSeeText('Runtime Mode');
        $pageResponse->assertSeeText('local');
        $pageResponse->assertSeeText('Review Channel');
        $pageResponse->assertSeeText('manual');
        $pageResponse->assertSeeText('Status Level');
        $pageResponse->assertSeeText('Medium');
    }

    public function test_admin_system_update_overwrites_the_existing_single_settings_record_without_creating_a_duplicate(): void
    {
        [$user] = $this->createAccessContext();

        $existing = SystemSetting::query()->create([
            'runtime_mode' => 'local',
            'review_channel' => 'manual',
            'status_level' => 'low',
        ]);

        $response = $this->actingAs($user)->put(route('admin.system.update'), [
            'runtime_mode' => 'review',
            'review_channel' => 'ops',
            'status_level' => 'elevated',
        ]);

        $response->assertStatus(302);
        $this->assertSame(route('admin.system.index'), $response->headers->get('Location'));
        $this->assertSame(1, SystemSetting::query()->count());
        $this->assertDatabaseHas('system_settings', [
            'id' => $existing->id,
            'runtime_mode' => 'review',
            'review_channel' => 'ops',
            'status_level' => 'elevated',
        ]);
    }

    public function test_admin_system_page_renders_real_db_backed_platform_state_signals(): void
    {
        [$user, $account] = $this->createAccessContext();

        SystemSetting::query()->create([
            'runtime_mode' => 'local',
            'review_channel' => 'manual',
            'status_level' => 'high',
        ]);

        $plan = SubscriptionPlan::factory()->create([
            'name' => 'System State Plan',
        ]);

        $subscription = Subscription::query()->create([
            'account_id' => $account->id,
            'subscription_plan_id' => $plan->id,
            'status' => 'active',
            'starts_at' => now()->subDay(),
        ]);

        $connection = BrokerConnection::query()->create([
            'account_id' => $account->id,
            'name' => 'System State Broker',
            'broker' => 'placeholder_primary',
            'status' => 'connected',
            'connected_at' => now()->subHour(),
        ]);

        $license = ApiLicense::query()->create([
            'account_id' => $account->id,
            'name' => 'System State License',
            'status' => 'active',
            'starts_at' => now()->subDay(),
        ]);

        ApiKey::query()->create([
            'api_license_id' => $license->id,
            'name' => 'System State Key',
            'key_hash' => hash('sha256', 'system-state-key'),
            'secret_hint' => 'system-state-secret',
            'status' => 'active',
        ]);

        AuditLog::query()->create([
            'account_id' => $account->id,
            'action' => 'account_reviewed',
            'target_type' => 'account',
            'target_id' => (string) $account->id,
            'summary' => 'Admin reviewed system-linked account state',
        ]);

        ActivityLog::query()->create([
            'account_id' => $account->id,
            'type' => 'system_state_reviewed',
            'level' => 'info',
            'message' => 'Admin reviewed platform state summary',
        ]);

        $response = $this->actingAs($user)->get(route('admin.system.index'));

        $response->assertOk();
        $response->assertSeeText('Current System Settings');
        $response->assertSeeText('local');
        $response->assertSeeText('manual');
        $response->assertSeeText('High');
        $response->assertSeeText('Platform State Signals');
        $response->assertSeeText('Accounts');
        $response->assertSeeText('Subscriptions');
        $response->assertSeeText('Broker Connections');
        $response->assertSeeText('API Licenses');
        $response->assertSeeText('API Keys');
        $response->assertSeeText('Audit Logs');
        $response->assertSeeText('Activity Logs');
        $response->assertSeeText('Persisted Settings Record');
        $response->assertSeeText('Present');
        $response->assertSeeText('Platform Record Coverage');
        $response->assertDontSeeText('system-state-secret');
    }

    public function test_admin_accounts_page_renders_real_db_backed_account_summary(): void
    {
        [$user, $account] = $this->createAccessContext();

        $plan = SubscriptionPlan::factory()->create([
            'name' => 'Admin Visibility Plan',
        ]);

        Subscription::query()->create([
            'account_id' => $account->id,
            'subscription_plan_id' => $plan->id,
            'status' => 'active',
            'starts_at' => now()->subDay(),
        ]);

        ApiLicense::query()->create([
            'account_id' => $account->id,
            'name' => 'Admin License',
            'status' => 'active',
            'starts_at' => now()->subDay(),
        ]);

        $response = $this->actingAs($user)->get(route('admin.accounts.index'));

        $response->assertOk();
        $response->assertSeeText('Admin Accounts');
        $response->assertSeeText($account->name);
        $response->assertSeeText('Owner: '.$user->name);
        $response->assertSeeText('Members: 1');
        $response->assertSeeText('Licenses: 1');
        $response->assertSeeText('Plan: Admin Visibility Plan');
    }

    public function test_admin_accounts_page_links_to_a_specific_account_detail_route(): void
    {
        [$user, $account] = $this->createAccessContext();

        $response = $this->actingAs($user)->get(route('admin.accounts.index'));

        $response->assertOk();
        $response->assertSee(route('admin.account-detail.index', ['account' => $account]), false);
    }

    public function test_admin_accounts_page_shows_a_clean_empty_state_when_no_account_records_exist(): void
    {
        [$user] = $this->createAccessContext();
        Account::query()->delete();

        $response = $this->actingAs($user)->get(route('admin.accounts.index'));

        $response->assertOk();
        $response->assertSeeText('No local accounts were found yet, so the page stays readable with defensive empty states.');
    }

    public function test_admin_licenses_page_renders_real_db_backed_license_inventory_with_masked_tokens(): void
    {
        [$user, $account] = $this->createAccessContext();

        $license = ApiLicense::query()->create([
            'account_id' => $account->id,
            'name' => 'Admin License',
            'status' => 'active',
            'starts_at' => now()->subDay(),
            'expires_at' => now()->addDays(30),
        ]);

        ApiKey::query()->create([
            'api_license_id' => $license->id,
            'name' => 'Admin Key',
            'key_hash' => hash('sha256', 'admin-license|token-local-7890'),
            'secret_hint' => 'token-local-7890',
            'status' => 'ready',
            'last_used_at' => now()->subHour(),
        ]);

        $response = $this->actingAs($user)->get(route('admin.licenses.index'));

        $response->assertOk();
        $response->assertSeeText('Admin Licenses');
        $response->assertSeeText('Admin License');
        $response->assertSeeText($account->name);
        $response->assertSeeText('Starts');
        $response->assertSeeText('Expires');
        $response->assertSeeText('Admin Key');
        $response->assertSeeText('Encrypted token ending in ***7890');
        $response->assertSeeText('Last Used');
        $response->assertDontSeeText('token-local-7890');
    }

    public function test_admin_licenses_page_shows_a_clean_empty_state_when_no_records_exist(): void
    {
        [$user] = $this->createAccessContext();

        $response = $this->actingAs($user)->get(route('admin.licenses.index'));

        $response->assertOk();
        $response->assertSeeText('No License Records Yet');
        $response->assertSeeText('No local API license or API key records are available yet for admin visibility.');
    }

    public function test_admin_audit_page_renders_real_db_backed_activity_and_audit_rows(): void
    {
        [$user, $account] = $this->createAccessContext();

        ActivityLog::query()->create([
            'account_id' => $account->id,
            'user_id' => $user->id,
            'type' => 'settings_update',
            'level' => 'info',
            'message' => 'Customer settings were updated locally with token=secret-token-123.',
            'context' => ['scope' => 'local'],
        ]);

        AuditLog::query()->create([
            'account_id' => $account->id,
            'user_id' => $user->id,
            'action' => 'license_saved',
            'target_type' => ApiLicense::class,
            'target_id' => 1,
            'summary' => 'Stored masked API key metadata only with access_secret=super-secret-value.',
            'context' => ['scope' => 'local'],
        ]);

        $response = $this->actingAs($user)->get(route('admin.audit.index'));

        $response->assertOk();
        $response->assertSeeText('Admin Audit');
        $response->assertSeeText('Settings update');
        $response->assertSeeText($account->name);
        $response->assertSeeText($user->name);
        $response->assertSeeText('License saved');
        $response->assertSeeText('Customer settings were updated locally with token=[masked]');
        $response->assertSeeText('ApiLicense #1');
        $response->assertSeeText('Stored masked API key metadata only with access_secret=[masked]');
        $response->assertDontSeeText('secret-token-123');
        $response->assertDontSeeText('super-secret-value');
    }

    public function test_admin_audit_page_shows_a_clean_empty_state_when_no_records_exist(): void
    {
        [$user] = $this->createAccessContext();

        $response = $this->actingAs($user)->get(route('admin.audit.index'));

        $response->assertOk();
        $response->assertSeeText('No Audit Records Yet');
        $response->assertSeeText('No local activity or audit log records are available yet for admin visibility.');
    }

    public function test_admin_account_detail_page_uses_the_bound_account_route_parameter(): void
    {
        [$user, $account] = $this->createAccessContext([
            'name' => 'Scoped Admin Detail Account',
            'slug' => 'scoped-admin-detail-account',
        ]);

        Subscription::query()->create([
            'account_id' => $account->id,
            'subscription_plan_id' => SubscriptionPlan::factory()->create(['name' => 'Scoped Detail Plan'])->id,
            'status' => 'active',
            'starts_at' => now()->subDay(),
        ]);

        $response = $this->actingAs($user)->get(route('admin.account-detail.index', ['account' => $account]));

        $response->assertOk();
        $response->assertSeeText('Admin Account Detail');
        $response->assertSeeText('Scoped Admin Detail Account');
        $response->assertSeeText('scoped-admin-detail-account');
        $response->assertSeeText('Scoped Detail Plan');
    }

    public function test_admin_account_detail_page_shows_broker_state_without_exposing_raw_secrets(): void
    {
        [$user, $account] = $this->createAccessContext([
            'name' => 'Broker Detail Admin Account',
            'slug' => 'broker-detail-admin-account',
        ]);

        $connection = BrokerConnection::query()->create([
            'account_id' => $account->id,
            'name' => 'Primary Admin Broker',
            'broker' => 'placeholder_primary',
            'status' => 'connected',
            'connected_at' => now()->subHour(),
            'last_synced_at' => now()->subMinutes(10),
        ]);

        BrokerCredential::query()->create([
            'broker_connection_id' => $connection->id,
            'label' => 'Primary Admin Credential',
            'status' => 'saved',
            'credential_payload' => [
                'access_key_id' => 'ADMINKEY-5566778899',
                'access_secret' => 'admin-secret-raw-AB',
                'environment' => 'sandbox',
                'access_mode' => 'read_only',
            ],
            'last_used_at' => now()->subMinutes(5),
        ]);

        $response = $this->actingAs($user)->get(route('admin.account-detail.index', ['account' => $account]));

        $response->assertOk();
        $response->assertSeeText('Broker Connections');
        $response->assertSeeText('Primary Admin Broker');
        $response->assertSeeText('Placeholder Primary');
        $response->assertSeeText('Broker Credentials');
        $response->assertSeeText('Primary Admin Credential');
        $response->assertSeeText('key ***8899');
        $response->assertSeeText('secret ***AB');
        $response->assertDontSeeText('ADMINKEY-5566778899');
        $response->assertDontSeeText('admin-secret-raw-AB');
    }

    public function test_admin_account_detail_page_returns_not_found_for_a_missing_account(): void
    {
        [$user] = $this->createAccessContext();

        $response = $this->actingAs($user)->get('/admin/account-detail/999999');

        $response->assertNotFound();
    }

    public function test_admin_account_detail_page_shows_clean_broker_visibility_when_no_broker_records_exist(): void
    {
        [$user, $account] = $this->createAccessContext([
            'name' => 'No Broker Admin Account',
            'slug' => 'no-broker-admin-account',
        ]);

        $response = $this->actingAs($user)->get(route('admin.account-detail.index', ['account' => $account]));

        $response->assertOk();
        $response->assertSeeText('Broker Connections');
        $response->assertSeeText('Broker Credentials');
        $response->assertSeeText('No broker connection records are linked to this account yet.');
        $response->assertSeeText('No broker credential metadata is linked to this account yet.');
    }

    public function test_admin_dashboard_renders_real_db_backed_summary_reads(): void
    {
        [$user, $account] = $this->createAccessContext();

        $plan = SubscriptionPlan::factory()->create([
            'name' => 'Admin Dashboard Plan',
        ]);

        $subscription = Subscription::query()->create([
            'account_id' => $account->id,
            'subscription_plan_id' => $plan->id,
            'status' => 'active',
            'starts_at' => now()->subDay(),
        ]);

        Invoice::query()->create([
            'account_id' => $account->id,
            'subscription_id' => $subscription->id,
            'number' => 'INV-ADMIN-DASH-001',
            'status' => 'paid',
            'subtotal' => 125,
            'total' => 125,
            'currency' => 'USD',
            'issued_at' => now()->subDay(),
            'paid_at' => now(),
        ]);

        $connection = BrokerConnection::query()->create([
            'account_id' => $account->id,
            'name' => 'Admin Dashboard Broker',
            'broker' => 'placeholder_primary',
            'status' => 'pending',
        ]);

        BrokerCredential::query()->create([
            'broker_connection_id' => $connection->id,
            'label' => 'Admin Dashboard Broker Local Dev',
            'status' => 'saved',
            'credential_payload' => [
                'access_key_id' => 'LOCALKEY-1234567890',
                'access_secret' => 'secret-local-XY',
                'environment' => 'sandbox',
                'access_mode' => 'read_only',
            ],
        ]);

        $license = ApiLicense::query()->create([
            'account_id' => $account->id,
            'name' => 'Admin Dashboard License',
            'status' => 'active',
            'starts_at' => now()->subDay(),
        ]);

        ApiKey::query()->create([
            'api_license_id' => $license->id,
            'name' => 'Admin Dashboard Key',
            'key_hash' => hash('sha256', 'admin-dashboard|token-local-7890'),
            'secret_hint' => 'token-local-7890',
            'status' => 'ready',
        ]);

        ActivityLog::query()->create([
            'account_id' => $account->id,
            'user_id' => $user->id,
            'type' => 'dashboard_review',
            'level' => 'info',
            'message' => 'Admin dashboard reviewed locally.',
        ]);

        AuditLog::query()->create([
            'account_id' => $account->id,
            'user_id' => $user->id,
            'action' => 'dashboard_snapshot',
            'summary' => 'Admin dashboard snapshot generated from local records.',
        ]);

        SystemSetting::query()->create([
            'runtime_mode' => 'local',
            'review_channel' => 'manual',
            'status_level' => 'medium',
        ]);

        $response = $this->actingAs($user)->get(route('admin.dashboard'));

        $response->assertOk();
        $response->assertSeeText('Admin Dashboard');
        $response->assertSeeText('Platform detail');
        $response->assertSeeText('Accounts Summary');
        $response->assertSeeText('Billing Summary');
        $response->assertSeeText('Broker Summary');
        $response->assertSeeText('License Summary');
        $response->assertSeeText('1 users / 1 subscriptions');
        $response->assertSeeText('Active Subscriptions');
        $response->assertSeeText('Latest Invoice');
        $response->assertSeeText('INV-ADMIN-DASH-001');
        $response->assertSeeText('Admin Dashboard Broker');
        $response->assertSeeText('Admin Dashboard Broker Local Dev');
        $response->assertSeeText('***7890');
        $response->assertSeeText('Admin Dashboard License');
        $response->assertSeeText('Encrypted token ending in ***7890');
        $response->assertSeeText('Recent Platform Activity');
        $response->assertSeeText('dashboard_review');
        $response->assertSeeText('dashboard_snapshot');
        $response->assertSeeText('Readiness 6/6');
        $response->assertSeeText('Platform Readiness');
        $response->assertSeeText('Settings Record');
        $response->assertSeeText('Runtime Mode');
        $response->assertSeeText('local');
        $response->assertSeeText('Activity Logs');
        $response->assertSeeText('1 local rows');
        $response->assertDontSeeText('secret-local-XY');
    }

    public function test_admin_reports_page_renders_real_db_backed_global_summary_reads(): void
    {
        [$user, $account] = $this->createAccessContext();

        $plan = SubscriptionPlan::factory()->create([
            'name' => 'Admin Reporting Plan',
        ]);

        $subscription = Subscription::query()->create([
            'account_id' => $account->id,
            'subscription_plan_id' => $plan->id,
            'status' => 'active',
            'starts_at' => now()->subDay(),
        ]);

        Invoice::query()->create([
            'account_id' => $account->id,
            'subscription_id' => $subscription->id,
            'number' => 'INV-ADMIN-001',
            'status' => 'paid',
            'subtotal' => 125,
            'total' => 125,
            'currency' => 'USD',
            'issued_at' => now()->subDay(),
            'paid_at' => now(),
        ]);

        $connection = BrokerConnection::query()->create([
            'account_id' => $account->id,
            'name' => 'Admin Reporting Broker',
            'broker' => 'placeholder_primary',
            'status' => 'pending',
        ]);

        BrokerCredential::query()->create([
            'broker_connection_id' => $connection->id,
            'label' => 'Admin Reporting Broker Local Dev',
            'status' => 'saved',
            'credential_payload' => [
                'access_key_id' => 'LOCALKEY-1234567890',
                'access_secret' => 'secret-local-XY',
                'environment' => 'sandbox',
                'access_mode' => 'read_only',
            ],
        ]);

        $license = ApiLicense::query()->create([
            'account_id' => $account->id,
            'name' => 'Admin Reporting License',
            'status' => 'active',
            'starts_at' => now()->subDay(),
        ]);

        ApiKey::query()->create([
            'api_license_id' => $license->id,
            'name' => 'Admin Reporting Key',
            'key_hash' => hash('sha256', 'admin-reporting|token-local-7890'),
            'secret_hint' => 'token-local-7890',
            'status' => 'ready',
        ]);

        ActivityLog::query()->create([
            'account_id' => $account->id,
            'user_id' => $user->id,
            'type' => 'report_review',
            'level' => 'info',
            'message' => 'Admin report data reviewed locally.',
        ]);

        AuditLog::query()->create([
            'account_id' => $account->id,
            'user_id' => $user->id,
            'action' => 'report_snapshot',
            'summary' => 'Admin report snapshot generated from local records.',
        ]);

        SystemSetting::query()->create([
            'runtime_mode' => 'local',
            'review_channel' => 'manual',
            'status_level' => 'medium',
        ]);

        $response = $this->actingAs($user)->get(route('admin.reports.index'));

        $response->assertOk();
        $response->assertSeeText('Admin Reports');
        $response->assertSeeText('Admin reporting is now a readable platform snapshot instead of a raw count dump.');
        $response->assertSeeText('Workspace Footprint');
        $response->assertSeeText('Commercial Coverage');
        $response->assertSeeText('Billing Follow-through');
        $response->assertSeeText('1 paid / 0 open');
        $response->assertSeeText('Broker Coverage');
        $response->assertSeeText('License Coverage');
        $response->assertSeeText('Oversight Coverage');
        $response->assertSeeText('System Posture');
        $response->assertSeeText('Present');
        $response->assertSeeText('Review manual');
    }

    public function test_admin_operations_pages_render_safe_customer_automation_control_surface(): void
    {
        [$user, $account] = $this->createAccessContext([
            'name' => 'Admin Ops Account',
            'slug' => 'admin-ops-account',
        ]);
        $this->seedConfirmedBismel1Subscription($account, 'BISMILLAH1_BOT_EXECUTE_BASIC');

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
            'ai_enabled' => true,
            'status' => 'armed',
            'risk_level' => 'balanced',
            'scanner_enabled' => true,
            'execution_enabled' => true,
            'run_health' => 'execution_submitted',
            'settings' => [
                'bismel1_runtime' => [
                    'last_runtime_status' => 'active',
                    'last_runtime_summary' => 'waiting for next bar close',
                ],
                'bismel1_scheduler' => [
                    'next_intended_run' => now()->addHour()->toIso8601String(),
                ],
                'bismel1_execution' => [
                    'last_execution_summary' => 'Broker open request for NVDA was submitted with safe execution handling.',
                ],
                'bismel1_position_manager' => [
                    'last_management_summary' => 'Bismel1 position manager reconciled broker and local state.',
                ],
            ],
        ]);

        $connection = BrokerConnection::query()->create([
            'account_id' => $account->getKey(),
            'name' => 'Primary Alpaca',
            'broker' => 'alpaca',
            'status' => 'connected',
            'connected_at' => now()->subHour(),
            'last_synced_at' => now()->subMinutes(10),
        ]);

        BrokerCredential::query()->create([
            'broker_connection_id' => $connection->getKey(),
            'label' => 'Primary Credential',
            'provider' => 'alpaca',
            'status' => 'verified',
            'environment' => 'paper',
            'access_mode' => 'trade',
            'credential_payload' => [
                'provider' => 'alpaca',
                'environment' => 'paper',
                'access_mode' => 'trade',
                'market_data_feed' => 'iex',
                'access_key_id' => 'PKTEST1234',
                'access_secret' => 'SECRET99',
            ],
            'is_encrypted' => true,
        ]);

        AlpacaAccount::query()->create([
            'account_id' => $account->getKey(),
            'broker_connection_id' => $connection->getKey(),
            'name' => 'Primary Alpaca',
            'environment' => 'paper',
            'data_feed' => 'iex',
            'status' => 'active',
            'sync_status' => 'success',
            'trade_stream_status' => 'credentials_verified',
            'is_primary' => true,
            'is_active' => true,
            'equity' => 12500,
            'last_synced_at' => now()->subMinutes(5),
            'last_account_sync_at' => now()->subMinutes(5),
            'metadata' => [],
        ]);

        BotRun::query()->create([
            'account_id' => $account->getKey(),
            'strategy_profile_id' => $strategyProfile->getKey(),
            'automation_setting_id' => $automationSetting->getKey(),
            'run_type' => 'execution',
            'status' => 'submitted',
            'risk_level' => 'balanced',
            'started_at' => now()->subMinutes(20),
            'finished_at' => now()->subMinutes(19),
            'summary' => [
                'safe_summary' => 'Broker open request for NVDA was submitted with safe execution handling.',
            ],
        ]);

        BotRun::query()->create([
            'account_id' => $account->getKey(),
            'strategy_profile_id' => $strategyProfile->getKey(),
            'automation_setting_id' => $automationSetting->getKey(),
            'run_type' => 'position_management',
            'status' => 'completed',
            'risk_level' => 'balanced',
            'started_at' => now()->subMinutes(10),
            'finished_at' => now()->subMinutes(9),
            'summary' => [
                'safe_summary' => 'Bismel1 position manager reconciled broker and local state.',
            ],
        ]);

        Signal::query()->create([
            'account_id' => $account->getKey(),
            'strategy_profile_id' => $strategyProfile->getKey(),
            'symbol' => 'NVDA',
            'timeframe' => '4h',
            'direction' => 'long',
            'strength' => 0.82,
            'status' => 'skip',
            'generated_at' => now()->subMinutes(15),
            'payload' => [
                'admin_summary' => 'Risk engine blocked a duplicate signal for the current bar-close window.',
                'public_summary' => 'risk blocked',
                'safe_flags' => [
                    'risk_blocked' => true,
                ],
                'risk_engine' => [
                    'allowed' => false,
                    'status' => 'block_action',
                    'reason_code' => 'duplicate_signal_protection',
                ],
            ],
        ]);

        ActivityLog::query()->create([
            'account_id' => $account->getKey(),
            'user_id' => $user->getKey(),
            'type' => 'bismel1_execution_submitted',
            'level' => 'info',
            'message' => 'Broker open request for NVDA was submitted with safe execution handling.',
        ]);

        SystemSetting::query()->create([
            'runtime_mode' => 'local',
            'review_channel' => 'manual',
            'status_level' => 'medium',
        ]);

        $dashboardResponse = $this->actingAs($user)->get(route('admin.dashboard'));
        $dashboardResponse->assertOk();
        $dashboardResponse->assertSeeText('Customer automation control surface');
        $dashboardResponse->assertSeeText('Active Automation');
        $dashboardResponse->assertSeeText('Broker ready');
        $dashboardResponse->assertSeeText('Risk engine blocked a duplicate signal for the current bar-close window.');
        $dashboardResponse->assertDontSeeText('EMA');

        $systemResponse = $this->actingAs($user)->get(route('admin.system.index'));
        $systemResponse->assertOk();
        $systemResponse->assertSeeText('Bismel1 Operations Summary');
        $systemResponse->assertSeeText('Customer Automation Accounts');
        $systemResponse->assertSeeText('Recent Automation Outcomes');
        $systemResponse->assertSeeText('Broker open request for NVDA was submitted with safe execution handling.');

        $reportsResponse = $this->actingAs($user)->get(route('admin.reports.index'));
        $reportsResponse->assertOk();
        $reportsResponse->assertSeeText('Bismel1 admin operations control surface');
        $reportsResponse->assertSeeText('Recent Reconciliation Outcomes');
        $reportsResponse->assertSeeText('Bismel1 position manager reconciled broker and local state.');

        $accountResponse = $this->actingAs($user)->get(route('admin.account-detail.index', ['account' => $account]));
        $accountResponse->assertOk();
        $accountResponse->assertSeeText('Runtime posture and recent safe outcomes');
        $accountResponse->assertSeeText('Account Automation Summary');
        $accountResponse->assertSeeText('Recent Risk Blocks');
        $accountResponse->assertSeeText('Recent Runtime Activity');
        $accountResponse->assertDontSeeText('SECRET99');
    }

    public function test_admin_operations_visibility_surfaces_entitlement_mismatches_safely(): void
    {
        [$user, $account] = $this->createAccessContext([
            'name' => 'Entitlement Review Account',
            'slug' => 'entitlement-review-account',
        ]);
        $this->seedConfirmedBismel1Subscription($account, 'BISMILLAH_AI_SCANNER');

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

        AutomationSetting::query()->create([
            'account_id' => $account->getKey(),
            'strategy_profile_id' => $strategyProfile->getKey(),
            'name' => 'Primary Automation',
            'ai_enabled' => true,
            'status' => 'review',
            'risk_level' => 'balanced',
            'scanner_enabled' => true,
            'execution_enabled' => false,
            'settings' => [
                'bismel1_runtime' => [
                    'last_runtime_status' => 'blocked',
                    'last_runtime_summary' => 'plan does not include this automation mode',
                ],
            ],
        ]);

        $response = $this->actingAs($user)->get(route('admin.dashboard'));

        $response->assertOk();
        $response->assertSeeText('Entitlement Mismatches');
        $response->assertSeeText('Scanner access is active, but full Bismel1 stocks automation is not included.');
        $response->assertDontSeeText('EMA');
    }

    public function test_admin_pages_remain_reachable_with_clean_empty_states_when_only_the_admin_user_exists(): void
    {
        [$user] = $this->createAccessContext();
        Account::query()->delete();

        $pages = [
            'admin.dashboard' => 'Admin Dashboard',
            'admin.accounts.index' => 'Admin Accounts',
            'admin.licenses.index' => 'Admin Licenses',
            'admin.audit.index' => 'Admin Audit',
            'admin.system.index' => 'System',
            'admin.reports.index' => 'Admin Reports',
        ];

        foreach ($pages as $route => $title) {
            $response = $this->actingAs($user)->get(route($route));

            $response->assertOk();
            $response->assertSeeText($title);
        }
    }
}
