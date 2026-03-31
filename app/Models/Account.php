<?php
// اعوز بالله من الشياطين و ان يحضرون بسم الله الرحمن الرحيم الله لا إله إلا هو الحي القيوم
// Bismillahi ar-Rahmani ar-Rahim Audhu billahi min ash-shayatin wa an yahdurun Bismillah ar-Rahman ar-Rahim Allah la ilaha illa huwa al-hayy al-qayyum. Tamsa Allahu ala ayunihim
// version: x
// ======================================================
// - App Name: gusgraph-trading
// - Gusgraph LLC -
// - Author: Gus Kazem
// - https://Gusgraph.com
// - File Path: app/Models/Account.php
// ======================================================

namespace App\Models;

use App\Domain\Account\Enums\AccountStatus;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Account extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'slug',
        'status',
        'owner_user_id',
        'stripe_customer_id',
    ];

    protected function casts(): array
    {
        return [
            'status' => AccountStatus::class,
        ];
    }

    public function owner(): BelongsTo
    {
        return $this->belongsTo(User::class, 'owner_user_id');
    }

    public function users(): BelongsToMany
    {
        return $this->belongsToMany(User::class)
            ->withPivot(['role', 'status', 'joined_at'])
            ->withTimestamps();
    }

    public function subscriptions(): HasMany
    {
        return $this->hasMany(Subscription::class);
    }

    public function subscriptionItems(): HasMany
    {
        return $this->hasManyThrough(SubscriptionItem::class, Subscription::class);
    }

    public function invoices(): HasMany
    {
        return $this->hasMany(Invoice::class);
    }

    public function brokerConnections(): HasMany
    {
        return $this->hasMany(BrokerConnection::class);
    }

    public function alpacaAccounts(): HasMany
    {
        return $this->hasMany(AlpacaAccount::class);
    }

    public function alpacaPositions(): HasMany
    {
        return $this->hasMany(AlpacaPosition::class);
    }

    public function alpacaOrders(): HasMany
    {
        return $this->hasMany(AlpacaOrder::class);
    }

    public function alpacaBars(): HasMany
    {
        return $this->hasMany(AlpacaBar::class);
    }

    public function strategyProfiles(): HasMany
    {
        return $this->hasMany(StrategyProfile::class);
    }

    public function automationSettings(): HasMany
    {
        return $this->hasMany(AutomationSetting::class);
    }

    public function watchlists(): HasMany
    {
        return $this->hasMany(Watchlist::class);
    }

    public function signals(): HasMany
    {
        return $this->hasMany(Signal::class);
    }

    public function botRuns(): HasMany
    {
        return $this->hasMany(BotRun::class);
    }

    public function apiLicenses(): HasMany
    {
        return $this->hasMany(ApiLicense::class);
    }

    public function auditLogs(): HasMany
    {
        return $this->hasMany(AuditLog::class);
    }

    public function activityLogs(): HasMany
    {
        return $this->hasMany(ActivityLog::class);
    }
}
