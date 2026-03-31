<?php
// اعوز بالله من الشياطين و ان يحضرون بسم الله الرحمن الرحيم الله لا إله إلا هو الحي القيوم
// Bismillahi ar-Rahmani ar-Rahim Audhu billahi min ash-shayatin wa an yahdurun Bismillah ar-Rahman ar-Rahim Allah la ilaha illa huwa al-hayy al-qayyum. Tamsa Allahu ala ayunihim
// version: x
// ======================================================
// - App Name: gusgraph-trading
// - Gusgraph LLC -
// - Author: Gus Kazem
// - https://Gusgraph.com
// - File Path: app/Models/BotRun.php
// ======================================================

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class BotRun extends Model
{
    use HasFactory;

    protected $fillable = [
        'account_id',
        'strategy_profile_id',
        'automation_setting_id',
        'alpaca_account_id',
        'run_type',
        'status',
        'risk_level',
        'started_at',
        'finished_at',
        'runtime_seconds',
        'summary',
        'error_message',
    ];

    protected function casts(): array
    {
        return [
            'started_at' => 'datetime',
            'finished_at' => 'datetime',
            'runtime_seconds' => 'integer',
            'summary' => 'array',
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

    public function automationSetting(): BelongsTo
    {
        return $this->belongsTo(AutomationSetting::class);
    }

    public function alpacaAccount(): BelongsTo
    {
        return $this->belongsTo(AlpacaAccount::class);
    }
}
