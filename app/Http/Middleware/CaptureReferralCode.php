<?php
// اعوز بالله من الشياطين و ان يحضرون بسم الله الرحمن الرحيم الله لا إله إلا هو الحي القيوم
// Bismillahi ar-Rahmani ar-Rahim Audhu billahi min ash-shayatin wa an yahdurun Bismillah ar-Rahman ar-Rahim Allah la ilaha illa huwa al-hayy al-qayyum. Tamsa Allahu ala ayunihim
// version: x
// ======================================================
// - App Name: gusgraph-trading
// - Gusgraph LLC -
// - Author: Gus Kazem
// - https://Gusgraph.com
// - File Path: app/Http/Middleware/CaptureReferralCode.php
// ======================================================

namespace App\Http\Middleware;

use App\Support\Billing\ReferralTrackingService;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CaptureReferralCode
{
    public function __construct(protected ReferralTrackingService $referralTrackingService)
    {
    }

    public function handle(Request $request, Closure $next): Response
    {
        $this->referralTrackingService->captureFromRequest($request);

        return $next($request);
    }
}
