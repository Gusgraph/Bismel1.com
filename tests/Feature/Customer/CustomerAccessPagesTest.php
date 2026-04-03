<?php
// اعوز بالله من الشياطين و ان يحضرون بسم الله الرحمن الرحيم الله لا إله إلا هو الحي القيوم
// Bismillahi ar-Rahmani ar-Rahim Audhu billahi min ash-shayatin wa an yahdurun Bismillah ar-Rahman ar-Rahim Allah la ilaha illa huwa al-hayy al-qayyum. Tamsa Allahu ala ayunihim
// version: x
// ======================================================
// - App Name: gusgraph-trading
// - Gusgraph LLC -
// - Author: Gus Kazem
// - https://Gusgraph.com
// - File Path: tests/Feature/Customer/CustomerAccessPagesTest.php
// ======================================================

namespace Tests\Feature\Customer;

use App\Models\Invoice;
use App\Models\Subscription;
use App\Models\SubscriptionPlan;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Concerns\CreatesAccessContext;
use Tests\TestCase;

class CustomerAccessPagesTest extends TestCase
{
    use CreatesAccessContext;
    use RefreshDatabase;

    public function test_customer_account_page_renders_real_db_backed_workspace_data(): void
    {
        [$user, $account] = $this->createAccessContext();

        $response = $this->actingAs($user)->get(route('customer.account.index'));

        $response->assertOk();
        $response->assertSeeText('Customer Account');
        $response->assertSeeText($account->name);
        $response->assertSeeText($account->slug);
        $response->assertSeeText($user->name);
        $response->assertSeeText('Active');
    }

    public function test_customer_account_page_prefers_the_current_users_accessible_account_over_a_global_first_record(): void
    {
        [, $otherAccount] = $this->createAccessContext([
            'name' => 'A First Global Account',
            'slug' => 'a-first-global-account',
        ]);
        [$user, $account] = $this->createAccessContext([
            'name' => 'Z Current User Account',
            'slug' => 'z-current-user-account',
        ]);

        $response = $this->actingAs($user)->get(route('customer.account.index'));

        $response->assertOk();
        $response->assertSeeText($account->name);
        $response->assertDontSeeText($otherAccount->name);
    }

    public function test_customer_pages_are_forbidden_for_a_user_without_customer_access(): void
    {
        $user = User::factory()->create();

        $pages = [
            'customer.dashboard' => 'Customer Dashboard',
            'customer.account.index' => 'Customer Account',
            'customer.settings.index' => 'Customer Settings',
            'customer.billing.index' => 'Customer Billing',
            'customer.invoices.index' => 'Customer Invoices',
            'customer.broker.index' => 'Customer Broker',
            'customer.license.index' => 'Customer License',
            'customer.onboarding.index' => 'Customer Onboarding',
            'customer.reports.index' => 'Customer Reports',
        ];

        foreach ($pages as $route => $title) {
            $response = $this->actingAs($user)->get(route($route));

            $response->assertForbidden();
            $response->assertSeeText('That page is outside your current access.');
        }
    }

    public function test_customer_summary_pages_show_clean_empty_states_when_the_current_account_has_no_related_records(): void
    {
        [$user] = $this->createAccessContext();

        $expectations = [
            'customer.dashboard' => ['Billing', 'Missing', 'License'],
            'customer.broker.index' => ['Alpaca Not Connected Yet', 'No broker connection details are available yet. Connect Alpaca first, then return here to verify the linked account.', 'No saved broker access is available yet.'],
            'customer.license.index' => ['License access has not been added yet', 'License details will appear here after access is added.', 'Use this status when a saved key is ready to use.'],
            'customer.onboarding.index' => ['Workspace', 'Ready:', 'Subscription', 'Still needed: choose and activate a plan', 'API Key', 'Still needed: save API access'],
            'customer.reports.index' => ['No active plan yet', '0 total invoices', 'No recent activity yet'],
        ];

        foreach ($expectations as $route => $messages) {
            $response = $this->actingAs($user)->get(route($route));

            $response->assertOk();

            foreach ($messages as $message) {
                $response->assertSeeText($message);
            }
        }
    }

