<?php
// اعوز بالله من الشياطين و ان يحضرون بسم الله الرحمن الرحيم الله لا إله إلا هو الحي القيوم
// Bismillahi ar-Rahmani ar-Rahim Audhu billahi min ash-shayatin wa an yahdurun Bismillah ar-Rahman ar-Rahim Allah la ilaha illa huwa al-hayy al-qayyum. Tamsa Allahu ala ayunihim
// version: x
// ======================================================
// - App Name: bismel1.com
// - Gusgraph LLC -
// - Author: Gus Kazem
// - https://Gusgraph.com
// - File Path: resources/views/home.blade.php
// ======================================================
?>
@extends('layouts.guest')

@section('title', 'Bismel1 | AI Markets')

@section('content')
    @php
        $capabilities = [
            [
                'eyebrow' => 'Market intelligence',
                'title' => 'Signals, catalysts, and market structure in one disciplined stream.',
                'body' => 'Bismel1 is built for traders who want sharper market context before capital moves. The product stays focused on signal quality, execution awareness, and operational clarity from the first scan forward.',
                'items' => [
                    'AI-assisted scanning shaped around momentum, catalysts, and market flow.',
                    'A cleaner path from watchlist focus into execution-aware workflows.',
                    'A product feel designed for market operators, not generic dashboard users.',
                ],
            ],
            [
                'eyebrow' => 'Automation control',
                'title' => 'A trading product designed to stay readable while automation is running.',
                'body' => 'Bismel1 keeps operators close to the action with clear runtime visibility, broker readiness context, and a practical view of orders, positions, activity, and operating status.',
                'items' => [
                    'Runtime awareness across scanning, orders, and position flow.',
                    'A clearer operating picture for readiness, reconciliation, and recovery.',
                    'Visibility that stays useful when the tape gets faster.',
                ],
            ],
            [
                'eyebrow' => 'Product feel',
                'title' => 'Built like an AI markets desk, not a generic SaaS dashboard.',
                'body' => 'The product language stays concise because the idea is concise: better market context, cleaner automation posture, and a more modern operating surface for traders.',
                'items' => [
                    'Market-native language instead of dashboard filler.',
                    'Focused design that keeps attention on movement and decisions.',
                    'A clean handoff into the protected workspace after login.',
                ],
            ],
        ];

        $marketTape = [
            ['symbol' => 'NVDA', 'company' => 'NVIDIA', 'price' => '$942.30', 'move' => '+2.18%', 'direction' => 'up'],
            ['symbol' => 'MSFT', 'company' => 'Microsoft', 'price' => '$428.16', 'move' => '+0.84%', 'direction' => 'up'],
            ['symbol' => 'AAPL', 'company' => 'Apple', 'price' => '$191.47', 'move' => '-0.26%', 'direction' => 'down'],
            ['symbol' => 'AMZN', 'company' => 'Amazon', 'price' => '$182.08', 'move' => '+1.11%', 'direction' => 'up'],
            ['symbol' => 'META', 'company' => 'Meta', 'price' => '$509.80', 'move' => '+0.67%', 'direction' => 'up'],
            ['symbol' => 'TSLA', 'company' => 'Tesla', 'price' => '$176.43', 'move' => '-1.42%', 'direction' => 'down'],
            ['symbol' => 'AMD', 'company' => 'AMD', 'price' => '$173.29', 'move' => '+1.56%', 'direction' => 'up'],
            ['symbol' => 'GOOGL', 'company' => 'Alphabet', 'price' => '$154.82', 'move' => '+0.09%', 'direction' => 'flat'],
        ];
    @endphp

    <section class="guest-hero" aria-labelledby="bismel1-hero-title">
        <article class="guest-panel">
            <p class="guest-section__eyebrow">AI markets platform</p>
            <h1 id="bismel1-hero-title" class="guest-hero__title">Trade with sharper market context and cleaner automation control.</h1>
            <p class="guest-hero__body">
                Bismel1 is an AI-markets product for traders who want stronger signal discovery, execution-aware workflows, and a clearer operating picture once automation is live.
            </p>

            <div class="guest-hero__split" aria-label="Bismel1 highlights">
                <article class="guest-pill">
                    <span class="guest-pill__label">Built for</span>
                    <span class="guest-pill__value">AI-guided stock and market workflows</span>
                </article>
                <article class="guest-pill">
                    <span class="guest-pill__label">Experience</span>
                    <span class="guest-pill__value">Operator clarity from scan to position</span>
                </article>
                <article class="guest-pill">
                    <span class="guest-pill__label">Style</span>
                    <span class="guest-pill__value">Premium, focused, market-native design</span>
                </article>
            </div>

            <div class="guest-hero__actions">
                <a href="{{ route('login') }}" class="guest-cta guest-cta--primary">Login</a>
                <a href="{{ route('plans') }}" class="guest-cta">Plans</a>
            </div>
        </article>

        <article class="guest-card" style="position: relative; overflow: hidden;">
            <span class="guest-symbol" aria-hidden="true" style="position: absolute; top: 19px; right: 19px; font-size: 11px;">﷽</span>
            <p class="guest-card__eyebrow">Why traders look twice</p>
            <h2 class="guest-card__title">A sharper front end for AI-assisted market operations.</h2>
            <p class="guest-card__body">
                Bismel1 brings market intelligence, automation visibility, and control-oriented product design into one disciplined experience built for active operators who want the whole trading chain to stay readable.
            </p>
            <ul class="guest-card__list">
                <li>High-signal scanning built around actionability and speed.</li>
                <li>Operational visibility across orders, positions, and recent activity.</li>
                <li>Cleaner control surfaces for readiness, sync, and recovery moments.</li>
            </ul>
        </article>
    </section>

    <section class="guest-card-grid" aria-label="Bismel1 capability themes">
        @foreach ($capabilities as $capability)
            <article class="guest-card">
                <p class="guest-card__eyebrow">{{ $capability['eyebrow'] }}</p>
                <h2 class="guest-card__title">{{ $capability['title'] }}</h2>
                <p class="guest-card__body">{{ $capability['body'] }}</p>
                <ul class="guest-card__list">
                    @foreach ($capability['items'] as $item)
                        <li>{{ $item }}</li>
                    @endforeach
                </ul>
            </article>
        @endforeach
    </section>

    <section class="guest-story-wrap" aria-label="Market character and product tone">
        <article class="guest-story">
            <h2 class="guest-story__title">Juha glanced at the tape, nodded once, and said, “Good. If the market wants chaos, at least now it has formatting.”</h2>
            <p class="guest-story__body">
                Then he went back to trading, which is exactly the point. A good product should calm the operator down before it starts trying to impress anyone.
            </p>
            <ul class="guest-story__list">
                <li>Built for fast reads, not dramatic dashboard theater.</li>
                <li>Clear enough for operators, sharp enough for market people.</li>
            </ul>
        </article>

        <article class="guest-card" style="position: relative; overflow: hidden;">
            <span class="guest-symbol" aria-hidden="true" style="position: absolute; top: 19px; right: 19px; font-size: 11px;">ﷺ</span>
            <p class="guest-card__eyebrow">Bismel1</p>
            <h2 class="guest-card__title">From signup to runtime oversight, the product stays coherent.</h2>
            <p class="guest-card__body">
                Start with the plan that fits your trading style, connect Alpaca, set your automation posture, and let Bismel1 scan, evaluate, and act internally while you track safe summaries, positions, orders, activity, and status in one place.
            </p>
            <div class="guest-login__actions">
                <a href="{{ route('plans') }}" class="guest-story__link">View plans</a>
                <a href="{{ route('login') }}" class="guest-story__link">Login</a>
            </div>
        </article>
    </section>

    <section class="guest-tape" aria-labelledby="market-tape-title">
        <div class="guest-tape__header">
            <div>
                <h2 id="market-tape-title" class="guest-card__title">Live Mark</h2>
            </div>
            <p class="guest-tape__subtle">A rolling view of large-cap momentum that keeps the page anchored in the market.</p>
        </div>

        <div class="guest-tape__track" aria-hidden="true">
            @for ($loopIndex = 0; $loopIndex < 2; $loopIndex++)
                <div class="guest-tape__group">
                    @foreach ($marketTape as $ticker)
                        <article class="guest-tape__item">
                            <div>
                                <div class="guest-tape__symbol">{{ $ticker['symbol'] }}</div>
                                <div class="guest-tape__company">{{ $ticker['company'] }}</div>
                            </div>
                            <div class="guest-tape__price">{{ $ticker['price'] }}</div>
                            <div class="guest-tape__move is-{{ $ticker['direction'] }}">{{ $ticker['move'] }}</div>
                        </article>
                    @endforeach
                </div>
            @endfor
        </div>
    </section>
@endsection
