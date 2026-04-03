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
        @include('customer.partials.dashboard-stats')

        <div class="dashboard-columns dashboard-columns--customer">
            <section class="dashboard-band dashboard-band--secondary" aria-labelledby="customer-dashboard-control-title">
                <header class="dashboard-band__header">
                    <div class="dashboard-band__heading">
                        @include('partials.ui.icon', ['icon' => 'fa-solid fa-gauge-high', 'tone' => 'sky', 'size' => 'xl'])
                        <div>
                            <p class="dashboard-band__eyebrow">Main blocks</p>
                            <h2 class="dashboard-band__title" id="customer-dashboard-control-title">Positions, orders, activity, signals, and action needed</h2>
                        </div>
                    </div>
                    <p class="dashboard-band__body">The customer dashboard is the trading-first control surface: live exposure, order flow, recent desk activity, signals, and the next action all stay together here.</p>
                </header>

                <div class="dashboard-stack">
                    @include('customer.partials.dashboard-sections')
                </div>
            </section>

            <aside class="dashboard-sidebar" aria-label="Customer dashboard sidebar">
                @unless ($hasDashboardData ?? false)
                    @include('partials.ui.empty-state', [
                        'title' => 'Trading data is still light',
                        'message' => 'The desk will fill in as broker sync, runtime updates, positions, and orders begin to land from real local records.',
                    ])
                @endunless

                <div class="dashboard-sidebar__block">
                    @include('partials.ui.info-card', ['title' => 'Side Panel', 'symbol' => '﷽'])
                    @include('customer.partials.dashboard-readiness')
                </div>
            </aside>
        </div>
    </div>
@endsection
