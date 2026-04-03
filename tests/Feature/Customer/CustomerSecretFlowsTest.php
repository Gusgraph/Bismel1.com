<?php

namespace Tests\Feature\Customer;

use App\Models\ApiKey;
use App\Models\ApiLicense;
use App\Models\BrokerConnection;
use App\Models\BrokerCredential;
use App\Models\Subscription;
use App\Models\SubscriptionPlan;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\Concerns\CreatesAccessContext;
use Tests\TestCase;

class CustomerSecretFlowsTest extends TestCase
{
    use CreatesAccessContext;
    use RefreshDatabase;

    public function test_customer_broker_store_persists_credentials_and_only_renders_masked_metadata(): void
    {
        [$user, $account] = $this->createAccessContext();
        $plan = SubscriptionPlan::factory()->create(['name' => 'Broker Access Plan']);

        Subscription::query()->create([
            'account_id' => $account->id,
            'subscription_plan_id' => $plan->id,
            'status' => 'active',
            'starts_at' => now()->subDay(),
            'stripe_confirmed_at' => now()->subDay(),
        ]);

        $accessKeyId = 'LOCALKEY-1234567890';
        $accessSecret = 'secret-local-XY';

        $response = $this->actingAs($user)->post(route('customer.broker.store'), [
            'provider' => 'alpaca',
            'account_label' => 'Primary Local Broker',
            'access_mode' => 'read_only',
            'environment' => 'paper',
            'market_data_feed' => 'iex',
            'access_key_id' => $accessKeyId,
            'access_secret' => $accessSecret,
        ]);

        $response->assertRedirect(route('customer.broker.index'));
        $response->assertSessionHas('status', 'Alpaca access was saved locally as a separate linked account. Secret values remain encrypted at rest, only masked connection metadata is shown, and feed plus sync-readiness metadata are ready for later automation services.');

        $connection = BrokerConnection::query()->where('account_id', $account->id)->first();

        $this->assertNotNull($connection);
        $this->assertDatabaseHas('broker_connections', [
            'id' => $connection->id,
            'account_id' => $account->id,
            'broker' => 'alpaca',
            'name' => 'Primary Local Broker',
        ]);

        $credential = BrokerCredential::query()->where('broker_connection_id', $connection->id)->first();

        $this->assertNotNull($credential);
        $this->assertSame('saved', $credential->status);
        $this->assertSame($accessKeyId, $credential->credential_payload['access_key_id']);
        $this->assertSame($accessSecret, $credential->credential_payload['access_secret']);
        $this->assertSame('Alpaca Saved, encrypted at rest, key ****7890, secret ****XY, Paper, Read only', $credential->maskedSummary());

        $storedPayload = DB::table('broker_credentials')->where('id', $credential->id)->value('credential_payload');

        $this->assertIsString($storedPayload);
        $this->assertNotSame($accessKeyId, $storedPayload);
        $this->assertStringNotContainsString($accessKeyId, $storedPayload);
        $this->assertStringNotContainsString($accessSecret, $storedPayload);
    }

    public function test_customer_broker_page_shows_current_account_connection_and_credential_detail_without_raw_secrets(): void
    {
        [$user, $account] = $this->createAccessContext([
            'name' => 'Broker Detail Account',
            'slug' => 'broker-detail-account',
        ]);

        $connection = BrokerConnection::query()->create([
            'account_id' => $account->id,
            'name' => 'Customer Detail Broker',
            'broker' => 'placeholder_primary',
            'status' => 'connected',
            'connected_at' => now()->subHour(),
            'last_synced_at' => now()->subMinutes(10),
        ]);

        BrokerCredential::query()->create([
            'broker_connection_id' => $connection->id,
            'label' => 'Customer Detail Broker Local Dev',
            'status' => 'saved',
            'credential_payload' => [
                'access_key_id' => 'LOCALKEY-1234567890',
                'access_secret' => 'secret-local-XY',
                'environment' => 'sandbox',
                'access_mode' => 'read_only',
            ],
            'last_used_at' => now()->subMinutes(5),
        ]);

        $response = $this->actingAs($user)->get(route('customer.broker.index'));

        $response->assertOk();
        $response->assertSeeText('Customer Broker');
        $response->assertSeeText('Linked Account Inventory');
        $response->assertSeeText('Masked Credential Metadata');
        $response->assertSeeText('Broker Detail Account');
        $response->assertSeeText('broker-detail-account');
        $response->assertSeeText('Customer Detail Broker');
        $response->assertSeeText('Customer Detail Broker Local Dev');
        $response->assertSeeText('encrypted at rest');
        $response->assertSeeText('***7890');
        $response->assertSeeText('***XY');
        $response->assertDontSeeText('LOCALKEY-1234567890');
        $response->assertDontSeeText('secret-local-XY');
    }

