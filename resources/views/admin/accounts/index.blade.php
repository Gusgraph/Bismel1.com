<?php
// اعوز بالله من الشياطين و ان يحضرون بسم الله الرحمن الرحيم الله لا إله إلا هو الحي القيوم
// Bismillahi ar-Rahmani ar-Rahim Audhu billahi min ash-shayatin wa an yahdurun Bismillah ar-Rahman ar-Rahim Allah la ilaha illa huwa al-hayy al-qayyum. Tamsa Allahu ala ayunihim
// version: x
// ======================================================
// - App Name: bismel1.com
// - Gusgraph LLC -
// - Author: Gus Kazem
// - https://Gusgraph.com
// - File Path: resources/views/admin/accounts/index.blade.php
// ======================================================
?>
@extends('layouts.app')

@section('title', 'Admin Accounts')

@section('content')
    @php
        $breadcrumbs = \App\Support\View\Breadcrumbs::make([
            ['label' => 'Home', 'route' => 'home'],
            ['label' => 'Admin', 'route' => 'admin.dashboard'],
            ['label' => 'Accounts'],
        ]);
        $sectionNavItems = \App\Support\ViewData\AdminSectionNavData::make();
    @endphp

    @include('partials.ui.breadcrumbs', ['items' => $breadcrumbs])
    @include('partials.ui.page-shell', ['headerPartial' => 'admin.partials.page-header', 'page' => $page])
    @include('partials.ui.section-nav', ['title' => 'Admin Section Navigation', 'items' => $sectionNavItems])

    <div class="admin-page admin-page--accounts">
        <section class="admin-page__hero">
            @include('partials.ui.info-card', ['title' => $summary['headline'], 'body' => $summary['details'], 'symbol' => 'ﷺ'])
        </section>

        <div class="admin-page__grid admin-page__grid--sidebar">
            <div class="admin-page__main">
                <section class="admin-section">
                    <header class="admin-section__header">
                        <div class="admin-section__heading">
                            @include('partials.ui.icon', ['icon' => 'fa-solid fa-users-gear', 'tone' => 'sky', 'size' => 'lg'])
                            <div>
                                <p class="admin-section__eyebrow">Account coverage</p>
                                <h2 class="admin-section__title">Visibility and management overview</h2>
                            </div>
                        </div>
                        <p class="admin-section__body">Keep account visibility, lifecycle summary, and operational labels grouped together so the admin read starts with platform coverage instead of isolated cards.</p>
                    </header>

                    <div class="admin-page__detail-grid">
                        <div class="admin-card-group">
                            @include('partials.ui.info-card', ['title' => 'Account Visibility Areas'])
                            @include('partials.ui.stat-list', ['items' => $page['sections'], 'labelKey' => 'heading', 'valueKey' => 'description'])
                        </div>

                        <div class="admin-card-group admin-card-group--full">
                            @include('partials.ui.info-card', ['title' => 'Account Management Summary'])
                            @include('admin.partials.account-management-summary')
                        </div>
                    </div>
                </section>

                <section class="admin-section admin-section--reference">
                    <header class="admin-section__header">
                        <div class="admin-section__heading">
                            @include('partials.ui.icon', ['icon' => 'fa-solid fa-tags', 'tone' => 'amber', 'size' => 'lg'])
                            <div>
                                <p class="admin-section__eyebrow">Reference</p>
                                <h2 class="admin-section__title">Account status labels</h2>
                            </div>
                        </div>
                    </header>

                    @include('partials.ui.stat-list', ['items' => collect($statusLabels)->map(fn ($label, $value) => ['label' => $label, 'value' => $value])->values()->all()])
                    @unless ($hasAccountData)
                        @include('partials.ui.empty-state', [
                            'title' => 'No Account Records Yet',
                            'message' => 'Workspace records will appear here once accounts are available for admin oversight.',
                        ])
                    @endunless
                    <p><small aria-hidden="true">﷽</small></p>
                </section>
            </div>

            <aside class="admin-page__side">
                <div class="admin-card-group">
                    @include('partials.ui.info-card', ['title' => 'Related Oversight Areas'])
                    @include('partials.ui.link-list', ['items' => [
                        ['route' => 'admin.licenses.index', 'label' => 'License Inventory', 'description' => 'Cross-check API access coverage across workspaces.'],
                        ['route' => 'admin.audit.index', 'label' => 'Audit Visibility', 'description' => 'Review recent operational activity and audit history.'],
                        ['route' => 'admin.accounts.index', 'label' => 'Account Detail', 'description' => 'Select a specific account from the list above to review its detail page.'],
                    ]])
                </div>
            </aside>
        </div>
    </div>
@endsection
