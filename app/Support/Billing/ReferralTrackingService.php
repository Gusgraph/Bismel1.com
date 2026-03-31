<?php
// اعوز بالله من الشياطين و ان يحضرون بسم الله الرحمن الرحيم الله لا إله إلا هو الحي القيوم
// Bismillahi ar-Rahmani ar-Rahim Audhu billahi min ash-shayatin wa an yahdurun Bismillah ar-Rahman ar-Rahim Allah la ilaha illa huwa al-hayy al-qayyum. Tamsa Allahu ala ayunihim
// version: x
// ======================================================
// - App Name: gusgraph-trading
// - Gusgraph LLC -
// - Author: Gus Kazem
// - https://Gusgraph.com
// - File Path: app/Support/Billing/ReferralTrackingService.php
// ======================================================

namespace App\Support\Billing;

use App\Models\Account;
use App\Models\BillingCheckoutSession;
use App\Models\ReferralAttribution;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cookie;

class ReferralTrackingService
{
    public const SESSION_CODE_KEY = 'billing.referral.code';
    public const SESSION_ATTRIBUTION_ID_KEY = 'billing.referral.attribution_id';

    public function captureFromRequest(Request $request): void
    {
        $referralCode = $this->sanitizeReferralCode(
            $request->query((string) config('stripe.referral.query_parameter', 'ref'))
                ?? $request->cookie((string) config('stripe.referral.cookie_name', 'gusgraph_referral_code'))
                ?? $request->session()->get(self::SESSION_CODE_KEY)
        );

        if (! $referralCode) {
            return;
        }

        $request->session()->put(self::SESSION_CODE_KEY, $referralCode);
        Cookie::queue(
            (string) config('stripe.referral.cookie_name', 'gusgraph_referral_code'),
            $referralCode,
            (int) config('stripe.referral.cookie_minutes', 43200)
        );

        $sessionId = $request->session()->getId();

        if (! $sessionId) {
            return;
        }

        $attribution = ReferralAttribution::query()->firstOrNew([
            'session_id' => $sessionId,
            'referral_code' => $referralCode,
        ]);

        $attribution->forceFill([
            'user_id' => $request->user()?->getKey() ?? $attribution->user_id,
            'landing_path' => $attribution->landing_path ?? $request->path(),
            'landing_url' => $attribution->landing_url ?? $request->fullUrl(),
            'first_seen_at' => $attribution->first_seen_at ?? now(),
            'last_seen_at' => now(),
            'metadata' => array_merge($attribution->metadata ?? [], [
                'ip' => $request->ip(),
                'user_agent' => substr((string) $request->userAgent(), 0, 1000),
            ]),
        ])->save();

        $request->session()->put(self::SESSION_ATTRIBUTION_ID_KEY, $attribution->getKey());
    }

    public function currentCode(Request $request): ?string
    {
        return $this->sanitizeReferralCode(
            $request->session()->get(self::SESSION_CODE_KEY)
                ?? $request->cookie((string) config('stripe.referral.cookie_name', 'gusgraph_referral_code'))
        );
    }

    public function currentAttribution(Request $request, ?Account $account = null): ?ReferralAttribution
    {
        $attributionId = $request->session()->get(self::SESSION_ATTRIBUTION_ID_KEY);

        $attribution = $attributionId
            ? ReferralAttribution::query()->find($attributionId)
            : null;

        if (! $attribution) {
            $sessionId = $request->session()->getId();
            $referralCode = $this->currentCode($request);

            if (! $sessionId || ! $referralCode) {
                return null;
            }

            $attribution = ReferralAttribution::query()
                ->where('session_id', $sessionId)
                ->where('referral_code', $referralCode)
                ->latest('id')
                ->first();
        }

        if ($attribution && $account && $attribution->account_id === null) {
            $attribution->forceFill(['account_id' => $account->getKey()])->save();
        }

        if ($attribution) {
            $request->session()->put(self::SESSION_ATTRIBUTION_ID_KEY, $attribution->getKey());
        }

        return $attribution;
    }

    public function attachCheckout(
        Request $request,
        BillingCheckoutSession $checkoutSession,
        ?Account $account = null
    ): ?ReferralAttribution {
        $attribution = $this->currentAttribution($request, $account ?? $checkoutSession->account);

        if (! $attribution) {
            return null;
        }

        $attribution->forceFill([
            'account_id' => $account?->getKey() ?? $checkoutSession->account_id,
            'user_id' => $request->user()?->getKey() ?? $attribution->user_id,
            'checkout_started_at' => now(),
            'last_seen_at' => now(),
        ])->save();

        $checkoutSession->forceFill([
            'referral_attribution_id' => $attribution->getKey(),
            'referral_code' => $attribution->referral_code,
        ])->save();

        return $attribution;
    }

    public function sanitizeReferralCode(mixed $value): ?string
    {
        if (! is_string($value)) {
            return null;
        }

        $normalized = strtoupper(trim($value));
        $normalized = preg_replace('/[^A-Z0-9_-]/', '', $normalized ?? '');

        return $normalized !== '' ? $normalized : null;
    }
}
