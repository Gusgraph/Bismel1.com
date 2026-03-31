<?php
// اعوز بالله من الشياطين و ان يحضرون بسم الله الرحمن الرحيم الله لا إله إلا هو الحي القيوم
// Bismillahi ar-Rahmani ar-Rahim Audhu billahi min ash-shayatin wa an yahdurun Bismillah ar-Rahman ar-Rahim Allah la ilaha illa huwa al-hayy al-qayyum. Tamsa Allahu ala ayunihim
// version: x
// ======================================================
// - App Name: gusgraph-trading
// - Gusgraph LLC -
// - Author: Gus Kazem
// - https://Gusgraph.com
// - File Path: app/Support/Billing/StripeApiClient.php
// ======================================================

namespace App\Support\Billing;

use Illuminate\Http\Client\Response;
use Illuminate\Support\Facades\Http;
use RuntimeException;

class StripeApiClient
{
    public function createCheckoutSession(array $payload): array
    {
        return $this->post('checkout/sessions', $payload);
    }

    public function fetchCheckoutSessionLineItems(string $sessionId): array
    {
        return $this->get('checkout/sessions/'.$sessionId.'/line_items', [
            'expand' => ['data.price'],
        ]);
    }

    public function fetchSubscription(string $subscriptionId): array
    {
        return $this->get('subscriptions/'.$subscriptionId, [
            'expand' => ['items.data.price', 'latest_invoice'],
        ]);
    }

    public function requireSecretKey(): string
    {
        $secret = config('stripe.secret');

        if (! is_string($secret) || trim($secret) === '') {
            throw new RuntimeException('Missing Stripe configuration value: STRIPE_SECRET');
        }

        return trim($secret);
    }

    protected function get(string $path, array $query = []): array
    {
        $response = $this->client()->get('https://api.stripe.com/v1/'.$path, $query);

        return $this->decodeResponse($response);
    }

    protected function post(string $path, array $payload = []): array
    {
        $response = $this->client()->asForm()->post('https://api.stripe.com/v1/'.$path, $payload);

        return $this->decodeResponse($response);
    }

    protected function client()
    {
        return Http::withToken($this->requireSecretKey())
            ->acceptJson();
    }

    protected function decodeResponse(Response $response): array
    {
        if ($response->failed()) {
            $message = (string) data_get($response->json(), 'error.message', 'Stripe API request failed.');

            throw new RuntimeException($message);
        }

        $json = $response->json();

        return is_array($json) ? $json : [];
    }
}
