<?php

namespace Tests\Feature\Support;

use App\Models\Account;
use App\Models\User;
use App\Support\Customer\CurrentCustomerAccountResolver;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Concerns\CreatesAccessContext;
use Tests\TestCase;

class CurrentCustomerAccountResolverTest extends TestCase
{
    use CreatesAccessContext;
    use RefreshDatabase;

    public function test_current_customer_account_resolver_prefers_owned_accounts_then_active_memberships_and_ignores_inactive_memberships(): void
    {
        $resolver = app(CurrentCustomerAccountResolver::class);

        $user = User::factory()->create();
        $ownedAccount = $this->createAccessibleAccount($user, [
            'name' => 'Owned Resolver Account',
            'slug' => 'owned-resolver-account',
        ]);
        $activeMembershipOwner = User::factory()->create();
        $activeMembershipAccount = Account::query()->create([
            'name' => 'Active Membership Account',
            'slug' => 'active-membership-account',
            'status' => 'active',
            'owner_user_id' => $activeMembershipOwner->id,
        ]);
        $activeMembershipAccount->users()->attach($user->id, [
            'role' => 'member',
            'status' => 'active',
            'joined_at' => now(),
        ]);

        $inactiveMembershipOwner = User::factory()->create();
        $inactiveMembershipAccount = Account::query()->create([
            'name' => 'Inactive Membership Account',
            'slug' => 'inactive-membership-account',
            'status' => 'active',
            'owner_user_id' => $inactiveMembershipOwner->id,
        ]);
        $inactiveMembershipAccount->users()->attach($user->id, [
            'role' => 'member',
            'status' => 'inactive',
            'joined_at' => now(),
        ]);

        $this->assertSame($ownedAccount->id, $resolver->resolveCurrent($user)?->id);

        $ownedAccount->users()->detach($user->id);
        $ownedAccount->delete();

        $this->assertSame($activeMembershipAccount->id, $resolver->resolveCurrent($user)?->id);
        $this->assertNotSame($inactiveMembershipAccount->id, $resolver->resolveCurrent($user)?->id);
        $this->assertNull($resolver->resolveCurrent(User::factory()->create()));
    }
}
