<?php
// اعوز بالله من الشياطين و ان يحضرون بسم الله الرحمن الرحيم الله لا إله إلا هو الحي القيوم
// Bismillahi ar-Rahmani ar-Rahim Audhu billahi min ash-shayatin wa an yahdurun Bismillah ar-Rahman ar-Rahim Allah la ilaha illa huwa al-hayy al-qayyum. Tamsa Allahu ala ayunihim
// version: x
// ======================================================
// - App Name: bismel1.com
// - Gusgraph LLC -
// - Author: Gus Kazem
// - https://Gusgraph.com
// - File Path: app/Models/SubscriptionPlan.php
// ======================================================

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class SubscriptionPlan extends Model
{
    use HasFactory;

    public const PRODUCTION_BASE_CODES = [
        'BISMILLAH_AI_SCANNER',
        'BISMILLAH1_BOT_OVERNIGHT_EQUITIES',
        'BISMILLAH1_BOT_OPTIONS',
        'BISMILLAH1_BOT_CRYPTO',
        'BISMILLAH1_BOT_PRIME',
        'BISMILLAH1_BOT_EXECUTE_BASIC',
    ];

    public const PRODUCTION_ADDON_CODES = [
        'BISMILLAH1_BOT_CUSTOM_STRATEGY_ADDON',
        'BISMILLAH1_BOT_ADDITIONAL_ACCOUNT_ADDON',
    ];

    public const TESTING_CODES = [
        'BISMILLAH1_BOT_SPEED_EXECUTE',
    ];

    protected $fillable = [
        'name',
        'code',
        'plan_type',
        'product_family',
        'status',
        'price',
        'currency',
        'interval',
        'billing_model',
        'sort_order',
        'stripe_lookup_key',
        'stripe_product_id',
        'stripe_price_id',
        'metadata',
    ];

    protected function casts(): array
    {
        return [
            'price' => 'decimal:2',
            'sort_order' => 'integer',
            'metadata' => 'array',
        ];
    }

    public function subscriptions(): HasMany
    {
        return $this->hasMany(Subscription::class);
    }

    public function isAddOn(): bool
    {
        return $this->plan_type === 'addon';
    }

    public function isTestingPlan(): bool
    {
        return in_array($this->code, self::TESTING_CODES, true);
    }

    public function isAffiliateEligibleBasePlan(): bool
    {
        return in_array($this->code, self::PRODUCTION_BASE_CODES, true);
    }

    public function resolvedStripePriceId(bool $affiliate = false): ?string
    {
        if ($affiliate && ! $this->isAffiliateEligibleBasePlan()) {
            $affiliate = false;
        }

        $configuredPriceId = $affiliate
            ? config('stripe.affiliate_price_ids.'.$this->code)
            : config('stripe.price_ids.'.$this->code);

        if ($affiliate && (! is_string($configuredPriceId) || trim($configuredPriceId) === '')) {
            $configuredPriceId = config('stripe.price_ids.'.$this->code);
        }

        return is_string($configuredPriceId) && trim($configuredPriceId) !== ''
            ? trim($configuredPriceId)
            : $this->stripe_price_id;
    }

    public function affiliateDisplayPrice(): ?string
    {
        if (! $this->isAffiliateEligibleBasePlan()) {
            return null;
        }

        $configuredPrice = config('stripe.affiliate_display_prices.'.$this->code);

        if (! is_numeric($configuredPrice)) {
            return null;
        }

        return number_format((float) $configuredPrice, 2, '.', '');
    }

    public static function findByResolvedStripePriceId(?string $stripePriceId): ?self
    {
        if (! is_string($stripePriceId) || trim($stripePriceId) === '') {
            return null;
        }

        $stripePriceId = trim($stripePriceId);

        $directMatch = static::query()
            ->where('stripe_price_id', $stripePriceId)
            ->first();

        if ($directMatch) {
            return $directMatch;
        }

        return static::query()
            ->get()
            ->first(fn (self $plan) => $plan->resolvedStripePriceId() === $stripePriceId || $plan->resolvedStripePriceId(true) === $stripePriceId);
    }

    public static function productionCatalogCodes(): array
    {
        return array_merge(self::PRODUCTION_BASE_CODES, self::PRODUCTION_ADDON_CODES);
    }

    public static function publicCatalogCodes(): array
    {
        return array_merge(self::productionCatalogCodes(), self::TESTING_CODES);
    }

    public static function productionBaseCodes(): array
    {
        return self::PRODUCTION_BASE_CODES;
    }

    public static function productionAddOnCodes(): array
    {
        return self::PRODUCTION_ADDON_CODES;
    }
}
