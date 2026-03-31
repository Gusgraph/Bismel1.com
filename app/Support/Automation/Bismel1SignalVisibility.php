<?php
// اعوز بالله من الشياطين و ان يحضرون بسم الله الرحمن الرحيم الله لا إله إلا هو الحي القيوم
// Bismillahi ar-Rahmani ar-Rahim Audhu billahi min ash-shayatin wa an yahdurun Bismillah ar-Rahman ar-Rahim Allah la ilaha illa huwa al-hayy al-qayyum. Tamsa Allahu ala ayunihim
// version: x
// ======================================================
// - App Name: gusgraph-trading
// - Gusgraph LLC -
// - Author: Gus Kazem
// - https://Gusgraph.com
// - File Path: app/Support/Automation/Bismel1SignalVisibility.php
// ======================================================

namespace App\Support\Automation;

class Bismel1SignalVisibility
{
    public function signalDirection(string $action): string
    {
        return match ($action) {
            'open', 'add' => 'long',
            'close' => 'flat',
            default => 'neutral',
        };
    }

    public function signalStrength(string $action): ?float
    {
        return in_array($action, ['open', 'add', 'close'], true) ? 1.0 : null;
    }

    public function publicSummary(string $action, array $flags = [], array $unresolvedGaps = []): string
    {
        if ($action === 'close') {
            if ((bool) ($flags['trailing_exit'] ?? false)) {
                return 'AI exited on trailing protection';
            }

            return 'risk blocked';
        }

        if (in_array($action, ['open', 'add'], true)
            && (bool) ($flags['trend_aligned'] ?? false)
            && (bool) ($flags['pullback_detected'] ?? false)
            && (bool) ($flags['reclaim_confirmed'] ?? false)) {
            return 'trend aligned, pullback detected, reclaim confirmed';
        }

        if ((bool) ($flags['risk_blocked'] ?? false)) {
            return 'risk blocked';
        }

        if ($unresolvedGaps !== []) {
            return 'AI skipped setup';
        }

        return 'AI skipped setup';
    }

    public function adminSummary(string $action, array $flags = [], array $unresolvedGaps = []): string
    {
        $summary = $this->publicSummary($action, $flags, $unresolvedGaps);

        return match ($action) {
            'open' => 'Open signal stored with safe summary: '.$summary,
            'add' => 'Add signal stored with safe summary: '.$summary,
            'close' => 'Close signal stored with safe summary: '.$summary,
            default => 'Skip result stored with safe summary: '.$summary,
        };
    }

    public function buildSignalPayload(array $evaluation, ?array $riskResult = null): array
    {
        $flags = is_array($evaluation['safe_flags'] ?? null) ? $evaluation['safe_flags'] : [];
        $internal = is_array($evaluation['internal_strategy_state'] ?? null) ? $evaluation['internal_strategy_state'] : [];
        $unresolvedGaps = is_array($internal['unresolved_gaps'] ?? null) ? $internal['unresolved_gaps'] : [];
        $action = (string) ($riskResult['final_action'] ?? $evaluation['action'] ?? 'skip');
        $publicSummary = is_string($riskResult['public_summary'] ?? null)
            ? (string) $riskResult['public_summary']
            : $this->publicSummary($action, $flags, $unresolvedGaps);
        $adminSummary = is_string($riskResult['admin_summary'] ?? null)
            ? (string) $riskResult['admin_summary']
            : $this->adminSummary($action, $flags, $unresolvedGaps);

        return [
            'public_summary' => $publicSummary,
            'admin_summary' => $adminSummary,
            'visibility' => [
                'customer' => 'safe_summary_only',
                'admin' => 'safe_summary_plus_counts',
                'internal' => 'restricted_strategy_state',
            ],
            'safe_flags' => [
                'trend_aligned' => (bool) ($flags['trend_aligned'] ?? false),
                'pullback_detected' => (bool) ($flags['pullback_detected'] ?? false),
                'reclaim_confirmed' => (bool) ($flags['reclaim_confirmed'] ?? false),
                'risk_blocked' => (bool) ($flags['risk_blocked'] ?? false),
                'trailing_exit' => (bool) ($flags['trailing_exit'] ?? false),
            ],
            'internal_strategy_state' => $internal,
            'risk_engine' => [
                'status' => (string) ($riskResult['status'] ?? 'not_applied'),
                'allowed' => (bool) ($riskResult['allowed'] ?? false),
                'reason_code' => (string) ($riskResult['reason_code'] ?? 'not_applied'),
                'proposed_action' => (string) ($riskResult['proposed_action'] ?? $evaluation['action'] ?? 'skip'),
                'final_action' => (string) ($riskResult['final_action'] ?? $evaluation['action'] ?? 'skip'),
                'internal_status' => is_array($riskResult['internal_status'] ?? null) ? $riskResult['internal_status'] : [],
            ],
        ];
    }
}
