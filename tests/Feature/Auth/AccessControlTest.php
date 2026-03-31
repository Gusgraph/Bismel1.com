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
}
