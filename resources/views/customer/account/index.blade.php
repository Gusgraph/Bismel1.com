<?php
// اعوز بالله من الشياطين و ان يحضرون بسم الله الرحمن الرحيم الله لا إله إلا هو الحي القيوم
// Bismillahi ar-Rahmani ar-Rahim Audhu billahi min ash-shayatin wa an yahdurun Bismillah ar-Rahman ar-Rahim Allah la ilaha illa huwa al-hayy al-qayyum. Tamsa Allahu ala ayunihim
// version: x
// ======================================================
// - App Name: bismel1.com
// - Gusgraph LLC -
// - Author: Gus Kazem
// - https://Gusgraph.com
// - File Path: resources/views/customer/account/index.blade.php
// ======================================================
?>
@extends('layouts.app')

@section('title', 'Customer Account')

@section('content')
    @php
        $breadcrumbs = \App\Support\View\Breadcrumbs::make([
            ['label' => 'Home', 'route' => 'home'],
            ['label' => 'Customer', 'route' => 'customer.dashboard'],
            ['label' => 'Account'],
        ]);
        $sectionNavItems = \App\Support\ViewData\CustomerSectionNavData::make();
    @endphp

    @include('partials.ui.breadcrumbs', ['items' => $breadcrumbs])
    @include('partials.ui.page-shell', ['headerPartial' => 'customer.partials.page-header', 'page' => $page])
    @include('partials.ui.section-nav', ['title' => 'Customer Section Navigation', 'items' => $sectionNavItems])

    <div class="customer-page customer-page--account">
        <section class="customer-page__hero">
            @include('partials.ui.info-card', ['title' => $summary['headline'], 'body' => $summary['details'], 'symbol' => '﷽'])
        </section>

        <div class="customer-page__grid customer-page__grid--sidebar">
            <div class="customer-page__main">
                <section class="customer-section">
                    <header class="customer-section__header">
                        <div class="customer-section__heading">
                            @include('partials.ui.icon', ['icon' => 'fa-solid fa-id-card', 'tone' => 'amber', 'size' => 'lg'])
                            <div>
                                <p class="customer-section__eyebrow">Account overview</p>
                                <h2 class="customer-section__title">Workspace identity and access</h2>
                            </div>
                        </div>
                        <p class="customer-section__body">Review workspace identity, membership, and access in one clear account view.</p>
                    </header>

                    <div class="customer-page__detail-grid">
                        <div class="customer-card-group">
                            @include('partials.ui.info-card', ['title' => 'Membership Summary'])
                            @include('customer.partials.account-membership-summary')
                        </div>

                        <div class="customer-card-group">
                            @include('partials.ui.info-card', ['title' => 'Current Access Context'])
                            @include('partials.ui.stat-list', ['items' => $accountContext])
                        </div>
                    </div>
                </section>

                <section class="customer-section">
                    <header class="customer-section__header">
                        <div class="customer-section__heading">
                            @include('partials.ui.icon', ['icon' => 'fa-solid fa-users', 'tone' => 'sky', 'size' => 'lg'])
                            <div>
                                <p class="customer-section__eyebrow">Workspace structure</p>
                                <h2 class="customer-section__title">Team and product sections</h2>
                            </div>
                        </div>
                    </header>

                    <div class="customer-page__detail-grid">
                        <div class="customer-card-group">
                            @include('partials.ui.info-card', ['title' => 'Workspace Sections'])
                            @include('partials.ui.stat-list', ['items' => $page['sections'], 'labelKey' => 'heading', 'valueKey' => 'description'])
                        </div>

                        <div class="customer-card-group">
                            @include('partials.ui.info-card', ['title' => 'Team Members'])
                            @include('customer.partials.team-member-list')
                        </div>
                    </div>
                </section>

                <section class="customer-section customer-section--reference">
                    <header class="customer-section__header">
                        <div class="customer-section__heading">
                            @include('partials.ui.icon', ['icon' => 'fa-solid fa-book-open', 'tone' => 'violet', 'size' => 'lg'])
                            <div>
                                <p class="customer-section__eyebrow">Reference</p>
                                <h2 class="customer-section__title">Account status labels</h2>
                            </div>
                        </div>
                    </header>
                    @include('partials.ui.stat-list', ['items' => collect($statusLabels)->map(fn ($label, $value) => ['label' => $label, 'value' => $value])->values()->all()])
                    <p><small aria-hidden="true">ﷻ</small></p>
                </section>
            </div>

            <aside class="customer-page__side">
                @unless ($hasAccountData ?? false)
                    @include('partials.ui.empty-state', [
                        'title' => 'Account details are not ready yet',
                        'message' => 'Finish workspace setup first, then return here to review account details and membership.',
                    ])
                @endunless

                <div class="customer-card-group">
                    @include('partials.ui.info-card', ['title' => 'Getting Started'])
                    @include('partials.ui.link-list', ['items' => [
                        ['route' => 'customer.onboarding.index', 'label' => 'Onboarding', 'description' => 'Review setup steps, broker trust, and readiness for this workspace.'],
                        ['route' => 'customer.invoices.index', 'label' => 'Invoices', 'description' => 'Review current billing and invoice visibility.'],
                        ['route' => 'customer.settings.index', 'label' => 'Settings', 'description' => 'Review current profile and workspace context.'],
                    ]])
                </div>
            </aside>
        </div>
    </div>
@endsection
