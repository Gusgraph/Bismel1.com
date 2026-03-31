<?php
// اعوز بالله من الشياطين و ان يحضرون بسم الله الرحمن الرحيم الله لا إله إلا هو الحي القيوم
// Bismillahi ar-Rahmani ar-Rahim Audhu billahi min ash-shayatin wa an yahdurun Bismillah ar-Rahman ar-Rahim Allah la ilaha illa huwa al-hayy al-qayyum. Tamsa Allahu ala ayunihim
// version: x
// ======================================================
// - App Name: gusgraph-trading
// - Gusgraph LLC -
// - Author: Gus Kazem
// - https://Gusgraph.com
// - File Path: app/Models/ApiKey.php
// =====================================================

namespace App\Models;

use App\Support\Display\SafeDisplay;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ApiKey extends Model
{
    use HasFactory;

    protected $fillable = [
        'api_license_id',
        'name',
        'key_hash',
        'secret_hint',
        'status',
        'last_used_at',
        'expires_at',
    ];

    protected function casts(): array
    {
        return [
            'secret_hint' => 'encrypted',
            'last_used_at' => 'datetime',
            'expires_at' => 'datetime',
        ];
    }

    public function apiLicense(): BelongsTo
    {
        return $this->belongsTo(ApiLicense::class);
    }

    public function maskedTokenSummary(): string
    {
        $maskedValue = SafeDisplay::maskedSuffix($this->secret_hint, 4, '');

        if ($maskedValue === '') {
            return 'Encrypted token unavailable';
        }

        return 'Encrypted token ending in '.$maskedValue;
    }
}