    public function test_customer_dashboard_onboarding_and_reports_survive_missing_firestore_credentials(): void
    {
        [$user] = $this->createAccessContext();

        $user->forceFill([
            'firestore_uid' => 'runtime-user-local-001',
        ])->save();

        config()->set('firestore.enabled', true);
        config()->set('firestore.project_id', 'local-runtime-project');
        config()->set('firestore.credentials', '/tmp/does-not-exist-firestore-service-account.json');

        $expectations = [
            'customer.dashboard' => [
                'title' => 'Customer Dashboard',
                'messages' => [],
            ],
            'customer.onboarding.index' => [
                'title' => 'Customer Onboarding',
                'messages' => ['Runtime Readiness', 'Firestore Runtime Mapping', 'Unavailable', 'The configured Firestore credentials file is missing or not readable'],
            ],
            'customer.reports.index' => [
                'title' => 'Customer Reports',
                'messages' => ['Runtime Signals', 'Firestore Runtime Mapping', 'Unavailable', 'The configured Firestore credentials file is missing or not readable'],
            ],
        ];

        foreach ($expectations as $route => $expectation) {
            $response = $this->actingAs($user)->get(route($route));

            $response->assertOk();
            $response->assertSeeText($expectation['title']);

            foreach ($expectation['messages'] as $message) {
                $response->assertSeeText($message);
            }
        }
    }

    public function test_customer_billing_page_renders_real_db_backed_subscription_data(): void
    {
        [$user, $account] = $this->createAccessContext();

        $plan = SubscriptionPlan::factory()->create([
            'name' => 'Local Pro Plan',
            'currency' => 'USD',
            'interval' => 'monthly',
            'price' => 0,
        ]);

        Subscription::query()->create([
            'account_id' => $account->id,
            'subscription_plan_id' => $plan->id,
            'status' => 'active',
            'starts_at' => now()->subDay(),
        ]);

        $response = $this->actingAs($user)->get(route('customer.billing.index'));

        $response->assertOk();
        $response->assertSeeText('Customer Billing');
        $response->assertSeeText($account->name);
        $response->assertSeeText($user->email);
        $response->assertSeeText('Local Pro Plan');
        $response->assertSeeText('Active');
        $response->assertSeeText('USD 0.00 / monthly');
        $response->assertSeeText('Subscription Detail');
        $response->assertSeeText('Account '.$account->name);
        $response->assertSeeText('Slug '.$account->slug);
    }

    public function test_customer_billing_page_shows_a_clean_empty_state_when_the_current_account_has_no_subscription(): void
    {
        [$user] = $this->createAccessContext();

        $response = $this->actingAs($user)->get(route('customer.billing.index'));

        $response->assertOk();
        $response->assertSeeText('No active subscription is available for this workspace yet.');
        $response->assertSeeText('Choose a package to start billing for this workspace and unlock the matching Bismel1 product access after the subscription becomes active.');
    }

    public function test_customer_settings_update_persists_to_the_current_user(): void
    {
        [$user, $account] = $this->createAccessContext();

        $response = $this->actingAs($user)->put(route('customer.settings.update'), [
            'name' => 'Updated Local User',
            'email' => 'updated-local-user@example.test',
        ]);

        $response->assertRedirect(route('customer.settings.index'));
        $response->assertSessionHas('status', 'Profile settings were saved to the current local user profile.');

        $this->assertDatabaseHas('users', [
            'id' => $user->id,
            'name' => 'Updated Local User',
            'email' => 'updated-local-user@example.test',
        ]);

        $pageResponse = $this->actingAs($user->fresh())->get(route('customer.settings.index'));

        $pageResponse->assertOk();
        $pageResponse->assertSeeText($account->name);
        $pageResponse->assertSeeText('Updated Local User');
        $pageResponse->assertSeeText('updated-local-user@example.test');
    }

    public function test_customer_settings_update_does_not_modify_any_other_user_record(): void
    {
        [$user] = $this->createAccessContext();
        $otherUser = User::factory()->create([
            'name' => 'Other Local User',
            'email' => 'other-local-user@example.test',
        ]);

        $response = $this->actingAs($user)->put(route('customer.settings.update'), [
            'name' => 'Updated Local User',
            'email' => 'updated-local-user@example.test',
        ]);

        $response->assertRedirect(route('customer.settings.index'));
        $this->assertDatabaseHas('users', [
            'id' => $otherUser->id,
            'name' => 'Other Local User',
            'email' => 'other-local-user@example.test',
        ]);
    }

