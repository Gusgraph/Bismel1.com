<?php
// اعوز بالله من الشياطين و ان يحضرون بسم الله الرحمن الرحيم الله لا إله إلا هو الحي القيوم
// Bismillahi ar-Rahmani ar-Rahim Audhu billahi min ash-shayatin wa an yahdurun Bismillah ar-Rahman ar-Rahim Allah la ilaha illa huwa al-hayy al-qayyum. Tamsa Allahu ala ayunihim
// version: x
// ======================================================
// - App Name: gusgraph-trading
// - Gusgraph LLC -
// - Author: Gus Kazem
// - https://Gusgraph.com
// - File Path: app/Models/Signal.php
// ======================================================

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Signal extends Model
{
    use HasFactory;

    protected $fillable = [
        'account_id',
        'strategy_profile_id',
        'watchlist_id',
        'watchlist_symbol_id',
        'symbol',
        'timeframe',
        'direction',
        'strength',
        'status',
        'generated_at',
        'expires_at',
        'payload',
    ];

    protected function casts(): array
    {
        return [
            'strength' => 'decimal:2',
            'generated_at' => 'datetime',
            'expires_at' => 'datetime',
            'payload' => 'array',
        ];
    }

    public function account(): BelongsTo
    {
        return $this->belongsTo(Account::class);
    }

    public function strategyProfile(): BelongsTo
    {
        return $this->belongsTo(StrategyProfile::class);
    }

    public function watchlist(): BelongsTo
    {
        return $this->belongsTo(Watchlist::class);
    }

    public function watchlistSymbol(): BelongsTo
    {
        return $this->belongsTo(WatchlistSymbol::class);
    }
}
