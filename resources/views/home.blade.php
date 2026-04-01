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

@section('title', 'Bismel1 | AI Trading Automation')

@section('content')
    @php
        $capabilityStrip = [
            'AI signal discovery',
            'Execution-aware automation',
            'Orders, positions, and runtime visibility',
            'Operator-grade control surfaces',
        ];

        $featureCards = [
            [
                'eyebrow' => 'Signal Discovery',
                'title' => 'AI market intelligence built for faster reads and cleaner trade selection.',
                'body' => 'Bismel1 helps traders scan for momentum, catalysts, and market structure with a tighter operating view before capital is committed.',
                'items' => [
                    'Sharper watchlist focus with AI-assisted market context.',
                    'Cleaner scan-to-decision flow built around actionability.',
                    'Signal quality designed for active operators, not generic dashboards.',
                ],
            ],
            [
                'eyebrow' => 'Automation Control',
                'title' => 'Automated execution that stays readable while the market moves.',
                'body' => 'The product keeps the operator close to readiness, broker state, and automation posture without turning the interface into noise.',
                'items' => [
                    'Clear automation posture and broker readiness context.',
                    'Simple runtime summaries for activity, sync, and recovery.',
                    'A cleaner operating surface when the tape gets faster.',
                ],
            ],
            [
                'eyebrow' => 'Execution Visibility',
                'title' => 'Orders, positions, and operating status in one disciplined view.',
                'body' => 'Bismel1 is built to keep the full execution picture legible, from signals and handoff to position flow and runtime oversight.',
                'items' => [
                    'Track positions, orders, and activity with less friction.',
                    'See runtime movement without losing the higher-level picture.',
                    'Stay closer to execution without drowning in dashboard clutter.',
                ],
            ],
        ];

        $terminalStats = [
            ['label' => 'Signal Score', 'value' => '91.7'],
            ['label' => 'Runtime State', 'value' => 'Ready'],
            ['label' => 'Broker Sync', 'value' => 'Stable'],
            ['label' => 'Active Focus', 'value' => 'Large Cap'],
        ];

        $watchlist = [
            ['symbol' => 'NVDA', 'price' => '$942.30', 'move' => '+2.18%', 'direction' => 'up'],
            ['symbol' => 'MSFT', 'price' => '$428.16', 'move' => '+0.84%', 'direction' => 'up'],
            ['symbol' => 'AAPL', 'price' => '$191.47', 'move' => '-0.26%', 'direction' => 'down'],
            ['symbol' => 'AMD', 'price' => '$173.29', 'move' => '+1.56%', 'direction' => 'up'],
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

    <section class="guest-hero guest-home-hero" aria-labelledby="bismel1-hero-title">
        <article class="guest-panel guest-home-hero__panel">
            <p class="guest-section__eyebrow">AI Trading Automation</p>
            <h1 id="bismel1-hero-title" class="guest-hero__title guest-home-hero__title">
                AI-powered trading automation with real-time market intelligence.
            </h1>
            <p class="guest-hero__body guest-home-hero__body">
                Bismel1 helps traders scan faster, judge market context more clearly, and manage automated
                execution with a cleaner view of orders, positions, activity, and runtime status.
            </p>

            <div class="guest-hero__actions guest-home-hero__actions">
                <a href="{{ route('plans') }}" class="guest-cta guest-cta--primary">View Plans</a>
                <a href="{{ route('login') }}" class="guest-cta">Login</a>
            </div>

            <div class="guest-home-strip" aria-label="Platform capabilities">
                @foreach ($capabilityStrip as $item)
                    <span class="guest-home-strip__item">{{ $item }}</span>
                @endforeach
            </div>
        </article>

        <aside class="guest-card guest-home-terminal" aria-label="AI market terminal preview">
            <span class="guest-symbol" aria-hidden="true">﷽</span>

            <div class="guest-home-terminal__header">
                <div>
                    <p class="guest-card__eyebrow">Live AI Market Intel</p>
                    <h2 class="guest-card__title">Signal and execution surface</h2>
                </div>
                <div class="guest-home-terminal__badge">Realtime</div>
            </div>

            <div class="guest-home-terminal__grid">
                @foreach ($terminalStats as $stat)
                    <div class="guest-home-terminal__metric">
                        <span class="guest-home-terminal__metric-label">{{ $stat['label'] }}</span>
                        <strong class="guest-home-terminal__metric-value">{{ $stat['value'] }}</strong>
                    </div>
                @endforeach
            </div>

            <div class="guest-home-terminal__chart" aria-hidden="true">
                <span class="guest-home-terminal__chart-line guest-home-terminal__chart-line--one"></span>
                <span class="guest-home-terminal__chart-line guest-home-terminal__chart-line--two"></span>
                <span class="guest-home-terminal__chart-glow"></span>
            </div>

            <div class="guest-home-terminal__watchlist">
                @foreach ($watchlist as $ticker)
                    <article class="guest-home-terminal__ticker">
                        <div>
                            <strong class="guest-home-terminal__symbol">{{ $ticker['symbol'] }}</strong>
                            <span class="guest-home-terminal__price">{{ $ticker['price'] }}</span>
                        </div>
                        <span class="guest-tape__move is-{{ $ticker['direction'] }}">{{ $ticker['move'] }}</span>
                    </article>
                @endforeach
            </div>
        </aside>
    </section>

    <section class="guest-home-feature-grid" aria-label="Core product capabilities">
        @foreach ($featureCards as $feature)
            <article class="guest-card guest-home-feature">
                <p class="guest-card__eyebrow">{{ $feature['eyebrow'] }}</p>
                <h2 class="guest-card__title">{{ $feature['title'] }}</h2>
                <p class="guest-card__body">{{ $feature['body'] }}</p>

                <ul class="guest-card__list">
                    @foreach ($feature['items'] as $item)
                        <li>{{ $item }}</li>
                    @endforeach
                </ul>
            </article>
        @endforeach
    </section>

    <section class="guest-story-wrap guest-home-preview" aria-label="Product preview and conversion">
        <article class="guest-story guest-home-preview__story">
            <p class="guest-story__label">Product Preview</p>
            <h2 class="guest-story__title">Built like a trading desk surface, not a generic SaaS front page.</h2>
            <p class="guest-story__body">
                The experience is designed to keep signal discovery, automation posture, and execution visibility in
                one disciplined flow. The goal is not more dashboard noise. The goal is a cleaner operating picture.
            </p>
            <ul class="guest-story__list">
                <li>Sharper scan-to-execution continuity.</li>
                <li>Cleaner summaries for orders, positions, and runtime state.</li>
                <li>Premium product feel built around market operators.</li>
            </ul>
        </article>

        <article class="guest-card guest-home-preview__cta">
            <span class="guest-symbol" aria-hidden="true">✧</span>
            <p class="guest-card__eyebrow">Bismel1</p>
            <h2 class="guest-card__title">Start with the plan that fits your trading workflow.</h2>
            <p class="guest-card__body">
                Connect your broker, set your automation posture, and move from signal discovery to runtime oversight
                inside one cleaner operating surface.
            </p>
            <div class="guest-login__actions">
                <a href="{{ route('plans') }}" class="guest-story__link">Explore Plans</a>
                <a href="{{ route('login') }}" class="guest-story__link">Access Login</a>
            </div>
        </article>
    </section>

    <section class="guest-tape" aria-labelledby="market-tape-title">
        <div class="guest-tape__header">
            <div>
                <p class="guest-card__eyebrow">Market Tape</p>
                <h2 id="market-tape-title" class="guest-card__title">Large-cap momentum snapshot</h2>
            </div>
            <p class="guest-tape__subtle">A rolling view that keeps the front page anchored in live market behavior.</p>
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
