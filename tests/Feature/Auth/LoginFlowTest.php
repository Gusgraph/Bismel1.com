<?php
// اعوز بالله من الشياطين و ان يحضرون بسم الله الرحمن الرحيم الله لا إله إلا هو الحي القيوم
// Bismillahi ar-Rahmani ar-Rahim Audhu billahi min ash-shayatin wa an yahdurun Bismillah ar-Rahman ar-Rahim Allah la ilaha illa huwa al-hayy al-qayyum. Tamsa Allahu ala ayunihim
// version: x
// ======================================================
// - App Name: gusgraph-trading
// - Gusgraph LLC -
// - Author: Gus Kazem
// - https://Gusgraph.com
// - File Path: tests/Feature/Auth/LoginFlowTest.php
// ======================================================

namespace Tests\Feature\Auth;

use App\Domain\Account\Enums\AccountRole;
use App\Models\Account;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Concerns\CreatesAccessContext;
use Tests\TestCase;

class LoginFlowTest extends TestCase
{
    use CreatesAccessContext;
    use RefreshDatabase;

    public function test_login_page_loads(): void
    {
        $response = $this->get(route('login'));

        $response->assertOk();
        $response->assertSeeText('Login');
    }

    public function test_valid_customer_login_redirects_to_customer_billing_when_subscription_is_not_active(): void
    {
        $user = User::factory()->create([
            'email' => 'customer@example.test',
            'password' => 'password123',
        ]);

        $account = Account::query()->create([
            'name' => 'Customer Login Account',
            'slug' => 'customer-login-account',
            'status' => 'active',
            'owner_user_id' => User::factory()->create()->id,
        ]);

        $account->users()->attach($user->id, [
            'role' => 'member',
            'status' => 'active',
            'joined_at' => now(),
        ]);

        $response = $this->post(route('login.store'), [
            'email' => 'customer@example.test',
            'password' => 'password123',
        ]);

        $response->assertRedirect(route('customer.billing.index'));
        $this->assertAuthenticatedAs($user);
    }

    public function test_valid_admin_login_redirects_to_admin_dashboard(): void
    {
        [$user] = $this->createAccessContext();
        $user->forceFill([
            'email' => 'admin@example.test',
            'password' => 'password123',
        ])->save();

        $response = $this->post(route('login.store'), [
            'email' => 'admin@example.test',
            'password' => 'password123',
        ]);

        $response->assertRedirect(route('admin.dashboard'));
        $this->assertAuthenticatedAs($user->fresh());
    }

    public function test_invalid_login_fails_cleanly(): void
    {
        User::factory()->create([
            'email' => 'invalid-check@example.test',
            'password' => 'password123',
        ]);

        $response = $this->from(route('login'))->post(route('login.store'), [
            'email' => 'invalid-check@example.test',
            'password' => 'wrong-password',
        ]);

        $response->assertRedirect(route('login'));
        $response->assertSessionHasErrors('email');
        $this->assertGuest();
    }

    public function test_logout_works_and_invalidates_the_session_cleanly(): void
    {
        [$user] = $this->createAccessContext();

        $response = $this->actingAs($user)->post(route('logout'));

        $response->assertRedirect(route('login'));
        $this->assertGuest();
    }

    public function test_protected_routes_redirect_when_logged_out(): void
    {
        $customerResponse = $this->get(route('customer.dashboard'));
        $customerResponse->assertRedirect(route('login'));

        $adminResponse = $this->get(route('admin.dashboard'));
        $adminResponse->assertRedirect(route('login'));
    }

    public function test_non_admin_user_requesting_an_admin_route_is_redirected_to_customer_billing_when_subscription_is_not_active(): void
    {
        $user = User::factory()->create([
            'email' => 'member@example.test',
            'password' => 'password123',
        ]);

        $account = Account::query()->create([
            'name' => 'Member Redirect Account',
            'slug' => 'member-redirect-account',
            'status' => 'active',
            'owner_user_id' => User::factory()->create()->id,
        ]);

        $account->users()->attach($user->id, [
            'role' => 'member',
            'status' => 'active',
            'joined_at' => now(),
        ]);

        $this->get(route('admin.dashboard'))->assertRedirect(route('login'));

        $response = $this->post(route('login.store'), [
            'email' => 'member@example.test',
            'password' => 'password123',
        ]);

        $response->assertRedirect(route('customer.billing.index'));
        $this->assertAuthenticatedAs($user);
        $this->actingAs($user)->get(route('admin.dashboard'))->assertForbidden();
    }

    public function test_new_signup_creates_a_customer_only_user_by_default(): void
    {
        $response = $this->post(route('signup.store'), [
            'name' => 'New Customer',
            'workspace_name' => 'New Customer Workspace',
            'email' => 'new-customer@example.test',
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ]);

        $user = User::query()->where('email', 'new-customer@example.test')->firstOrFail();
        $account = Account::query()->where('owner_user_id', $user->id)->firstOrFail();
        $membership = $account->users()->where('users.id', $user->id)->first();

        $response->assertRedirect(route('customer.billing.index'));
        $this->assertAuthenticatedAs($user);
        $this->assertNotNull($membership);
        $this->assertSame(AccountRole::Member->value, $membership->pivot->role);
        $this->assertTrue($user->fresh()->hasCustomerAccess());
        $this->assertFalse($user->fresh()->hasAdminAccess());
    }
}
