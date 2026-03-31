<?php
// اعوز بالله من الشياطين و ان يحضرون بسم الله الرحمن الرحيم الله لا إله إلا هو الحي القيوم
// Bismillahi ar-Rahmani ar-Rahim Audhu billahi min ash-shayatin wa an yahdurun Bismillah ar-Rahman ar-Rahim Allah la ilaha illa huwa al-hayy al-qayyum. Tamsa Allahu ala ayunihim
// version: x
// ======================================================
// - App Name: bismel1.com
// - Gusgraph LLC -
// - Author: Gus Kazem
// - https://Gusgraph.com
// - File Path: resources/views/customer/onboarding/index.blade.php
// ======================================================
?>
@extends('layouts.app')

@section('title', 'Customer Onboarding')

@section('content')
    @php
        $breadcrumbs = \App\Support\View\Breadcrumbs::make([
            ['label' => 'Home', 'route' => 'home'],
            ['label' => 'Customer', 'route' => 'customer.dashboard'],
            ['label' => 'Onboarding'],
        ]);
        $sectionNavItems = \App\Support\ViewData\CustomerSectionNavData::make();
    @endphp

    @include('partials.ui.breadcrumbs', ['items' => $breadcrumbs])
    @include('partials.ui.page-shell', ['headerPartial' => 'customer.partials.page-header', 'page' => $page])
    @include('partials.ui.section-nav', ['title' => 'Customer Section Navigation', 'items' => $sectionNavItems])

    <div class="customer-page customer-page--onboarding">
        <section class="customer-page__hero">
            @include('partials.ui.info-card', ['title' => $summary['headline'], 'body' => $summary['details'], 'symbol' => '﷽'])
        </section>

        <div class="customer-page__grid customer-page__grid--sidebar">
            <div class="customer-page__main">
                <section class="customer-section">
                    <header class="customer-section__header">
                        <div class="customer-section__heading">
                            @include('partials.ui.icon', ['icon' => 'fa-solid fa-route', 'tone' => 'emerald', 'size' => 'lg'])
                            <div>
                                <p class="customer-section__eyebrow">Setup flow</p>
                                <h2 class="customer-section__title">What still needs to be done</h2>
                            </div>
                        </div>
                        <p class="customer-section__body">Each lane below reflects the current setup status for this workspace.</p>
                    </header>
                    @include('partials.ui.stat-list', ['items' => $page['sections'], 'labelKey' => 'heading', 'valueKey' => 'description'])
                </section>

                <section class="customer-section">
                    <header class="customer-section__header">
                        <div class="customer-section__heading">
                            @include('partials.ui.icon', ['icon' => 'fa-solid fa-list-check', 'tone' => 'amber', 'size' => 'lg'])
                            <div>
                                <p class="customer-section__eyebrow">Checklist</p>
                                <h2 class="customer-section__title">Readiness checklist</h2>
                            </div>
                        </div>
                        <p class="customer-section__body">Use this sequence: confirm the workspace, activate the plan, connect Alpaca, verify paper or live mode, then finish the remaining setup items.</p>
                    </header>
                    @include('customer.partials.onboarding-checklist')
                </section>

                <section class="customer-section">
                    <header class="customer-section__header">
                        <div class="customer-section__heading">
                            @include('partials.ui.icon', ['icon' => 'fa-solid fa-shield-keyhole', 'tone' => 'violet', 'size' => 'lg'])
                            <div>
                                <p class="customer-section__eyebrow">Connection security</p>
                                <h2 class="customer-section__title">Broker access trust check</h2>
                            </div>
                        </div>
                        <p class="customer-section__body">Saved access stays masked while still confirming that the broker step has been completed and the account mode is known.</p>
                    </header>
                    @include('customer.partials.broker-credential-checklist')
                    <p><small aria-hidden="true">ﷻ</small></p>
                </section>
            </div>

            <aside class="customer-page__side">
                    @unless ($hasOnboardingData ?? false)
                        @include('partials.ui.empty-state', [
                            'title' => 'Local Onboarding Data Missing',
                            'message' => 'The workspace is still at the beginning of setup, so onboarding remains focused on the next required steps.',
                        ])
                    @endunless

                <div class="customer-card-group">
                    @include('partials.ui.firestore-summary-card', [
                        'title' => 'Runtime Readiness',
                        'summary' => $firestoreReadSummary ?? [],
                    ])
                </div>

                <div class="customer-card-group">
                    @include('partials.ui.info-card', ['title' => 'Setup Notes'])
                    @include('customer.partials.customer-alerts')
                </div>
            </aside>
        </div>
    </div>
@endsection
