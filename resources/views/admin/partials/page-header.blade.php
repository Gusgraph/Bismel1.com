@include('partials.page-header', [
    'title' => $page['title'] ?? 'Admin Page',
    'subtitle' => $page['subtitle'] ?? ($page['intro'] ?? null),
])
