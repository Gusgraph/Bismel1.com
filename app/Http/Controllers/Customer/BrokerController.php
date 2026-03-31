<?php
// اعوز بالله من الشياطين و ان يحضرون بسم الله الرحمن الرحيم الله لا إله إلا هو الحي القيوم
// Bismillahi ar-Rahmani ar-Rahim Audhu billahi min ash-shayatin wa an yahdurun Bismillah ar-Rahman ar-Rahim Allah la ilaha illa huwa al-hayy al-qayyum. Tamsa Allahu ala ayunihim
// version: x
// ======================================================
// - App Name: gusgraph-trading
// - Gusgraph LLC -
// - Author: Gus Kazem
// - https://Gusgraph.com
// - File Path: app/Http/Controllers/Customer/BrokerController.php
// ======================================================

namespace App\Http\Controllers\Customer;

use App\Domain\Broker\Enums\BrokerConnectionStatus;
use App\Http\Controllers\Controller;
use App\Http\Requests\Customer\StoreBrokerCredentialRequest;
use App\Models\AlpacaAccount;
use App\Models\BrokerCredential;
use App\Support\Billing\Bismel1EntitlementService;
use App\Support\Broker\AlpacaAccountSyncService;
use App\Support\Customer\CurrentCustomerAccountResolver;
use App\Support\Navigation\CustomerNavigation;
use App\Support\ViewData\BrokerPageData;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\DB;
use Throwable;

class BrokerController extends Controller
{
    public function index(
        CurrentCustomerAccountResolver $currentCustomerAccountResolver,
        AlpacaAccountSyncService $alpacaAccountSyncService,
        Bismel1EntitlementService $bismel1EntitlementService,
    )
    {
        $account = $currentCustomerAccountResolver->resolveForPreset(request()->user(), 'broker');

        if ($account) {
            $alpacaAccountSyncService->syncLatestForAccount($account);
            $account = $account->fresh($currentCustomerAccountResolver->relationsForPreset('broker'));
        }

        $data = BrokerPageData::make($account, [
            'entitlements' => $bismel1EntitlementService->resolve($account),
        ]);

        return view('customer.broker.index', [
            'navItems' => CustomerNavigation::items(),
            'page' => $data['page'],
            'sectionLabel' => $data['sectionLabel'],
            'providers' => $data['providers'],
            'connectionDetails' => $data['connectionDetails'],
            'brokerCredentialChecklist' => $data['brokerCredentialChecklist'],
            'connectionInventory' => $data['connectionInventory'],
            'credentialInventory' => $data['credentialInventory'],
            'summary' => $data['summary'],
            'hasBrokerData' => $data['hasBrokerData'],
        ]);
    }

    public function create()
    {
        return view('customer.broker.create', [
            'navItems' => CustomerNavigation::items(),
            'page' => [
                'title' => 'Add Alpaca Connection',
                'subtitle' => 'Save one local Alpaca credential set for the current workspace with encrypted-at-rest handling, paper/live tracking, feed selection, and masked display.',
            ],
            'providerLabels' => ['alpaca' => 'Alpaca'],
            'feedLabels' => ['iex' => 'IEX', 'sip' => 'SIP'],
        ]);
    }

    public function store(
        StoreBrokerCredentialRequest $request,
        CurrentCustomerAccountResolver $currentCustomerAccountResolver,
        Bismel1EntitlementService $bismel1EntitlementService,
    ): RedirectResponse
    {
        $validated = $request->validated();
        $account = $currentCustomerAccountResolver->resolveCurrent($request->user());

        if (! $account) {
            return redirect()
                ->route('customer.broker.create')
                ->with('status', 'Broker access was not saved because no current customer workspace record is available.');
        }

        $brokerLinkingSummary = $bismel1EntitlementService->brokerLinkingSummary($account);

        if (! ($brokerLinkingSummary['allowed'] ?? false)) {
            return redirect()
                ->route('customer.broker.create')
                ->with('status', 'Alpaca access was not saved because '.$brokerLinkingSummary['summary'].'.');
        }

        try {
            DB::transaction(function () use ($account, $validated, $request): void {
                $provider = 'alpaca';
                $providerLabel = 'Alpaca';
                $hasExistingAlpacaAccount = $account->alpacaAccounts()->exists();

                $connection = $account->brokerConnections()->create([
                    'name' => $validated['account_label'],
                    'broker' => $provider,
                    'managed_by_user_id' => $request->user()?->getKey(),
                    'status' => BrokerConnectionStatus::Pending->value,
                ]);

                $credential = new BrokerCredential();

                $credential->fill([
                    'label' => $providerLabel.' '.ucfirst($validated['environment']).' API Access',
                    'provider' => $provider,
                    'status' => 'saved',
                    'environment' => $validated['environment'],
                    'access_mode' => $validated['access_mode'],
                    'credential_payload' => [
                        'provider' => $provider,
                        'provider_label' => $providerLabel,
                        'account_label' => $validated['account_label'],
                        'access_mode' => $validated['access_mode'],
                        'environment' => $validated['environment'],
                        'market_data_feed' => $validated['market_data_feed'],
                        'access_key_id' => $validated['access_key_id'],
                        'access_secret' => $validated['access_secret'],
                        'saved_via' => 'customer_broker_store',
                    ],
                    'key_last_four' => substr($validated['access_key_id'], -4),
                    'secret_hint' => substr($validated['access_secret'], -2),
                    'is_encrypted' => true,
                    'last_used_at' => null,
                ]);

                $connection->brokerCredentials()->save($credential);

                AlpacaAccount::query()->updateOrCreate(
                    ['id' => null],
                    [
                        'account_id' => $account->getKey(),
                        'broker_connection_id' => $connection->getKey(),
                        'name' => $validated['account_label'],
                        'environment' => $validated['environment'],
                        'broker_credential_id' => $credential->getKey(),
                        'data_feed' => $validated['market_data_feed'],
                        'status' => 'pending_sync',
                        'sync_status' => 'pending',
                        'trade_stream_status' => 'not_ready',
                        'is_primary' => ! $hasExistingAlpacaAccount,
                        'is_active' => true,
                        'last_synced_at' => null,
                        'last_account_sync_at' => null,
                        'last_positions_sync_at' => null,
                        'last_orders_sync_at' => null,
                        'metadata' => [
                            'access_mode' => $validated['access_mode'],
                            'market_data_feed' => $validated['market_data_feed'],
                            'credential_label' => $credential->label,
                            'managed_via' => 'customer_broker_store',
                        ],
                    ]
                );
            });
        } catch (Throwable) {
            return redirect()
                ->route('customer.broker.create')
                ->with('status', 'Alpaca access could not be saved locally. Secret values were not displayed.');
        }

        return redirect()
            ->route('customer.broker.index')
            ->with('status', 'Alpaca access was saved locally as a separate linked account. Secret values remain encrypted at rest, only masked connection metadata is shown, and feed plus sync-readiness metadata are ready for later automation services.');
    }
}
