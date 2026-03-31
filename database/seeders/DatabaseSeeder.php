<?php
// اعوز بالله من الشياطين و ان يحضرون بسم الله الرحمن الرحيم الله لا إله إلا هو الحي القيوم
// Bismillahi ar-Rahmani ar-Rahim Audhu billahi min ash-shayatin wa an yahdurun Bismillah ar-Rahman ar-Rahim Allah la ilaha illa huwa al-hayy al-qayyum. Tamsa Allahu ala ayunihim
// version: x
// ======================================================
// - App Name: gusgraph-trading
// - Gusgraph LLC -
// - Author: Gus Kazem
// - https://Gusgraph.com
// - File Path: database/seeders/DatabaseSeeder.php
// ======================================================

namespace Database\Seeders;

use App\Models\Account;
use App\Models\ActivityLog;
use App\Models\ApiKey;
use App\Models\ApiLicense;
use App\Models\AuditLog;
use App\Models\BrokerConnection;
use App\Models\BrokerCredential;
use App\Models\Invoice;
use App\Models\Subscription;
use App\Models\SubscriptionPlan;
use App\Models\SystemSetting;
use App\Models\User;
use Carbon\CarbonImmutable;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $seededAt = CarbonImmutable::create(2026, 1, 15, 12, 0, 0, 'UTC');

        Schema::disableForeignKeyConstraints();

        foreach ([
            'account_user',
            'api_keys',
            'api_licenses',
            'broker_credentials',
            'broker_connections',
            'invoices',
            'subscriptions',
            'subscription_plans',
            'activity_logs',
            'audit_logs',
            'system_settings',
            'accounts',
            'users',
        ] as $table) {
            DB::table($table)->delete();
        }

        Schema::enableForeignKeyConstraints();

        $admin = User::factory()->create([
            'name' => 'Local Admin',
            'email' => 'admin.local@gusgraph.test',
        ]);

        $customerOwner = User::factory()->create([
            'name' => 'Amina Carter',
            'email' => 'amina@gusgraph.test',
        ]);

        $customerAnalyst = User::factory()->create([
            'name' => 'Jonah Reed',
            'email' => 'jonah@gusgraph.test',
        ]);

        $starterPlan = SubscriptionPlan::factory()->create([
            'name' => 'Starter Workspace',
            'code' => 'STARTER_LOCAL',
            'price' => 99,
            'currency' => 'USD',
            'interval' => 'monthly',
        ]);

        $proPlan = SubscriptionPlan::factory()->create([
            'name' => 'Pro Workspace',
            'code' => 'PRO_LOCAL',
            'price' => 249,
            'currency' => 'USD',
            'interval' => 'monthly',
        ]);

        $activeAccount = Account::query()->create([
            'name' => 'Northwind Alpha',
            'slug' => 'northwind-alpha',
            'status' => 'active',
            'owner_user_id' => $customerOwner->id,
        ]);

        $setupAccount = Account::query()->create([
            'name' => 'Sandbox Delta',
            'slug' => 'sandbox-delta',
            'status' => 'pending',
            'owner_user_id' => $admin->id,
        ]);

        $this->attachMembership($activeAccount, $customerOwner, 'owner', $seededAt->subDays(30));
        $this->attachMembership($activeAccount, $customerAnalyst, 'member', $seededAt->subDays(20));
        $this->attachMembership($setupAccount, $admin, 'owner', $seededAt->subDays(10));

        $activeSubscription = Subscription::query()->create([
            'account_id' => $activeAccount->id,
            'subscription_plan_id' => $proPlan->id,
            'status' => 'active',
            'starts_at' => $seededAt->subDays(14),
        ]);

        Subscription::query()->create([
            'account_id' => $setupAccount->id,
            'subscription_plan_id' => $starterPlan->id,
            'status' => 'trial',
            'trial_ends_at' => $seededAt->addDays(10),
            'starts_at' => $seededAt->subDays(2),
        ]);

        Invoice::query()->create([
            'account_id' => $activeAccount->id,
            'subscription_id' => $activeSubscription->id,
            'number' => 'INV-LOCAL-1001',
            'status' => 'paid',
            'subtotal' => 249.00,
            'total' => 249.00,
            'currency' => 'USD',
            'issued_at' => $seededAt->subDays(7),
            'paid_at' => $seededAt->subDays(6),
        ]);

        Invoice::query()->create([
            'account_id' => $activeAccount->id,
            'subscription_id' => $activeSubscription->id,
            'number' => 'INV-LOCAL-1002',
            'status' => 'open',
            'subtotal' => 249.00,
            'total' => 249.00,
            'currency' => 'USD',
            'issued_at' => $seededAt->subDay(),
        ]);

        $brokerConnection = BrokerConnection::query()->create([
            'account_id' => $activeAccount->id,
            'name' => 'Northwind Primary Broker',
            'broker' => 'placeholder_primary',
            'status' => 'connected',
            'connected_at' => $seededAt->subDays(10),
            'last_synced_at' => $seededAt->subMinutes(20),
        ]);

        BrokerCredential::query()->create([
            'broker_connection_id' => $brokerConnection->id,
            'label' => 'Northwind Primary Broker Local Dev',
            'status' => 'saved',
            'credential_payload' => [
                'provider' => 'placeholder_primary',
                'provider_label' => 'Placeholder Primary',
                'account_label' => 'Northwind Primary Broker',
                'access_mode' => 'read_only',
                'environment' => 'sandbox',
                'access_key_id' => 'LOCALKEY-ALPHA001',
                'access_secret' => 'local-secret-alpha01',
                'saved_via' => 'database_seeder',
            ],
            'last_used_at' => $seededAt->subHours(3),
        ]);

        $license = ApiLicense::query()->create([
            'account_id' => $activeAccount->id,
            'name' => 'Northwind Workspace License',
            'status' => 'active',
            'starts_at' => $seededAt->subDays(14),
            'expires_at' => $seededAt->addMonth(),
        ]);

        $seedToken = 'local-token-alpha-0001';

        ApiKey::query()->create([
            'api_license_id' => $license->id,
            'name' => 'Northwind Primary Key',
            'key_hash' => hash('sha256', $license->id.'|'.$seedToken),
            'secret_hint' => $seedToken,
            'status' => 'ready',
            'last_used_at' => $seededAt->subHours(2),
            'expires_at' => $seededAt->addMonth(),
        ]);

        ActivityLog::query()->create([
            'account_id' => $activeAccount->id,
            'user_id' => $customerOwner->id,
            'type' => 'workspace_reviewed',
            'level' => 'info',
            'message' => 'Workspace readiness reviewed locally for the seeded MVP fixture.',
            'context' => ['source' => 'database_seeder'],
            'created_at' => $seededAt->subHour(),
            'updated_at' => $seededAt->subHour(),
        ]);

        AuditLog::query()->create([
            'account_id' => $activeAccount->id,
            'user_id' => $admin->id,
            'action' => 'license_inventory_reviewed',
            'target_type' => ApiLicense::class,
            'target_id' => $license->id,
            'summary' => 'Seeded local license inventory reviewed with token=[masked] metadata only.',
            'context' => ['source' => 'database_seeder'],
            'created_at' => $seededAt->subMinutes(30),
            'updated_at' => $seededAt->subMinutes(30),
        ]);

        SystemSetting::query()->create([
            'runtime_mode' => 'local',
            'review_channel' => 'manual',
            'status_level' => 'medium',
            'created_at' => $seededAt,
            'updated_at' => $seededAt,
        ]);
    }

    protected function attachMembership(Account $account, User $user, string $role, CarbonImmutable $joinedAt): void
    {
        $account->users()->attach($user->id, [
            'role' => $role,
            'status' => 'active',
            'joined_at' => $joinedAt,
        ]);
    }
}