    public function test_customer_broker_store_persists_to_the_current_users_account_instead_of_a_global_first_account(): void
    {
        [, $otherAccount] = $this->createAccessContext([
            'name' => 'A First Global Account',
            'slug' => 'a-first-global-account',
        ]);
        [$user, $account] = $this->createAccessContext([
            'name' => 'Z Current User Account',
            'slug' => 'z-current-user-account',
        ]);
        $plan = SubscriptionPlan::factory()->create(['name' => 'Scoped Broker Plan']);

        Subscription::query()->create([
            'account_id' => $account->id,
            'subscription_plan_id' => $plan->id,
            'status' => 'active',
            'starts_at' => now()->subDay(),
            'stripe_confirmed_at' => now()->subDay(),
        ]);

        $response = $this->actingAs($user)->post(route('customer.broker.store'), [
            'provider' => 'alpaca',
            'account_label' => 'Scoped Broker',
            'access_mode' => 'read_only',
            'environment' => 'paper',
            'market_data_feed' => 'iex',
            'access_key_id' => 'LOCALKEY-1234567890',
            'access_secret' => 'secret-local-XY',
        ]);

        $response->assertRedirect(route('customer.broker.index'));
        $this->assertDatabaseHas('broker_connections', [
            'account_id' => $account->id,
            'name' => 'Scoped Broker',
        ]);
        $this->assertDatabaseMissing('broker_connections', [
            'account_id' => $otherAccount->id,
            'name' => 'Scoped Broker',
        ]);
    }

    public function test_customer_broker_store_is_forbidden_when_the_user_has_no_customer_access(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->post(route('customer.broker.store'), [
            'provider' => 'alpaca',
            'account_label' => 'No Account Broker',
            'access_mode' => 'read_only',
            'environment' => 'paper',
            'market_data_feed' => 'iex',
            'access_key_id' => 'LOCALKEY-1234567890',
            'access_secret' => 'secret-local-XY',
        ]);

        $response->assertForbidden();
        $response->assertSeeText('That page is outside your current access.');
        $this->assertDatabaseCount('broker_connections', 0);
        $this->assertDatabaseCount('broker_credentials', 0);
    }

    public function test_customer_broker_store_only_shows_masked_values_after_redirect_back_to_the_index(): void
    {
        [$user, $account] = $this->createAccessContext();
        $plan = SubscriptionPlan::factory()->create(['name' => 'Masked Broker Plan']);

        Subscription::query()->create([
            'account_id' => $account->id,
            'subscription_plan_id' => $plan->id,
            'status' => 'active',
            'starts_at' => now()->subDay(),
            'stripe_confirmed_at' => now()->subDay(),
        ]);

        $response = $this->actingAs($user)
            ->followingRedirects()
            ->post(route('customer.broker.store'), [
                'provider' => 'alpaca',
                'account_label' => 'Masked Redirect Broker',
                'access_mode' => 'read_only',
                'environment' => 'paper',
                'market_data_feed' => 'iex',
                'access_key_id' => 'LOCALKEY-1234567890',
                'access_secret' => 'secret-local-XY',
            ]);

        $response->assertOk();
        $response->assertSeeText('Alpaca access was saved locally as a separate linked account. Secret values remain encrypted at rest, only masked connection metadata is shown, and feed plus sync-readiness metadata are ready for later automation services.');
        $response->assertSeeText('Masked Redirect Broker');
        $response->assertSeeText('***7890');
        $response->assertSeeText('***XY');
        $response->assertDontSeeText('LOCALKEY-1234567890');
        $response->assertDontSeeText('secret-local-XY');
    }

