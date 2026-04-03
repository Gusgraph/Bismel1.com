<?php
// اعوز بالله من الشياطين و ان يحضرون بسم الله الرحمن الرحيم الله لا إله إلا هو الحي القيوم
// Bismillahi ar-Rahmani ar-Rahim Audhu billahi min ash-shayatin wa an yahdurun Bismillah ar-Rahman ar-Rahim Allah la ilaha illa huwa al-hayy al-qayyum. Tamsa Allahu ala ayunihim
// version: x
// ======================================================
// - App Name: bismel1.com
// - Gusgraph LLC -
// - Author: Gus Kazem
// - https://Gusgraph.com
// - File Path: resources/views/customer/trading/index.blade.php
// ======================================================
?>
@extends('layouts.app')

@section('title', $page['title'] ?? 'Customer Trading')

@section('content')
    @php
        $breadcrumbs = \App\Support\View\Breadcrumbs::make([
            ['label' => 'Home', 'route' => 'home'],
            ['label' => 'Customer', 'route' => 'customer.dashboard'],
            ['label' => $page['title'] ?? 'Trading'],
        ]);
        $sectionNavItems = \App\Support\ViewData\CustomerSectionNavData::make();
    @endphp

    @include('partials.ui.breadcrumbs', ['items' => $breadcrumbs])
    @include('partials.ui.page-shell', [
        'headerPartial' => 'customer.partials.page-header',
        'page' => $page,
        'summary' => [
            'eyebrow' => 'Trading visibility',
            'title' => $summary['headline'] ?? 'Customer trading visibility',
            'body' => $summary['details'] ?? null,
            'icon' => 'fa-solid fa-chart-line',
            'tone' => 'sky',
        ],
    ])
    @include('partials.ui.section-nav', ['title' => 'Customer Section Navigation', 'items' => $sectionNavItems])

    <div class="customer-page customer-page--trading">
        <div class="customer-page__grid customer-page__grid--sidebar">
            <div class="customer-page__main">
                <section class="customer-section">
                    <header class="customer-section__header">
                        <div class="customer-section__heading">
                            @include('partials.ui.icon', ['icon' => 'fa-solid fa-chart-line', 'tone' => 'sky', 'size' => 'lg'])
                            <div>
                                <p class="customer-section__eyebrow">Trading visibility</p>
                                <h2 class="customer-section__title">Your current view</h2>
                            </div>
                        </div>
                        <p class="customer-section__body">This page keeps positions, orders, and activity clear, current, and easy to follow for this workspace.</p>
                    </header>
                    @include('partials.ui.stat-list', ['items' => $page['sections'] ?? [], 'labelKey' => 'heading', 'valueKey' => 'description'])
                    <p><small aria-hidden="true">﷽</small></p>
                </section>

                <section class="customer-section">
                    <header class="customer-section__header">
                        <div class="customer-section__heading">
                            @include('partials.ui.icon', ['icon' => 'fa-solid fa-table-columns', 'tone' => 'emerald', 'size' => 'lg'])
                            <div>
                                <p class="customer-section__eyebrow">Workspace summary</p>
                                <h2 class="customer-section__title">Current status</h2>
                            </div>
                        </div>
                    </header>
                    @include('partials.ui.summary-grid', ['items' => $summaryItems ?? []])
                </section>

                <section class="customer-section">
                    <header class="customer-section__header">
                        <div class="customer-section__heading">
                            @include('partials.ui.icon', ['icon' => 'fa-solid fa-layer-group', 'tone' => 'violet', 'size' => 'lg'])
                            <div>
                                <p class="customer-section__eyebrow">Primary visibility</p>
                                <h2 class="customer-section__title">{{ $primaryTitle ?? 'Primary records' }}</h2>
                            </div>
                        </div>
                    </header>
                    @include('partials.ui.record-list', [
                        'items' => $primaryItems ?? [],
                        'emptyMessage' => $primaryEmptyMessage ?? 'Nothing is showing here yet.',
                    ])
                </section>

                <section class="customer-section">
                    <header class="customer-section__header">
                        <div class="customer-section__heading">
                            @include('partials.ui.icon', ['icon' => 'fa-solid fa-wave-square', 'tone' => 'rose', 'size' => 'lg'])
                            <div>
                                <p class="customer-section__eyebrow">Recent follow-through</p>
                                <h2 class="customer-section__title">{{ $secondaryTitle ?? 'Recent summaries' }}</h2>
                            </div>
                        </div>
                    </header>
                    @include('partials.ui.record-list', [
                        'items' => $secondaryItems ?? [],
                        'emptyMessage' => $secondaryEmptyMessage ?? 'Nothing recent is showing here yet.',
                    ])
                </section>
            </div>

            <aside class="customer-page__side">
                @unless ($hasTradingData ?? false)
                    @include('partials.ui.empty-state', [
                        'title' => $emptyStateTitle ?? 'No Data Yet',
                        'message' => $emptyStateMessage ?? 'This area will update as activity begins in the workspace.',
                    ])
                @endunless

                <div class="customer-card-group">
                    @include('partials.ui.info-card', ['title' => 'Related Pages', 'symbol' => 'ﷻ'])
                    @include('partials.ui.link-list', ['items' => $relatedLinks ?? []])
                </div>
            </aside>
        </div>
    </div>
@endsection
