<?php
// اعوز بالله من الشياطين و ان يحضرون بسم الله الرحمن الرحيم الله لا إله إلا هو الحي القيوم
// Bismillahi ar-Rahmani ar-Rahim Audhu billahi min ash-shayatin wa an yahdurun Bismillah ar-Rahman ar-Rahim Allah la ilaha illa huwa al-hayy al-qayyum. Tamsa Allahu ala ayunihim
// version: x
// ======================================================
// - App Name: gusgraph-trading
// - Gusgraph LLC -
// - Author: Gus Kazem
// - https://Gusgraph.com
// - File Path: app/Models/AlpacaPosition.php
// ======================================================

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AlpacaPosition extends Model
{
    use HasFactory;

    protected $fillable = [
        'account_id',
        'alpaca_account_id',
        'broker_connection_id',
        'strategy_profile_id',
        'last_signal_id',
        'last_bot_run_id',
        'symbol',
        'alpaca_asset_id',
        'asset_class',
        'exchange',
        'side',
        'qty',
        'qty_available',
        'market_value',
        'cost_basis',
        'current_price',
        'avg_entry_price',
        'unrealized_pl',
        'unrealized_plpc',
        'change_today',
        'high_water_price',
        'management_state',
        'status_summary',
        'last_managed_at',
        'synced_at',
    ];

    protected function casts(): array
    {
        return [
            'qty' => 'decimal:6',
            'qty_available' => 'decimal:6',
            'market_value' => 'decimal:2',
            'cost_basis' => 'decimal:2',
            'current_price' => 'decimal:6',
            'avg_entry_price' => 'decimal:6',
            'unrealized_pl' => 'decimal:2',
            'unrealized_plpc' => 'decimal:6',
            'change_today' => 'decimal:6',
            'high_water_price' => 'decimal:6',
            'last_managed_at' => 'datetime',
            'synced_at' => 'datetime',
        ];
    }

    public function account(): BelongsTo
    {
        return $this->belongsTo(Account::class);
    }

    public function alpacaAccount(): BelongsTo
    {
        return $this->belongsTo(AlpacaAccount::class);
    }

    public function brokerConnection(): BelongsTo
    {
        return $this->belongsTo(BrokerConnection::class);
    }
}