    public function test_customer_license_store_persists_key_material_and_only_renders_masked_metadata(): void
    {
        [$user, $account] = $this->createAccessContext();

        $tokenValue = 'token-local-1234567890';

        $response = $this->actingAs($user)
            ->followingRedirects()
            ->post(route('customer.license.store'), [
                'license_name' => 'Workspace API License',
                'key_name' => 'Primary Local Key',
                'token_value' => $tokenValue,
                'expires_at' => '2026-12-31 00:00:00',
            ]);

        $response->assertOk();
        $response->assertSeeText('License access was saved locally. Token values remain encrypted at rest and only masked license metadata is shown.');
        $response->assertSeeText($account->name);
        $response->assertSeeText('Workspace API License');
        $response->assertSeeText('***7890');
        $response->assertDontSeeText($tokenValue);

        $license = ApiLicense::query()->where('account_id', $account->id)->first();

        $this->assertNotNull($license);
        $this->assertDatabaseHas('api_licenses', [
            'id' => $license->id,
            'account_id' => $account->id,
            'name' => 'Workspace API License',
        ]);

        $apiKey = ApiKey::query()->where('api_license_id', $license->id)->first();

        $this->assertNotNull($apiKey);
        $this->assertSame(hash('sha256', $license->id.'|'.$tokenValue), $apiKey->key_hash);
        $this->assertSame($tokenValue, $apiKey->secret_hint);

        $storedSecretHint = DB::table('api_keys')->where('id', $apiKey->id)->value('secret_hint');

        $this->assertIsString($storedSecretHint);
        $this->assertNotSame($tokenValue, $storedSecretHint);
        $this->assertStringNotContainsString($tokenValue, $storedSecretHint);
    }

    public function test_customer_license_page_shows_current_account_license_and_key_detail_without_raw_tokens(): void
    {
        [$user, $account] = $this->createAccessContext([
            'name' => 'License Detail Account',
            'slug' => 'license-detail-account',
        ]);

        $license = ApiLicense::query()->create([
            'account_id' => $account->id,
            'name' => 'Customer Detail License',
            'status' => 'active',
            'starts_at' => now()->subDay(),
            'expires_at' => now()->addDays(30),
        ]);

        ApiKey::query()->create([
            'api_license_id' => $license->id,
            'name' => 'Customer Detail Key',
            'key_hash' => hash('sha256', 'customer-detail|token-local-7890'),
            'secret_hint' => 'token-local-7890',
            'status' => 'ready',
            'last_used_at' => now()->subHour(),
            'expires_at' => now()->addDays(30),
        ]);

        $response = $this->actingAs($user)->get(route('customer.license.index'));

        $response->assertOk();
        $response->assertSeeText('Customer License');
        $response->assertSeeText('License Inventory');
        $response->assertSeeText('API Key Detail');
        $response->assertSeeText('License Detail Account');
        $response->assertSeeText('license-detail-account');
        $response->assertSeeText('Customer Detail License');
        $response->assertSeeText('Customer Detail Key');
        $response->assertSeeText('Encrypted token ending in ***7890');
        $response->assertDontSeeText('token-local-7890');
    }

    public function test_customer_license_store_is_forbidden_when_the_user_has_no_customer_access(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->post(route('customer.license.store'), [
            'license_name' => 'No Account License',
            'key_name' => 'No Account Key',
            'token_value' => 'token-local-1234567890',
            'expires_at' => '2026-12-31 00:00:00',
        ]);

        $response->assertForbidden();
        $response->assertSeeText('That page is outside your current access.');
        $this->assertDatabaseCount('api_licenses', 0);
        $this->assertDatabaseCount('api_keys', 0);
    }
}
