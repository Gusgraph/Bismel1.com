<?php
// اعوز بالله من الشياطين و ان يحضرون بسم الله الرحمن الرحيم الله لا إله إلا هو الحي القيوم
// Bismillahi ar-Rahmani ar-Rahim Audhu billahi min ash-shayatin wa an yahdurun Bismillah ar-Rahman ar-Rahim Allah la ilaha illa huwa al-hayy al-qayyum. Tamsa Allahu ala ayunihim
// version: x
// ======================================================
// - App Name: bismel1.com
// - Gusgraph LLC -
// - Author: Gus Kazem
// - https://Gusgraph.com
// - File Path: app/Http/Controllers/Auth/SessionController.php
// ======================================================

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use App\Models\Account;
use App\Models\User;
use App\Support\Billing\Bismel1EntitlementService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class SessionController extends Controller
{
    public function create(Request $request)
    {
        if ($request->user()) {
            return redirect()->to($this->defaultPathFor($request->user()));
        }

        return view('auth.login');
    }

    public function store(LoginRequest $request): RedirectResponse
    {
        $request->authenticate();
        $request->session()->regenerate();

        $user = $request->user();

        if (! $user instanceof User || (! $user->hasCustomerAccess() && ! $user->hasAdminAccess())) {
            Auth::guard('web')->logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();

            return back()
                ->withInput($request->safe()->only('email'))
                ->withErrors([
                    'email' => 'Your account does not have access to the workspace.',
                ]);
        }

        $this->forgetUnauthorizedIntendedLocation($request, $user);

        return redirect()->intended($this->defaultPathFor($user));
    }

    public function destroy(Request $request): RedirectResponse
    {
        Auth::guard('web')->logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login');
    }

    protected function defaultPathFor(User $user): string
    {
        if ($user->hasAdminAccess()) {
            return route('admin.dashboard');
        }

        if ($user->hasCustomerAccess()) {
            $account = $this->defaultCustomerAccountFor($user);

            if ($account && ! app(Bismel1EntitlementService::class)->resolve($account)['subscription_active']) {
                return route('customer.billing.index');
            }

            return route('customer.dashboard');
        }

        return route('login');
    }

    protected function defaultCustomerAccountFor(User $user): ?Account
    {
        $ownedAccount = $user->ownedAccounts()
            ->orderBy('name')
            ->first();

        if ($ownedAccount) {
            return $ownedAccount;
        }

        return $user->accounts()
            ->wherePivot('status', 'active')
            ->orderBy('accounts.name')
            ->first();
    }

    protected function forgetUnauthorizedIntendedLocation(Request $request, User $user): void
    {
        $intended = $request->session()->get('url.intended');

        if (! is_string($intended)) {
            return;
        }

        $path = (string) parse_url($intended, PHP_URL_PATH);

        if ($path === '') {
            return;
        }

        if (str_starts_with($path, '/admin') && ! $user->hasAdminAccess()) {
            $request->session()->forget('url.intended');
        }

        if (str_starts_with($path, '/customer') && ! $user->hasCustomerAccess()) {
            $request->session()->forget('url.intended');
        }
    }
}
