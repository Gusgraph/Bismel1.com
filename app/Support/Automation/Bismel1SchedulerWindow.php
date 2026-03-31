<?php
// اعوز بالله من الشياطين و ان يحضرون بسم الله الرحمن الرحيم الله لا إله إلا هو الحي القيوم
// Bismillahi ar-Rahmani ar-Rahim Audhu billahi min ash-shayatin wa an yahdurun Bismillah ar-Rahman ar-Rahim Allah la ilaha illa huwa al-hayy al-qayyum. Tamsa Allahu ala ayunihim
// version: x
// ======================================================
// - App Name: gusgraph-trading
// - Gusgraph LLC -
// - Author: Gus Kazem
// - https://Gusgraph.com
// - File Path: app/Support/Automation/Bismel1SchedulerWindow.php
// ======================================================

namespace App\Support\Automation;

use Carbon\CarbonImmutable;
use InvalidArgumentException;

class Bismel1SchedulerWindow
{
    public function dueWindows(CarbonImmutable $nowUtc, array $requestedTimeframes = ['1H', '4H']): array
    {
        $windows = [];

        foreach ($requestedTimeframes as $timeframe) {
            $normalized = strtoupper(trim((string) $timeframe));

            if (! in_array($normalized, ['1H', '4H'], true)) {
                throw new InvalidArgumentException('Unsupported Bismel1 scheduler timeframe ['.$normalized.'].');
            }

            $window = $this->windowForTimeframe($nowUtc, $normalized);

            if ($window['is_due']) {
                $windows[$normalized] = $window;
            }
        }

        return $windows;
    }

    public function nextWindow(CarbonImmutable $nowUtc, string $timeframe): array
    {
        $normalized = strtoupper(trim($timeframe));

        return $this->windowForTimeframe($nowUtc, $normalized);
    }

    protected function windowForTimeframe(CarbonImmutable $nowUtc, string $timeframe): array
    {
        $alignedNow = $nowUtc->startOfHour();

        if ($timeframe === '1H') {
            $barCloseAt = $alignedNow;
            $nextBarCloseAt = $barCloseAt->addHour();
        } elseif ($timeframe === '4H') {
            $alignedHour = (int) floor($alignedNow->hour / 4) * 4;
            $barCloseAt = $alignedNow->setTime($alignedHour, 0);

            if ($barCloseAt->equalTo($alignedNow) && ($alignedNow->minute !== 0 || $alignedNow->second !== 0)) {
                $barCloseAt = $alignedNow;
            }

            if ($barCloseAt->greaterThan($alignedNow)) {
                $barCloseAt = $barCloseAt->subHours(4);
            }

            $nextBarCloseAt = $barCloseAt->addHours(4);
        } else {
            throw new InvalidArgumentException('Unsupported Bismel1 scheduler timeframe ['.$timeframe.'].');
        }

        return [
            'timeframe' => $timeframe,
            'bar_close_at' => $barCloseAt,
            'next_bar_close_at' => $nextBarCloseAt,
            'is_due' => $nowUtc->greaterThanOrEqualTo($barCloseAt),
        ];
    }
}
