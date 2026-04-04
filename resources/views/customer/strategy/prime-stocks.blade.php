<?php
// اعوز بالله من الشياطين و ان يحضرون بسم الله الرحمن الرحيم الله لا إله إلا هو الحي القيوم
// Bismillahi ar-Rahmani ar-Rahim Audhu billahi min ash-shayatin wa an yahdurun Bismillah ar-Rahman ar-Rahim Allah la ilaha illa huwa al-hayy al-qayyum. Tamsa Allahu ala ayunihim
// version: x
// ======================================================
// - App Name: bismel1.com
// - Gusgraph LLC -
// - Author: Gus Kazem
// - https://Gusgraph.com
// - File Path: resources/views/customer/strategy/prime-stocks.blade.php
// ======================================================
?>
@extends('layouts.app')

@section('title', 'Prime Stocks Test Console')

@section('content')
    @php
        $breadcrumbs = \App\Support\View\Breadcrumbs::make([
            ['label' => 'Home', 'route' => 'home'],
            ['label' => 'Customer', 'route' => 'customer.dashboard'],
            ['label' => 'Prime Stocks Test Console'],
        ]);
        $sectionNavItems = \App\Support\ViewData\CustomerSectionNavData::make();
    @endphp

    @include('partials.ui.page-shell', [
        'headerPartial' => 'customer.partials.page-header',
        'page' => $page,
        'summary' => $summary,
    ])
    @include('partials.ui.section-nav', ['title' => 'Customer Section Navigation', 'items' => $sectionNavItems])

    <div class="customer-page customer-page--prime-stocks">
        <div class="customer-page__grid customer-page__grid--sidebar">
            <div class="customer-page__main">
                <section class="customer-section">
                    <header class="customer-section__header">
                        <div class="customer-section__heading">
                            @include('partials.ui.icon', ['icon' => 'fa-solid fa-chart-line', 'tone' => 'sky', 'size' => 'lg'])
                            <div>
                                <p class="customer-section__eyebrow">Runtime preview</p>
                                <h2 class="customer-section__title">Prime Stocks customer-facing demo status</h2>
                            </div>
                        </div>
                        <p class="customer-section__body">These values are static demo placeholders for visual review only. They describe how the customer surface should read once the server-side runtime is connected later.</p>
                    </header>

                    @include('partials.ui.summary-grid', ['items' => $statusItems])
                </section>

                <section class="customer-section">
                    <header class="customer-section__header">
                        <div class="customer-section__heading">
                            @include('partials.ui.icon', ['icon' => 'fa-solid fa-compass-drafting', 'tone' => 'emerald', 'size' => 'lg'])
                            <div>
                                <p class="customer-section__eyebrow">Approved frame</p>
                                <h2 class="customer-section__title">Strategy defaults and timing model</h2>
                            </div>
                        </div>
                        <p class="customer-section__body">This surface keeps the approved Prime Stocks framing visible: stocks only, 1H for when, 1D for whether, and pullback window 5.</p>
                    </header>

                    <div class="customer-page__detail-grid">
                        <div class="customer-card-group">
                            @include('partials.ui.info-card', [
                                'title' => 'Prime Stocks strategy frame',
                                'body' => 'The customer workspace shows the current defaults and language without suggesting the browser is the runtime.',
                            ])
                            @include('partials.ui.stat-list', ['items' => $strategyFrame])
                        </div>

                        <div class="customer-card-group">
                            @include('partials.ui.info-card', [
                                'title' => 'Runtime boundary',
                                'body' => 'Bot runtime target: Cloud Run serverless. User page role: control / monitoring only. Trading does not require the page to stay open.',
                                'icon' => 'fa-solid fa-cloud',
                                'tone' => 'sky',
                            ])
                        </div>
                    </div>
                </section>

                <section class="customer-section">
                    <header class="customer-section__header">
                        <div class="customer-section__heading">
                            @include('partials.ui.icon', ['icon' => 'fa-solid fa-layer-group', 'tone' => 'violet', 'size' => 'lg'])
                            <div>
                                <p class="customer-section__eyebrow">Behavior language</p>
                                <h2 class="customer-section__title">Entry, add, pause, and exit concepts</h2>
                            </div>
                        </div>
                        <p class="customer-section__body">The concepts below are visible explanations for customer review. They do not introduce live runtime wiring, browser automation, or polling.</p>
                    </header>

                    <div class="customer-page__detail-grid">
                        <div class="customer-card-group customer-card-group--full">
                            @include('partials.ui.info-card', [
                                'title' => 'Prime Stocks behavior model',
                                'body' => 'Prime Stocks concept source of truth is the canonical strategy spec in the Python repo. This customer surface mirrors the approved concepts visually with demo-only values.',
                            ])
                            @include('partials.ui.stat-list', ['items' => $behaviorItems])
                        </div>
                    </div>
                </section>
            </div>

            <aside class="customer-page__side">
                <div class="customer-card-group">
                    @include('partials.ui.info-card', [
                        'title' => 'Control surface rules',
                        'body' => 'This page exists to let the customer understand posture, defaults, and control concepts while the bot remains a server-side process on Cloud Run.',
                    ])
                    @include('partials.ui.stat-list', ['items' => $controlItems])
                </div>

                <div class="customer-card-group">
                    @include('partials.ui.info-card', [
                        'title' => 'Related Pages',
                        'symbol' => 'ﷻ',
                    ])
                    @include('partials.ui.link-list', ['items' => $relatedLinks])
                </div>
            </aside>
        </div>
    </div>
@endsection
