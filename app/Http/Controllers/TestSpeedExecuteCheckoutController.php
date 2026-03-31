<?php
// اعوز بالله من الشياطين و ان يحضرون بسم الله الرحمن الرحيم الله لا إله إلا هو الحي القيوم
// Bismillahi ar-Rahmani ar-Rahim Audhu billahi min ash-shayatin wa an yahdurun Bismillah ar-Rahman ar-Rahim Allah la ilaha illa huwa al-hayy al-qayyum. Tamsa Allahu ala ayunihim
// version: x
// ======================================================
// - App Name: bismel1.com
// - Gusgraph LLC -
// - Author: Gus Kazem
// - https://Gusgraph.com
// - File Path: app/Http/Controllers/TestSpeedExecuteCheckoutController.php
// ======================================================

namespace App\Http\Controllers;

use Illuminate\Http\Client\Response;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Http;
use RuntimeException;

class TestSpeedExecuteCheckoutController extends Controller
{
    public function start(): RedirectResponse
    {
        if (! $this->enabled()) {
            abort(404);
        }

        try {
            $session = $this->createCheckoutSession();
        } catch (RuntimeException $exception) {
            report($exception);

            return redirect()->route('plans', [
                'test_checkout' => 'failed',
            ]);
        }

        $checkoutUrl = data_get($session, 'url');

        if (! is_string($checkoutUrl) || trim($checkoutUrl) === '') {
            return redirect()->route('plans', [
                'test_checkout' => 'failed',
            ]);
        }

        return redirect()->away($checkoutUrl);
    }

    public function success(): RedirectResponse
    {
        if (! $this->enabled()) {
            abort(404);
        }

        return redirect()->route('plans', [
            'test_checkout' => 'success',
        ]);
    }

    public function cancel(): RedirectResponse
    {
        if (! $this->enabled()) {
            abort(404);
        }

        return redirect()->route('plans', [
            'test_checkout' => 'cancelled',
        ]);
    }

    protected function createCheckoutSession(): array
    {
        $response = Http::withToken($this->requireSecret())
            ->acceptJson()
            ->asForm()
            ->post('https://api.stripe.com/v1/checkout/sessions', [
                'mode' => 'subscription',
                'success_url' => route('plans.test.speed-execute.success', [], true).'?session_id={CHECKOUT_SESSION_ID}',
                'cancel_url' => route('plans.test.speed-execute.cancel', [], true),
                'line_items[0][price]' => $this->requirePriceId(),
                'line_items[0][quantity]' => 1,
                'allow_promotion_codes' => 'false',
                'metadata[checkout_lane]' => 'speed_execute_test',
                'metadata[site]' => 'bismel1.com',
            ]);

        return $this->decodeResponse($response);
    }

    protected function decodeResponse(Response $response): array
    {
        if ($response->failed()) {
            $message = (string) data_get($response->json(), 'error.message', 'Stripe test checkout request failed.');

            throw new RuntimeException($message);
        }

        $json = $response->json();

        return is_array($json) ? $json : [];
    }

    protected function enabled(): bool
    {
        return (bool) config('stripe.test_speed_execute.enabled', false);
    }

    protected function requireSecret(): string
    {
        $secret = config('stripe.test_speed_execute.secret');

        if (! is_string($secret) || trim($secret) === '') {
            throw new RuntimeException('Missing Stripe test configuration value: STRIPE_TEST_SPEED_EXECUTE_SECRET');
        }

        return trim($secret);
    }

    protected function requirePriceId(): string
    {
        $priceId = config('stripe.test_speed_execute.price_id');

        if (! is_string($priceId) || trim($priceId) === '') {
            throw new RuntimeException('Missing Stripe test configuration value: STRIPE_TEST_SPEED_EXECUTE_PRICE_ID');
        }

        return trim($priceId);
    }
}
