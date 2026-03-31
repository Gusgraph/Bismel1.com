<?php

namespace Tests\Concerns;

use App\Models\Account;
use App\Models\User;

trait CreatesAccessContext
{
    protected function createAccessContext(array $accountAttributes = []): array
    {
        $user = User::factory()->create();
        $account = $this->createAccessibleAccount($user, $accountAttributes);

        return [$user, $account];
    }

    protected function createAccessibleAccount(User $user, array $accountAttributes = []): Account
    {
        $account = Account::query()->create(array_merge([
            'name' => 'Test Access Account',
            'slug' => 'test-access-account',
            'status' => 'active',
            'owner_user_id' => $user->id,
        ], $accountAttributes));

        $account->users()->attach($user->id, [
            'role' => 'owner',
            'status' => 'active',
            'joined_at' => now(),
        ]);

        return $account;
    }
}
