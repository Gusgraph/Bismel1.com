<?php
// اعوز بالله من الشياطين و ان يحضرون بسم الله الرحمن الرحيم الله لا إله إلا هو الحي القيوم
// Bismillahi ar-Rahmani ar-Rahim Audhu billahi min ash-shayatin wa an yahdurun Bismillah ar-Rahman ar-Rahim Allah la ilaha illa huwa al-hayy al-qayyum. Tamsa Allahu ala ayunihim
// version: x
// ======================================================
// - App Name: bismel1.com
// - Gusgraph LLC -
// - Author: Gus Kazem
// - https://Gusgraph.com
// - File Path: resources/views/customer/dashboard.blade.php
// ======================================================
?>
@extends('layouts.app')

@section('title', 'Customer Dashboard')

@section('content')
    @php
        $breadcrumbs = \App\Support\View\Breadcrumbs::make([
            ['label' => 'Home', 'route' => 'home'],
            ['label' => 'Customer', 'route' => 'customer.dashboard'],
            ['label' => 'Dashboard'],
        ]);
        $sectionNavItems = \App\Support\ViewData\CustomerSectionNavData::make();
    @endphp

    @include('partials.ui.breadcrumbs', ['items' => $breadcrumbs])
    @include('partials.ui.page-shell', ['headerPartial' => 'customer.partials.page-header', 'page' => $page])
    @include('partials.ui.section-nav', ['title' => 'Customer Section Navigation', 'items' => $sectionNavItems])

    <div class="dashboard-view dashboard-view--customer">
        <section class="dashboard-hero">
            @include('customer.partials.dashboard-stats')
            @include('customer.partials.dashboard-sections')
        </section>

        <section class="dashboard-band dashboard-band--primary" aria-labelledby="customer-dashboard-health-title">
            <header class="dashboard-band__header">
                <div class="dashboard-band__heading">
                    @include('partials.ui.icon', ['icon' => 'fa-solid fa-gauge-high', 'tone' => 'sky', 'size' => 'xl'])
                    <div>
                        <p class="dashboard-band__eyebrow">Current account overview</p>
                        <h2 class="dashboard-band__title" id="customer-dashboard-health-title">Your workspace at a glance</h2>
                    </div>
                </div>
                <p class="dashboard-band__body">These cards give you a calm view of account access, billing, broker connection, and license status.</p>
            </header>

            <div class="dashboard-grid dashboard-grid--primary">
                @include('customer.partials.account-summary')
                @include('customer.partials.billing-summary')
                @include('customer.partials.broker-summary')
                @include('customer.partials.license-summary')
            </div>
        </section>

        <div class="dashboard-columns dashboard-columns--customer">
            <section class="dashboard-band dashboard-band--secondary" aria-labelledby="customer-dashboard-progress-title">
                <header class="dashboard-band__header">
                    <div class="dashboard-band__heading">
                        @include('partials.ui.icon', ['icon' => 'fa-solid fa-list-check', 'tone' => 'emerald', 'size' => 'xl'])
                        <div>
                            <p class="dashboard-band__eyebrow">Progress signals</p>
                            <h2 class="dashboard-band__title" id="customer-dashboard-progress-title">Readiness, activity, and next steps</h2>
                        </div>
                    </div>
                    <p class="dashboard-band__body">Recent activity and setup guidance stay grouped here so the next step is easy to spot.</p>
                </header>

                <div class="dashboard-stack">
                    @include('customer.partials.dashboard-readiness')
                    @include('customer.partials.dashboard-activity')
                </div>
            </section>

            <aside class="dashboard-sidebar" aria-label="Customer dashboard sidebar">
                @unless ($hasDashboardData ?? false)
                    @include('partials.ui.empty-state', [
                        'title' => 'Your workspace is still getting started',
                        'message' => 'Core account, billing, broker, and license details will appear here as setup is completed.',
                    ])
                @endunless

                <div class="dashboard-sidebar__block">
                    @include('partials.ui.info-card', ['title' => 'Workspace Notes', 'symbol' => '﷽'])
                    @include('customer.partials.customer-alerts')
                </div>

                <div class="dashboard-sidebar__block">
                    @include('partials.ui.firestore-summary-card', [
                        'title' => 'Runtime Support',
                        'summary' => $firestoreReadSummary ?? [],
                    ])
                </div>

                <div class="dashboard-sidebar__block">
                    @include('partials.ui.info-card', ['title' => 'Flow Areas', 'symbol' => '﷽'])
                    @include('partials.ui.link-list', ['items' => [
                        ['route' => 'customer.strategy.index', 'label' => 'Strategy', 'description' => 'Review strategy setup before expanding automation.'],
                        ['route' => 'customer.automation.index', 'label' => 'Automation', 'description' => 'Check automation state, readiness, and next run timing.'],
                        ['route' => 'customer.onboarding.index', 'label' => 'Onboarding', 'description' => 'Complete the remaining setup steps for this workspace.'],
                        ['route' => 'customer.invoices.index', 'label' => 'Invoices', 'description' => 'Review billing status and invoice history.'],
                        ['route' => 'customer.reports.index', 'label' => 'Reports', 'description' => 'Open a broader summary of this workspace.'],
                        ['route' => 'customer.settings.index', 'label' => 'Settings', 'description' => 'Review your profile and workspace defaults.'],
                    ]])
                </div>
            </aside>
        </div>

        <section class="dashboard-band dashboard-band--reference" aria-labelledby="customer-dashboard-reference-title">
            <header class="dashboard-band__header">
                <div class="dashboard-band__heading">
                    @include('partials.ui.icon', ['icon' => 'fa-solid fa-book-open', 'tone' => 'violet', 'size' => 'lg'])
                    <div>
                        <p class="dashboard-band__eyebrow">Reference</p>
                        <h2 class="dashboard-band__title" id="customer-dashboard-reference-title">Status labels used across the workspace</h2>
                    </div>
                </div>
                <p class="dashboard-band__body">These labels explain the status language used across the workspace.</p>
            </header>
            <div class="dashboard-reference-grid">
                @foreach ($statusGroups as $group => $labels)
                    <section class="dashboard-reference-card">
                        <h3>{{ $group }}</h3>
                        @include('partials.ui.stat-list', ['items' => collect($labels)->map(fn ($label, $value) => ['label' => $label, 'value' => $value])->values()->all()])
                    </section>
                @endforeach
            </div>
            <p><small aria-hidden="true">ﷻ</small></p>
        </section>
    </div>
@endsection
