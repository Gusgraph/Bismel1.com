<?php
// اعوز بالله من الشياطين و ان يحضرون بسم الله الرحمن الرحيم الله لا إله إلا هو الحي القيوم
// Bismillahi ar-Rahmani ar-Rahim Audhu billahi min ash-shayatin wa an yahdurun Bismillah ar-Rahman ar-Rahim Allah la ilaha illa huwa al-hayy al-qayyum. Tamsa Allahu ala ayunihim
// version: x
// ======================================================
// - App Name: bismel1.com
// - Gusgraph LLC -
// - Author: Gus Kazem
// - https://Gusgraph.com
// - File Path: resources/views/customer/partials/plan-catalog.blade.php
// ======================================================
?>
<form method="POST" action="{{ route('customer.billing.checkout.store') }}" class="ui-list__stack">
    @csrf

    @if (!empty($activeReferralCode))
        <div class="ui-list__meta">
            <small>Referral tracked: <strong>{{ $activeReferralCode }}</strong>. Eligible base plans will use affiliate pricing at checkout.</small>
        </div>
    @endif

    <div class="ui-list__stack">
        <strong>Base Plans</strong>
        @forelse (($basePlans ?? []) as $plan)
            <label class="ui-list__stack" style="border: 1px solid rgba(148, 163, 184, 0.18); border-radius: 11px; padding: 0.85rem;">
                <span class="ui-inline-copy">
                    <input type="radio" name="base_plan_code" value="{{ $plan['code'] }}" @checked(old('base_plan_code', collect($basePlans ?? [])->firstWhere('is_current', true)['code'] ?? null) === $plan['code'])>
                    <strong>{{ $plan['label'] }}</strong>
                </span>
                <small>Standard price: {{ $plan['price'] }}</small>
                @if (!empty($activeReferralCode) && !empty($plan['affiliate_price']))
                    <small>Affiliate checkout price: {{ $plan['affiliate_price'] }}</small>
                @endif
                <small>{{ $plan['summary'] }}</small>
                <small>{{ $plan['checkout_ready'] ? 'Ready to purchase.' : 'Purchase is temporarily unavailable for this plan.' }}</small>
            </label>
        @empty
            <p><small>No base plans available.</small></p>
        @endforelse
    </div>

    <div class="ui-list__stack">
        <strong>Add-Ons</strong>
        <div class="ui-list__meta">
            <small>Add-ons require a selected base plan and always use standard monthly pricing.</small>
        </div>
        @forelse (($addOnPlans ?? []) as $plan)
            <label class="ui-list__stack" style="border: 1px solid rgba(148, 163, 184, 0.18); border-radius: 11px; padding: 0.85rem;">
                <span class="ui-inline-copy">
                    <input type="checkbox" name="addon_codes[]" value="{{ $plan['code'] }}" @checked(collect(old('addon_codes', []))->contains($plan['code']))>
                    <strong>{{ $plan['label'] }}</strong>
                </span>
                <small>Standard price: {{ $plan['price'] }}</small>
                <small>{{ $plan['summary'] }}</small>
                <span class="ui-inline-copy">
                    <small>Quantity</small>
                    <input type="number" min="1" max="25" name="addon_quantities[{{ $plan['code'] }}]" value="{{ old('addon_quantities.'.$plan['code'], 1) }}" style="max-width: 89px;">
                </span>
            </label>
        @empty
            <p><small>No add-ons available.</small></p>
        @endforelse
    </div>

    <button type="submit">Continue to purchase</button>

    <div class="ui-list__meta">
        <small>Checkout continues in Stripe. Access turns on after the subscription is confirmed and active.</small>
    </div>
</form>
