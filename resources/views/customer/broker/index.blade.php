<?php
// اعوز بالله من الشياطين و ان يحضرون بسم الله الرحمن الرحيم الله لا إله إلا هو الحي القيوم
// Bismillahi ar-Rahmani ar-Rahim Audhu billahi min ash-shayatin wa an yahdurun Bismillah ar-Rahman ar-Rahim Allah la ilaha illa huwa al-hayy al-qayyum. Tamsa Allahu ala ayunihim
// version: x
// ======================================================
// - App Name: bismel1.com
// - Gusgraph LLC -
// - Author: Gus Kazem
// - https://Gusgraph.com
// - File Path: resources/views/customer/broker/index.blade.php
// ======================================================
?>
@extends('layouts.app')

@section('title', 'Customer Broker')

@section('content')
    @php
        $breadcrumbs = \App\Support\View\Breadcrumbs::make([
            ['label' => 'Home', 'route' => 'home'],
            ['label' => 'Customer', 'route' => 'customer.dashboard'],
            ['label' => 'Broker'],
        ]);
        $sectionNavItems = \App\Support\ViewData\CustomerSectionNavData::make();
    @endphp

    @include('partials.ui.breadcrumbs', ['items' => $breadcrumbs])
    @include('partials.ui.page-shell', ['headerPartial' => 'customer.partials.page-header', 'page' => $page])
    @include('partials.ui.section-nav', ['title' => 'Customer Section Navigation', 'items' => $sectionNavItems])

    <div class="customer-page customer-page--broker">
        <section class="customer-page__hero">
            @include('partials.ui.info-card', ['title' => $sectionLabel, 'body' => $summary['details'], 'symbol' => '﷽'])
        </section>

        <div class="customer-page__grid customer-page__grid--sidebar">
            <div class="customer-page__main">
                <section class="customer-section">
                    <header class="customer-section__header">
                        <div class="customer-section__heading">
                            @include('partials.ui.icon', ['icon' => 'fa-solid fa-plug-circle-bolt', 'tone' => 'blue', 'size' => 'lg'])
                            <div>
                                <p class="customer-section__eyebrow">Alpaca overview</p>
                                <h2 class="customer-section__title">Connected account, mode, readiness, and masked access</h2>
                            </div>
                        </div>
                    </header>

                    <div class="customer-page__detail-grid">
                        <div class="customer-card-group">
                            @include('partials.ui.info-card', ['title' => 'Connection Areas'])
                            @include('partials.ui.stat-list', ['items' => $page['sections'], 'labelKey' => 'heading', 'valueKey' => 'description'])
                        </div>
                        <div class="customer-card-group">
                            @include('partials.ui.info-card', ['title' => 'Broker Provider'])
                            @include('customer.partials.broker-provider-list')
                        </div>
                        <div class="customer-card-group">
                            @include('partials.ui.info-card', ['title' => 'Primary Alpaca Connection Details'])
                            @include('customer.partials.broker-connection-details')
                        </div>
                        <div class="customer-card-group">
                            @include('partials.ui.info-card', ['title' => 'Linked Account Inventory'])
                            @include('partials.ui.stat-list', ['items' => $connectionInventory])
                        </div>
                        <div class="customer-card-group customer-card-group--full">
                            @include('partials.ui.info-card', ['title' => 'Masked Credential Metadata'])
                            @include('partials.ui.stat-list', ['items' => $credentialInventory])
                        </div>
                    </div>
                </section>

                <section class="customer-section">
                    <header class="customer-section__header">
                        <div class="customer-section__heading">
                            @include('partials.ui.icon', ['icon' => 'fa-solid fa-shield-keyhole', 'tone' => 'violet', 'size' => 'lg'])
                            <div>
                                <p class="customer-section__eyebrow">Safe display</p>
                                <h2 class="customer-section__title">Saved access and readiness flow</h2>
                            </div>
                        </div>
                        <p class="customer-section__body">Saved access stays masked while still showing whether the linked Alpaca account is in paper or live mode and whether the connection is ready for automation.</p>
                    </header>
                    @include('customer.partials.broker-credential-checklist')
                    <p><small aria-hidden="true">ﷻ</small></p>
                </section>
            </div>

            <aside class="customer-page__side">
                @unless ($hasBrokerData ?? false)
                    @include('partials.ui.empty-state', [
                        'title' => 'Alpaca Not Connected Yet',
                        'message' => 'No Alpaca connection is saved for this workspace yet. Connect Alpaca first, then return here to confirm the account mode and readiness.',
                    ])
                @endunless

                <div class="customer-card-group">
                    @include('partials.ui.info-card', ['title' => 'Connection Actions'])
                    @include('partials.ui.link-list', ['items' => [
                        ['route' => 'customer.broker.create', 'label' => 'Add Linked Alpaca Account', 'description' => $connectionActionDescription ?? 'Add another linked Alpaca account for this workspace.'],
                    ]])
                </div>
            </aside>
        </div>
    </div>
@endsection
