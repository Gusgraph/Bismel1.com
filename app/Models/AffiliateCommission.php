<?php
// اعوز بالله من الشياطين و ان يحضرون بسم الله الرحمن الرحيم الله لا إله إلا هو الحي القيوم
// Bismillahi ar-Rahmani ar-Rahim Audhu billahi min ash-shayatin wa an yahdurun Bismillah ar-Rahman ar-Rahim Allah la ilaha illa huwa al-hayy al-qayyum. Tamsa Allahu ala ayunihim
// version: x
// ======================================================
// - App Name: gusgraph-trading
// - Gusgraph LLC -
// - Author: Gus Kazem
// - https://Gusgraph.com
// - File Path: app/Models/AffiliateCommission.php
// ======================================================

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AffiliateCommission extends Model
{
    use HasFactory;

    protected $fillable = [
        'referral_attribution_id',
        'subscription_id',
        'invoice_id',
        'affiliate_username',
        'commission_status',
        'commission_rate',
        'commission_base_amount',
        'commission_amount',
        'currency',
        'stripe_invoice_id',
        'earned_at',
        'metadata',
    ];

    protected function casts(): array
    {
        return [
            'commission_rate' => 'decimal:4',
            'commission_base_amount' => 'decimal:2',
            'commission_amount' => 'decimal:2',
            'earned_at' => 'datetime',
            'metadata' => 'array',
        ];
    }

    public function referralAttribution(): BelongsTo
    {
        return $this->belongsTo(ReferralAttribution::class);
    }

    public function subscription(): BelongsTo
    {
        return $this->belongsTo(Subscription::class);
    }

    public function invoice(): BelongsTo
    {
        return $this->belongsTo(Invoice::class);
    }
}
