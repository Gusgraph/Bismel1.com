<?php
// اعوز بالله من الشياطين و ان يحضرون بسم الله الرحمن الرحيم الله لا إله إلا هو الحي القيوم
// Bismillahi ar-Rahmani ar-Rahim Audhu billahi min ash-shayatin wa an yahdurun Bismillah ar-Rahman ar-Rahim Allah la ilaha illa huwa al-hayy al-qayyum. Tamsa Allahu ala ayunihim
// version: x
// ======================================================
// - App Name: bismel1.com
// - Gusgraph LLC -
// - Author: Gus Kazem
// - https://Gusgraph.com
// - File Path: resources/views/customer/billing/index.blade.php
// ======================================================
?>
@extends('layouts.app')

@section('title', 'Customer Billing')

@section('content')
    @php
        $breadcrumbs = \App\Support\View\Breadcrumbs::make([
            ['label' => 'Home', 'route' => 'home'],
            ['label' => 'Customer', 'route' => 'customer.dashboard'],
            ['label' => 'Billing'],
        ]);
        $sectionNavItems = \App\Support\ViewData\CustomerSectionNavData::make();
    @endphp

    @include('partials.ui.breadcrumbs', ['items' => $breadcrumbs])
    @include('partials.ui.page-shell', ['headerPartial' => 'customer.partials.page-header', 'page' => $page])
    @include('partials.ui.section-nav', ['title' => 'Customer Section Navigation', 'items' => $sectionNavItems])

    <div class="customer-page customer-page--billing">
        <section class="customer-page__hero">
            @include('partials.ui.info-card', ['title' => $summary['headline'], 'body' => $summary['details'], 'symbol' => 'ﷺ'])
        </section>

        @if (!empty($checkoutBanner))
            <section class="customer-page__hero">
                @include('partials.ui.info-card', ['title' => $checkoutBanner['title'] ?? 'Billing update', 'body' => $checkoutBanner['body'] ?? null])
            </section>
        @endif

        <div class="customer-page__grid customer-page__grid--sidebar">
            <div class="customer-page__main">
                <section class="customer-section">
                    <header class="customer-section__header">
                        <div class="customer-section__heading">
                            @include('partials.ui.icon', ['icon' => 'fa-solid fa-wallet', 'tone' => 'emerald', 'size' => 'lg'])
                            <div>
                                <p class="customer-section__eyebrow">Billing overview</p>
                                <h2 class="customer-section__title">Subscription detail, base plans, and add-on structure</h2>
                            </div>
                        </div>
                    </header>

                    <div class="customer-page__detail-grid">
                        <div class="customer-card-group">
                            @include('partials.ui.info-card', ['title' => 'Billing Detail Areas'])
                            @include('partials.ui.stat-list', ['items' => $page['sections'], 'labelKey' => 'heading', 'valueKey' => 'description'])
                        </div>

                        <div class="customer-card-group">
                            @include('partials.ui.info-card', ['title' => 'Current Subscription Detail'])
                            @include('customer.partials.account-membership-summary')
                        </div>

                        <div class="customer-card-group">
                            @include('partials.ui.info-card', ['title' => 'Package Coverage'])
                            @include('partials.ui.stat-list', ['items' => [
                                ['label' => 'Base Plans', 'value' => (string) ($basePlanCount ?? 0)],
                                ['label' => 'Add-On Packages', 'value' => (string) ($addOnPlanCount ?? 0)],
                                ['label' => 'Referral Code', 'value' => $activeReferralCode ?? 'No referral tracked'],
                            ]])
                        </div>

                        <div class="customer-card-group customer-card-group--full">
                            @include('partials.ui.info-card', ['title' => 'Subscription Detail'])
                            @include('partials.ui.stat-list', ['items' => $subscriptionDetails])
                        </div>
                    </div>
                </section>

                <section class="customer-section customer-section--reference">
                    <header class="customer-section__header">
                        <div class="customer-section__heading">
                            @include('partials.ui.icon', ['icon' => 'fa-solid fa-tags', 'tone' => 'amber', 'size' => 'lg'])
                            <div>
                                <p class="customer-section__eyebrow">Reference</p>
                                <h2 class="customer-section__title">Billing status labels</h2>
                            </div>
                        </div>
                    </header>
                    @include('partials.ui.stat-list', ['items' => collect($statusLabels)->map(fn ($label, $value) => ['label' => $label, 'value' => $value])->values()->all()])
                    <p><small aria-hidden="true" style="color: rgba(148, 163, 184, 0.19);">﷽</small></p>
                </section>
            </div>

            <aside class="customer-page__side">
                @if ($errors->has('checkout') || $errors->has('addon_codes') || $errors->has('base_plan_code'))
                    @include('partials.ui.info-card', [
                        'title' => 'Checkout Error',
                        'body' => $errors->first('checkout') ?: $errors->first('addon_codes') ?: $errors->first('base_plan_code'),
                    ])
                @endif

                    @unless ($hasBillingData ?? false)
                        @include('partials.ui.empty-state', [
                            'title' => 'Current Subscription Missing',
                            'message' => 'No subscription is active for this workspace yet, so the billing page stays focused on package selection and setup.',
                        ])
                    @endunless

                <div class="customer-card-group">
                    @include('partials.ui.info-card', ['title' => 'Billing Notes'])
                    @include('customer.partials.customer-alerts')
                </div>

                <div class="customer-card-group">
                    @include('partials.ui.info-card', ['title' => 'Current Package Catalog'])
                    @include('customer.partials.plan-catalog')
                </div>

                <div class="customer-card-group">
                    @include('partials.ui.info-card', ['title' => 'Next Steps'])
                    @include('partials.ui.link-list', ['items' => [
                        ['route' => 'customer.onboarding.index', 'label' => 'Onboarding', 'description' => 'Review workspace setup before turning on a new subscription.'],
                        ['route' => 'customer.invoices.index', 'label' => 'Invoices', 'description' => 'Review invoice history and current billing status.'],
                    ]])
                </div>
            </aside>
        </div>
    </div>
@endsection