    public function test_customer_account_and_settings_pages_show_current_user_and_account_detail(): void
    {
        [$user, $account] = $this->createAccessContext([
            'name' => 'Customer Detail Account',
            'slug' => 'customer-detail-account',
        ]);

        $accountResponse = $this->actingAs($user)->get(route('customer.account.index'));

        $accountResponse->assertOk();
        $accountResponse->assertSeeText('Current Access Context');
        $accountResponse->assertSeeText('Current User');
        $accountResponse->assertSeeText($user->name);
        $accountResponse->assertSeeText('Current Email');
        $accountResponse->assertSeeText($user->email);
        $accountResponse->assertSeeText('Access Role');
        $accountResponse->assertSeeText('Owner');
        $accountResponse->assertSeeText('Membership Status');
        $accountResponse->assertSeeText('Active');
        $accountResponse->assertSeeText('Customer Detail Account');

        $settingsResponse = $this->actingAs($user)->get(route('customer.settings.index'));

        $settingsResponse->assertOk();
        $settingsResponse->assertSeeText('Your account settings are ready.');
        $settingsResponse->assertSeeText('Workspace Context');
        $settingsResponse->assertSeeText('Customer Detail Account');
        $settingsResponse->assertSeeText('Display Name');
        $settingsResponse->assertSeeText($user->name);
        $settingsResponse->assertSeeText('Email Address');
        $settingsResponse->assertSeeText($user->email);
    }

    public function test_customer_invoices_page_renders_real_invoice_oriented_records_for_the_current_account(): void
    {
        [$user, $account] = $this->createAccessContext();

        $plan = SubscriptionPlan::factory()->create([
            'name' => 'Invoice Plan',
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
            'number' => 'INV-CURRENT-001',
            'status' => 'paid',
            'subtotal' => 125,
            'total' => 125,
            'currency' => 'USD',
            'issued_at' => now()->subDay(),
            'paid_at' => now(),
        ]);

        $response = $this->actingAs($user)->get(route('customer.invoices.index'));

        $response->assertOk();
        $response->assertSeeText('Customer Invoices');
        $response->assertSeeText($account->name);
        $response->assertSeeText('Invoice Plan');
        $response->assertSeeText('INV-CURRENT-001');
        $response->assertSeeText('Paid');
        $response->assertSeeText('USD 125.00');
        $response->assertSeeText('Invoice Detail');
        $response->assertSeeText('subtotal');
        $response->assertSeeText('total');
        $response->assertSeeText('Account '.$account->name);
    }

    public function test_customer_invoices_page_shows_a_clean_empty_state_when_the_current_account_has_no_invoices(): void
    {
        [$user, $account] = $this->createAccessContext();

        Subscription::query()->create([
            'account_id' => $account->id,
            'subscription_plan_id' => SubscriptionPlan::factory()->create(['name' => 'No Invoice Plan'])->id,
            'status' => 'active',
            'starts_at' => now()->subDay(),
        ]);

        $response = $this->actingAs($user)->get(route('customer.invoices.index'));

        $response->assertOk();
        $response->assertSeeText('No Invoices Yet');
        $response->assertSeeText('Invoices will appear here after the first billing event is recorded for this workspace.');
    }

    public function test_customer_invoices_page_prefers_the_current_users_accessible_account_over_a_global_first_accounts_invoice(): void
    {
        [, $otherAccount] = $this->createAccessContext([
            'name' => 'A First Global Account',
            'slug' => 'a-first-global-account',
        ]);
        [$user, $account] = $this->createAccessContext([
            'name' => 'Z Current User Account',
            'slug' => 'z-current-user-account',
        ]);

        $plan = SubscriptionPlan::factory()->create(['name' => 'Scoped Invoice Plan']);
        $otherSubscription = Subscription::query()->create([
            'account_id' => $otherAccount->id,
            'subscription_plan_id' => $plan->id,
            'status' => 'active',
            'starts_at' => now()->subDay(),
        ]);
        $currentSubscription = Subscription::query()->create([
            'account_id' => $account->id,
            'subscription_plan_id' => $plan->id,
            'status' => 'active',
            'starts_at' => now()->subDay(),
        ]);

        Invoice::query()->create([
            'account_id' => $otherAccount->id,
            'subscription_id' => $otherSubscription->id,
            'number' => 'INV-OTHER-001',
            'status' => 'paid',
            'subtotal' => 99,
            'total' => 99,
            'currency' => 'USD',
            'issued_at' => now()->subDay(),
            'paid_at' => now(),
        ]);
        Invoice::query()->create([
            'account_id' => $account->id,
            'subscription_id' => $currentSubscription->id,
            'number' => 'INV-CURRENT-002',
            'status' => 'paid',
            'subtotal' => 125,
            'total' => 125,
            'currency' => 'USD',
            'issued_at' => now()->subDay(),
            'paid_at' => now(),
        ]);

        $response = $this->actingAs($user)->get(route('customer.invoices.index'));

        $response->assertOk();
        $response->assertSeeText('INV-CURRENT-002');
        $response->assertDontSeeText('INV-OTHER-001');
    }
}
