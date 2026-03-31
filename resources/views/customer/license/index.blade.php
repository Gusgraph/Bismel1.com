<?php
// اعوز بالله من الشياطين و ان يحضرون بسم الله الرحمن الرحيم الله لا إله إلا هو الحي القيوم
// Bismillahi ar-Rahmani ar-Rahim Audhu billahi min ash-shayatin wa an yahdurun Bismillah ar-Rahman ar-Rahim Allah la ilaha illa huwa al-hayy al-qayyum. Tamsa Allahu ala ayunihim
// version: x
// ======================================================
// - App Name: bismel1.com
// - Gusgraph LLC -
// - Author: Gus Kazem
// - https://Gusgraph.com
// - File Path: resources/views/customer/license/index.blade.php
// ======================================================
?>
@extends('layouts.app')

@section('title', 'Customer License')

@section('content')
    @php
        $breadcrumbs = \App\Support\View\Breadcrumbs::make([
            ['label' => 'Home', 'route' => 'home'],
            ['label' => 'Customer', 'route' => 'customer.dashboard'],
            ['label' => 'License'],
        ]);
        $sectionNavItems = \App\Support\ViewData\CustomerSectionNavData::make();
    @endphp

    @include('partials.ui.breadcrumbs', ['items' => $breadcrumbs])
    @include('partials.ui.page-shell', ['headerPartial' => 'customer.partials.page-header', 'page' => $page])
    @include('partials.ui.section-nav', ['title' => 'Customer Section Navigation', 'items' => $sectionNavItems])

    <div class="customer-page customer-page--license">
        <section class="customer-page__hero">
            @include('partials.ui.info-card', ['title' => $sectionLabel, 'body' => $summary['details'], 'symbol' => 'ﷺ'])
        </section>

        <div class="customer-page__grid customer-page__grid--sidebar">
            <div class="customer-page__main">
                <section class="customer-section">
                    <header class="customer-section__header">
                        <div class="customer-section__heading">
                            @include('partials.ui.icon', ['icon' => 'fa-solid fa-key', 'tone' => 'violet', 'size' => 'lg'])
                            <div>
                                <p class="customer-section__eyebrow">License overview</p>
                                <h2 class="customer-section__title">License access, key status, and inventory</h2>
                            </div>
                        </div>
                    </header>

                    <div class="customer-page__detail-grid">
                        <div class="customer-card-group">
                            @include('partials.ui.info-card', ['title' => 'License Areas'])
                            @include('partials.ui.stat-list', ['items' => $page['sections'], 'labelKey' => 'heading', 'valueKey' => 'description'])
                        </div>
                        <div class="customer-card-group">
                            @include('partials.ui.info-card', ['title' => 'API Key Status'])
                            @include('customer.partials.license-key-status')
                        </div>
                        <div class="customer-card-group">
                            @include('partials.ui.info-card', ['title' => 'License Details'])
                            @include('customer.partials.license-details')
                        </div>
                        <div class="customer-card-group">
                            @include('partials.ui.info-card', ['title' => 'License Inventory'])
                            @include('partials.ui.stat-list', ['items' => $licenseInventory])
                        </div>
                        <div class="customer-card-group customer-card-group--full">
                            @include('partials.ui.info-card', ['title' => 'API Key Detail'])
                            @include('partials.ui.stat-list', ['items' => $keyInventory])
                        </div>
                    </div>
                </section>

                <section class="customer-section">
                    <header class="customer-section__header">
                        <div class="customer-section__heading">
                            @include('partials.ui.icon', ['icon' => 'fa-solid fa-user-shield', 'tone' => 'amber', 'size' => 'lg'])
                            <div>
                                <p class="customer-section__eyebrow">Safe display</p>
                                <h2 class="customer-section__title">License and key handling</h2>
                            </div>
                        </div>
                        <p class="customer-section__body">License details stay readable while API keys remain masked and secure.</p>
                    </header>
                    <p><small aria-hidden="true">﷽</small></p>
                </section>
            </div>

            <aside class="customer-page__side">
                @unless ($hasLicenseData ?? false)
                    @include('partials.ui.empty-state', [
                        'title' => 'License access has not been added yet',
                        'message' => 'Add a license and API key first, then return here to review status and renewal timing.',
                    ])
                @endunless

                <div class="customer-card-group">
                    @include('partials.ui.info-card', ['title' => 'Related Flow Areas'])
                    @include('partials.ui.link-list', ['items' => [
                        ['route' => 'customer.license.create', 'label' => 'Add License Key', 'description' => 'Save a license and API key while keeping the key masked after save.'],
                        ['route' => 'customer.onboarding.index', 'label' => 'Onboarding', 'description' => 'Review setup steps before activating license access.'],
                        ['route' => 'customer.invoices.index', 'label' => 'Invoices', 'description' => 'Review current billing status and invoice history.'],
                    ]])
                </div>
            </aside>
        </div>
    </div>
@endsection
