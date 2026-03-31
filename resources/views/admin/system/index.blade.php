<?php
// اعوز بالله من الشياطين و ان يحضرون بسم الله الرحمن الرحيم الله لا إله إلا هو الحي القيوم
// Bismillahi ar-Rahmani ar-Rahim Audhu billahi min ash-shayatin wa an yahdurun Bismillah ar-Rahman ar-Rahim Allah la ilaha illa huwa al-hayy al-qayyum. Tamsa Allahu ala ayunihim
// version: x
// ======================================================
// - App Name: bismel1.com
// - Gusgraph LLC -
// - Author: Gus Kazem
// - https://Gusgraph.com
// - File Path: resources/views/admin/system/index.blade.php
// ======================================================
?>
@extends('layouts.app')

@section('title', 'Admin System')

@section('content')
    @php
        $breadcrumbs = \App\Support\View\Breadcrumbs::make([
            ['label' => 'Home', 'route' => 'home'],
            ['label' => 'Admin', 'route' => 'admin.dashboard'],
            ['label' => 'System'],
        ]);
        $sectionNavItems = \App\Support\ViewData\AdminSectionNavData::make();
    @endphp

    @include('partials.ui.breadcrumbs', ['items' => $breadcrumbs])
    @include('partials.ui.page-shell', ['headerPartial' => 'admin.partials.page-header', 'page' => $page])
    @include('partials.ui.section-nav', ['title' => 'Admin Section Navigation', 'items' => $sectionNavItems])

    <div class="admin-page admin-page--system">
        <section class="admin-page__hero">
            @include('partials.ui.info-card', ['title' => $summary['headline'], 'body' => $summary['details'], 'symbol' => 'ﷻ'])
        </section>

        <div class="admin-page__grid admin-page__grid--sidebar">
            <div class="admin-page__main">
                <section class="admin-section">
                    <header class="admin-section__header">
                        <div class="admin-section__heading">
                            @include('partials.ui.icon', ['icon' => 'fa-solid fa-server', 'tone' => 'sky', 'size' => 'lg'])
                            <div>
                                <p class="admin-section__eyebrow">Platform state</p>
                                <h2 class="admin-section__title">Health, settings, and current posture</h2>
                            </div>
                        </div>
                        <p class="admin-section__body">Use this system view to spot degraded runtime state quickly, review recovery order, and confirm whether platform settings still match current operations.</p>
                    </header>

                    <div class="admin-page__detail-grid">
                        <div class="admin-card-group">
                            @include('partials.ui.info-card', ['title' => 'System Areas'])
                            @include('partials.ui.stat-list', ['items' => $page['sections'], 'labelKey' => 'heading', 'valueKey' => 'description'])
                        </div>

                        <div class="admin-card-group">
                            @include('partials.ui.info-card', ['title' => 'Operations Notes'])
                            @include('admin.partials.admin-alerts')
                        </div>

                        <div class="admin-card-group">
                            @include('partials.ui.info-card', ['title' => 'System Health Summary'])
                            @include('admin.partials.system-health-summary')
                        </div>

                        <div class="admin-card-group">
                            @include('partials.ui.info-card', ['title' => 'Audit Overview'])
                            @include('admin.partials.audit-overview')
                        </div>

                        <div class="admin-card-group admin-card-group--full">
                            @include('partials.ui.info-card', ['title' => 'Current System Settings'])
                            @include('partials.ui.stat-list', ['items' => $currentSettings])
                        </div>

                        <div class="admin-card-group admin-card-group--full">
                            @include('partials.ui.info-card', ['title' => 'Platform State Signals'])
                            @include('admin.partials.system-status-list')
                        </div>

                        <div class="admin-card-group admin-card-group--full">
                            @include('partials.ui.info-card', ['title' => 'Bismel1 Operations Summary'])
                            @include('admin.partials.bismel1-operations-summary', ['operationsOverview' => $operationsOverview ?? []])
                        </div>

                        <div class="admin-card-group admin-card-group--full">
                            @include('partials.ui.info-card', ['title' => 'Customer Automation Accounts'])
                            @include('admin.partials.bismel1-operations-account-table', ['rows' => data_get($operationsOverview ?? [], 'account_rows', [])])
                        </div>

                        <div class="admin-card-group admin-card-group--full">
                            @include('partials.ui.info-card', ['title' => 'Recent Automation Outcomes'])
                            @include('admin.partials.bismel1-operations-outcomes', [
                                'recentExecutionItems' => data_get($operationsOverview ?? [], 'recent_execution_items', []),
                                'recentRiskItems' => data_get($operationsOverview ?? [], 'recent_risk_items', []),
                                'recentPositionItems' => data_get($operationsOverview ?? [], 'recent_position_items', []),
                                'runtimeWarningItems' => data_get($operationsOverview ?? [], 'runtime_warning_items', []),
                            ])
                        </div>
                    </div>
                </section>

                <section class="admin-section admin-section--reference">
                    <header class="admin-section__header">
                        <div class="admin-section__heading">
                            @include('partials.ui.icon', ['icon' => 'fa-solid fa-signal', 'tone' => 'emerald', 'size' => 'lg'])
                            <div>
                                <p class="admin-section__eyebrow">Reference</p>
                                <h2 class="admin-section__title">Status levels</h2>
                            </div>
                        </div>
                    </header>

                    @include('partials.ui.stat-list', ['items' => collect($statusLevels)->map(fn ($label, $value) => ['label' => $label, 'value' => $value])->values()->all()])
                    <p><small aria-hidden="true">﷽</small></p>
                    <p><small aria-hidden="true">ﷺ</small></p>
                </section>
            </div>

            <aside class="admin-page__side">
                <div class="admin-card-group">
                    @include('partials.ui.info-card', ['title' => 'System Actions'])
                    @include('partials.ui.link-list', ['items' => [
                        ['route' => 'admin.system.edit', 'label' => 'Edit System Settings', 'description' => 'Update the current runtime mode, review channel, and status level values.'],
                    ]])
                </div>

                <div class="admin-card-group">
                    @include('partials.ui.info-card', ['title' => 'Oversight Areas'])
                    @include('partials.ui.link-list', ['items' => [
                        ['route' => 'admin.licenses.index', 'label' => 'License Inventory', 'description' => 'Review current API license and key visibility.'],
                        ['route' => 'admin.audit.index', 'label' => 'Audit Visibility', 'description' => 'Review current logs, audit activity, and oversight flow.'],
                    ]])
                </div>

                @unless ($hasOperationsData ?? false)
                    <div class="admin-card-group">
                        @include('partials.ui.empty-state', [
                            'title' => 'No Automation Signals Yet',
                            'message' => 'Operations summaries appear here once customer automation activity is recorded.',
                        ])
                    </div>
                @endunless
            </aside>
        </div>
    </div>
@endsection
