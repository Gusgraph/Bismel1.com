<?php

namespace Tests\Feature\Customer;

use App\Models\ActivityLog;
use App\Models\ApiKey;
use App\Models\ApiLicense;
use App\Models\BrokerConnection;
use App\Models\BrokerCredential;
use App\Models\Invoice;
use App\Models\Subscription;
use App\Models\SubscriptionPlan;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Concerns\CreatesAccessContext;
use Tests\TestCase;

class CustomerDashboardReportsTest extends TestCase
{
    use CreatesAccessContext;
    use RefreshDatabase;

    public function test_customer_onboarding_page_renders_real_db_backed_current_state_signals(): void
    {
        [$user, $account] = $this->createAccessContext();

        $plan = SubscriptionPlan::factory()->create([
            'name' => 'Onboarding Plan',
        ]);

        Subscription::query()->create([
            'account_id' => $account->id,
            'subscription_plan_id' => $plan->id,
            'status' => 'active',
            'starts_at' => now()->subDay(),
        ]);

        $connection = BrokerConnection::query()->create([
            'account_id' => $account->id,
            'name' => 'Onboarding Broker',
            'broker' => 'placeholder_primary',
            'status' => 'pending',
        ]);

        BrokerCredential::query()->create([
            'broker_connection_id' => $connection->id,
            'label' => 'Onboarding Broker Local Dev',
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
            'name' => 'Onboarding License',
            'status' => 'active',
            'starts_at' => now()->subDay(),
        ]);

        ApiKey::query()->create([
            'api_license_id' => $license->id,
            'name' => 'Onboarding Key',
            'key_hash' => hash('sha256', 'onboarding|token-local-7890'),
            'secret_hint' => 'token-local-7890',
            'status' => 'ready',
        ]);

        Invoice::query()->create([
            'account_id' => $account->id,
            'subscription_id' => Subscription::query()->first()->id,
            'number' => 'INV-ONBOARD-001',
            'status' => 'paid',
            'subtotal' => 100,
            'total' => 100,
            'currency' => 'USD',
            'issued_at' => now()->subDay(),
            'paid_at' => now(),
        ]);

        ActivityLog::query()->create([
            'account_id' => $account->id,
            'user_id' => $user->id,
            'type' => 'onboarding_review',
            'level' => 'info',
            'message' => 'Current-account onboarding readiness reviewed locally.',
        ]);

        $response = $this->actingAs($user)->get(route('customer.onboarding.index'));

        $response->assertOk();
        $response->assertSeeText('Customer Onboarding');
        $response->assertSeeText('Setup progress is visible from the current workspace state.');
        $response->assertSeeText($account->name);
        $response->assertSeeText($user->email);
        $response->assertSeeText('Onboarding Plan');
        $response->assertSeeText('Alpaca Connection');
        $response->assertSeeText('Onboarding Broker');
        $response->assertSeeText('License');
        $response->assertSeeText('Onboarding License');
        $response->assertSeeText('Invoices');
        $response->assertSeeText('INV-ONBOARD-001');
        $response->assertSeeText('Readiness checklist');
        $response->assertSeeText('Recent Activity');
        $response->assertSeeText('onboarding_review');
        $response->assertSeeText('***7890');
        $response->assertDontSeeText('token-local-7890');
    }

    public function test_customer_dashboard_renders_real_db_backed_summary_reads(): void
    {
        [$user, $account] = $this->createAccessContext();

        $plan = SubscriptionPlan::factory()->create([
            'name' => 'Dashboard Plan',
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
            'number' => 'INV-DASH-001',
            'status' => 'paid',
            'subtotal' => 100,
            'total' => 100,
            'currency' => 'USD',
            'issued_at' => now()->subDay(),
            'paid_at' => now(),
        ]);

        $connection = BrokerConnection::query()->create([
            'account_id' => $account->id,
            'name' => 'Dashboard Broker',
            'broker' => 'placeholder_primary',
            'status' => 'pending',
        ]);

        BrokerCredential::query()->create([
            'broker_connection_id' => $connection->id,
            'label' => 'Dashboard Broker Local Dev',
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
            'name' => 'Dashboard License',
            'status' => 'active',
            'starts_at' => now()->subDay(),
        ]);

        ApiKey::query()->create([
            'api_license_id' => $license->id,
            'name' => 'Dashboard Key',
            'key_hash' => hash('sha256', 'dashboard|token-local-7890'),
            'secret_hint' => 'token-local-7890',
            'status' => 'ready',
        ]);

        ActivityLog::query()->create([
            'account_id' => $account->id,
            'user_id' => $user->id,
            'type' => 'dashboard_review',
            'level' => 'info',
            'message' => 'Customer dashboard reviewed locally.',
        ]);

        $response = $this->actingAs($user)->get(route('customer.dashboard'));

        $response->assertOk();
        $response->assertSeeText('Customer Dashboard');
        $response->assertSeeText($account->name);
        $response->assertSeeText($account->slug);
        $response->assertSeeText('Dashboard Plan');
        $response->assertSeeText('Trading Readiness');
        $response->assertSeeText('Readiness Score');
        $response->assertSeeText('Latest Activity');
        $response->assertSeeText('dashboard_review');
        $response->assertDontSeeText('token-local-7890');
    }

