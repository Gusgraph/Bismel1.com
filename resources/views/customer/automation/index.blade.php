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
                            @include('partials.ui.icon', ['icon' => 'fa-solid fa-wallet', 'tone' => 'amber', 'size' => 'lg'])
                            <div>
                                <p class="customer-section__eyebrow">Product access</p>
                                <h2 class="customer-section__title">Automation products available to this workspace</h2>
                            </div>
                        </div>
                        <p class="customer-section__body">This page now reads as one honest active-plan local test surface for Prime Stocks Bot Trader, with a clean no-active-product fallback when access is unavailable.</p>
                    </header>

                    <div class="customer-page__detail-grid">
                        <div class="customer-card-group">
                            @include('partials.ui.info-card', ['title' => 'Current automation access'])
                            @include('partials.ui.stat-list', ['items' => $accessItems ?? []])
                        </div>

                        <div class="customer-card-group">
                            @include('partials.ui.info-card', ['title' => 'Current product details'])
                            @include('partials.ui.stat-list', ['items' => $productItems ?? []])
                        </div>

                        <div class="customer-card-group">
                            @include('partials.ui.info-card', ['title' => 'Last signal state'])
                            @include('partials.ui.stat-list', ['items' => $signalItems ?? []])
                        </div>
                    </div>
                </section>

                <section class="customer-section">
                    <header class="customer-section__header">
                        <div class="customer-section__heading">
                            @include('partials.ui.icon', ['icon' => 'fa-solid fa-sliders', 'tone' => 'sky', 'size' => 'lg'])
                            <div>
                                <p class="customer-section__eyebrow">Control zone</p>
                                <h2 class="customer-section__title">Control / monitoring zone for the current automation product</h2>
                            </div>
                        </div>
                        <p class="customer-section__body">This Laravel page stays the control / monitoring zone while Cloud Run remains the Serverless Bot runtime target. Trading does not require the page to stay open.</p>
                    </header>

                    <div class="customer-page__detail-grid">
                        <form class="ui-card ui-form-stack customer-form-card" method="POST" action="{{ route('customer.automation.update') }}">
                            @csrf
                            @method('PUT')

                            <div class="ui-form-section">
                                <div class="ui-form-section__header">
                                    <div>
                                        <p class="ui-form-section__eyebrow">Control setup</p>
                                        <h3 class="ui-form-section__title">Automation settings and controls</h3>
                                    </div>
                                    <p class="ui-form-section__body">Use these controls to save the current product posture, then start or stop automation with clear access context.</p>
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
                                <p class="ui-form-actions__note">Start AI turns automation on when access and readiness are in place. Stop AI pauses automation. Save settings keeps the current configuration without changing runtime state.</p>
                                <div class="ui-form-actions__buttons">
                                    <button class="ui-button ui-button--primary" type="submit" name="action_mode" value="start">Start AI</button>
                                    <button class="ui-button ui-button--ghost" type="submit" name="action_mode" value="stop">Stop AI</button>
                                    <button class="ui-button ui-button--secondary" type="submit" name="action_mode" value="save">Save settings</button>
                                    <a class="ui-button ui-button--ghost" href="{{ route('customer.automation.index') }}">Refresh</a>
                                </div>
                            </div>
                        </form>

                        <div class="customer-card-group">
                            @include('partials.ui.info-card', [
                                'title' => 'Current control / monitoring zone',
                                'body' => 'Cloud Run runs the Serverless Bot server-side. This page remains the customer control / monitoring zone only.',
                            ])
                            @include('partials.ui.stat-list', ['items' => $controlZoneItems ?? []])
                        </div>
                    </div>
                </section>

                <section class="customer-section">
                    <header class="customer-section__header">
                        <div class="customer-section__heading">
                            @include('partials.ui.icon', ['icon' => 'fa-solid fa-wave-square', 'tone' => 'violet', 'size' => 'lg'])
                            <div>
                                <p class="customer-section__eyebrow">Access support</p>
                                <h2 class="customer-section__title">Subscription, readiness, and rollout posture</h2>
                            </div>
                        </div>
                        <p class="customer-section__body">The supporting details below stay aligned with active plan testing, local full-access posture, and the later Stripe wiring stage.</p>
                    </header>

                    <div class="customer-page__detail-grid">
                        <div class="customer-card-group">
                            @include('partials.ui.info-card', [
                                'title' => 'Access support',
                                'body' => 'These items explain whether the workspace is ready for the current product state without falling back to generic placeholder bands.',
                            ])
                            @include('partials.ui.stat-list', ['items' => $supportItems ?? []])
                        </div>
                    </div>
                    <p><small aria-hidden="true">﷽</small></p>
                </section>
            </div>

            <aside class="customer-page__side">
                @unless ($hasAutomationData ?? false)
                    @include('partials.ui.empty-state', [
                        'title' => 'No active automation product yet',
                        'message' => 'This page will still show product access posture, but the workspace does not have a live automation product attached yet.',
                    ])
                @endunless

                <div class="customer-card-group">
                    @include('partials.ui.info-card', ['title' => 'Product access notes'])
                    @include('partials.ui.stat-list', ['items' => $productNotes ?? []])
                </div>

                <div class="customer-card-group">
                    @include('partials.ui.info-card', ['title' => 'Related Pages'])
                    @include('partials.ui.link-list', ['items' => $relatedLinks ?? []])
                </div>
            </aside>
        </div>
    </div>
@endsection
