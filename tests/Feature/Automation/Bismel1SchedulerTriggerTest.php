<?php
// اعوز بالله من الشياطين و ان يحضرون بسم الله الرحمن الرحيم الله لا إله إلا هو الحي القيوم
// Bismillahi ar-Rahmani ar-Rahim Audhu billahi min ash-shayatin wa an yahdurun Bismillah ar-Rahman ar-Rahim Allah la ilaha illa huwa al-hayy al-qayyum. Tamsa Allahu ala ayunihim
// version: x
// ======================================================
// - App Name: bismel1.com
// - Gusgraph LLC -
// - Author: Gus Kazem
// - https://Gusgraph.com
// - File Path: tests/Feature/Automation/Bismel1SchedulerTriggerTest.php
// ======================================================

namespace Tests\Feature\Automation;

use App\Models\Account;
use App\Models\AlpacaAccount;
use App\Models\AutomationSetting;
use App\Models\BotRun;
use App\Models\BrokerConnection;
use App\Models\StrategyProfile;
use App\Support\Automation\Bismel1SchedulerTrigger;
use App\Support\Automation\Bismel1SmallSymbolScanner;
use Carbon\CarbonImmutable;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Tests\Concerns\CreatesBismel1Entitlements;
use Tests\TestCase;

class Bismel1SchedulerTriggerTest extends TestCase
{
    use RefreshDatabase;
    use CreatesBismel1Entitlements;

    public function test_it_triggers_due_1h_and_4h_scheduler_runs(): void
    {
        $account = $this->makeSchedulableAccount('both');

        $this->mock(Bismel1SmallSymbolScanner::class, function ($mock) use ($account): void {
            $mock->shouldReceive('scanAccount')
                ->twice()
                ->andReturnUsing(function ($scannedAccount, $symbols, $triggerContext) use ($account): array {
                    $this->assertSame($account->getKey(), $scannedAccount->getKey());
                    $this->assertContains(data_get($triggerContext, 'scheduler.timeframe'), ['1H', '4H']);

                    return [
                        'status' => 'completed',
                        'bot_run_id' => 100 + (data_get($triggerContext, 'scheduler.timeframe') === '4H' ? 4 : 1),
                    ];
                });
        });

        $result = app(Bismel1SchedulerTrigger::class)->triggerDueRuns(
            CarbonImmutable::parse('2026-04-01T08:00:00Z')
        );

        $this->assertSame('completed', $result['status']);
        $this->assertCount(2, $result['results']);
        $this->assertSame(['1H', '4H'], collect($result['results'])->pluck('timeframe')->sort()->values()->all());
    }

    public function test_it_skips_duplicate_scheduler_runs_for_the_same_bar_close_window(): void
    {
        $account = $this->makeSchedulableAccount('1h');

        BotRun::query()->create([
            'account_id' => $account->getKey(),
            'strategy_profile_id' => $account->strategyProfiles()->first()->getKey(),
            'automation_setting_id' => $account->automationSettings()->first()->getKey(),
            'alpaca_account_id' => $account->alpacaAccounts()->first()->getKey(),
            'run_type' => 'scan_scheduler_1h',
            'status' => 'completed',
            'risk_level' => 'balanced',
            'started_at' => CarbonImmutable::parse('2026-04-01T08:00:00Z'),
            'finished_at' => CarbonImmutable::parse('2026-04-01T08:01:00Z'),
            'runtime_seconds' => 60,
            'summary' => [
                'scheduler' => [
                    'timeframe' => '1H',
                    'bar_close_at' => '2026-04-01T08:00:00+00:00',
                    'next_bar_close_at' => '2026-04-01T09:00:00+00:00',
                ],
            ],
        ]);

        $this->mock(Bismel1SmallSymbolScanner::class, function ($mock): void {
            $mock->shouldNotReceive('scanAccount');
        });

        $result = app(Bismel1SchedulerTrigger::class)->triggerDueRuns(
            CarbonImmutable::parse('2026-04-01T08:05:00Z'),
            ['1H']
        );

        $this->assertSame('completed', $result['status']);
        $this->assertCount(1, $result['results']);
        $this->assertSame('duplicate_skipped', $result['results'][0]['status']);
        $this->assertSame('1H', $result['results'][0]['timeframe']);
    }

    public function test_it_does_not_trigger_scheduler_runs_when_automation_is_not_armed(): void
    {
        $account = $this->makeSchedulableAccount('1h', 'review', false);

        $this->mock(Bismel1SmallSymbolScanner::class, function ($mock): void {
            $mock->shouldNotReceive('scanAccount');
        });

        $result = app(Bismel1SchedulerTrigger::class)->triggerDueRuns(
            CarbonImmutable::parse('2026-04-01T08:05:00Z'),
            ['1H']
        );

        $this->assertSame('completed', $result['status']);
        $this->assertCount(0, $result['results']);
    }

    public function test_it_skips_scheduler_runs_when_a_runtime_lock_is_already_held(): void
    {
        $account = $this->makeSchedulableAccount('1h');
        Cache::put('bismel1:runtime:scheduler-scan:'.$account->getKey().':1h:20260401080000', now()->toIso8601String(), now()->addMinutes(15));

        $this->mock(Bismel1SmallSymbolScanner::class, function ($mock): void {
            $mock->shouldNotReceive('scanAccount');
        });

        $result = app(Bismel1SchedulerTrigger::class)->triggerDueRuns(
            CarbonImmutable::parse('2026-04-01T08:05:00Z'),
            ['1H']
        );

        $this->assertSame('completed', $result['status']);
        $this->assertCount(1, $result['results']);
        $this->assertSame('locked_skipped', $result['results'][0]['status']);
    }

    protected function makeSchedulableAccount(string $frequency, string $status = 'armed', bool $aiEnabled = true): Account
    {
        $account = Account::factory()->create();
        $this->seedConfirmedBismel1Subscription($account, 'BISMILLAH1_BOT_EXECUTE_BASIC');

        StrategyProfile::query()->create([
            'account_id' => $account->getKey(),
            'name' => 'Bismel1 Runtime',
            'mode' => 'stocks_swing',
            'timeframe' => '1h_4h',
            'symbol_scope' => 'watchlist',
            'style' => 'balanced',
            'engine' => 'python',
            'is_active' => true,
        ]);

        AutomationSetting::query()->create([
            'account_id' => $account->getKey(),
            'name' => 'Scheduler',
            'status' => $status,
            'risk_level' => 'balanced',
            'ai_enabled' => $aiEnabled,
            'scanner_enabled' => true,
            'execution_enabled' => false,
            'scheduler_frequency' => $frequency,
        ]);

        $brokerConnection = BrokerConnection::query()->create([
            'account_id' => $account->getKey(),
            'name' => 'Primary Alpaca',
            'broker' => 'alpaca',
            'status' => 'connected',
        ]);

        AlpacaAccount::query()->create([
            'account_id' => $account->getKey(),
            'broker_connection_id' => $brokerConnection->getKey(),
            'name' => 'Primary Alpaca',
            'environment' => 'paper',
            'data_feed' => 'iex',
            'status' => 'active',
            'sync_status' => 'success',
            'trade_stream_status' => 'credentials_verified',
            'is_primary' => true,
            'is_active' => true,
            'last_synced_at' => now()->subMinutes(5),
            'last_account_sync_at' => now()->subMinutes(5),
            'last_positions_sync_at' => now()->subMinutes(5),
            'last_orders_sync_at' => now()->subMinutes(5),
            'metadata' => [
                'positions_sync_result' => 'verified',
                'orders_sync_result' => 'verified',
            ],
        ]);

        return $account;
    }
}
