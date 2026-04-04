<?php
// اعوز بالله من الشياطين و ان يحضرون بسم الله الرحمن الرحيم الله لا إله إلا هو الحي القيوم
// Bismillahi ar-Rahmani ar-Rahim Audhu billahi min ash-shayatin wa an yahdurun Bismillah ar-Rahman ar-Rahim Allah la ilaha illa huwa al-hayy al-qayyum. Tamsa Allahu ala ayunihim
// version: x
// ======================================================
// - App Name: bismel1.com
// - Gusgraph LLC -
// - Author: Gus Kazem
// - https://Gusgraph.com
// - File Path: resources/views/customer/automation/index.blade.php
// ======================================================
?>
@extends('layouts.app')

@section('title', 'Customer Automation')

@section('content')
    @php
        $breadcrumbs = \App\Support\View\Breadcrumbs::make([
            ['label' => 'Home', 'route' => 'home'],
            ['label' => 'Customer', 'route' => 'customer.dashboard'],
            ['label' => 'Automation'],
        ]);
        $sectionNavItems = \App\Support\ViewData\CustomerSectionNavData::make();
    @endphp

    @include('partials.ui.page-shell', [
        'headerPartial' => 'customer.partials.page-header',
        'page' => $page,
        'summary' => [
            'eyebrow' => 'Automation status',
            'title' => $summary['headline'],
            'body' => $summary['details'],
            'icon' => 'fa-solid fa-robot',
            'tone' => 'amber',
        ],
    ])
    @include('partials.ui.section-nav', ['title' => 'Customer Section Navigation', 'items' => $sectionNavItems])

    <div class="customer-page customer-page--automation">
        <div class="customer-page__grid customer-page__grid--sidebar">
            <div class="customer-page__main">
                <section class="customer-section">
                    <header class="customer-section__header">
                        <div class="customer-section__heading">
                            @include('partials.ui.icon', ['icon' => 'fa-solid fa-robot', 'tone' => 'amber', 'size' => 'lg'])
                            <div>
                                <p class="customer-section__eyebrow">Automation controls</p>
                                <h2 class="customer-section__title">Automation state, readiness, and controls</h2>
                            </div>
                        </div>
                        <p class="customer-section__body">This page shows whether automation is on, paused, blocked, or waiting, along with recent timing and activity.</p>
                    </header>

                    <div class="customer-page__detail-grid">
                        <div class="customer-card-group">
                            @include('partials.ui.info-card', ['title' => 'Automation overview'])
                            @include('partials.ui.stat-list', ['items' => $page['sections'], 'labelKey' => 'heading', 'valueKey' => 'description'])
                        </div>

                        <div class="customer-card-group">
                            @include('partials.ui.info-card', ['title' => 'Current Automation State'])
                            @include('partials.ui.stat-list', ['items' => $automationState])
                        </div>

                        <div class="customer-card-group">
                            @include('partials.ui.stat-list', ['items' => $runtimeItems ?? []])
                        </div>
                    </div>
                </section>

                <section class="customer-section">
                    <header class="customer-section__header">
                        <div class="customer-section__heading">
                            @include('partials.ui.icon', ['icon' => 'fa-solid fa-sliders', 'tone' => 'sky', 'size' => 'lg'])
                            <div>
                                <p class="customer-section__eyebrow">Workspace settings</p>
                                <h2 class="customer-section__title">Automation configuration and start/stop controls</h2>
                            </div>
                        </div>
                        <p class="customer-section__body">Set the automation posture for this workspace, then start or stop AI with clear status and readiness context.</p>
                    </header>

                    <form class="ui-card ui-form-stack customer-form-card" method="POST" action="{{ route('customer.automation.update') }}">
                        @csrf
                        @method('PUT')

                        <div class="ui-form-section">
                            <div class="ui-form-section__header">
                                <div>
                                    <p class="ui-form-section__eyebrow">Control setup</p>
                                    <h3 class="ui-form-section__title">Automation settings and controls</h3>
                                </div>
                                <p class="ui-form-section__body">Use these controls to save your preferred posture, then start or stop automation with a clear operating view.</p>
                            </div>

                            <div class="ui-form-grid">
                                @include('partials.ui.form-field', [
                                    'name' => 'name',
                                    'label' => 'Configuration Name',
                                    'value' => $form['name'] ?? '',
                                    'help' => 'Saved as the current workspace automation configuration name.',
                                    'autocomplete' => 'off',
                                ])
                                @include('partials.ui.form-field', [
                                    'name' => 'status',
                                    'label' => 'Automation Status',
                                    'value' => $form['status'] ?? 'draft',
                                    'help' => 'Use draft, review, or armed to show how close this workspace is to running.',
                                    'autocomplete' => 'off',
                                ])
                                @include('partials.ui.form-field', [
                                    'name' => 'risk_level',
                                    'label' => 'Risk Level',
                                    'value' => $form['risk_level'] ?? 'conservative',
                                    'help' => 'Use conservative, balanced, or aggressive.',
                                    'autocomplete' => 'off',
                                ])
                            </div>

                            <div class="ui-form-field @error('ai_enabled') ui-form-field--invalid @enderror">
                                <div class="ui-form-field__header">
                                    <label class="ui-form-field__label" for="ai_enabled">AI Enabled</label>
                                    <p class="ui-form-field__meta">Current workspace state</p>
                                </div>
                                <label class="ui-inline-copy" for="ai_enabled">
                                    <input id="ai_enabled" name="ai_enabled" type="checkbox" value="1" @checked($form['ai_enabled'] ?? false)>
                                    <span>Allow AI assistance in this workspace. The start and stop actions below still control when automation actually runs.</span>
                                </label>
                                @error('ai_enabled')
                                    <small class="ui-field-error">{{ $message }}</small>
                                @enderror
                            </div>
                        </div>

                        <div class="ui-form-actions">
                            <p class="ui-form-actions__note">Start AI turns automation on when readiness is in place. Stop AI pauses automation. Save settings keeps your configuration without changing the current run state.</p>
                            <div class="ui-form-actions__buttons">
                                <button class="ui-button ui-button--primary" type="submit" name="action_mode" value="start">Start AI</button>
                                <button class="ui-button ui-button--ghost" type="submit" name="action_mode" value="stop">Stop AI</button>
                                <button class="ui-button ui-button--secondary" type="submit" name="action_mode" value="save">Save settings</button>
                                <a class="ui-button ui-button--ghost" href="{{ route('customer.automation.index') }}">Refresh</a>
                            </div>
                        </div>
                    </form>
                </section>

                <section class="customer-section">
                    <header class="customer-section__header">
                        <div class="customer-section__heading">
                            @include('partials.ui.icon', ['icon' => 'fa-solid fa-chart-line', 'tone' => 'emerald', 'size' => 'lg'])
                            <div>
                                <p class="customer-section__eyebrow">Integrated product module</p>
                                <h2 class="customer-section__title">{{ $primeStocksProduct['title'] ?? 'Prime Stocks' }}</h2>
                            </div>
                        </div>
                        <p class="customer-section__body">Prime Stocks now lives inside the existing Automation area as an integrated module, using demo/static data only during this phase.</p>
                    </header>

                    <div class="customer-page__detail-grid">
                        <div class="customer-card-group">
                            @include('partials.ui.info-card', [
                                'title' => $primeStocksProduct['label'] ?? 'Demo Access product',
                                'body' => $primeStocksProduct['body'] ?? null,
                                'icon' => 'fa-solid fa-tag',
                                'tone' => 'amber',
                            ])
                            @include('partials.ui.stat-list', ['items' => $primeStocksStatusItems ?? []])
                        </div>

                        <div class="customer-card-group">
                            @include('partials.ui.info-card', [
                                'title' => 'Prime Stocks operating concepts',
                                'body' => 'This section keeps the current product language visible without creating a standalone page or live backend dependency.',
                            ])
                            @include('partials.ui.stat-list', ['items' => $primeStocksConceptItems ?? []])
                        </div>
                    </div>
                </section>

                <section class="customer-section">
                    <header class="customer-section__header">
                        <div class="customer-section__heading">
                            @include('partials.ui.icon', ['icon' => 'fa-solid fa-wave-square', 'tone' => 'violet', 'size' => 'lg'])
                            <div>
                                <p class="customer-section__eyebrow">Runtime readiness</p>
                                <h2 class="customer-section__title">Health, timing, and recent activity</h2>
                            </div>
                        </div>
                        <p class="customer-section__body">These blocks explain whether the workspace is ready, when automation last ran, and what happened most recently.</p>
                    </header>

                    <div class="customer-page__detail-grid">
                        <div class="customer-card-group">
                            @include('partials.ui.info-card', ['title' => 'Run Window'])
                            @include('partials.ui.stat-list', ['items' => $runWindow ?? []])
                        </div>

                        <div class="customer-card-group">
                            @include('partials.ui.stat-list', ['items' => $healthItems])
                        </div>

                        <div class="customer-card-group">
                            @include('partials.ui.info-card', ['title' => 'Recent Activity'])
                            @include('partials.ui.stat-list', ['items' => $recentActivityItems ?? []])
                        </div>

                        <div class="customer-card-group">
                            @include('partials.ui.info-card', ['title' => 'System linkage'])
                            @include('partials.ui.stat-list', ['items' => $linkageItems])
                        </div>
                    </div>
                    <p><small aria-hidden="true">﷽</small></p>
                </section>
            </div>

            <aside class="customer-page__side">
                @unless ($hasAutomationData ?? false)
                    @include('partials.ui.empty-state', [
                        'title' => 'Automation is not ready yet',
                        'message' => 'Complete workspace setup first, then return here to review readiness and turn automation on.',
                    ])
                @endunless

                <div class="customer-card-group">
                    @include('partials.ui.info-card', ['title' => 'Automation Notes'])
                    @include('customer.partials.customer-alerts')
                </div>

                <div class="customer-card-group">
                    @include('partials.ui.link-list', ['items' => $relatedLinks ?? []])
                </div>
            </aside>
        </div>
    </div>
@endsection
