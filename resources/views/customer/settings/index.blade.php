<?php
// اعوز بالله من الشياطين و ان يحضرون بسم الله الرحمن الرحيم الله لا إله إلا هو الحي القيوم
// Bismillahi ar-Rahmani ar-Rahim Audhu billahi min ash-shayatin wa an yahdurun Bismillah ar-Rahman ar-Rahim Allah la ilaha illa huwa al-hayy al-qayyum. Tamsa Allahu ala ayunihim
// version: x
// ======================================================
// - App Name: bismel1.com
// - Gusgraph LLC -
// - Author: Gus Kazem
// - https://Gusgraph.com
// - File Path: resources/views/customer/settings/index.blade.php
// ======================================================
?>
@extends('layouts.app')

@section('title', 'Customer Settings')

@section('content')
    @php
        $breadcrumbs = \App\Support\View\Breadcrumbs::make([
            ['label' => 'Home', 'route' => 'home'],
            ['label' => 'Customer', 'route' => 'customer.dashboard'],
            ['label' => 'Settings'],
        ]);
        $sectionNavItems = \App\Support\ViewData\CustomerSectionNavData::make();
    @endphp

    @include('partials.ui.breadcrumbs', ['items' => $breadcrumbs])
    @include('partials.ui.page-shell', [
        'headerPartial' => 'customer.partials.page-header',
        'page' => $page,
        'summary' => [
            'eyebrow' => 'Workspace settings',
            'title' => $summary['headline'],
            'body' => $summary['details'],
            'icon' => 'fa-solid fa-sliders',
            'tone' => 'violet',
        ],
    ])
    @include('partials.ui.section-nav', ['title' => 'Customer Section Navigation', 'items' => $sectionNavItems])

    <div class="customer-page customer-page--settings">
        <div class="customer-page__grid customer-page__grid--sidebar">
            <div class="customer-page__main">
                <section class="customer-section">
                    <header class="customer-section__header">
                        <div class="customer-section__heading">
                            @include('partials.ui.icon', ['icon' => 'fa-solid fa-sliders', 'tone' => 'violet', 'size' => 'lg'])
                            <div>
                                <p class="customer-section__eyebrow">Profile and workspace</p>
                                <h2 class="customer-section__title">Account details and current workspace context</h2>
                            </div>
                        </div>
                    </header>

                    <div class="customer-page__detail-grid">
                        <div class="customer-card-group">
                            @include('partials.ui.info-card', ['title' => 'Settings Areas'])
                            @include('partials.ui.stat-list', ['items' => $page['sections'], 'labelKey' => 'heading', 'valueKey' => 'description'])
                        </div>

                        <div class="customer-card-group">
                            @include('partials.ui.info-card', ['title' => 'Customer Profile Preferences'])
                            @include('customer.partials.customer-preferences')
                        </div>
                    </div>
                </section>

                <section class="customer-section">
                    <header class="customer-section__header">
                        <div class="customer-section__heading">
                            @include('partials.ui.icon', ['icon' => 'fa-solid fa-users-gear', 'tone' => 'sky', 'size' => 'lg'])
                            <div>
                                <p class="customer-section__eyebrow">People and membership</p>
                                <h2 class="customer-section__title">Workspace access and team visibility</h2>
                            </div>
                        </div>
                    </header>

                    <div class="customer-page__detail-grid">
                        <div class="customer-card-group">
                            @include('partials.ui.info-card', ['title' => 'Membership Summary'])
                            @include('customer.partials.account-membership-summary')
                        </div>

                        <div class="customer-card-group">
                            @include('partials.ui.info-card', ['title' => 'Team Members'])
                            @include('customer.partials.team-member-list')
                        </div>
                    </div>
                </section>
            </div>

            <aside class="customer-page__side">
                @unless ($hasSettingsData ?? false)
                    @include('partials.ui.empty-state', [
                        'title' => 'Settings will appear once your account is available',
                        'message' => 'Sign in with an active customer account to review profile details, workspace context, and team access.',
                    ])
                @endunless

                <div class="customer-card-group">
                    @include('partials.ui.info-card', ['title' => 'Settings Actions'])
                    @include('partials.ui.link-list', ['items' => [
                        ['route' => 'customer.settings.edit', 'label' => 'Edit Settings', 'description' => 'Update the profile details used across your customer workspace.'],
                    ]])
                </div>
            </aside>
        </div>
    </div>
@endsection
