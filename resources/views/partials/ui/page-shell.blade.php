@php
    $pageTitle = $title ?? ($page['title'] ?? null);
    $pageSubtitle = $subtitle ?? ($page['subtitle'] ?? ($page['intro'] ?? null));
    $headerPartial = $headerPartial ?? null;
@endphp

@if ($headerPartial)
    @include('partials.ui.page-intro', ['page' => ['title' => $pageTitle, 'subtitle' => $pageSubtitle, 'intro' => $page['intro'] ?? null]])
@elseif ($pageTitle || $pageSubtitle)
    @include('partials.ui.page-intro', ['page' => ['title' => $pageTitle ?? 'Page', 'subtitle' => $pageSubtitle, 'intro' => $page['intro'] ?? null]])
@endif

{{ $slot ?? '' }}
