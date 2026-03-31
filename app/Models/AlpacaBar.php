<?php
// اعوز بالله من الشياطين و ان يحضرون بسم الله الرحمن الرحيم الله لا إله إلا هو الحي القيوم
// Bismillahi ar-Rahmani ar-Rahim Audhu billahi min ash-shayatin wa an yahdurun Bismillah ar-Rahman ar-Rahim Allah la ilaha illa huwa al-hayy al-qayyum. Tamsa Allahu ala ayunihim
// version: x
// ======================================================
// - App Name: gusgraph-trading
// - Gusgraph LLC -
// - Author: Gus Kazem
// - https://Gusgraph.com
// - File Path: app/Models/AlpacaBar.php
// ======================================================

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AlpacaBar extends Model
{
    use HasFactory;

    protected $fillable = [
        'account_id',
        'alpaca_account_id',
        'broker_connection_id',
        'symbol',
        'timeframe',
        'feed',
        'starts_at',
        'ends_at',
        'open',
        'high',
        'low',
        'close',
        'volume',
        'trade_count',
        'vwap',
        'fetched_at',
    ];

    protected function casts(): array
    {
        return [
            'starts_at' => 'datetime',
            'ends_at' => 'datetime',
            'open' => 'decimal:6',
            'high' => 'decimal:6',
            'low' => 'decimal:6',
            'close' => 'decimal:6',
            'volume' => 'integer',
            'trade_count' => 'integer',
            'vwap' => 'decimal:6',
            'fetched_at' => 'datetime',
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
