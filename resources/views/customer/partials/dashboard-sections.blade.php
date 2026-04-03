<?php
// اعوز بالله من الشياطين و ان يحضرون بسم الله الرحمن الرحيم الله لا إله إلا هو الحي القيوم
// Bismillahi ar-Rahmani ar-Rahim Audhu billahi min ash-shayatin wa an yahdurun Bismillah ar-Rahman ar-Rahim Allah la ilaha illa huwa al-hayy al-qayyum. Tamsa Allahu ala ayunihim
// version: x
// ======================================================
// - App Name: bismel1.com
// - Gusgraph LLC -
// - Author: Gus Kazem
// - https://Gusgraph.com
// - File Path: resources/views/customer/partials/dashboard-sections.blade.php
// ======================================================
?>
@php
    $cards = $dashboardSurface['sections'] ?? ($page['sections'] ?? []);
@endphp

<section aria-labelledby="customer-dashboard-sections-title">
    <header class="dashboard-card__header">
        <div class="dashboard-card__heading">
            @include('partials.ui.icon', ['icon' => 'fa-solid fa-table-cells-large', 'tone' => 'sky', 'size' => 'lg'])
            <div>
                <p class="dashboard-card__eyebrow">Main blocks</p>
                <h3 class="dashboard-card__title" id="customer-dashboard-sections-title">Trading previews, desk activity, and action queue</h3>
            </div>
        </div>
        <p class="dashboard-card__body">Each block links into an existing customer route, so the dashboard stays operational instead of becoming a dead summary layer.</p>
    </header>

    <div class="dashboard-grid dashboard-grid--primary">
        @foreach ($cards as $card)
            @if (($card['kind'] ?? null) === 'activity')
                @include('customer.partials.dashboard-activity', ['card' => $card])
            @else
                <section class="dashboard-card">
                    <header class="dashboard-card__header">
                        <div class="dashboard-card__heading">
                            @include('partials.ui.icon', [
                                'icon' => $card['icon'] ?? 'fa-solid fa-circle-nodes',
                                'tone' => $card['tone'] ?? 'sky',
                                'size' => 'lg',
                            ])
                            <div>
                                <p class="dashboard-card__eyebrow">Control block</p>
                                <h3 class="dashboard-card__title">{{ $card['title'] ?? 'Dashboard Block' }}</h3>
                            </div>
                        </div>
                        <p class="dashboard-card__body">{{ $card['description'] ?? '' }}</p>
                    </header>

                    <ul class="ui-list ui-record-list">
                        @forelse (($card['items'] ?? []) as $item)
                            <li>
                                <div class="ui-list__stack">
                                    <div class="ui-inline-copy">
                                        <strong>{{ $item['title'] ?? $item['label'] ?? 'Item' }}</strong>
                                        @if (!empty($item['status']))
                                            <span class="ui-list__meta">{{ $item['status'] }}</span>
                                        @endif
                                    </div>
                                    @if (!empty($item['summary']))
                                        <span class="ui-list__meta">{{ $item['summary'] }}</span>
                                    @elseif (!empty($item['context']))
                                        <span class="ui-list__meta">{{ $item['context'] }}</span>
                                    @endif
                                    @if (!empty($item['meta']))
                                        <span class="ui-list__meta">{{ $item['meta'] }}</span>
                                    @endif
                                </div>
                            </li>
                        @empty
                            <li>{{ $card['empty'] ?? 'No items are available yet.' }}</li>
                        @endforelse
                    </ul>

                    @if (!empty($card['route']))
                        <p><a href="{{ route($card['route']) }}">{{ $card['routeLabel'] ?? 'Open section' }}</a></p>
                    @endif
                </section>
            @endif
        @endforeach
    </div>
</section>
