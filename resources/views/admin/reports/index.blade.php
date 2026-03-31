<?php
// اعوز بالله من الشياطين و ان يحضرون بسم الله الرحمن الرحيم الله لا إله إلا هو الحي القيوم
// Bismillahi ar-Rahmani ar-Rahim Audhu billahi min ash-shayatin wa an yahdurun Bismillah ar-Rahman ar-Rahim Allah la ilaha illa huwa al-hayy al-qayyum. Tamsa Allahu ala ayunihim
// version: x
// ======================================================
// - App Name: bismel1.com
// - Gusgraph LLC -
// - Author: Gus Kazem
// - https://Gusgraph.com
// - File Path: resources/views/admin/reports/index.blade.php
// ======================================================
?>
@extends('layouts.app')

@section('title', 'Admin Reports')

@section('content')
    @php
        $breadcrumbs = \App\Support\View\Breadcrumbs::make([
            ['label' => 'Home', 'route' => 'home'],
            ['label' => 'Admin', 'route' => 'admin.dashboard'],
            ['label' => 'Reports'],
        ]);
        $sectionNavItems = \App\Support\ViewData\AdminSectionNavData::make();
    @endphp

    @include('partials.ui.breadcrumbs', ['items' => $breadcrumbs])
    @include('partials.ui.page-shell', ['headerPartial' => 'admin.partials.page-header', 'page' => $page])
    @include('partials.ui.section-nav', ['title' => 'Admin Section Navigation', 'items' => $sectionNavItems])

    <div class="admin-page admin-page--reports">
        <section class="admin-page__hero">
            @include('partials.ui.info-card', ['title' => $summary['headline'] ?? 'Admin reporting', 'body' => $summary['details'] ?? null, 'symbol' => 'ﷺ'])
        </section>

        <div class="admin-page__grid admin-page__grid--sidebar">
            <div class="admin-page__main">
                <section class="admin-section">
                    <header class="admin-section__header">
                        <div class="admin-section__heading">
                            @include('partials.ui.icon', ['icon' => 'fa-solid fa-chart-pie', 'tone' => 'amber', 'size' => 'lg'])
                            <div>
                                <p class="admin-section__eyebrow">Report overview</p>
                                <h2 class="admin-section__title">Platform coverage and reporting structure</h2>
                            </div>
                        </div>
                        <p class="admin-section__body">The reporting surface stays compact: platform footprint, operational coverage, and current system posture in a single scannable read.</p>
                    </header>

                    <div class="admin-page__detail-grid">
                        <div class="admin-card-group admin-card-group--full">
                            <section class="ui-panel" aria-labelledby="admin-reports-frame-title">
                                <header class="ui-panel__header">
                                    <div class="ui-panel__heading">
                                        @include('partials.ui.icon', ['icon' => 'fa-solid fa-file-waveform', 'tone' => 'sky', 'size' => 'lg'])
                                        <div>
                                            <p class="ui-panel__eyebrow">Report frame</p>
                                            <h2 class="ui-panel__title" id="admin-reports-frame-title">How this platform report is organized</h2>
                                        </div>
                                    </div>
                                    <p class="ui-panel__body">The report stays simple: workspace coverage, operational activity, and current system posture.</p>
                                </header>
                                @include('partials.ui.stat-list', ['items' => $page['sections'] ?? [], 'labelKey' => 'heading', 'valueKey' => 'description'])
                                <p><small aria-hidden="true">﷽</small></p>
                            </section>
                        </div>

                        <div class="admin-card-group admin-card-group--full">
                            @include('admin.partials.report-summary', [
                                'summary' => $summary,
                                'metrics' => $metrics,
                                'trendColumns' => $trendColumns,
                                'trendRows' => $trendRows,
                            ])
                        </div>

                        <div class="admin-card-group admin-card-group--full">
                            <section class="ui-panel" aria-labelledby="admin-operations-report-title">
                                <header class="ui-panel__header">
                                    <div class="ui-panel__heading">
                                        @include('partials.ui.icon', ['icon' => 'fa-solid fa-robot', 'tone' => 'emerald', 'size' => 'lg'])
                                        <div>
                                            <p class="ui-panel__eyebrow">Operations report</p>
                                            <h2 class="ui-panel__title" id="admin-operations-report-title">Bismel1 admin operations control surface</h2>
                                        </div>
                                    </div>
                                    <p class="ui-panel__body">These summaries keep automation oversight narrow and safe: active vs stopped accounts, blocked categories, broker readiness, last and next runs, and recent outcomes.</p>
                                </header>
                                @include('admin.partials.bismel1-operations-summary', ['operationsOverview' => $operationsOverview ?? []])
                                @include('admin.partials.bismel1-operations-account-table', ['rows' => data_get($operationsOverview ?? [], 'account_rows', [])])
                                @include('admin.partials.bismel1-operations-outcomes', [
                                    'recentExecutionItems' => data_get($operationsOverview ?? [], 'recent_execution_items', []),
                                    'recentRiskItems' => data_get($operationsOverview ?? [], 'recent_risk_items', []),
                                    'recentPositionItems' => data_get($operationsOverview ?? [], 'recent_position_items', []),
                                    'runtimeWarningItems' => data_get($operationsOverview ?? [], 'runtime_warning_items', []),
                                ])
                            </section>
                        </div>
                    </div>
                </section>
            </div>

            <aside class="admin-page__side">
                @unless ($hasReportData ?? false)
                    @include('partials.ui.empty-state', [
                        'title' => 'Local Admin Report Data Missing',
                        'message' => 'Accounts, billing, broker access, licenses, audit activity, and system posture will appear here as the platform becomes active.',
                    ])
                @endunless

                <div class="admin-card-group">
                    @include('partials.ui.firestore-summary-card', [
                        'title' => 'Runtime Mapping Signals',
                        'summary' => $firestoreAdminSummary ?? [],
                    ])
                </div>

                @unless ($hasOperationsData ?? false)
                    <div class="admin-card-group">
                        @include('partials.ui.empty-state', [
                            'title' => 'No Operations Reporting Yet',
                            'message' => 'Automation reporting will appear here once customer runtime, signal, and execution activity is recorded.',
                        ])
                    </div>
                @endunless

                <div class="admin-card-group">
                    <section class="ui-panel" aria-labelledby="admin-reports-links-title">
                        <header class="ui-panel__header">
                            <div class="ui-panel__heading">
                                @include('partials.ui.icon', ['icon' => 'fa-solid fa-compass-drafting', 'tone' => 'violet', 'size' => 'lg'])
                                <div>
                                    <p class="ui-panel__eyebrow">Next stops</p>
                                    <h2 class="ui-panel__title" id="admin-reports-links-title">Related pages <small aria-hidden="true">ﷻ</small></h2>
                                </div>
                            </div>
                            <p class="ui-panel__body">Use these pages to move from platform summary into the exact control area that needs review.</p>
                        </header>
                        @include('partials.ui.link-list', ['items' => $relatedLinks ?? []])
                    </section>
                </div>
            </aside>
        </div>
    </div>
@endsection
