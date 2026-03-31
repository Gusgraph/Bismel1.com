<?php
// اعوز بالله من الشياطين و ان يحضرون بسم الله الرحمن الرحيم الله لا إله إلا هو الحي القيوم
// Bismillahi ar-Rahmani ar-Rahim Audhu billahi min ash-shayatin wa an yahdurun Bismillah ar-Rahman ar-Rahim Allah la ilaha illa huwa al-hayy al-qayyum. Tamsa Allahu ala ayunihim
// version: x
// ======================================================
// - App Name: gusgraph-trading
// - Gusgraph LLC -
// - Author: Gus Kazem
// - https://Gusgraph.com
// - File Path: app/Models/AlpacaOrder.php
// ======================================================

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AlpacaOrder extends Model
{
    use HasFactory;

    protected $fillable = [
        'account_id',
        'alpaca_account_id',
        'broker_connection_id',
        'strategy_profile_id',
        'signal_id',
        'bot_run_id',
        'request_action',
        'alpaca_order_id',
        'client_order_id',
        'alpaca_asset_id',
        'symbol',
        'asset_class',
        'side',
        'order_type',
        'time_in_force',
        'status',
        'status_summary',
        'broker_message',
        'qty',
        'filled_qty',
        'notional',
        'limit_price',
        'stop_price',
        'filled_avg_price',
        'submitted_at',
        'filled_at',
        'canceled_at',
        'expired_at',
        'failed_at',
        'synced_at',
    ];

    protected function casts(): array
    {
        return [
            'qty' => 'decimal:6',
            'filled_qty' => 'decimal:6',
            'notional' => 'decimal:2',
            'limit_price' => 'decimal:6',
            'stop_price' => 'decimal:6',
            'filled_avg_price' => 'decimal:6',
            'submitted_at' => 'datetime',
            'filled_at' => 'datetime',
            'canceled_at' => 'datetime',
            'expired_at' => 'datetime',
            'failed_at' => 'datetime',
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
