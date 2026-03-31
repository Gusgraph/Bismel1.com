<?php
// اعوز بالله من الشياطين و ان يحضرون بسم الله الرحمن الرحيم الله لا إله إلا هو الحي القيوم
// Bismillahi ar-Rahmani ar-Rahim Audhu billahi min ash-shayatin wa an yahdurun Bismillah ar-Rahman ar-Rahim Allah la ilaha illa huwa al-hayy al-qayyum. Tamsa Allahu ala ayunihim
// version: x
// ======================================================
// - App Name: bismel1.com
// - Gusgraph LLC -
// - Author: Gus Kazem
// - https://Gusgraph.com
// - File Path: resources/views/customer/reports/index.blade.php
// ======================================================
?>
@extends('layouts.app')

@section('title', 'Customer Reports')

@section('content')
    @php
        $breadcrumbs = \App\Support\View\Breadcrumbs::make([
            ['label' => 'Home', 'route' => 'home'],
            ['label' => 'Customer', 'route' => 'customer.dashboard'],
            ['label' => 'Reports'],
        ]);
        $sectionNavItems = \App\Support\ViewData\CustomerSectionNavData::make();
    @endphp

    @include('partials.ui.breadcrumbs', ['items' => $breadcrumbs])
    @include('partials.ui.page-shell', ['headerPartial' => 'customer.partials.page-header', 'page' => $page])
    @include('partials.ui.section-nav', ['title' => 'Customer Section Navigation', 'items' => $sectionNavItems])

    <div class="customer-page customer-page--reports">
        <section class="customer-page__hero">
            @include('partials.ui.info-card', ['title' => $summary['headline'] ?? 'Customer reporting', 'body' => $summary['details'] ?? null, 'symbol' => 'ﷺ'])
        </section>

        <div class="customer-page__grid customer-page__grid--sidebar">
            <div class="customer-page__main">
                <section class="customer-section">
                    <header class="customer-section__header">
                        <div class="customer-section__heading">
                            @include('partials.ui.icon', ['icon' => 'fa-solid fa-chart-line', 'tone' => 'sky', 'size' => 'lg'])
                            <div>
                                <p class="customer-section__eyebrow">Report overview</p>
                                <h2 class="customer-section__title">How this workspace summary is organized</h2>
                            </div>
                        </div>
                        <p class="customer-section__body">The report stays simple: plan and billing, setup progress, and recent workspace activity.</p>
                    </header>
                    @include('partials.ui.stat-list', ['items' => $page['sections'] ?? [], 'labelKey' => 'heading', 'valueKey' => 'description'])
                    <p><small aria-hidden="true">﷽</small></p>
                </section>

                @include('customer.partials.report-summary', [
                    'summary' => $summary,
                    'metrics' => $metrics,
                    'trendColumns' => $trendColumns,
                    'trendRows' => $trendRows,
                ])
            </div>

            <aside class="customer-page__side">
                @unless ($hasReportData ?? false)
                    @include('partials.ui.empty-state', [
                        'title' => 'Your report will fill in as setup continues',
                        'message' => 'Billing, broker access, API access, and account activity will begin to appear here as your workspace becomes active.',
                    ])
                @endunless

                <div class="customer-card-group">
                    @include('partials.ui.firestore-summary-card', [
                        'title' => 'Runtime Signals',
                        'summary' => $firestoreReadSummary ?? [],
                    ])
                </div>

                <div class="customer-card-group">
                    @include('partials.ui.info-card', ['title' => 'Related Pages', 'symbol' => 'ﷻ'])
                    @include('partials.ui.link-list', ['items' => $relatedLinks ?? []])
                </div>
            </aside>
        </div>
    </div>
@endsection
