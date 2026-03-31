<?php
// اعوز بالله من الشياطين و ان يحضرون بسم الله الرحمن الرحيم الله لا إله إلا هو الحي القيوم
// Bismillahi ar-Rahmani ar-Rahim Audhu billahi min ash-shayatin wa an yahdurun Bismillah ar-Rahman ar-Rahim Allah la ilaha illa huwa al-hayy al-qayyum. Tamsa Allahu ala ayunihim
// version: x
// ======================================================
// - App Name: gusgraph-trading
// - Gusgraph LLC -
// - Author: Gus Kazem
// - https://Gusgraph.com
// - File Path: app/Policies/ApiLicensePolicy.php
// =====================================================

namespace App\Policies;

use App\Models\ApiLicense;
use App\Models\User;

class ApiLicensePolicy
{
    public function viewAny(User $user): bool
    {
        return true;
    }

    public function view(User $user, ApiLicense $apiLicense): bool
    {
        return true;
    }
}
