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

@section('title', 'Bismel1 | Plans')

@section('content')
    <section class="guest-plans-intro" aria-labelledby="plans-title">
        <h1 id="plans-title" class="guest-panel__title">Production plans for the premium Bismel1 workflow</h1>
        <p class="guest-panel__body">Choose a plan, complete checkout, and Bismel1 creates your workspace so you can return and log in.</p>
    </section>

    <section class="guest-plans-comparison" aria-label="Production plan comparison">
        <header class="guest-plans-comparison__header">
            <p class="guest-card__eyebrow">Production plans</p>
            <h2 class="guest-card__title" id="base-plans-title">Pure automation, production clarity</h2>
            <p class="guest-card__body">Compare the premium lineup immediately; each column shows price, purpose, and a straight link into checkout.</p>
        </header>
        <div class="guest-plans-comparison__grid">
            @foreach ($basePlans as $plan)
                <article class="guest-plans-comparison__plan{{ !empty($plan['featured']) ? ' guest-plan--featured' : '' }}">
                    <div class="guest-plan__header">
                        <div>
                            <h3 class="guest-plan__title">{{ $plan['name'] }}</h3>
                            <p class="guest-plan__subtitle">{{ $plan['summary'] }}</p>
                        </div>
                        <span class="guest-plan__tag">{{ $plan['tag'] }}</span>
                    </div>
                    <p class="guest-plan__price">{{ $plan['price'] }}</p>
                    <ul class="guest-plans-comparison__features">
                        @foreach ($plan['items'] as $item)
                            <li>{{ $item }}</li>
                        @endforeach
                    </ul>
                    <a href="{{ route('signup', ['plan' => $plan['code']]) }}" class="guest-cta guest-cta--primary guest-plans-comparison__cta">{{ $plan['action_label'] }}</a>
                </article>
            @endforeach
        </div>
    </section>

    <section class="guest-card" style="margin: 1.35rem 0 1.19rem;" aria-labelledby="addon-plans-title">
        <p class="guest-card__eyebrow">Add-ons</p>
        <h2 id="addon-plans-title" class="guest-card__title">Extensions for production plans</h2>
        <p class="guest-card__body">Layer these packages on top of an active base subscription when you need more coverage, alerts, or automation depth.</p>
    </section>

    <section class="guest-plan-grid" aria-label="Add-on plans">
        @foreach ($addOnPlans as $plan)
            <article class="guest-plan guest-plan--addon">
                <div class="guest-plan__header">
                    <div>
                        <h2 class="guest-plan__title">{{ $plan['name'] }}</h2>
                        <p class="guest-card__body">{{ $plan['summary'] }}</p>
                    </div>
                    <span class="guest-plan__tag">{{ $plan['tag'] }}</span>
                </div>

                <p class="guest-plan__price">{{ $plan['price'] }}</p>

                <ul class="guest-plans__list">
                    @foreach ($plan['items'] as $item)
                        <li>{{ $item }}</li>
                    @endforeach
                </ul>

                <div class="guest-login__actions">
                    <a href="{{ route('signup', ['plan' => $plan['code']]) }}" class="guest-cta">{{ $plan['action_label'] }}</a>
                </div>
            </article>
        @endforeach
    </section>

    <section class="guest-card" style="margin: 1.35rem 0 1.19rem;" aria-labelledby="demo-plan-title">
        <p class="guest-card__eyebrow">Demo lane</p>
        <h2 id="demo-plan-title" class="guest-card__title">Speed Executor demo</h2>
        <p class="guest-card__body">Stay in the demo lane to explore Speed Executor solo; it remains a separate path from the production plans.</p>
    </section>

    <section class="guest-plan-grid" aria-label="Demo plan">
        @foreach ($testingPlans as $plan)
            <article class="guest-plan guest-plan--demo">
                <div class="guest-plan__header">
                    <div>
                        <h2 class="guest-plan__title">{{ $plan['name'] }}</h2>
                        <p class="guest-card__body">{{ $plan['summary'] }}</p>
                    </div>
                    <span class="guest-plan__tag">{{ $plan['tag'] }}</span>
                </div>

                <p class="guest-plan__price">{{ $plan['price'] }}</p>

                <ul class="guest-plans__list">
                    @foreach ($plan['items'] as $item)
                        <li>{{ $item }}</li>
                    @endforeach
                </ul>

                <div class="guest-login__actions">
                    @if ($plan['checkout_ready'])
                        <a href="{{ route('signup', ['plan' => $plan['code']]) }}" class="guest-cta">{{ $plan['action_label'] }}</a>
                    @else
                        <span class="guest-cta" style="opacity: 0.62; cursor: default;">{{ $plan['action_label'] }}</span>
                    @endif
                </div>
                @if (!empty($plan['missing_link_value']))
                    <p class="guest-card__body" style="margin-top: 0.65rem;">{{ $plan['missing_link_value'] }}</p>
                @endif
            </article>
        @endforeach
    </section>

    @if ($temporaryTestPlan)
        <section class="guest-card" style="margin: 1.19rem 0 0.95rem; border-color: rgba(251, 191, 36, 0.28); position: relative; overflow: hidden;" aria-labelledby="temporary-test-plan-title">
            <span class="guest-symbol" aria-hidden="true" style="position: absolute; top: 19px; right: 19px; font-size: 11px;">ﷺ</span>
            <p class="guest-card__eyebrow">Validation lane</p>
            <h2 id="temporary-test-plan-title" class="guest-card__title">Quick preview access</h2>
            <p class="guest-card__body">A compact lane for temporary proof-of-concept checkouts before production plans go live.</p>
        </section>

        <section aria-label="Temporary Stripe test checkout" style="display: grid; grid-template-columns: minmax(0, 379px); gap: 0.95rem; margin-bottom: 0.95rem;">
            <article class="guest-plan" style="padding: 1.15rem; border-color: rgba(251, 191, 36, 0.34); background: linear-gradient(180deg, rgba(28, 21, 8, 0.9), rgba(16, 13, 7, 0.92)); box-shadow: 0 19px 61px rgba(0, 0, 0, 0.34);">
                <div class="guest-plan__header">
                    <div>
                        <h2 class="guest-plan__title" style="font-family: 'Arial Black', 'Helvetica Neue', var(--font-sans); font-size: clamp(1.11rem, 2vw, 1.35rem); font-weight: 873; line-height: 0.99; letter-spacing: -0.05em; text-transform: uppercase;">{{ $temporaryTestPlan['name'] }}</h2>
                        <p class="guest-card__body">{{ $temporaryTestPlan['summary'] }}</p>
                    </div>
                    <span class="guest-plan__tag" style="border-color: rgba(251, 191, 36, 0.25); color: #fcd34d;">{{ $temporaryTestPlan['tag'] }}</span>
                </div>

                <p class="guest-card__body" style="margin-bottom: 0.73rem; color: var(--guest-text); font-size: 1.05rem;">{{ $temporaryTestPlan['price'] }}</p>

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
        <section class="guest-card" style="margin-top: 1.35rem;">
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
