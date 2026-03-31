<header>
    <h1>{{ $title ?? ($page['title'] ?? 'Page') }}</h1>
    @if (($subtitle ?? ($page['subtitle'] ?? null)))
        <p>{{ $subtitle ?? $page['subtitle'] }}</p>
    @endif
</header>
