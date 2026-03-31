<?php
// اعوز بالله من الشياطين و ان يحضرون بسم الله الرحمن الرحيم الله لا إله إلا هو الحي القيوم
// Bismillahi ar-Rahmani ar-Rahim Audhu billahi min ash-shayatin wa an yahdurun Bismillah ar-Rahman ar-Rahim Allah la ilaha illa huwa al-hayy al-qayyum. Tamsa Allahu ala ayunihim
// version: x
// ======================================================
// - App Name: gusgraph-trading
// - Gusgraph LLC -
// - Author: Gus Kazem
// - https://Gusgraph.com
// - File Path: app/Models/BrokerCredential.php
// ======================================================

namespace App\Models;

use App\Support\Display\SafeDisplay;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class BrokerCredential extends Model
{
    use HasFactory;

    protected $fillable = [
        'broker_connection_id',
        'label',
        'provider',
        'status',
        'environment',
        'access_mode',
        'credential_payload',
        'key_last_four',
        'secret_hint',
        'is_encrypted',
        'last_used_at',
    ];

    protected function casts(): array
    {
        return [
            'credential_payload' => 'encrypted:array',
            'is_encrypted' => 'boolean',
            'last_used_at' => 'datetime',
        ];
    }

    public function brokerConnection(): BelongsTo
    {
        return $this->belongsTo(BrokerConnection::class);
    }

    public function maskedSummary(): string
    {
        $payload = is_array($this->credential_payload) ? $this->credential_payload : [];

        if ($payload === []) {
            return SafeDisplay::status((string) $this->status).', encrypted payload unavailable.';
        }

        $keyId = $this->key_last_four
            ? '****'.$this->key_last_four
            : SafeDisplay::maskedSuffix($payload['access_key_id'] ?? null, 4);
        $secret = $this->secret_hint
            ? '****'.$this->secret_hint
            : SafeDisplay::maskedSuffix($payload['access_secret'] ?? null, 2);
        $environment = SafeDisplay::status((string) ($this->environment ?: ($payload['environment'] ?? 'paper')));
        $accessMode = SafeDisplay::status((string) ($this->access_mode ?: ($payload['access_mode'] ?? 'read_only')));
        $provider = SafeDisplay::status((string) ($this->provider ?: ($payload['provider_label'] ?? 'Alpaca')));
        $encryptionState = $this->is_encrypted ? 'encrypted at rest' : 'unencrypted';

        return $provider.' '.SafeDisplay::status((string) $this->status).', '.$encryptionState.', key '.$keyId.', secret '.$secret.', '.$environment.', '.$accessMode;
    }
}
