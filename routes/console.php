<?php
// اعوز بالله من الشياطين و ان يحضرون بسم الله الرحمن الرحيم الله لا إله إلا هو الحي القيوم
// Bismillahi ar-Rahmani ar-Rahim Audhu billahi min ash-shayatin wa an yahdurun Bismillah ar-Rahman ar-Rahim Allah la ilaha illa huwa al-hayy al-qayyum. Tamsa Allahu ala ayunihim
// version: x
// ======================================================
// - App Name: gusgraph-trading
// - Gusgraph LLC -
// - Author: Gus Kazem
// - https://Gusgraph.com
// - File Path: routes/console.php
// =====================================================

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use App\Models\Account;
use App\Support\Automation\Bismel1SmallSymbolScanner;
use App\Support\Automation\Bismel1SchedulerTrigger;
use Carbon\CarbonImmutable;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

Artisan::command('bismel1:scan-small {accountId?} {--symbols=}', function (Bismel1SmallSymbolScanner $scanner): int {
    $accountId = $this->argument('accountId');
    $symbolsOption = (string) $this->option('symbols');
    $symbols = $symbolsOption !== ''
        ? array_values(array_filter(array_map('trim', explode(',', $symbolsOption))))
        : null;

    $account = $accountId !== null
        ? Account::query()->findOrFail($accountId)
        : Account::query()->whereHas('strategyProfiles', fn ($query) => $query->where('engine', 'python')->where('is_active', true))->orderBy('id')->firstOrFail();

    $result = $scanner->scanAccount($account, $symbols);

    $this->info($result['message'] ?? 'Bismel1 small-symbol scan completed.');

    return self::SUCCESS;
})->purpose('Run the first internal Bismel1 small-symbol scanner and store safe relational signals.');

Artisan::command('bismel1:scheduler-trigger {accountId?} {--timeframes=} {--at=}', function (Bismel1SchedulerTrigger $trigger): int {
    $timeframesOption = (string) $this->option('timeframes');
    $timeframes = $timeframesOption !== ''
        ? array_values(array_filter(array_map('trim', explode(',', $timeframesOption))))
        : null;
    $atOption = (string) $this->option('at');
    $at = $atOption !== ''
        ? CarbonImmutable::parse($atOption)->setTimezone('UTC')
        : CarbonImmutable::now('UTC');
    $accountId = $this->argument('accountId');

    $result = $trigger->triggerDueRuns($at, $timeframes, $accountId !== null ? (int) $accountId : null);

    $this->info('Bismel1 scheduler trigger completed.');
    $this->line(json_encode($result, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));

    return self::SUCCESS;
})->purpose('Trigger closed-candle Bismel1 scheduler runs for due 1H and 4H windows.');
