@php
    $pageTitle = $title ?? ($page['title'] ?? null);
    $pageSubtitle = $subtitle ?? ($page['subtitle'] ?? ($page['intro'] ?? null));
    $headerPartial = $headerPartial ?? null;
    $pageSummary = $summary ?? null;
@endphp

@if ($headerPartial)
    @include('partials.ui.page-intro', [
        'page' => ['title' => $pageTitle, 'subtitle' => $pageSubtitle, 'intro' => $page['intro'] ?? null],
        'summary' => $pageSummary,
    ])
@elseif ($pageTitle || $pageSubtitle)
    @include('partials.ui.page-intro', [
        'page' => ['title' => $pageTitle ?? 'Page', 'subtitle' => $pageSubtitle, 'intro' => $page['intro'] ?? null],
        'summary' => $pageSummary,
    ])
@endif

{{ $slot ?? '' }}
