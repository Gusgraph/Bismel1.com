<?php
// اعوز بالله من الشياطين و ان يحضرون بسم الله الرحمن الرحيم الله لا إله إلا هو الحي القيوم
// Bismillahi ar-Rahmani ar-Rahim Audhu billahi min ash-shayatin wa an yahdurun Bismillah ar-Rahman ar-Rahim Allah la ilaha illa huwa al-hayy al-qayyum. Tamsa Allahu ala ayunihim
// version: x
// ======================================================
// - App Name: gusgraph-trading
// - Gusgraph LLC -
// - Author: Gus Kazem
// - https://Gusgraph.com
// - File Path: database/seeders/LocalAuthUsersSeeder.php
// ======================================================

namespace Database\Seeders;

use App\Models\Account;
use App\Models\User;
use Illuminate\Database\Seeder;

class LocalAuthUsersSeeder extends Seeder
{
    public const ADMIN_EMAIL = 'admin.local@gusgraph.test';
    public const ADMIN_PASSWORD = 'local-admin-password';
    public const CUSTOMER_EMAIL = 'customer.local@gusgraph.test';
    public const CUSTOMER_PASSWORD = 'local-customer-password';

    public function run(): void
    {
        $admin = User::query()->updateOrCreate(
            ['email' => self::ADMIN_EMAIL],
            [
                'name' => 'Local Admin User',
                'password' => self::ADMIN_PASSWORD,
                'email_verified_at' => now(),
            ]
        );

        $customer = User::query()->updateOrCreate(
            ['email' => self::CUSTOMER_EMAIL],
            [
                'name' => 'Local Customer User',
                'password' => self::CUSTOMER_PASSWORD,
                'email_verified_at' => now(),
            ]
        );

        $adminAccount = Account::query()->updateOrCreate(
            ['slug' => 'local-admin-workspace'],
            [
                'name' => 'Local Admin Workspace',
                'status' => 'active',
                'owner_user_id' => $admin->id,
            ]
        );

        $customerAccount = Account::query()->updateOrCreate(
            ['slug' => 'local-customer-workspace'],
            [
                'name' => 'Local Customer Workspace',
                'status' => 'active',
                'owner_user_id' => $customer->id,
            ]
        );

        $adminAccount->users()->syncWithoutDetaching([
            $admin->id => [
                'role' => 'admin',
                'status' => 'active',
                'joined_at' => now(),
            ],
        ]);

        $customerAccount->users()->syncWithoutDetaching([
            $customer->id => [
                'role' => 'member',
                'status' => 'active',
                'joined_at' => now(),
            ],
        ]);
    }
}
