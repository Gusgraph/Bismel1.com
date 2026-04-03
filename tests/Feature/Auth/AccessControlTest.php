<?php
// اعوز بالله من الشياطين و ان يحضرون بسم الله الرحمن الرحيم الله لا إله إلا هو الحي القيوم
// Bismillahi ar-Rahmani ar-Rahim Audhu billahi min ash-shayatin wa an yahdurun Bismillah ar-Rahman ar-Rahim Allah la ilaha illa huwa al-hayy al-qayyum. Tamsa Allahu ala ayunihim
// version: x
// ======================================================
// - App Name: gusgraph-trading
// - Gusgraph LLC -
// - Author: Gus Kazem
// - https://Gusgraph.com
// - File Path: tests/Feature/Auth/AccessControlTest.php
// ======================================================

namespace Tests\Feature\Auth;

use Database\Seeders\LocalAuthUsersSeeder;
use App\Models\Account;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Concerns\CreatesAccessContext;
use Tests\TestCase;

class AccessControlTest extends TestCase
{
    use CreatesAccessContext;
    use RefreshDatabase;

    public function test_customer_routes_require_login(): void
    {
        $response = $this->get(route('customer.dashboard'));

        $response->assertRedirect(route('login'));
    }

    public function test_admin_routes_require_login(): void
    {
        $response = $this->get(route('admin.dashboard'));

        $response->assertRedirect(route('login'));
    }

    public function test_customer_access_allows_an_authenticated_user_with_customer_access(): void
    {
        [$user] = $this->createAccessContext();

        $response = $this->actingAs($user)->get(route('customer.dashboard'));

        $response->assertOk();
    }

    public function test_admin_routes_require_structured_admin_access(): void
    {
        $user = User::factory()->create();
        $account = Account::query()->create([
            'name' => 'Member Access Account',
            'slug' => 'member-access-account',
            'status' => 'active',
            'owner_user_id' => User::factory()->create()->id,
        ]);

        $account->users()->attach($user->id, [
            'role' => 'member',
            'status' => 'active',
            'joined_at' => now(),
        ]);

        $response = $this->actingAs($user)->get(route('admin.dashboard'));

        $response->assertForbidden();
    }

    public function test_customer_only_member_can_access_customer_routes_but_not_admin_routes(): void
    {
        $user = User::factory()->create();
        $account = Account::query()->create([
            'name' => 'Customer Member Account',
            'slug' => 'customer-member-account',
            'status' => 'active',
            'owner_user_id' => User::factory()->create()->id,
        ]);

        $account->users()->attach($user->id, [
            'role' => 'member',
            'status' => 'active',
            'joined_at' => now(),
        ]);

        $customerResponse = $this->actingAs($user)->get(route('customer.dashboard'));
        $adminResponse = $this->actingAs($user)->get(route('admin.dashboard'));

        $customerResponse->assertOk();
        $adminResponse->assertForbidden();
    }

    public function test_customer_only_member_does_not_see_admin_workspace_navigation(): void
    {
        $user = User::factory()->create();
        $account = Account::query()->create([
            'name' => 'Navigation Member Account',
            'slug' => 'navigation-member-account',
            'status' => 'active',
            'owner_user_id' => User::factory()->create()->id,
        ]);

        $account->users()->attach($user->id, [
            'role' => 'member',
            'status' => 'active',
            'joined_at' => now(),
        ]);

        $response = $this->actingAs($user)->get(route('customer.dashboard'));

        $response->assertOk();
        $response->assertSeeText('Customer Workspace');
        $response->assertDontSeeText('Admin Workspace');
        $response->assertDontSee(route('admin.dashboard'));
    }

    public function test_admin_access_user_can_still_open_customer_routes_under_current_code(): void
    {
        [$user] = $this->createAccessContext();

        $adminResponse = $this->actingAs($user)->get(route('admin.dashboard'));
        $customerResponse = $this->actingAs($user)->get(route('customer.dashboard'));

        $adminResponse->assertOk();
        $customerResponse->assertOk();
    }

    public function test_plain_account_ownership_without_admin_membership_is_not_admin_access(): void
    {
        $user = User::factory()->create();

        Account::query()->create([
            'name' => 'Owned But Customer Only',
            'slug' => 'owned-but-customer-only',
            'status' => 'active',
            'owner_user_id' => $user->id,
        ]);

        $this->assertTrue($user->fresh()->hasCustomerAccess());
        $this->assertFalse($user->fresh()->hasAdminAccess());
    }

    public function test_local_auth_demo_users_match_customer_and_admin_roles(): void
    {
        $this->seed(LocalAuthUsersSeeder::class);

        $admin = User::query()->where('email', LocalAuthUsersSeeder::ADMIN_EMAIL)->firstOrFail();
        $customer = User::query()->where('email', LocalAuthUsersSeeder::CUSTOMER_EMAIL)->firstOrFail();

        $this->assertTrue($admin->hasCustomerAccess());
        $this->assertTrue($admin->hasAdminAccess());
        $this->assertTrue($customer->hasCustomerAccess());
        $this->assertFalse($customer->hasAdminAccess());
    }
}
