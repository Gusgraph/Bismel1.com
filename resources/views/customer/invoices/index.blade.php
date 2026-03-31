<?php
// اعوز بالله من الشياطين و ان يحضرون بسم الله الرحمن الرحيم الله لا إله إلا هو الحي القيوم
// Bismillahi ar-Rahmani ar-Rahim Audhu billahi min ash-shayatin wa an yahdurun Bismillah ar-Rahman ar-Rahim Allah la ilaha illa huwa al-hayy al-qayyum. Tamsa Allahu ala ayunihim
// version: x
// ======================================================
// - App Name: bismel1.com
// - Gusgraph LLC -
// - Author: Gus Kazem
// - https://Gusgraph.com
// - File Path: resources/views/customer/invoices/index.blade.php
// ======================================================
?>
@extends('layouts.app')

@section('title', 'Customer Invoices')

@section('content')
    @php
        $breadcrumbs = \App\Support\View\Breadcrumbs::make([
            ['label' => 'Home', 'route' => 'home'],
            ['label' => 'Customer', 'route' => 'customer.dashboard'],
            ['label' => 'Invoices'],
        ]);
        $sectionNavItems = \App\Support\ViewData\CustomerSectionNavData::make();
    @endphp

    @include('partials.ui.breadcrumbs', ['items' => $breadcrumbs])
    @include('partials.ui.page-shell', ['headerPartial' => 'customer.partials.page-header', 'page' => $page])
    @include('partials.ui.section-nav', ['title' => 'Customer Section Navigation', 'items' => $sectionNavItems])

    <div class="customer-page customer-page--invoices">
        <section class="customer-page__hero">
            @include('partials.ui.info-card', ['title' => $summary['headline'], 'body' => $summary['details'], 'symbol' => 'ﷺ'])
        </section>

        <div class="customer-page__grid customer-page__grid--sidebar">
            <div class="customer-page__main">
                <section class="customer-section">
                    <header class="customer-section__header">
                        <div class="customer-section__heading">
                            @include('partials.ui.icon', ['icon' => 'fa-solid fa-file-invoice-dollar', 'tone' => 'emerald', 'size' => 'lg'])
                            <div>
                                <p class="customer-section__eyebrow">Invoice overview</p>
                                <h2 class="customer-section__title">Subscription and invoice overview</h2>
                            </div>
                        </div>
                    </header>

                    <div class="customer-page__detail-grid">
                        <div class="customer-card-group">
                            @include('partials.ui.info-card', ['title' => 'Invoice Detail Areas'])
                            @include('partials.ui.stat-list', ['items' => $page['sections'], 'labelKey' => 'heading', 'valueKey' => 'description'])
                        </div>

                        <div class="customer-card-group">
                            @include('partials.ui.info-card', ['title' => 'Subscription Summary'])
                            @include('customer.partials.subscription-summary')
                        </div>
                    </div>
                </section>

                <section class="customer-section">
                    <header class="customer-section__header">
                        <div class="customer-section__heading">
                            @include('partials.ui.icon', ['icon' => 'fa-solid fa-receipt', 'tone' => 'amber', 'size' => 'lg'])
                            <div>
                                <p class="customer-section__eyebrow">Billing records</p>
                                <h2 class="customer-section__title">Invoice summary and detail</h2>
                            </div>
                        </div>
                    </header>

                    <div class="customer-card-group">
                        @include('partials.ui.info-card', ['title' => 'Invoice Summary'])
                        @include('customer.partials.invoice-summary', ['invoiceSummaryMeta' => $invoiceSummaryMeta ?? null])
                    </div>

                    <div class="customer-card-group">
                        @include('partials.ui.info-card', ['title' => 'Invoice Detail', 'symbol' => 'ﷻ'])
                        @include('partials.ui.record-list', ['items' => $invoiceDetails, 'meta' => $invoiceDetailsMeta ?? null, 'emptyMessage' => 'Invoices will appear here after the first billing event is recorded.'])
                    </div>
                    <p><small aria-hidden="true">﷽</small></p>
                </section>
            </div>

            <aside class="customer-page__side">
                @unless ($hasSubscriptionData ?? false)
                    @include('partials.ui.empty-state', [
                        'title' => 'No active subscription yet',
                        'message' => 'Choose and activate a plan first, then return here to review billing and invoices.',
                    ])
                @endunless

                @unless ($hasInvoiceData ?? false)
                    @include('partials.ui.empty-state', [
                        'title' => 'No Invoices Yet',
                        'message' => 'Invoices will appear here after the first billing event is recorded for this workspace.',
                    ])
                @endunless
            </aside>
        </div>
    </div>
@endsection
