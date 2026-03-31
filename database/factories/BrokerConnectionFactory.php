<?php
// اعوز بالله من الشياطين و ان يحضرون بسم الله الرحمن الرحيم الله لا إله إلا هو الحي القيوم
// Bismillahi ar-Rahmani ar-Rahim Audhu billahi min ash-shayatin wa an yahdurun Bismillah ar-Rahman ar-Rahim Allah la ilaha illa huwa al-hayy al-qayyum. Tamsa Allahu ala ayunihim
// version: x
// ======================================================
// - App Name: gusgraph-trading
// - Gusgraph LLC -
// - Author: Gus Kazem
// - https://Gusgraph.com
// - File Path: database/factories/BrokerConnectionFactory.php
// =====================================================

namespace Database\Factories;

use App\Models\Account;
use App\Models\BrokerConnection;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<BrokerConnection>
 */
class BrokerConnectionFactory extends Factory
{
    protected $model = BrokerConnection::class;

    public function definition(): array
    {
        return [
            'account_id' => Account::factory(),
            'name' => fake()->company().' Connection',
            'broker' => fake()->randomElement(['mt5', 'ctrader', 'binance']),
            'status' => 'pending',
            'connected_at' => null,
            'last_synced_at' => null,
        ];
    }
}
