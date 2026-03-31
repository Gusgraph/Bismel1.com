<?php
// اعوز بالله من الشياطين و ان يحضرون بسم الله الرحمن الرحيم الله لا إله إلا هو الحي القيوم
// Bismillahi ar-Rahmani ar-Rahim Audhu billahi min ash-shayatin wa an yahdurun Bismillah ar-Rahman ar-Rahim Allah la ilaha illa huwa al-hayy al-qayyum. Tamsa Allahu ala ayunihim
// version: x
// ======================================================
// - App Name: bismel1.com
// - Gusgraph LLC -
// - Author: Gus Kazem
// - https://Gusgraph.com
// - File Path: resources/views/admin/audit/index.blade.php
// ======================================================
?>
@extends('layouts.app')

@section('title', 'Admin Audit')

@section('content')
    @php
        $breadcrumbs = \App\Support\View\Breadcrumbs::make([
            ['label' => 'Home', 'route' => 'home'],
            ['label' => 'Admin', 'route' => 'admin.dashboard'],
            ['label' => 'Audit'],
        ]);
        $sectionNavItems = \App\Support\ViewData\AdminSectionNavData::make();
    @endphp

    @include('partials.ui.breadcrumbs', ['items' => $breadcrumbs])
    @include('partials.ui.page-shell', ['headerPartial' => 'admin.partials.page-header', 'page' => $page])
    @include('partials.ui.section-nav', ['title' => 'Admin Section Navigation', 'items' => $sectionNavItems])

    <div class="admin-page admin-page--audit">
        <section class="admin-page__hero">
            @include('partials.ui.info-card', ['title' => $summary['headline'], 'body' => $summary['details'], 'symbol' => 'ﷺ'])
        </section>

        <div class="admin-page__grid admin-page__grid--sidebar">
            <div class="admin-page__main">
                <section class="admin-section">
                    <header class="admin-section__header">
                        <div class="admin-section__heading">
                            @include('partials.ui.icon', ['icon' => 'fa-solid fa-shield-halved', 'tone' => 'rose', 'size' => 'lg'])
                            <div>
                                <p class="admin-section__eyebrow">Oversight stream</p>
                                <h2 class="admin-section__title">Operational activity and audit detail</h2>
                            </div>
                        </div>
                        <p class="admin-section__body">Platform notes and recent oversight streams stay grouped here so operational review is scannable before deeper detail work.</p>
                    </header>

                    <div class="admin-page__detail-grid">
                        <div class="admin-card-group">
                            @include('partials.ui.info-card', ['title' => 'Audit Visibility Areas'])
                            @include('partials.ui.stat-list', ['items' => $page['sections'], 'labelKey' => 'heading', 'valueKey' => 'description'])
                        </div>

                        <div class="admin-card-group">
                            @include('partials.ui.info-card', ['title' => 'Operations Notes'])
                            @include('admin.partials.admin-alerts', ['alerts' => $alerts ?? []])
                        </div>

                        <div class="admin-card-group">
                            @include('partials.ui.info-card', ['title' => 'Recent Activity Detail'])
                            @include('admin.partials.activity-log-summary', ['activitySummaryMeta' => $activitySummaryMeta ?? null])
                        </div>

                        <div class="admin-card-group">
                            @include('partials.ui.info-card', ['title' => 'Recent Audit Detail'])
                            @include('admin.partials.audit-log-summary', ['auditSummaryMeta' => $auditSummaryMeta ?? null])
                            @unless ($hasAuditData)
                                @include('partials.ui.empty-state', [
                                    'title' => 'No Audit Records Yet',
                                    'message' => 'Activity and audit history will appear here once the platform records oversight events.',
                                ])
                            @endunless
                            <p><small aria-hidden="true">﷽</small></p>
                        </div>
                    </div>
                </section>
            </div>
        </div>
    </div>
@endsection
