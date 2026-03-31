<?php
// اعوز بالله من الشياطين و ان يحضرون بسم الله الرحمن الرحيم الله لا إله إلا هو الحي القيوم
// Bismillahi ar-Rahmani ar-Rahim Audhu billahi min ash-shayatin wa an yahdurun Bismillah ar-Rahman ar-Rahim Allah la ilaha illa huwa al-hayy al-qayyum. Tamsa Allahu ala ayunihim
// version: x
// ======================================================
// - App Name: gusgraph-trading
// - Gusgraph LLC -
// - Author: Gus Kazem
// - https://Gusgraph.com
// - File Path: app/Http/Controllers/StripeWebhookController.php
// ======================================================

namespace App\Http\Controllers;

use App\Support\Billing\StripeWebhookService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use RuntimeException;

class StripeWebhookController extends Controller
{
    public function __invoke(Request $request, StripeWebhookService $stripeWebhookService): JsonResponse
    {
        try {
            $result = $stripeWebhookService->handle(
                $request->getContent(),
                $request->header('Stripe-Signature')
            );
        } catch (RuntimeException $exception) {
            return response()->json([
                'status' => 'failed',
                'message' => $exception->getMessage(),
            ], 400);
        }

        return response()->json($result);
    }
}