    public function test_customer_dashboard_prefers_the_current_users_accessible_account_over_a_global_first_record(): void
    {
        [, $otherAccount] = $this->createAccessContext([
            'name' => 'A First Global Account',
            'slug' => 'a-first-global-account',
        ]);
        [$user, $account] = $this->createAccessContext([
            'name' => 'Z Current User Account',
            'slug' => 'z-current-user-account',
        ]);

        $response = $this->actingAs($user)->get(route('customer.dashboard'));

        $response->assertOk();
        $response->assertSeeText($account->name);
        $response->assertDontSeeText($otherAccount->name);
    }

    public function test_customer_dashboard_shows_missing_state_labels_when_the_current_account_has_no_related_records(): void
    {
        [$user, $account] = $this->createAccessContext([
            'name' => 'Empty Dashboard Account',
            'slug' => 'empty-dashboard-account',
        ]);

        $response = $this->actingAs($user)->get(route('customer.dashboard'));

        $response->assertOk();
        $response->assertSeeText($account->name);
        $response->assertSeeText('Trading Readiness');
        $response->assertSeeText('Missing');
        $response->assertSeeText('Readiness Score');
        $response->assertSeeText('1/5');
        $response->assertSeeText('Connect Alpaca');
        $response->assertSeeText('Review automation state');
    }

    public function test_customer_reports_page_renders_real_db_backed_summary_reads(): void
    {
        [$user, $account] = $this->createAccessContext();

        $plan = SubscriptionPlan::factory()->create([
            'name' => 'Reporting Plan',
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
            'number' => 'INV-LOCAL-001',
            'status' => 'paid',
            'subtotal' => 100,
            'total' => 100,
            'currency' => 'USD',
            'issued_at' => now()->subDay(),
            'paid_at' => now(),
        ]);

        $connection = BrokerConnection::query()->create([
            'account_id' => $account->id,
            'name' => 'Reporting Broker',
            'broker' => 'placeholder_primary',
            'status' => 'pending',
        ]);

        BrokerCredential::query()->create([
            'broker_connection_id' => $connection->id,
            'label' => 'Reporting Broker Local Dev',
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
            'name' => 'Reporting License',
            'status' => 'active',
            'starts_at' => now()->subDay(),
        ]);

        ApiKey::query()->create([
            'api_license_id' => $license->id,
            'name' => 'Reporting Key',
            'key_hash' => hash('sha256', 'reporting|token-local-7890'),
            'secret_hint' => 'token-local-7890',
            'status' => 'ready',
        ]);

        ActivityLog::query()->create([
            'account_id' => $account->id,
            'user_id' => $user->id,
            'type' => 'report_review',
            'level' => 'info',
            'message' => 'Customer report summary reviewed locally.',
        ]);

        $response = $this->actingAs($user)->get(route('customer.reports.index'));

        $response->assertOk();
        $response->assertSeeText('Customer Reports');
        $response->assertSeeText('Your workspace summary is ready.');
        $response->assertSeeText($account->name);
        $response->assertSeeText('Reporting Plan');
        $response->assertSeeText('1 total invoices');
        $response->assertSeeText('1 paid / 0 open');
        $response->assertSeeText('Workspace coverage');
        $response->assertSeeText('4 of 4 complete');
        $response->assertSeeText('Status snapshot');
        $response->assertSeeText('report_review');
        $response->assertSeeText('Saved API access is ready for this workspace');
        $response->assertDontSeeText('token-local-7890');
    }
}
