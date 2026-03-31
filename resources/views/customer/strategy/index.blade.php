<?php
// اعوز بالله من الشياطين و ان يحضرون بسم الله الرحمن الرحيم الله لا إله إلا هو الحي القيوم
// Bismillahi ar-Rahmani ar-Rahim Audhu billahi min ash-shayatin wa an yahdurun Bismillah ar-Rahman ar-Rahim Allah la ilaha illa huwa al-hayy al-qayyum. Tamsa Allahu ala ayunihim
// version: x
// ======================================================
// - App Name: bismel1.com
// - Gusgraph LLC -
// - Author: Gus Kazem
// - https://Gusgraph.com
// - File Path: resources/views/customer/strategy/index.blade.php
// ======================================================
?>
@extends('layouts.app')

@section('title', 'Customer Strategy')

@section('content')
    @php
        $breadcrumbs = \App\Support\View\Breadcrumbs::make([
            ['label' => 'Home', 'route' => 'home'],
            ['label' => 'Customer', 'route' => 'customer.dashboard'],
            ['label' => 'Strategy'],
        ]);
        $sectionNavItems = \App\Support\ViewData\CustomerSectionNavData::make();
    @endphp

    @include('partials.ui.breadcrumbs', ['items' => $breadcrumbs])
    @include('partials.ui.page-shell', ['headerPartial' => 'customer.partials.page-header', 'page' => $page])
    @include('partials.ui.section-nav', ['title' => 'Customer Section Navigation', 'items' => $sectionNavItems])

    <div class="customer-page customer-page--strategy">
        <section class="customer-page__hero">
            @include('partials.ui.info-card', ['title' => $summary['headline'], 'body' => $summary['details'], 'symbol' => '﷽'])
        </section>

        <div class="customer-page__grid customer-page__grid--sidebar">
            <div class="customer-page__main">
                <section class="customer-section">
                    <header class="customer-section__header">
                        <div class="customer-section__heading">
                            @include('partials.ui.icon', ['icon' => 'fa-solid fa-compass-drafting', 'tone' => 'emerald', 'size' => 'lg'])
                            <div>
                                <p class="customer-section__eyebrow">Strategy design</p>
                                <h2 class="customer-section__title">Mode, timeframe, and symbol coverage</h2>
                            </div>
                        </div>
                        <p class="customer-section__body">Use this page to keep your strategy choices clear before turning on automation.</p>
                    </header>

                    <div class="customer-page__detail-grid">
                        <div class="customer-card-group">
                            @include('partials.ui.info-card', ['title' => 'Strategy Areas'])
                            @include('partials.ui.stat-list', ['items' => $page['sections'], 'labelKey' => 'heading', 'valueKey' => 'description'])
                        </div>

                        <div class="customer-card-group">
                            @include('partials.ui.info-card', ['title' => 'Current Strategy Frame'])
                            @include('partials.ui.stat-list', ['items' => $strategyFrame])
                        </div>
                    </div>
                </section>

                <section class="customer-section">
                    <header class="customer-section__header">
                        <div class="customer-section__heading">
                            @include('partials.ui.icon', ['icon' => 'fa-solid fa-gear', 'tone' => 'amber', 'size' => 'lg'])
                            <div>
                                <p class="customer-section__eyebrow">Current-account settings</p>
                                <h2 class="customer-section__title">Strategy configuration flow</h2>
                            </div>
                        </div>
                        <p class="customer-section__body">These settings shape how the workspace is configured. Saving here updates the strategy profile only and does not start trading.</p>
                    </header>

                    <form class="ui-card ui-form-stack customer-form-card" method="POST" action="{{ route('customer.strategy.update') }}">
                        @csrf
                        @method('PUT')

                        <div class="ui-form-section">
                            <div class="ui-form-section__header">
                                <div>
                                    <p class="ui-form-section__eyebrow">Profile setup</p>
                                    <h3 class="ui-form-section__title">Strategy profile settings</h3>
                                </div>
                                <p class="ui-form-section__body">Use this profile to describe how the workspace should trade and what it should pay attention to.</p>
                            </div>

                            <div class="ui-form-grid">
                                @include('partials.ui.form-field', [
                                    'name' => 'name',
                                    'label' => 'Profile Name',
                                    'value' => $form['name'] ?? '',
                                    'help' => 'Saved as the current workspace strategy profile name.',
                                    'autocomplete' => 'off',
                                ])
                                @include('partials.ui.form-field', [
                                    'name' => 'mode',
                                    'label' => 'Strategy Mode',
                                    'value' => $form['mode'] ?? 'review_first',
                                    'help' => 'Use review_first, assist_only, or scanner_ready.',
                                    'autocomplete' => 'off',
                                ])
                                @include('partials.ui.form-field', [
                                    'name' => 'timeframe',
                                    'label' => 'Timeframe',
                                    'value' => $form['timeframe'] ?? 'mixed',
                                    'help' => 'Use intraday, swing, or mixed.',
                                    'autocomplete' => 'off',
                                ])
                                @include('partials.ui.form-field', [
                                    'name' => 'symbol_scope',
                                    'label' => 'Symbol Scope',
                                    'value' => $form['symbol_scope'] ?? 'focused',
                                    'help' => 'Use focused, watchlist, or account.',
                                    'autocomplete' => 'off',
                                ])
                                @include('partials.ui.form-field', [
                                    'name' => 'style',
                                    'label' => 'Style',
                                    'value' => $form['style'] ?? 'balanced',
                                    'help' => 'Use conservative, balanced, or aggressive.',
                                    'autocomplete' => 'off',
                                ])
                            </div>

                            <div class="ui-form-field @error('is_active') ui-form-field--invalid @enderror">
                                <div class="ui-form-field__header">
                                    <label class="ui-form-field__label" for="is_active">Enabled State</label>
                                    <p class="ui-form-field__meta">Saved profile state</p>
                                </div>
                                <label class="ui-inline-copy" for="is_active">
                                    <input id="is_active" name="is_active" type="checkbox" value="1" @checked($form['is_active'] ?? false)>
                                    <span>Mark this strategy profile as the active current-account configuration.</span>
                                </label>
                                @error('is_active')
                                    <small class="ui-field-error">{{ $message }}</small>
                                @enderror
                            </div>
                        </div>

                        @include('partials.ui.form-actions', [
                            'submitLabel' => 'Save strategy settings',
                            'cancelRoute' => 'customer.strategy.index',
                            'cancelLabel' => 'Stay on strategy',
                            'note' => 'Saving here updates the current strategy profile for this workspace. It does not start automation or trading.',
                        ])
                    </form>
                </section>

                <section class="customer-section">
                    <header class="customer-section__header">
                        <div class="customer-section__heading">
                            @include('partials.ui.icon', ['icon' => 'fa-solid fa-sliders', 'tone' => 'sky', 'size' => 'lg'])
                            <div>
                                <p class="customer-section__eyebrow">Style posture</p>
                                <h2 class="customer-section__title">Conservative, balanced, and aggressive concepts</h2>
                            </div>
                        </div>
                        <p class="customer-section__body">These profiles help you describe the trading style you want in clear, high-level language.</p>
                    </header>

                    <div class="customer-page__detail-grid">
                        <div class="customer-card-group">
                            @include('partials.ui.info-card', ['title' => 'Style Profiles'])
                            @include('partials.ui.stat-list', ['items' => $styleProfiles])
                        </div>

                        <div class="customer-card-group">
                            @include('partials.ui.info-card', ['title' => 'Watchlist Relationship'])
                            @include('partials.ui.stat-list', ['items' => $watchlistSummary ?? []])
                        </div>

                        <div class="customer-card-group">
                            @include('partials.ui.info-card', ['title' => 'Later Engine Linkage'])
                            @include('partials.ui.stat-list', ['items' => $linkageItems])
                        </div>
                    </div>
                    <p><small aria-hidden="true">ﷻ</small></p>
                </section>
            </div>

            <aside class="customer-page__side">
                @unless ($hasStrategyData ?? false)
                    @include('partials.ui.empty-state', [
                        'title' => 'Strategy setup has not started yet',
                        'message' => 'Complete workspace setup first, then return here to configure the strategy profile.',
                    ])
                @endunless

                <div class="customer-card-group">
                    @include('partials.ui.info-card', ['title' => 'Strategy Notes'])
                    @include('customer.partials.customer-alerts')
                </div>

                <div class="customer-card-group">
                    @include('partials.ui.info-card', ['title' => 'Related Pages', 'symbol' => 'ﷻ'])
                    @include('partials.ui.link-list', ['items' => $relatedLinks ?? []])
                </div>
            </aside>
        </div>
    </div>
@endsection
