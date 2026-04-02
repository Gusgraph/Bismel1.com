<?php
// اعوز بالله من الشياطين و ان يحضرون بسم الله الرحمن الرحيم الله لا إله إلا هو الحي القيوم
// Bismillahi ar-Rahmani ar-Rahim Audhu billahi min ash-shayatin wa an yahdurun Bismillah ar-Rahman ar-Rahim Allah la ilaha illa huwa al-hayy al-qayyum. Tamsa Allahu ala ayunihim
// version: x
// ======================================================
// - App Name: bismel1.com
// - Gusgraph LLC -
// - Author: Gus Kazem
// - https://Gusgraph.com
// - File Path: resources/views/plans.blade.php
// ======================================================
?>
@extends('layouts.guest')

@section('title', 'Bismel1 | Products')

@section('content')
    @php
        $hasAffiliatePricing = !empty($activeReferralCode ?? null);

        $flagshipPlan = null;
        $executionPlan = null;
        $comingSoonProducts = [];
        $demoPlan = null;

        $comingSoonMap = [
            'BISMILLAH1_BOT_CRYPTO' => [
                'name' => 'Crypto Bot Trader',
                'summary' => 'Built for traders who want a crypto-focused automation product with the same cleaner Bismel1 operating feel.',
                'price' => $hasAffiliatePricing ? '$79/month' : '$97/month',
            ],
            'BISMILLAH_AI_SCANNER' => [
                'name' => 'AI-guided Market Scanning',
                'summary' => 'Built for traders who want earlier market context, cleaner scanning, and stronger watchlist preparation before execution.',
                'price' => $hasAffiliatePricing ? '$79/month' : '$97/month',
            ],
            'BISMILLAH1_BOT_OVERNIGHT_EQUITIES' => [
                'name' => 'Overnight Equities Bot',
                'summary' => 'Built for traders who want an overnight equities product with a steadier hold-through-session workflow.',
                'price' => $hasAffiliatePricing ? '$79/month' : '$97/month',
            ],
            'BISMILLAH1_BOT_OPTIONS' => [
                'name' => 'Options-focused Bot',
                'summary' => 'Built for traders who want a more structured options workflow with stronger operating clarity.',
                'price' => $hasAffiliatePricing ? '$79/month' : '$97/month',
            ],
        ];

        foreach ($basePlans as $plan) {
            $code = (string) ($plan['code'] ?? '');

            if ($code === 'BISMILLAH1_BOT_PRIME') {
                $plan['display_name'] = 'Prime Stocks Bot Trader';
                $plan['display_summary'] = 'The flagship Bismel1 trading product, built around the Prime winner strategy with AI oversight, safety layers, and drawdown-margin protection for traders who want stronger control once capital is live.';
                $plan['display_price'] = $hasAffiliatePricing ? '$79/month' : '$97/month';
                $plan['display_state'] = 'Live';
                $plan['display_action'] = 'Purchase';
                $flagshipPlan = $plan;
                continue;
            }

            if ($code === 'BISMILLAH1_BOT_EXECUTE_BASIC') {
                $plan['display_name'] = 'Execution';
                $plan['display_summary'] = 'Execution access for traders who want a simpler live product without stepping into a not-ready bot product.';
                $plan['display_price'] = $hasAffiliatePricing ? '$23.97/month' : '$29/month';
                $plan['display_state'] = 'Live';
                $plan['display_action'] = 'Purchase';
                $executionPlan = $plan;
                continue;
            }

            $mapped = $comingSoonMap[$code] ?? [
                'name' => $plan['name'] ?? 'Product',
                'summary' => $plan['summary'] ?? 'Separate Bismel1 product not released yet.',
                'price' => $plan['price'] ?? '',
            ];

            $plan['display_name'] = $mapped['name'];
            $plan['display_summary'] = $mapped['summary'];
            $plan['display_price'] = $mapped['price'];
            $plan['display_state'] = 'Coming Soon';
            $plan['display_action'] = 'Coming Soon';
            $comingSoonProducts[] = $plan;
        }

        foreach ($testingPlans as $plan) {
            $plan['display_name'] = 'Demo Access';
            $plan['display_summary'] = 'A lower-risk entry path for users who want to step inside the platform, review the workspace, understand the controls, and decide whether to move into Prime Stocks Bot Trader.';
            $plan['display_price'] = '$10/month';
            $plan['display_state'] = !empty($plan['checkout_ready']) ? 'Live' : 'Coming Soon';
            $plan['display_action'] = !empty($plan['checkout_ready']) ? 'Start Demo Access' : 'Coming Soon';
            $demoPlan = $plan;
            break;
        }
    @endphp

    <section class="guest-plans-intro-copy" aria-labelledby="products-title">
        <h1 id="products-title" class="guest-plans-intro-copy__title">Choose the product that fits how you want to trade.</h1>
        @if ($hasAffiliatePricing)
            <p class="guest-plans-intro-copy__affiliate">Affiliate pricing active for <strong>{{ $activeReferralCode }}</strong> — save up to 20% on eligible products.</p>
        @endif
        <p class="guest-plans-intro-copy__body">
            Bismel1 is built for traders who want live automation with stronger discipline, clearer execution, and less operational noise once money is on the line.
            Prime Stocks Bot Trader is the main entry point for serious stock traders who want a sharper system, stronger protection logic, and a product built to stay readable under pressure.
        </p>
    </section>

    @if ($flagshipPlan)
        <section class="guest-plans-flagship" aria-labelledby="flagship-product-title">
            <article class="guest-plan-card guest-plan-card--flagship">
                <div class="guest-plan-card__head">
                    <div>
                        <p class="guest-card__eyebrow">Flagship product</p>
                        <h2 id="flagship-product-title" class="guest-plan-card__title">{{ $flagshipPlan['display_name'] }}</h2>
                        <p class="guest-plan-card__body">{{ $flagshipPlan['display_summary'] }}</p>
                    </div>
                    <span class="guest-plan-card__tag guest-plan-card__tag--live">{{ $flagshipPlan['display_state'] }}</span>
                </div>

                <div class="guest-plan-card__price-row">
                    <p class="guest-plan-card__price">{{ $flagshipPlan['display_price'] }}</p>
                    <p class="guest-plan-card__meta">Prime Stocks Bot Trader is built for traders who want the strongest Bismel1 stock lane first: the Prime winner strategy, AI oversight across the operating flow, built-in safety logic, and drawdown-margin protection designed to keep the bot more disciplined when pressure rises. It is built for a broad field of U.S.-listed equities and ETFs, then narrowed by the strategy’s own filters, protections, and execution rules to keep selection disciplined.</p>
                </div>

                <div class="guest-plan-card__content">
                    <ul class="guest-plans__list">
                        <li>Prime winner strategy structure built for stock-focused trading execution.</li>
                        <li>AI oversight across the operating flow to keep the product sharper and more readable.</li>
                        <li>Safety features designed to reduce weak behavior when market conditions get unstable.</li>
                        <li>Drawdown-margin protection logic for stronger risk discipline once automation is live.</li>
                    </ul>

                    <div class="guest-login__actions">
                        <a href="{{ route('signup', ['plan' => $flagshipPlan['code']]) }}" class="guest-cta guest-cta--primary">
                            {{ $flagshipPlan['display_action'] }}
                        </a>
                    </div>
                </div>
            </article>
        </section>
    @endif

    @if (!empty($comingSoonProducts))
        <section class="guest-plans-heading" aria-labelledby="roadmap-products-title">
            <p class="guest-card__eyebrow">Roadmap products</p>
            <h2 id="roadmap-products-title" class="guest-card__title">Separate products not released yet</h2>
            <p class="guest-card__body">These products are part of the Bismel1 roadmap, but they are not open as live products yet.</p>
        </section>

        <section class="guest-plan-grid guest-plan-grid--products" aria-label="Coming soon products">
            @foreach ($comingSoonProducts as $plan)
                <article class="guest-plan-card guest-plan-card--muted">
                    <div class="guest-plan-card__head">
                        <div>
                            <h3 class="guest-plan-card__title">{{ $plan['display_name'] }}</h3>
                            <p class="guest-plan-card__body">{{ $plan['display_summary'] }}</p>
                        </div>
                        <span class="guest-plan-card__tag guest-plan-card__tag--soon">{{ $plan['display_state'] }}</span>
                    </div>

                    <p class="guest-plan-card__price">{{ $plan['display_price'] }}</p>

                    <ul class="guest-plans__list">
                        @foreach ($plan['items'] as $item)
                            <li>{{ $item }}</li>
                        @endforeach
                    </ul>

                    <div class="guest-login__actions">
                        <span class="guest-cta guest-cta--disabled">{{ $plan['display_action'] }}</span>
                    </div>
                </article>
            @endforeach
        </section>
    @endif

    @if ($executionPlan)
        <section class="guest-plans-heading" aria-labelledby="execution-product-title">
            <p class="guest-card__eyebrow">Live product</p>
            <h2 id="execution-product-title" class="guest-card__title">Execution</h2>
            <p class="guest-card__body">Execution access for traders who want a simpler live product without stepping into a not-ready bot product.</p>
        </section>

        <section class="guest-plan-grid guest-plan-grid--support" aria-label="Execution product">
            <article class="guest-plan-card">
                <div class="guest-plan-card__head">
                    <div>
                        <h3 class="guest-plan-card__title">{{ $executionPlan['display_name'] }}</h3>
                        <p class="guest-plan-card__body">{{ $executionPlan['display_summary'] }}</p>
                    </div>
                    <span class="guest-plan-card__tag guest-plan-card__tag--live">{{ $executionPlan['display_state'] }}</span>
                </div>

                <p class="guest-plan-card__price">{{ $executionPlan['display_price'] }}</p>

                <ul class="guest-plans__list">
                    @foreach ($executionPlan['items'] as $item)
                        <li>{{ $item }}</li>
                    @endforeach
                </ul>

                <div class="guest-login__actions">
                    <a href="{{ route('signup', ['plan' => $executionPlan['code']]) }}" class="guest-cta">
                        {{ $executionPlan['display_action'] }}
                    </a>
                </div>
            </article>
        </section>
    @endif

    @if (!empty($addOnPlans))
        <section class="guest-plans-heading" aria-labelledby="addons-title">
            <p class="guest-card__eyebrow">Live products</p>
            <h2 id="addons-title" class="guest-card__title">Add-ons</h2>
            <p class="guest-card__body">Add more capacity, account coverage, or custom workflow support on top of an active Bismel1 product.</p>
        </section>

        <section class="guest-plan-grid guest-plan-grid--addons" aria-label="Add-ons">
            @foreach ($addOnPlans as $plan)
                <article class="guest-plan-card">
                    <div class="guest-plan-card__head">
                        <div>
                            <h3 class="guest-plan-card__title">{{ $plan['name'] }}</h3>
                            <p class="guest-plan-card__body">{{ $plan['summary'] }}</p>
                        </div>
                        <span class="guest-plan-card__tag guest-plan-card__tag--live">Live</span>
                    </div>

                    <p class="guest-plan-card__price">{{ $plan['price'] }}</p>

                    <ul class="guest-plans__list">
                        @foreach ($plan['items'] as $item)
                            <li>{{ $item }}</li>
                        @endforeach
                    </ul>

                    <div class="guest-login__actions">
                        <a href="{{ route('signup', ['plan' => $plan['code']]) }}" class="guest-cta">
                            {{ $plan['action_label'] }}
                        </a>
                    </div>
                </article>
            @endforeach
        </section>
    @endif

    @if ($demoPlan)
        <section class="guest-plans-heading" aria-labelledby="demo-access-title">
            <p class="guest-card__eyebrow">Live product</p>
            <h2 id="demo-access-title" class="guest-card__title">Demo Access</h2>
            <p class="guest-card__body">Start here if you want to explore the platform from the inside, review the user controls, and see how the product feels before stepping into the flagship product.</p>
        </section>

        <section class="guest-plan-grid guest-plan-grid--support" aria-label="Demo access">
            <article class="guest-plan-card guest-plan-card--demo">
                <div class="guest-plan-card__head">
                    <div>
                        <h3 class="guest-plan-card__title">{{ $demoPlan['display_name'] }}</h3>
                        <p class="guest-plan-card__body">{{ $demoPlan['display_summary'] }}</p>
                    </div>
                    <span class="guest-plan-card__tag {{ $demoPlan['display_state'] === 'Live' ? 'guest-plan-card__tag--live' : 'guest-plan-card__tag--soon' }}">
                        {{ $demoPlan['display_state'] }}
                    </span>
                </div>

                <p class="guest-plan-card__price">{{ $demoPlan['display_price'] }}</p>

                <ul class="guest-plans__list">
                    <li>Lets users explore the platform experience before stepping into the flagship product.</li>
                    <li>Built as a lower-risk preview path so users can evaluate the workspace and controls first.</li>
                    <li>Made for users who want more confidence before committing to Prime.</li>
                </ul>

                <div class="guest-login__actions">
                    @if (!empty($demoPlan['checkout_ready']))
                        <a href="{{ route('signup', ['plan' => $demoPlan['code']]) }}" class="guest-cta">
                            {{ $demoPlan['display_action'] }}
                        </a>
                    @else
                        <span class="guest-cta guest-cta--disabled">{{ $demoPlan['display_action'] }}</span>
                    @endif
                </div>
            </article>
        </section>
    @endif

    @if (false && $temporaryTestPlan)
        <section class="guest-plans-heading guest-plans-heading--warning" aria-labelledby="temporary-test-plan-title">
            <span class="guest-symbol" aria-hidden="true" style="position: absolute; top: 19px; right: 19px; font-size: 11px;">ﷺ</span>
            <p class="guest-card__eyebrow">Stripe test mode</p>
            <h2 id="temporary-test-plan-title" class="guest-card__title">Temporary checkout lane</h2>
            <p class="guest-card__body">This exists only for Stripe test validation and stays separate from live product sales.</p>
        </section>

        <section class="guest-plan-grid guest-plan-grid--support" aria-label="Temporary Stripe test checkout">
            <article class="guest-plan-card guest-plan-card--warning">
                <div class="guest-plan-card__head">
                    <div>
                        <h3 class="guest-plan-card__title">{{ $temporaryTestPlan['name'] }}</h3>
                        <p class="guest-plan-card__body">{{ $temporaryTestPlan['summary'] }}</p>
                    </div>
                    <span class="guest-plan-card__tag" style="border-color: rgba(251, 191, 36, 0.25); color: #fcd34d;">{{ $temporaryTestPlan['tag'] }}</span>
                </div>

                <p class="guest-plan-card__price">{{ $temporaryTestPlan['price'] }}</p>

                <ul class="guest-plans__list">
                    @foreach ($temporaryTestPlan['items'] as $item)
                        <li>{{ $item }}</li>
                    @endforeach
                </ul>

                <div class="guest-login__actions">
                    <a href="{{ $temporaryTestPlan['checkout_url'] }}" class="guest-cta guest-cta--primary">Purchase</a>
                </div>
            </article>
        </section>
    @endif

    @if ($hasMissingPlanLinks)
        <section class="guest-card" style="margin-top: 19px;">
            <p class="guest-card__eyebrow">Current link blocker</p>
            <h2 class="guest-card__title">One plan still needs a mapped payment value.</h2>
            <ul class="guest-card__list">
                @foreach ($missingPlanLinks as $item)
                    <li>{{ $item }}</li>
                @endforeach
            </ul>
        </section>
    @endif
@endsection
