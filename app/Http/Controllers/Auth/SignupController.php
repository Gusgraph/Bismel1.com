<?php
// اعوز بالله من الشياطين و ان يحضرون بسم الله الرحمن الرحيم الله لا إله إلا هو الحي القيوم
// Bismillahi ar-Rahmani ar-Rahim Audhu billahi min ash-shayatin wa an yahdurun Bismillah ar-Rahman ar-Rahim Allah la ilaha illa huwa al-hayy al-qayyum. Tamsa Allahu ala ayunihim
// version: x
// ======================================================
// - App Name: bismel1.com
// - Gusgraph LLC -
// - Author: Gus Kazem
// - https://Gusgraph.com
// - File Path: app/Http/Controllers/Auth/SignupController.php
// ======================================================

namespace App\Http\Controllers\Auth;

use App\Domain\Account\Enums\AccountRole;
use App\Domain\Account\Enums\AccountStatus;
use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\SignupRequest;
use App\Models\Account;
use App\Models\SubscriptionPlan;
use App\Models\User;
use App\Support\Billing\StripeCheckoutService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use RuntimeException;

class SignupController extends Controller
{
    public function create(Request $request)
    {
        if ($request->user()) {
            return redirect()->route('customer.billing.index');
        }

        $selectedPlanCode = $request->query('plan');
        $selectedPlan = is_string($selectedPlanCode) && $selectedPlanCode !== ''
            ? SubscriptionPlan::query()->whereIn('code', SubscriptionPlan::publicCatalogCodes())->where('code', $selectedPlanCode)->first()
            : null;

        $basePlans = SubscriptionPlan::query()
            ->where('status', 'active')
            ->whereIn('code', SubscriptionPlan::productionBaseCodes())
            ->where('plan_type', 'base')
            ->orderBy('sort_order')
            ->get(['code', 'name']);

        return view('auth.signup', [
            'selectedPlanCode' => $selectedPlanCode,
            'selectedPlan' => $selectedPlan,
            'basePlans' => $basePlans,
        ]);
    }

    public function store(SignupRequest $request, StripeCheckoutService $stripeCheckoutService): RedirectResponse
    {
        $user = DB::transaction(function () use ($request): User {
            $validated = $request->validated();

            $user = User::query()->create([
                'name' => $validated['name'],
                'email' => $validated['email'],
                'password' => $validated['password'],
                'email_verified_at' => now(),
            ]);

            $workspaceName = trim((string) $validated['workspace_name']);
            $baseSlug = Str::slug($workspaceName);
            $slug = $baseSlug !== '' ? $baseSlug : 'bismel1-workspace';
            $candidate = $slug;
            $counter = 2;

            while (Account::query()->where('slug', $candidate)->exists()) {
                $candidate = $slug.'-'.$counter;
                $counter++;
            }

            $account = Account::query()->create([
                'name' => $workspaceName,
                'slug' => $candidate,
                'status' => AccountStatus::Active->value,
                'owner_user_id' => $user->getKey(),
            ]);

            $account->users()->syncWithoutDetaching([
                $user->getKey() => [
                    'role' => AccountRole::Member->value,
                    'status' => 'active',
                    'joined_at' => now(),
                ],
            ]);

            return $user;
        });

        Auth::login($user);
        $request->session()->regenerate();

        $checkoutRedirect = $this->startCheckoutIfRequested($request, $user, $stripeCheckoutService);

        if ($checkoutRedirect) {
            return $checkoutRedirect;
        }

        return redirect()
            ->route('customer.billing.index')
            ->with('billing_checkout_banner', [
                'title' => 'Choose your Bismel1 plan',
                'body' => 'Your workspace is ready. Pick a plan to activate billing and continue.',
            ]);
    }

    protected function startCheckoutIfRequested(SignupRequest $request, User $user, StripeCheckoutService $stripeCheckoutService): ?RedirectResponse
    {
        $selectedPlanCode = trim((string) $request->input('selected_plan_code', ''));

        if ($selectedPlanCode === '') {
            return null;
        }

        $account = $user->ownedAccounts()->orderBy('name')->first();

        if (! $account) {
            return null;
        }

        $selectedPlan = $this->ensurePublicCheckoutPlan($selectedPlanCode);

        if (! $selectedPlan) {
            return redirect()->route('customer.billing.index');
        }

        $basePlan = $selectedPlan;
        $addOnSelections = [];

        if ($selectedPlan->plan_type === 'addon') {
            $basePlanCode = trim((string) $request->input('selected_base_plan_code', ''));
            $basePlan = $this->ensurePublicCheckoutPlan($basePlanCode);

            if (! $basePlan || $basePlan->plan_type !== 'base' || ! in_array($basePlan->code, SubscriptionPlan::productionBaseCodes(), true)) {
                return redirect()
                    ->route('signup', ['plan' => $selectedPlanCode])
                    ->withInput($request->except('password', 'password_confirmation'))
                    ->withErrors([
                        'selected_base_plan_code' => 'Select a base plan to continue with this add-on.',
                    ]);
            }

            $addOnSelections[] = [
                'plan' => $selectedPlan,
                'quantity' => 1,
            ];
        }

        try {
            $result = $stripeCheckoutService->createCheckoutSession(
                $request,
                $account,
                $user,
                $basePlan,
                $addOnSelections,
            );
        } catch (RuntimeException $exception) {
            return redirect()
                ->route('customer.billing.index')
                ->with('billing_checkout_banner', [
                    'title' => 'Checkout unavailable for the selected plan',
                    'body' => 'Your workspace was created, but the selected plan could not open Stripe checkout right now. Open billing and choose a plan that is ready in the current Stripe mode.',
                ]);
        }

        $checkoutUrl = data_get($result, 'stripe_checkout_session.url');

        return is_string($checkoutUrl) && trim($checkoutUrl) !== ''
            ? redirect()->away($checkoutUrl)
            : redirect()->route('customer.billing.index');
    }

    protected function ensurePublicCheckoutPlan(string $code): ?SubscriptionPlan
    {
        if (! in_array($code, SubscriptionPlan::publicCatalogCodes(), true)) {
            return null;
        }

        if ($code === 'BISMILLAH1_BOT_SPEED_EXECUTE') {
            return SubscriptionPlan::query()->updateOrCreate(
                ['code' => $code],
                [
                    'name' => 'Speed Executor',
                    'plan_type' => 'base',
                    'product_family' => 'testing',
                    'status' => 'active',
                    'price' => 10,
                    'currency' => 'USD',
                    'interval' => 'monthly',
                    'billing_model' => 'monthly',
                    'sort_order' => 130,
                    'stripe_lookup_key' => 'bismillah1-bot-speed-execute',
                    'stripe_price_id' => config('stripe.price_ids.'.$code),
                    'metadata' => ['testing_plan' => true],
                ]
            );
        }

        $plan = SubscriptionPlan::query()->where('code', $code)->first();

        if (! $plan) {
            return null;
        }

        if ($code === 'BISMILLAH1_BOT_EXECUTE_BASIC' && $plan->plan_type !== 'base') {
            $plan->forceFill(['plan_type' => 'base'])->save();
            $plan->refresh();
        }

        return $plan;
    }
}
