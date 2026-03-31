<?php
// اعوز بالله من الشياطين و ان يحضرون بسم الله الرحمن الرحيم الله لا إله إلا هو الحي القيوم
// Bismillahi ar-Rahmani ar-Rahim Audhu billahi min ash-shayatin wa an yahdurun Bismillah ar-Rahman ar-Rahim Allah la ilaha illa huwa al-hayy al-qayyum. Tamsa Allahu ala ayunihim
// version: x
// ======================================================
// - App Name: gusgraph-trading
// - Gusgraph LLC -
// - Author: Gus Kazem
// - https://Gusgraph.com
// - File Path: app/Models/AutomationSetting.php
// ======================================================

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class AutomationSetting extends Model
{
    use HasFactory;

    protected $fillable = [
        'account_id',
        'strategy_profile_id',
        'name',
        'ai_enabled',
        'status',
        'scheduler_frequency',
        'run_health',
        'risk_level',
        'scanner_enabled',
        'execution_enabled',
        'last_checked_at',
        'settings',
    ];

    protected function casts(): array
    {
        return [
            'ai_enabled' => 'boolean',
            'scanner_enabled' => 'boolean',
            'execution_enabled' => 'boolean',
            'last_checked_at' => 'datetime',
            'settings' => 'array',
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

    public function botRuns(): HasMany
    {
        return $this->hasMany(BotRun::class);
    }
}
