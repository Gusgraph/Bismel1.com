<?php
// اعوز بالله من الشياطين و ان يحضرون بسم الله الرحمن الرحيم الله لا إله إلا هو الحي القيوم
// Bismillahi ar-Rahmani ar-Rahim Audhu billahi min ash-shayatin wa an yahdurun Bismillah ar-Rahman ar-Rahim Allah la ilaha illa huwa al-hayy al-qayyum. Tamsa Allahu ala ayunihim
// version: x
// ======================================================
// - App Name: bismel1.com
// - Gusgraph LLC -
// - Author: Gus Kazem
// - https://Gusgraph.com
// - File Path: resources/views/admin/dashboard.blade.php
// ======================================================
?>
@extends('layouts.app')

@section('title', 'Admin Dashboard')

@section('content')
    @php
        $breadcrumbs = \App\Support\View\Breadcrumbs::make([
            ['label' => 'Home', 'route' => 'home'],
            ['label' => 'Admin', 'route' => 'admin.dashboard'],
            ['label' => 'Dashboard'],
        ]);
        $sectionNavItems = \App\Support\ViewData\AdminSectionNavData::make();
    @endphp

    @include('partials.ui.breadcrumbs', ['items' => $breadcrumbs])
    @include('partials.ui.page-shell', ['headerPartial' => 'admin.partials.page-header', 'page' => $page])
    @include('partials.ui.section-nav', ['title' => 'Admin Section Navigation', 'items' => $sectionNavItems])

    <div class="dashboard-view dashboard-view--admin">
        <section class="dashboard-hero">
            @include('admin.partials.dashboard-stats')
            @include('admin.partials.dashboard-sections')
        </section>

        <section class="dashboard-band dashboard-band--primary" aria-labelledby="admin-dashboard-detail-title">
            <header class="dashboard-band__header">
                <div class="dashboard-band__heading">
                    @include('partials.ui.icon', ['icon' => 'fa-solid fa-tower-broadcast', 'tone' => 'rose', 'size' => 'xl'])
                    <div>
                        <p class="dashboard-band__eyebrow">Platform overview</p>
                        <h2 class="dashboard-band__title" id="admin-dashboard-detail-title">Platform detail <small aria-hidden="true">ﷺ</small></h2>
                    </div>
                </div>
                <p class="dashboard-band__body">Current platform visibility is grouped here across accounts, billing, broker access, licenses, system posture, and audit activity.</p>
            </header>

            <div class="dashboard-grid dashboard-grid--admin-core">
                @include('admin.partials.accounts-summary')
                @include('admin.partials.dashboard-billing')
                @include('admin.partials.dashboard-broker')
                @include('admin.partials.dashboard-license')
                @include('admin.partials.system-summary')
                @include('admin.partials.dashboard-activity')
            </div>
        </section>

        <div class="dashboard-columns dashboard-columns--admin">
            <section class="dashboard-band dashboard-band--secondary" aria-labelledby="admin-dashboard-oversight-title">
                <header class="dashboard-band__header">
                    <div class="dashboard-band__heading">
                        @include('partials.ui.icon', ['icon' => 'fa-solid fa-shield-halved', 'tone' => 'amber', 'size' => 'xl'])
                        <div>
                            <p class="dashboard-band__eyebrow">Operational oversight</p>
                            <h2 class="dashboard-band__title" id="admin-dashboard-oversight-title">What needs attention right now</h2>
                        </div>
                    </div>
                    <p class="dashboard-band__body">Use these operational groups to judge platform readiness, drill into workspaces, and spot missing system coverage.</p>
                </header>

                <div class="dashboard-stack">
                    <section class="dashboard-subpanel">
                        <header class="dashboard-subpanel__header">
                            <div class="dashboard-subpanel__heading">
                                @include('partials.ui.icon', ['icon' => 'fa-solid fa-building-shield', 'tone' => 'amber', 'size' => 'lg'])
                                <div>
                                    <p class="dashboard-band__eyebrow">Workspaces</p>
                                    <h3 class="dashboard-subpanel__title">Admin control summary <small aria-hidden="true">﷽</small></h3>
                                </div>
                            </div>
                            <p class="dashboard-subpanel__body">Recent workspaces are listed here with the plan, owner, and license context needed for follow-up.</p>
                        </header>
                        @include('admin.partials.account-management-summary')
                    </section>

                    <section class="dashboard-subpanel">
                        <header class="dashboard-subpanel__header">
                            <div class="dashboard-subpanel__heading">
                                @include('partials.ui.icon', ['icon' => 'fa-solid fa-robot', 'tone' => 'sky', 'size' => 'lg'])
                                <div>
                                    <p class="dashboard-band__eyebrow">Bismel1 operations</p>
                                    <h3 class="dashboard-subpanel__title">Customer automation control surface</h3>
                                </div>
                            </div>
                            <p class="dashboard-subpanel__body">This summary stays operational: which customer bots are active, which are blocked, broker readiness, last and next runs, and safe recent outcomes.</p>
                        </header>
                        @include('admin.partials.bismel1-operations-summary', ['operationsOverview' => $operationsOverview ?? []])
                        @include('admin.partials.bismel1-operations-account-table', ['rows' => data_get($operationsOverview ?? [], 'account_rows', [])])
                    </section>

                    <section class="dashboard-subpanel">
                        <header class="dashboard-subpanel__header">
                            <div class="dashboard-subpanel__heading">
                                @include('partials.ui.icon', ['icon' => 'fa-solid fa-server', 'tone' => 'rose', 'size' => 'lg'])
                                <div>
                                    <p class="dashboard-band__eyebrow">Configuration</p>
                                    <h3 class="dashboard-subpanel__title">System health</h3>
                                </div>
                            </div>
                            <p class="dashboard-subpanel__body">A compact read of the key signals needed to judge whether the platform is configured and ready for review.</p>
                        </header>
                        @include('admin.partials.system-health-summary')
                    </section>

                    <section class="dashboard-subpanel">
                        <header class="dashboard-subpanel__header">
                            <div class="dashboard-subpanel__heading">
                                @include('partials.ui.icon', ['icon' => 'fa-solid fa-clipboard-check', 'tone' => 'violet', 'size' => 'lg'])
                                <div>
                                    <p class="dashboard-band__eyebrow">Oversight</p>
                                    <h3 class="dashboard-subpanel__title">Audit overview</h3>
                                </div>
                            </div>
                            <p class="dashboard-subpanel__body">Recent operational evidence from activity, audit, and billing events.</p>
                        </header>
                        @include('admin.partials.audit-overview')
                    </section>

                    <section class="dashboard-subpanel">
                        <header class="dashboard-subpanel__header">
                            <div class="dashboard-subpanel__heading">
                                @include('partials.ui.icon', ['icon' => 'fa-solid fa-wave-square', 'tone' => 'emerald', 'size' => 'lg'])
                                <div>
                                    <p class="dashboard-band__eyebrow">Recent outcomes</p>
                                    <h3 class="dashboard-subpanel__title">Execution, risk, and reconciliation</h3>
                                </div>
                            </div>
                            <p class="dashboard-subpanel__body">Recent automation outcomes stay grouped here so admin can review health without reading raw logs or hidden strategy logic.</p>
                        </header>
                        @include('admin.partials.bismel1-operations-outcomes', [
                            'recentExecutionItems' => data_get($operationsOverview ?? [], 'recent_execution_items', []),
                            'recentRiskItems' => data_get($operationsOverview ?? [], 'recent_risk_items', []),
                            'recentPositionItems' => data_get($operationsOverview ?? [], 'recent_position_items', []),
                            'runtimeWarningItems' => data_get($operationsOverview ?? [], 'runtime_warning_items', []),
                        ])
                    </section>
                </div>
            </section>

            <aside class="dashboard-sidebar" aria-label="Admin dashboard sidebar">
                @unless ($hasDashboardData ?? false)
                    @include('partials.ui.empty-state', [
                        'title' => 'Local Admin Dashboard Data Missing',
                        'message' => 'Accounts, billing, broker access, licenses, audit activity, and system posture will appear here as the platform becomes active.',
                    ])
                @endunless

                <div class="dashboard-sidebar__block">
                    @include('partials.ui.info-card', ['title' => 'Operations Notes', 'symbol' => '﷽'])
                    @include('admin.partials.admin-alerts')
                </div>

                @unless ($hasOperationsData ?? false)
                    <div class="dashboard-sidebar__block">
                        @include('partials.ui.empty-state', [
                            'title' => 'No Automation Operations Yet',
                            'message' => 'Customer automation, execution, and risk summaries will appear here once runtime activity is recorded.',
                        ])
                    </div>
                @endunless

                <div class="dashboard-sidebar__block">
                    @include('partials.ui.info-card', ['title' => 'Visibility Areas'])
                    @include('partials.ui.link-list', ['items' => [
                        ['route' => 'admin.licenses.index', 'label' => 'License Inventory', 'description' => 'Review API license and key oversight across all workspaces.'],
                        ['route' => 'admin.audit.index', 'label' => 'Audit Logs', 'description' => 'Open the activity and audit trail when an operations signal needs explanation.'],
                        ['route' => 'admin.reports.index', 'label' => 'Reports', 'description' => 'Review broader platform summaries and trend snapshots.'],
                        ['route' => 'admin.accounts.index', 'label' => 'Account Detail', 'description' => 'Move from platform summary into a specific workspace control context.'],
                    ]])
                </div>
            </aside>
        </div>

        <section class="dashboard-band dashboard-band--reference" aria-labelledby="admin-dashboard-reference-title">
            <header class="dashboard-band__header">
                <div class="dashboard-band__heading">
                    @include('partials.ui.icon', ['icon' => 'fa-solid fa-book-bookmark', 'tone' => 'violet', 'size' => 'lg'])
                    <div>
                        <p class="dashboard-band__eyebrow">Reference</p>
                        <h2 class="dashboard-band__title" id="admin-dashboard-reference-title">Reference labels</h2>
                    </div>
                </div>
                <p class="dashboard-band__body">These labels define the state language used across the admin pages.</p>
            </header>
            <div class="dashboard-reference-grid">
                <section class="dashboard-reference-card">
                    <h3>Account Statuses</h3>
                    @include('partials.ui.stat-list', ['items' => collect($statusLabels)->map(fn ($label, $value) => ['label' => $label, 'value' => $value])->values()->all()])
                </section>
                <section class="dashboard-reference-card">
                    <h3>Audit Events</h3>
                    @include('partials.ui.stat-list', ['items' => collect($auditLabels)->map(fn ($label, $value) => ['label' => $label, 'value' => $value])->values()->all()])
                </section>
            </div>
            <p><small aria-hidden="true">ﷻ</small></p>
        </section>
    </div>
@endsection
