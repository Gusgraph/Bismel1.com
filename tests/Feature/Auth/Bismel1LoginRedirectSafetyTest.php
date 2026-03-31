<?php
// اعوز بالله من الشياطين و ان يحضرون بسم الله الرحمن الرحيم الله لا إله إلا هو الحي القيوم
// Bismillahi ar-Rahmani ar-Rahim Audhu billahi min ash-shayatin wa an yahdurun Bismillah ar-Rahman ar-Rahim Allah la ilaha illa huwa al-hayy al-qayyum. Tamsa Allahu ala ayunihim
// version: x
// ======================================================
// - App Name: bismel1.com
// - Gusgraph LLC -
// - Author: Gus Kazem
// - https://Gusgraph.com
// - File Path: tests/Feature/Auth/Bismel1LoginRedirectSafetyTest.php
// ======================================================

namespace Tests\Feature\Auth;

use App\Models\Account;
use App\Models\Subscription;
use App\Models\SubscriptionPlan;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class Bismel1LoginRedirectSafetyTest extends TestCase
{
    use RefreshDatabase;

    public function test_customer_with_unconfirmed_local_subscription_is_redirected_to_billing_after_login(): void
    {
        $user = User::factory()->create([
            'email' => 'billing-safety@example.test',
            'password' => 'password123',
        ]);

        $account = Account::query()->create([
            'name' => 'Billing Safety Account',
            'slug' => 'billing-safety-account',
            'status' => 'active',
            'owner_user_id' => User::factory()->create()->id,
        ]);

        $account->users()->attach($user->id, [
            'role' => 'member',
            'status' => 'active',
            'joined_at' => now(),
        ]);

        $plan = SubscriptionPlan::query()->create([
            'name' => 'Legacy Local Plan',
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

        Subscription::query()->create([
            'account_id' => $account->id,
            'subscription_plan_id' => $plan->id,
            'status' => 'active',
            'stripe_status' => 'active',
            'stripe_confirmed_at' => null,
            'starts_at' => now()->subDay(),
        ]);

        $response = $this->post(route('login.store'), [
            'email' => 'billing-safety@example.test',
            'password' => 'password123',
        ]);

        $response->assertRedirect(route('customer.billing.index'));
        $this->assertAuthenticatedAs($user);
    }
}
