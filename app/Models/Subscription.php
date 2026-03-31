<?php
// اعوز بالله من الشياطين و ان يحضرون بسم الله الرحمن الرحيم الله لا إله إلا هو الحي القيوم
// Bismillahi ar-Rahmani ar-Rahim Audhu billahi min ash-shayatin wa an yahdurun Bismillah ar-Rahman ar-Rahim Allah la ilaha illa huwa al-hayy al-qayyum. Tamsa Allahu ala ayunihim
// version: x
// ======================================================
// - App Name: gusgraph-trading
// - Gusgraph LLC -
// - Author: Gus Kazem
// - https://Gusgraph.com
// - File Path: app/Models/Subscription.php
// ======================================================

namespace App\Models;

use App\Domain\Billing\Enums\SubscriptionStatus;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Subscription extends Model
{
    use HasFactory;

    protected $fillable = [
        'account_id',
        'subscription_plan_id',
        'stripe_subscription_id',
        'stripe_price_id',
        'status',
        'stripe_status',
        'last_stripe_event_id',
        'last_stripe_event_type',
        'stripe_confirmed_at',
        'trial_ends_at',
        'starts_at',
        'ends_at',
        'cancel_at',
        'cancel_at_period_end',
        'referral_attribution_id',
        'referral_code',
        'uses_affiliate_pricing',
        'metadata',
    ];

    protected function casts(): array
    {
        return [
            'status' => SubscriptionStatus::class,
            'stripe_confirmed_at' => 'datetime',
            'trial_ends_at' => 'datetime',
            'starts_at' => 'datetime',
            'ends_at' => 'datetime',
            'cancel_at' => 'datetime',
            'cancel_at_period_end' => 'boolean',
            'uses_affiliate_pricing' => 'boolean',
            'metadata' => 'array',
        ];
    }

    public function account(): BelongsTo
    {
        return $this->belongsTo(Account::class);
    }

    public function subscriptionPlan(): BelongsTo
    {
        return $this->belongsTo(SubscriptionPlan::class);
    }

    public function invoices(): HasMany
    {
        return $this->hasMany(Invoice::class);
    }

    public function items(): HasMany
    {
        return $this->hasMany(SubscriptionItem::class);
    }

    public function referralAttribution(): BelongsTo
    {
        return $this->belongsTo(ReferralAttribution::class);
    }
}
