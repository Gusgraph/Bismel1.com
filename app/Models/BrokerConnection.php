<?php
// اعوز بالله من الشياطين و ان يحضرون بسم الله الرحمن الرحيم الله لا إله إلا هو الحي القيوم
// Bismillahi ar-Rahmani ar-Rahim Audhu billahi min ash-shayatin wa an yahdurun Bismillah ar-Rahman ar-Rahim Allah la ilaha illa huwa al-hayy al-qayyum. Tamsa Allahu ala ayunihim
// version: x
// ======================================================
// - App Name: gusgraph-trading
// - Gusgraph LLC -
// - Author: Gus Kazem
// - https://Gusgraph.com
// - File Path: app/Models/BrokerConnection.php
// ======================================================

namespace App\Models;

use App\Domain\Broker\Enums\BrokerConnectionStatus;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class BrokerConnection extends Model
{
    use HasFactory;

    protected $fillable = [
        'account_id',
        'managed_by_user_id',
        'name',
        'broker',
        'status',
        'connected_at',
        'last_synced_at',
    ];

    protected function casts(): array
    {
        return [
            'status' => BrokerConnectionStatus::class,
            'connected_at' => 'datetime',
            'last_synced_at' => 'datetime',
        ];
    }

    public function account(): BelongsTo
    {
        return $this->belongsTo(Account::class);
    }

    public function managedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'managed_by_user_id');
    }

    public function brokerCredentials(): HasMany
    {
        return $this->hasMany(BrokerCredential::class);
    }

    public function alpacaAccounts(): HasMany
    {
        return $this->hasMany(AlpacaAccount::class);
    }
}
