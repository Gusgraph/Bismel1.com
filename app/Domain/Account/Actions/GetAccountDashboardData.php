<?php
// اعوز بالله من الشياطين و ان يحضرون بسم الله الرحمن الرحيم الله لا إله إلا هو الحي القيوم
// Bismillahi ar-Rahmani ar-Rahim Audhu billahi min ash-shayatin wa an yahdurun Bismillah ar-Rahman ar-Rahim Allah la ilaha illa huwa al-hayy al-qayyum. Tamsa Allahu ala ayunihim
// version: x
// ======================================================
// - App Name: gusgraph-trading
// - Gusgraph LLC -
// - Author: Gus Kazem
// - https://Gusgraph.com
// - File Path: app/Domain/Account/Actions/GetAccountDashboardData.php
// =====================================================

namespace App\Domain\Account\Actions;

class GetAccountDashboardData
{
    public function handle(string $area = 'customer'): array
    {
        return [
            'title' => 'Account Summary',
            'message' => $area === 'admin'
                ? 'Admin account oversight placeholder.'
                : 'Customer account summary placeholder.',
        ];
    }
}
