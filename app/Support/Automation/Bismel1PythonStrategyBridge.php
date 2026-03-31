<?php
// اعوز بالله من الشياطين و ان يحضرون بسم الله الرحمن الرحيم الله لا إله إلا هو الحي القيوم
// Bismillahi ar-Rahmani ar-Rahim Audhu billahi min ash-shayatin wa an yahdurun Bismillah ar-Rahman ar-Rahim Allah la ilaha illa huwa al-hayy al-qayyum. Tamsa Allahu ala ayunihim
// version: x
// ======================================================
// - App Name: gusgraph-trading
// - Gusgraph LLC -
// - Author: Gus Kazem
// - https://Gusgraph.com
// - File Path: app/Support/Automation/Bismel1PythonStrategyBridge.php
// ======================================================

namespace App\Support\Automation;

use Illuminate\Support\Collection;
use JsonException;
use RuntimeException;
use Symfony\Component\Process\Process;

class Bismel1PythonStrategyBridge
{
    public function evaluateSymbol(array $payload): array
    {
        try {
            $encoded = json_encode($payload, JSON_THROW_ON_ERROR);
        } catch (JsonException $exception) {
            throw new RuntimeException('Bismel1 scanner payload could not be encoded for Python execution.', 0, $exception);
        }

        $process = new Process(
            ['python3', '-m', 'python.bismel1_engine.scanner_cli'],
            base_path()
        );

        $process->setInput($encoded);
        $process->mustRun();

        try {
            $decoded = json_decode($process->getOutput(), true, 512, JSON_THROW_ON_ERROR);
        } catch (JsonException $exception) {
            throw new RuntimeException('Bismel1 Python bridge returned invalid JSON.', 0, $exception);
        }

        if (! is_array($decoded)) {
            throw new RuntimeException('Bismel1 Python bridge returned an invalid scanner response.');
        }

        return $decoded;
    }

    public function formatBars(Collection $bars): array
    {
        return $bars->map(function ($bar) {
            return [
                'starts_at' => $bar->starts_at?->toIso8601String(),
                'ends_at' => $bar->ends_at?->toIso8601String(),
                'open' => (float) $bar->open,
                'high' => (float) $bar->high,
                'low' => (float) $bar->low,
                'close' => (float) $bar->close,
                'volume' => $bar->volume !== null ? (float) $bar->volume : null,
            ];
        })->values()->all();
    }
}
