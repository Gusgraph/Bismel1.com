<?php
// اعوز بالله من الشياطين و ان يحضرون بسم الله الرحمن الرحيم الله لا إله إلا هو الحي القيوم
// Bismillahi ar-Rahmani ar-Rahim Audhu billahi min ash-shayatin wa an yahdurun Bismillah ar-Rahman ar-Rahim Allah la ilaha illa huwa al-hayy al-qayyum. Tamsa Allahu ala ayunihim
// version: x
// ======================================================
// - App Name: gusgraph-trading
// - Gusgraph LLC -
// - Author: Gus Kazem
// - https://Gusgraph.com
// - File Path: app/Models/AlpacaAccount.php
// ======================================================

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class AlpacaAccount extends Model
{
    use HasFactory;

    protected $fillable = [
        'account_id',
        'broker_connection_id',
        'broker_credential_id',
        'name',
        'environment',
        'data_feed',
        'status',
        'sync_status',
        'trade_stream_status',
        'is_primary',
        'is_active',
        'alpaca_account_id',
        'account_number',
        'buying_power',
        'cash',
        'equity',
        'last_synced_at',
        'last_account_sync_at',
        'last_positions_sync_at',
        'last_orders_sync_at',
        'metadata',
    ];

    protected function casts(): array
    {
        return [
            'buying_power' => 'decimal:2',
            'cash' => 'decimal:2',
            'equity' => 'decimal:2',
            'last_synced_at' => 'datetime',
            'last_account_sync_at' => 'datetime',
            'last_positions_sync_at' => 'datetime',
            'last_orders_sync_at' => 'datetime',
            'is_primary' => 'boolean',
            'is_active' => 'boolean',
            'metadata' => 'array',
        ];
    }

    public function account(): BelongsTo
    {
        return $this->belongsTo(Account::class);
    }

    public function brokerConnection(): BelongsTo
    {
        return $this->belongsTo(BrokerConnection::class);
    }

    public function brokerCredential(): BelongsTo
    {
        return $this->belongsTo(BrokerCredential::class);
    }

    public function botRuns(): HasMany
    {
        return $this->hasMany(BotRun::class);
    }

    public function positions(): HasMany
    {
        return $this->hasMany(AlpacaPosition::class);
    }

    public function orders(): HasMany
    {
        return $this->hasMany(AlpacaOrder::class);
    }

    public function bars(): HasMany
    {
        return $this->hasMany(AlpacaBar::class);
    }
}
