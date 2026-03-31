@include('partials.page-header', [
    'title' => $page['title'] ?? 'Customer Page',
    'subtitle' => $page['subtitle'] ?? ($page['intro'] ?? null),
])
