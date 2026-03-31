<?php
// اعوز بالله من الشياطين و ان يحضرون بسم الله الرحمن الرحيم الله لا إله إلا هو الحي القيوم
// Bismillahi ar-Rahmani ar-Rahim Audhu billahi min ash-shayatin wa an yahdurun Bismillah ar-Rahman ar-Rahim Allah la ilaha illa huwa al-hayy al-qayyum. Tamsa Allahu ala ayunihim
// version: x
// ======================================================
// - App Name: gusgraph-trading
// - Gusgraph LLC -
// - Author: Gus Kazem
// - https://Gusgraph.com
// - File Path: app/Models/BillingCheckoutSession.php
// ======================================================

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class BillingCheckoutSession extends Model
{
    use HasFactory;

    protected $fillable = [
        'account_id',
        'user_id',
        'subscription_plan_id',
        'referral_attribution_id',
        'referral_code',
        'stripe_checkout_session_id',
        'stripe_customer_id',
        'status',
        'uses_affiliate_pricing',
        'selected_add_on_plan_codes',
        'success_url',
        'cancel_url',
        'metadata',
    ];

    protected function casts(): array
    {
        return [
            'uses_affiliate_pricing' => 'boolean',
            'selected_add_on_plan_codes' => 'array',
            'metadata' => 'array',
        ];
    }

    public function account(): BelongsTo
    {
        return $this->belongsTo(Account::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function subscriptionPlan(): BelongsTo
    {
        return $this->belongsTo(SubscriptionPlan::class);
    }

    public function referralAttribution(): BelongsTo
    {
        return $this->belongsTo(ReferralAttribution::class);
    }
}
