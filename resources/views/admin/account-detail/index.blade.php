<?php
// اعوز بالله من الشياطين و ان يحضرون بسم الله الرحمن الرحيم الله لا إله إلا هو الحي القيوم
// Bismillahi ar-Rahmani ar-Rahim Audhu billahi min ash-shayatin wa an yahdurun Bismillah ar-Rahman ar-Rahim Allah la ilaha illa huwa al-hayy al-qayyum. Tamsa Allahu ala ayunihim
// version: x
// ======================================================
// - App Name: bismel1.com
// - Gusgraph LLC -
// - Author: Gus Kazem
// - https://Gusgraph.com
// - File Path: resources/views/admin/account-detail/index.blade.php
// ======================================================
?>
@extends('layouts.app')

@section('title', 'Admin Account Detail')

@section('content')
    @php
        $breadcrumbs = \App\Support\View\Breadcrumbs::make([
            ['label' => 'Home', 'route' => 'home'],
            ['label' => 'Admin', 'route' => 'admin.dashboard'],
            ['label' => 'Account Detail'],
        ]);
        $sectionNavItems = \App\Support\ViewData\AdminSectionNavData::make();
    @endphp

    @include('partials.ui.breadcrumbs', ['items' => $breadcrumbs])
    @include('partials.ui.page-shell', ['headerPartial' => 'admin.partials.page-header', 'page' => $page])
    @include('partials.ui.section-nav', ['title' => 'Admin Section Navigation', 'items' => $sectionNavItems])

    <div class="admin-page admin-page--account-detail">
        <section class="admin-page__hero">
            @include('partials.ui.info-card', ['title' => $summary['headline'], 'body' => $summary['details'], 'symbol' => 'ﷺ'])
        </section>

        <div class="admin-page__grid admin-page__grid--sidebar">
            <div class="admin-page__main">
                <section class="admin-section">
                    <header class="admin-section__header">
                        <div class="admin-section__heading">
                            @include('partials.ui.icon', ['icon' => 'fa-solid fa-id-card-clip', 'tone' => 'emerald', 'size' => 'lg'])
                            <div>
                                <p class="admin-section__eyebrow">Account snapshot</p>
                                <h2 class="admin-section__title">Tenant and account detail</h2>
                            </div>
                        </div>
                        <p class="admin-section__body">Primary account identity, tenant context, and current platform visibility stay together here so detailed review reads as one workspace rather than separate cards.</p>
                    </header>

                    <div class="admin-page__detail-grid">
                        <div class="admin-card-group">
                            @include('partials.ui.info-card', ['title' => 'Account Detail Areas'])
                            @include('partials.ui.stat-list', ['items' => $page['sections'], 'labelKey' => 'heading', 'valueKey' => 'description'])
                        </div>

                        <div class="admin-card-group">
                            @include('partials.ui.info-card', ['title' => 'Account Detail Summary'])
                            @include('admin.partials.account-detail-summary')
                        </div>

                        <div class="admin-card-group admin-card-group--full">
                            @include('partials.ui.info-card', ['title' => 'Tenant Overview'])
                            @include('admin.partials.tenant-overview')
                            @unless ($hasAccountData)
                                @include('partials.ui.empty-state', [
                                    'title' => 'No Account Detail Yet',
                                    'message' => 'Choose a workspace first, then return here to review account detail and recent operational context.',
                                ])
                            @endunless
                        </div>
                    </div>
                </section>

                <section class="admin-section">
                    <header class="admin-section__header">
                        <div class="admin-section__heading">
                            @include('partials.ui.icon', ['icon' => 'fa-solid fa-plug-circle-bolt', 'tone' => 'violet', 'size' => 'lg'])
                            <div>
                                <p class="admin-section__eyebrow">Broker state</p>
                                <h2 class="admin-section__title">Connections and credential metadata</h2>
                            </div>
                        </div>
                    </header>

                    <div class="admin-page__detail-grid">
                        <div class="admin-card-group">
                            @include('partials.ui.info-card', ['title' => 'Broker Connections'])
                            @include('partials.ui.record-list', ['items' => $brokerConnections, 'meta' => $brokerConnectionsMeta ?? null, 'emptyMessage' => 'No broker connections are recorded for this workspace yet.'])
                            @if ($hasAccountData && empty($brokerConnections))
                                <p class="admin-section__body">Connect Alpaca for this workspace to begin broker visibility here.</p>
                            @endif
                            <p><small aria-hidden="true">﷽</small></p>
                        </div>

                        <div class="admin-card-group">
                            @include('partials.ui.info-card', ['title' => 'Broker Credentials'])
                            @include('partials.ui.record-list', ['items' => $brokerCredentials, 'meta' => $brokerCredentialsMeta ?? null, 'emptyMessage' => 'No broker credential metadata is recorded for this workspace yet.'])
                            @if ($hasAccountData && empty($brokerCredentials))
                                <p class="admin-section__body">No broker credential metadata is linked to this account yet.</p>
                            @endif
                            <p><small aria-hidden="true">ﷻ</small></p>
                        </div>
                    </div>
                </section>

                <section class="admin-section">
                    @php
                        $operatorActions = data_get($accountOperations ?? [], 'operator_actions', []);
                    @endphp
                    <header class="admin-section__header">
                        <div class="admin-section__heading">
                            @include('partials.ui.icon', ['icon' => 'fa-solid fa-robot', 'tone' => 'sky', 'size' => 'lg'])
                            <div>
                                <p class="admin-section__eyebrow">Automation operations</p>
                                <h2 class="admin-section__title">Runtime posture and recent safe outcomes</h2>
                            </div>
                        </div>
                        <p class="admin-section__body">This account-level control surface keeps automation state, broker readiness, recovery priority, safe operator tools, and recent outcomes visible without exposing strategy internals.</p>
                    </header>

                    <div class="admin-page__detail-grid">
                        <div class="admin-card-group admin-card-group--full">
                            @include('partials.ui.info-card', ['title' => 'Account Automation Summary'])
                            @include('partials.ui.summary-grid', ['items' => data_get($accountOperations ?? [], 'summary_items', [])])
                        </div>

                        <div class="admin-card-group">
                            @include('partials.ui.info-card', ['title' => 'Operator Action Summary'])
                            @include('partials.ui.summary-grid', ['items' => data_get($accountOperations ?? [], 'operator_summary_items', [])])
                        </div>

                        <div class="admin-card-group admin-card-group--full">
                            @include('partials.ui.info-card', ['title' => 'Manual Operator Tools'])
                            <div class="admin-page__detail-grid">
                                @forelse ($operatorActions as $operatorAction)
                                    <div class="admin-card-group">
                                        <div class="ui-panel">
                                            <div class="ui-panel__header">
                                                <div class="ui-inline-copy">
                                                    <h3 class="ui-panel__title">{{ $operatorAction['label'] ?? 'Operator action' }}</h3>
                                                    @include('partials.ui.status-badge', ['status' => $operatorAction['state_status'] ?? 'review'])
                                                </div>
                                            </div>
                                            <p class="ui-panel__body">{{ $operatorAction['guidance'] ?? 'Review the current workspace status before running this action.' }}</p>
                                            @if (! empty($operatorAction['blocked_summary']))
                                                <p class="ui-list__meta"><small>{{ $operatorAction['blocked_summary'] }}</small></p>
                                            @endif
                                            <form method="POST" action="{{ route('admin.account-detail.operator-action', ['account' => request()->route('account')]) }}" @if(!empty($operatorAction['confirm_message'])) onsubmit="return window.confirm(@js($operatorAction['confirm_message']))" @endif>
                                                @csrf
                                                <input type="hidden" name="action" value="{{ $operatorAction['action'] ?? '' }}">
                                                <input type="hidden" name="confirm_action" value="{{ $operatorAction['action'] ?? '' }}">
                                                <button class="ui-button {{ ($operatorAction['allowed'] ?? false) ? 'ui-button--primary' : 'ui-button--secondary' }}" type="submit" @disabled(! ($operatorAction['allowed'] ?? false))>
                                                    @if (($operatorAction['state'] ?? null) === 'in_progress')
                                                        Already running
                                                    @elseif (($operatorAction['allowed'] ?? false))
                                                        {{ $operatorAction['label'] ?? 'Run action' }}
                                                    @else
                                                        Currently blocked
                                                    @endif
                                                </button>
                                            </form>
                                        </div>
                                    </div>
                                @empty
                                    @include('partials.ui.empty-state', [
                                        'title' => 'No Operator Actions Yet',
                                        'message' => 'Operator actions will appear here when this workspace is eligible for manual admin controls.',
                                    ])
                                @endforelse
                            </div>
                        </div>

                        <div class="admin-card-group">
                            @include('partials.ui.info-card', ['title' => 'Recent Execution Outcomes'])
                            @include('partials.ui.record-list', [
                                'items' => data_get($accountOperations ?? [], 'recent_execution_items', []),
                                'emptyMessage' => 'Recent execution outcomes will appear here once this workspace records execution activity.',
                            ])
                        </div>

                        <div class="admin-card-group">
                            @include('partials.ui.info-card', ['title' => 'Recent Risk Blocks'])
                            @include('partials.ui.record-list', [
                                'items' => data_get($accountOperations ?? [], 'recent_risk_items', []),
                                'emptyMessage' => 'Recent risk blocks will appear here once this workspace records risk activity.',
                            ])
                        </div>

                        <div class="admin-card-group">
                            @include('partials.ui.info-card', ['title' => 'Recent Reconciliation Outcomes'])
                            @include('partials.ui.record-list', [
                                'items' => data_get($accountOperations ?? [], 'recent_position_items', []),
                                'emptyMessage' => 'Recent reconciliation outcomes will appear here once this workspace records broker-position activity.',
                            ])
                        </div>

                        <div class="admin-card-group">
                            @include('partials.ui.info-card', ['title' => 'Recent Operator Actions'])
                            @include('partials.ui.record-list', [
                                'items' => data_get($accountOperations ?? [], 'recent_operator_items', []),
                                'emptyMessage' => 'Recent operator actions will appear here once manual admin actions are recorded for this workspace.',
                            ])
                        </div>

                        <div class="admin-card-group">
                            @include('partials.ui.info-card', ['title' => 'Recent Runtime Activity'])
                            @include('partials.ui.record-list', [
                                'items' => data_get($accountOperations ?? [], 'recent_activity_items', []),
                                'emptyMessage' => 'Recent runtime activity will appear here once this workspace records automation updates.',
                            ])
                        </div>
                    </div>
                </section>
            </div>
        </div>
    </div>
@endsection
