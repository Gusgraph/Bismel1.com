<?php
// اعوز بالله من الشياطين و ان يحضرون بسم الله الرحمن الرحيم الله لا إله إلا هو الحي القيوم
// Bismillahi ar-Rahmani ar-Rahim Audhu billahi min ash-shayatin wa an yahdurun Bismillah ar-Rahman ar-Rahim Allah la ilaha illa huwa al-hayy al-qayyum. Tamsa Allahu ala ayunihim
// version: x
// ======================================================
// - App Name: bismel1.com
// - Gusgraph LLC -
// - Author: Gus Kazem
// - https://Gusgraph.com
// - File Path: tests/Feature/Billing/Bismel1BillingIntegrityTest.php
// ======================================================

namespace Tests\Feature\Billing;

use App\Models\SubscriptionPlan;
use App\Support\Billing\Bismel1EntitlementService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Concerns\CreatesAccessContext;
use Tests\Concerns\CreatesBismel1Entitlements;
use Tests\TestCase;

class Bismel1BillingIntegrityTest extends TestCase
{
    use CreatesAccessContext;
    use CreatesBismel1Entitlements;
    use RefreshDatabase;

    public function test_customer_billing_catalog_excludes_legacy_local_and_testing_plans(): void
    {
        [$user] = $this->createAccessContext();

        SubscriptionPlan::query()->create([
            'name' => 'Starter Workspace',
            'code' => 'STARTER_LOCAL',
            'plan_type' => 'base',
            'product_family' => 'legacy',
            'status' => 'active',
            'price' => 99,
            'currency' => 'USD',
            'interval' => 'monthly',
            'billing_model' => 'monthly',
            'sort_order' => 11,
        ]);

        $this->ensureBismel1Plan('BISMILLAH1_BOT_PRIME');
        $this->ensureBismel1Plan('BISMILLAH1_BOT_ADDITIONAL_ACCOUNT_ADDON');
        $this->ensureBismel1Plan('BISMILLAH1_BOT_SPEED_EXECUTE');

        $response = $this->actingAs($user)->get(route('customer.billing.index'));

        $response->assertOk();
        $response->assertSeeText('Bismel1 Stocks Bot - AI Prime');
        $response->assertSeeText('Bismel1 Bot - Additional Account');
        $response->assertDontSeeText('Starter Workspace');
        $response->assertDontSeeText('Speed Executor');
    }

    public function test_customer_billing_checkout_rejects_the_testing_plan_from_the_production_checkout_lane(): void
    {
        [$user] = $this->createAccessContext();

        $this->ensureBismel1Plan('BISMILLAH1_BOT_SPEED_EXECUTE');

        $response = $this->actingAs($user)->from(route('customer.billing.index'))->post(route('customer.billing.checkout.store'), [
            'base_plan_code' => 'BISMILLAH1_BOT_SPEED_EXECUTE',
            'addon_codes' => [],
        ]);

        $response->assertRedirect(route('customer.billing.index'));
        $response->assertSessionHasErrors('base_plan_code');
    }

    public function test_confirmed_speed_execute_subscription_stays_isolated_from_production_entitlements(): void
    {
        [, $account] = $this->createAccessContext();
        $this->seedConfirmedBismel1Subscription($account, 'BISMILLAH1_BOT_SPEED_EXECUTE');

        $entitlements = app(Bismel1EntitlementService::class)->resolve($account);
        $brokerSummary = app(Bismel1EntitlementService::class)->brokerLinkingSummary($account);

        $this->assertTrue($entitlements['subscription_active']);
        $this->assertTrue(data_get($entitlements, 'capabilities.can_use_speed_execute'));
        $this->assertFalse(data_get($entitlements, 'capabilities.can_use_scanner'));
        $this->assertFalse(data_get($entitlements, 'capabilities.can_use_stocks_automation'));
        $this->assertFalse(data_get($entitlements, 'capabilities.can_use_execute'));
        $this->assertSame('demo plan is isolated from the production automation lineup', $entitlements['blocked_summary']);
        $this->assertFalse($brokerSummary['allowed']);
        $this->assertSame('demo plan is isolated from the production automation lineup', $brokerSummary['summary']);
    }
}
