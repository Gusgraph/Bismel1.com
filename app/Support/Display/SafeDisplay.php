<?php
// اعوز بالله من الشياطين و ان يحضرون بسم الله الرحمن الرحيم الله لا إله إلا هو الحي القيوم
// Bismillahi ar-Rahmani ar-Rahim Audhu billahi min ash-shayatin wa an yahdurun Bismillah ar-Rahman ar-Rahim Allah la ilaha illa huwa al-hayy al-qayyum. Tamsa Allahu ala ayunihim
// version: x
// ======================================================
// - App Name: gusgraph-trading
// - Gusgraph LLC -
// - Author: Gus Kazem
// - https://Gusgraph.com
// - File Path: app/Support/Display/SafeDisplay.php
// ======================================================

namespace App\Support\Display;

use BackedEnum;
use DateTimeInterface;

class SafeDisplay
{
    public static function status(BackedEnum|string|null $status, array $labels = []): string
    {
        $statusValue = $status instanceof BackedEnum ? $status->value : (string) $status;

        return $labels[$statusValue] ?? ucfirst(str_replace('_', ' ', $statusValue));
    }

    public static function prefixedDate(?DateTimeInterface $value, string $prefix, string $fallback): string
    {
        return $value ? $prefix.self::date($value) : $fallback;
    }

    public static function prefixedDateTime(?DateTimeInterface $value, string $prefix, string $fallback): string
    {
        return $value ? $prefix.self::dateTime($value) : $fallback;
    }

    public static function date(?DateTimeInterface $value, string $fallback = 'Not available'): string
    {
        return $value ? $value->format('M j, Y') : $fallback;
    }

    public static function dateTime(?DateTimeInterface $value, string $fallback = 'Not available'): string
    {
        return $value ? $value->format('M j, Y g:i A').' UTC' : $fallback;
    }

    public static function statusMeta(BackedEnum|string|null $status, array $labels = []): array
    {
        $statusValue = $status instanceof BackedEnum ? $status->value : strtolower(trim((string) $status));
        $statusValue = $statusValue !== '' ? $statusValue : 'unknown';
        $label = self::status($statusValue, $labels);

        return [
            'value' => $statusValue,
            'label' => $label,
            'tone' => self::statusTone($statusValue),
        ];
    }

    public static function statusTone(BackedEnum|string|null $status): string
    {
        $statusValue = $status instanceof BackedEnum ? $status->value : strtolower(trim((string) $status));

        return match (true) {
            $statusValue === '',
            str_contains($statusValue, 'placeholder'),
            str_contains($statusValue, 'review'),
            str_contains($statusValue, 'waiting'),
            str_contains($statusValue, 'not_started') => 'muted',
            str_contains($statusValue, 'active'),
            str_contains($statusValue, 'ready'),
            str_contains($statusValue, 'connected'),
            str_contains($statusValue, 'paid'),
            str_contains($statusValue, 'present'),
            str_contains($statusValue, 'available'),
            str_contains($statusValue, 'local') => 'positive',
            str_contains($statusValue, 'trial'),
            str_contains($statusValue, 'pending'),
            str_contains($statusValue, 'expiring'),
            str_contains($statusValue, 'manual'),
            str_contains($statusValue, 'attention'),
            str_contains($statusValue, 'medium'),
            str_contains($statusValue, 'elevated') => 'warning',
            str_contains($statusValue, 'error'),
            str_contains($statusValue, 'expired'),
            str_contains($statusValue, 'past_due'),
            str_contains($statusValue, 'cancelled'),
            str_contains($statusValue, 'suspended'),
            str_contains($statusValue, 'missing'),
            str_contains($statusValue, 'unpaid'),
            str_contains($statusValue, 'forbidden'),
            str_contains($statusValue, 'failed') => 'danger',
            default => 'neutral',
        };
    }

    public static function maskedSuffix(?string $value, int $visibleSuffixLength = 4, string $fallback = 'not provided'): string
    {
        $trimmedValue = trim((string) $value);

        if ($trimmedValue === '') {
            return $fallback;
        }

        $length = mb_strlen($trimmedValue);
        $suffixLength = min(max($visibleSuffixLength, 1), max($length - 1, 1));

        return '***'.mb_substr($trimmedValue, -1 * $suffixLength);
    }

    public static function sanitizedText(?string $value, string $fallback = 'Not available'): string
    {
        $text = trim((string) $value);

        if ($text === '') {
            return $fallback;
        }

        $text = preg_replace(
            '/\b(access_secret|secret_hint|token_value|access_key_id|password|secret|token|key)\b\s*[:=]\s*([^\s,;]+)/i',
            '$1=[masked]',
            $text
        );

        $text = preg_replace('/[\x00-\x1F\x7F]+/u', ' ', (string) $text);

        return trim((string) preg_replace('/\s{2,}/', ' ', (string) $text));
    }
}
