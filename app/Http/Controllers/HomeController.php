<?php
// اعوز بالله من الشياطين و ان يحضرون بسم الله الرحمن الرحيم الله لا إله إلا هو الحي القيوم
// Bismillahi ar-Rahmani ar-Rahim Audhu billahi min ash-shayatin wa an yahdurun Bismillah ar-Rahman ar-Rahim Allah la ilaha illa huwa al-hayy al-qayyum. Tamsa Allahu ala ayunihim
// version: x
// ======================================================
// - App Name: bismel1.com
// - Gusgraph LLC -
// - Author: Gus Kazem
// - https://Gusgraph.com
// - File Path: app/Http/Controllers/HomeController.php
// ======================================================

namespace App\Http\Controllers;

use App\Models\SubscriptionPlan;
use App\Support\Billing\ReferralTrackingService;
use Illuminate\Http\Request;

class HomeController extends Controller
{
    public function index()
    {
        return view('home');
    }

    public function plans(Request $request, ReferralTrackingService $referralTrackingService)
    {
        $signupEntry = route('signup');
        $testSpeedExecute = config('stripe.test_speed_execute');
        $catalog = collect([
            [
                'name' => 'Bismel1 Stocks Bot - AI Prime',
                'code' => 'BISMILLAH1_BOT_PRIME',
                'group' => 'base',
                'tag' => 'Top plan',
                'price' => '$97/month',
                'summary' => 'The flagship Bismel1 stocks plan for traders who want the strongest blend of AI market context, automation flow, and operator visibility.',
                'items' => [
                    'AI-first stock workflow guidance with a premium product feel.',
                    'Designed for traders who want the full Bismel1 identity in one plan.',
                    'The clearest expression of the platform across signal flow and runtime control.',
                ],
                'requires_base_plan' => false,
                'featured' => true,
            ],
            [
                'name' => 'Bismel1 AI - Scanner (News catalyst)',
                'code' => 'BISMILLAH_AI_SCANNER',
                'group' => 'base',
                'tag' => 'Base plan',
                'price' => '$49/month',
                'summary' => 'AI-guided market scanning built for traders who want earlier catalyst awareness and cleaner watchlist focus.',
                'items' => [
                    'Built for sharper market context before stepping into full automation.',
                    'News catalyst coverage shaped for fast market reads.',
                    'A clean starting point for the wider Bismel1 lineup.',
                ],
                'requires_base_plan' => false,
                'featured' => false,
            ],
            [
                'name' => 'Bismel1 Bot - Overnight Equities',
                'code' => 'BISMILLAH1_BOT_OVERNIGHT_EQUITIES',
                'group' => 'base',
                'tag' => 'Base plan',
                'price' => '$97/month',
                'summary' => 'Overnight equities automation for traders who want structured stock workflow coverage with a cleaner operating surface.',
                'items' => [
                    'Built for overnight stock workflow discipline.',
                    'Designed around broker-connected execution flow.',
                    'Suited to traders who want steadier automation posture.',
                ],
                'requires_base_plan' => false,
                'featured' => false,
            ],
            [
                'name' => 'Bismel1 Bot - Options',
                'code' => 'BISMILLAH1_BOT_OPTIONS',
                'group' => 'base',
                'tag' => 'Base plan',
                'price' => '$97/month',
                'summary' => 'Options-focused automation for traders who want AI-guided workflow support and a more readable operating picture.',
                'items' => [
                    'Structured around options workflow visibility and trading discipline.',
                    'Built for traders who want tighter operational reads.',
                    'Keeps the product feel focused instead of cluttered.',
                ],
                'requires_base_plan' => false,
                'featured' => false,
            ],
            [
                'name' => 'Bismel1 Bot - Crypto',
                'code' => 'BISMILLAH1_BOT_CRYPTO',
                'group' => 'base',
                'tag' => 'Base plan',
                'price' => '$97/month',
                'summary' => 'Crypto automation for traders who want AI-markets tooling in a faster-moving environment with cleaner visibility.',
                'items' => [
                    'Built for higher-velocity market conditions.',
                    'Designed to keep runtime visibility readable.',
                    'Made for traders who want modern crypto workflow support.',
                ],
                'requires_base_plan' => false,
                'featured' => false,
            ],
            [
                'name' => 'Bismel1 Stocks Bot - Execute',
                'code' => 'BISMILLAH1_BOT_EXECUTE_BASIC',
                'group' => 'base',
                'tag' => 'Base plan',
                'price' => '$29/month',
                'summary' => 'A focused execution product for traders who want a lighter entry into broker-aware workflow control.',
                'items' => [
                    'Designed for execution visibility, readiness, and operator-style control.',
                    'A lower-friction entry into the Bismel1 workflow.',
                    'Well suited for traders who want focused execution coverage.',
                ],
                'requires_base_plan' => false,
                'featured' => false,
            ],
            [
                'name' => 'Bismel1 Bot - Custom Strategy (Add-on)',
                'code' => 'BISMILLAH1_BOT_CUSTOM_STRATEGY_ADDON',
                'group' => 'addon',
                'tag' => 'Add-on',
                'price' => '$97/month',
                'summary' => 'A custom-strategy add-on for customers who need tailored workflow coverage on top of a base plan.',
                'items' => [
                    'Available only with an active base subscription.',
                    'Built for customers who need a more custom operating lane.',
                    'Stays separate from affiliate-discount pricing.',
                ],
                'requires_base_plan' => true,
                'featured' => false,
            ],
            [
                'name' => 'Bismel1 Bot - Additional Account (Add-On)',
                'code' => 'BISMILLAH1_BOT_ADDITIONAL_ACCOUNT_ADDON',
                'group' => 'addon',
                'tag' => 'Add-on',
                'price' => '$29/month',
                'summary' => 'An add-on for customers who need another linked account under the current Bismel1 subscription structure.',
                'items' => [
                    'Available only with an active base subscription.',
                    'Designed for multi-account expansion inside the existing structure.',
                    'Stays separate from affiliate-discount pricing.',
                ],
                'requires_base_plan' => true,
                'featured' => false,
            ],
            [
                'name' => 'Speed Executor',
                'code' => 'BISMILLAH1_BOT_SPEED_EXECUTE',
                'group' => 'testing',
                'tag' => 'Demo plan',
                'price' => '$10/month',
                'summary' => 'A separate demo lane for Speed Executor, presented clearly outside the main production lineup.',
                'items' => [
                    'Built for demo and evaluation use.',
                    'Kept separate from the main production catalog.',
                    'Presented as a clearly separate demo offer.',
                ],
                'requires_base_plan' => false,
                'featured' => false,
            ],
        ])->map(function (array $plan) use ($signupEntry): array {
            $model = SubscriptionPlan::query()->where('code', $plan['code'])->first();
            $standardPriceId = $model?->resolvedStripePriceId() ?? config('stripe.price_ids.'.$plan['code']);
            $affiliatePrice = $plan['group'] === 'base' ? ($model?->affiliateDisplayPrice() ?? config('stripe.affiliate_display_prices.'.$plan['code'])) : null;
            $checkoutReady = is_string($standardPriceId) && trim($standardPriceId) !== '';

            return array_merge($plan, [
                'affiliate_price' => is_string($affiliatePrice) && trim($affiliatePrice) !== '' ? '$'.number_format((float) $affiliatePrice, 2).'/month' : null,
                'checkout_ready' => $checkoutReady,
                'checkout_url' => $checkoutReady ? $signupEntry.'?plan='.$plan['code'] : null,
                'action_label' => $checkoutReady ? 'Purchase' : 'Purchase',
                'missing_link_value' => $checkoutReady ? null : 'Stripe price mapping missing for '.$plan['code'],
            ]);
        })->values();

        $activeReferralCode = $referralTrackingService->currentCode($request);

        return view('plans', [
            'activeReferralCode' => $activeReferralCode,
            'basePlans' => $catalog->where('group', 'base')->values()->all(),
            'addOnPlans' => $catalog->where('group', 'addon')->values()->all(),
            'testingPlans' => $catalog->where('group', 'testing')->values()->all(),
            'temporaryTestPlan' => (bool) data_get($testSpeedExecute, 'enabled') ? [
                'name' => 'Speed Execute Test',
                'tag' => 'Temporary test',
                'price' => '$59/month',
                'summary' => 'A separate Stripe test-mode checkout for temporary payment verification outside the main live catalog.',
                'items' => [
                    'Runs on a separate Stripe test checkout path.',
                    'Kept intentionally small and easy to switch off from the page.',
                    'Meant for temporary payment-flow validation only.',
                ],
                'checkout_url' => route('plans.test.speed-execute.start'),
            ] : null,
            'testCheckoutStatus' => $request->query('test_checkout'),
            'hasMissingPlanLinks' => $catalog->contains(fn (array $plan) => $plan['missing_link_value'] !== null),
            'missingPlanLinks' => $catalog->filter(fn (array $plan) => $plan['missing_link_value'] !== null)->pluck('missing_link_value')->all(),
        ]);
    }
}
