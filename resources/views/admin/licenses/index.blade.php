<?php
// اعوز بالله من الشياطين و ان يحضرون بسم الله الرحمن الرحيم الله لا إله إلا هو الحي القيوم
// Bismillahi ar-Rahmani ar-Rahim Audhu billahi min ash-shayatin wa an yahdurun Bismillah ar-Rahman ar-Rahim Allah la ilaha illa huwa al-hayy al-qayyum. Tamsa Allahu ala ayunihim
// version: x
// ======================================================
// - App Name: bismel1.com
// - Gusgraph LLC -
// - Author: Gus Kazem
// - https://Gusgraph.com
// - File Path: resources/views/admin/licenses/index.blade.php
// ======================================================
?>
@extends('layouts.app')

@section('title', 'Admin Licenses')

@section('content')
    @php
        $breadcrumbs = \App\Support\View\Breadcrumbs::make([
            ['label' => 'Home', 'route' => 'home'],
            ['label' => 'Admin', 'route' => 'admin.dashboard'],
            ['label' => 'Licenses'],
        ]);
        $sectionNavItems = \App\Support\ViewData\AdminSectionNavData::make();
    @endphp

    @include('partials.ui.breadcrumbs', ['items' => $breadcrumbs])
    @include('partials.ui.page-shell', ['headerPartial' => 'admin.partials.page-header', 'page' => $page])
    @include('partials.ui.section-nav', ['title' => 'Admin Section Navigation', 'items' => $sectionNavItems])

    <div class="admin-page admin-page--licenses">
        <section class="admin-page__hero">
            @include('partials.ui.info-card', ['title' => $summary['headline'], 'body' => $summary['details'], 'symbol' => '﷽'])
        </section>

        <div class="admin-page__grid admin-page__grid--sidebar">
            <div class="admin-page__main">
                <section class="admin-section">
                    <header class="admin-section__header">
                        <div class="admin-section__heading">
                            @include('partials.ui.icon', ['icon' => 'fa-solid fa-key', 'tone' => 'amber', 'size' => 'lg'])
                            <div>
                                <p class="admin-section__eyebrow">License inventory</p>
                                <h2 class="admin-section__title">Entitlements and key visibility</h2>
                            </div>
                        </div>
                        <p class="admin-section__body">License coverage and masked key visibility stay side by side here so admin review can confirm readiness without exposing raw secret material.</p>
                    </header>

                    <div class="admin-page__detail-grid">
                        <div class="admin-card-group">
                            @include('partials.ui.info-card', ['title' => 'License Detail Areas'])
                            @include('partials.ui.stat-list', ['items' => $page['sections'], 'labelKey' => 'heading', 'valueKey' => 'description'])
                        </div>

                        <div class="admin-card-group">
                            @include('partials.ui.info-card', ['title' => 'Recent License Detail'])
                            @include('admin.partials.license-inventory', ['items' => $licenseInventory, 'meta' => $licenseInventoryMeta ?? null])
                        </div>

                        <div class="admin-card-group admin-card-group--full">
                            <section class="ui-panel" aria-labelledby="admin-license-key-detail-title">
                                <header class="ui-panel__header">
                                    <div class="ui-panel__heading">
                                        @include('partials.ui.icon', ['icon' => 'fa-solid fa-shield-keyhole', 'tone' => 'rose', 'size' => 'lg'])
                                        <div>
                                            <p class="ui-panel__eyebrow">Safe display</p>
                                            <h2 class="ui-panel__title" id="admin-license-key-detail-title">Recent API key detail</h2>
                                        </div>
                                    </div>
                                    <p class="ui-panel__body">Key inventory remains operationally useful while token material stays masked and never renders raw.</p>
                                </header>
                                @include('admin.partials.license-inventory', ['items' => $keyInventory, 'meta' => $keyInventoryMeta ?? null])
                                @unless ($hasLicenseData)
                                @include('partials.ui.empty-state', [
                                    'title' => 'No License Records Yet',
                                    'message' => 'API licenses and masked key records will appear here once workspace access is configured.',
                                ])
                                @endunless
                                <p><small aria-hidden="true">ﷻ</small></p>
                            </section>
                        </div>
                    </div>
                </section>
            </div>
        </div>
    </div>
@endsection
