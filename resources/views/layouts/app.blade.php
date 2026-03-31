<?php
// اعوز بالله من الشياطين و ان يحضرون بسم الله الرحمن الرحيم الله لا إله إلا هو الحي القيوم
// Bismillahi ar-Rahmani ar-Rahim Audhu billahi min ash-shayatin wa an yahdurun Bismillah ar-Rahman ar-Rahim Allah la ilaha illa huwa al-hayy al-qayyum. Tamsa Allahu ala ayunihim
// version: x
// ======================================================
// - App Name: gusgraph-trading
// - Gusgraph LLC -
// - Author: Gus Kazem
// - https://Gusgraph.com
// - File Path: resources/views/layouts/app.blade.php
// ======================================================
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('title', 'Laravel')</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="app-body">
    @php
        $routeName = request()->route()?->getName();
        $pageTitle = trim($__env->yieldContent('title', 'Workspace'));
        $currentSection = 'Public';
        $contextLabel = 'Public Area';
        $contextDescription = 'Open the public landing area or continue into a workspace.';

        $publicItems = [
            [
                'label' => 'Home',
                'route' => 'home',
                'description' => 'Return to the public starting point for the app.',
            ],
        ];

        $customerItems = \App\Support\Navigation\CustomerNavigation::items();
        $adminItems = \App\Support\Navigation\AdminNavigation::items();

        if (request()->routeIs('customer.*')) {
            $currentSection = 'Customer';
            $contextLabel = 'Customer Workspace';
            $contextDescription = 'Account-level setup, billing, broker, reporting, and settings areas.';
        } elseif (request()->routeIs('admin.*')) {
            $currentSection = 'Admin';
            $contextLabel = 'Admin Workspace';
            $contextDescription = 'Platform oversight, reporting, system, account, and audit areas.';
        } elseif (request()->routeIs('login*')) {
            $contextLabel = 'Session Access';
            $contextDescription = 'Sign in to continue into the customer or admin workspace.';
        }

        $navigationGroups = [
            ['title' => 'General', 'items' => $publicItems],
            ['title' => 'Customer Workspace', 'items' => $customerItems],
            ['title' => 'Admin Workspace', 'items' => $adminItems],
        ];

        $isRouteActive = static function (?string $itemRoute) use ($routeName): bool {
            if (!$itemRoute || !$routeName) {
                return false;
            }

            $patterns = match ($itemRoute) {
                'customer.broker.index' => ['customer.broker.*'],
                'customer.license.index' => ['customer.license.*'],
                'customer.settings.index' => ['customer.settings.*'],
                'admin.system.index' => ['admin.system.*'],
                'admin.accounts.index' => ['admin.accounts.*', 'admin.account-detail.*'],
                default => [$itemRoute],
            };

            foreach ($patterns as $pattern) {
                if (request()->routeIs($pattern)) {
                    return true;
                }
            }

            return false;
        };

        $activeItem = null;

        foreach ($navigationGroups as $group) {
            foreach ($group['items'] as $item) {
                if ($isRouteActive($item['route'] ?? null)) {
                    $activeItem = $item;
                    break 2;
                }
            }
        }

        $collapsedRailLinks = match (true) {
            request()->routeIs('customer.*') => [
                ['route' => 'customer.dashboard', 'icon' => 'fa-solid fa-chart-line', 'tooltip' => 'Dashboard'],
                ['route' => 'customer.account.index', 'icon' => 'fa-solid fa-user', 'tooltip' => 'Account'],
                ['route' => 'customer.settings.index', 'icon' => 'fa-solid fa-sliders', 'tooltip' => 'Settings'],
            ],
            request()->routeIs('admin.*') => [
                ['route' => 'admin.dashboard', 'icon' => 'fa-solid fa-chart-line', 'tooltip' => 'Dashboard'],
                ['route' => 'admin.accounts.index', 'icon' => 'fa-solid fa-user-gear', 'tooltip' => 'Accounts'],
                ['route' => 'admin.system.index', 'icon' => 'fa-solid fa-sliders', 'tooltip' => 'System'],
            ],
            default => [
                ['route' => 'home', 'icon' => 'fa-solid fa-house', 'tooltip' => 'Home'],
            ],
        };
    @endphp

    <div class="app-shell">
        <button type="button" class="app-shell__overlay" data-shell-close aria-label="Close navigation"></button>

        <aside class="app-sidebar" id="app-shell-sidebar" aria-label="Primary navigation">
            <div class="app-sidebar__header">
                <div class="app-sidebar__header-main">
                    <div class="app-sidebar__brand">
                        <p class="app-sidebar__eyebrow">Gusgraph Trading <span aria-hidden="true" class="app-sidebar__symbol">﷽</span></p>
                        <h1 class="app-sidebar__title">Workspace Navigation</h1>
                    </div>

                    <button
                        type="button"
                        class="app-sidebar__toggle app-menu-toggle"
                        data-shell-toggle
                        aria-controls="app-shell-sidebar"
                        aria-expanded="false"
                        aria-label="Open sidebar"
                        data-shell-tooltip="Open sidebar"
                    >
                        <i class="app-shell-control fa-regular fa-window-maximize" data-shell-toggle-icon aria-hidden="true"></i>
                    </button>
                </div>
                <p class="app-sidebar__body">{{ $contextDescription }}</p>
            </div>

            <div class="app-sidebar__rail" aria-label="Quick navigation">
                @foreach ($collapsedRailLinks as $railLink)
                    @php
                        $isRailActive = $isRouteActive($railLink['route']);
                    @endphp
                    <a
                        href="{{ route($railLink['route']) }}"
                        class="app-sidebar__rail-link{{ $isRailActive ? ' is-active' : '' }}"
                        data-shell-tooltip="{{ $railLink['tooltip'] }}"
                        aria-label="{{ $railLink['tooltip'] }}"
                        @if ($isRailActive) aria-current="page" @endif
                    >
                        <i class="app-shell-control {{ $railLink['icon'] }}" aria-hidden="true"></i>
                    </a>
                @endforeach
            </div>

            <nav class="app-sidebar__nav" aria-label="Workspace sections">
                @foreach ($navigationGroups as $group)
                    <section class="app-nav-group" aria-labelledby="{{ \Illuminate\Support\Str::slug($group['title'], '-') }}-nav-title">
                        <div class="app-nav-group__header">
                            <p class="app-nav-group__label" id="{{ \Illuminate\Support\Str::slug($group['title'], '-') }}-nav-title">{{ $group['title'] }}</p>
                        </div>
                        <ul class="app-nav-list">
                            @foreach ($group['items'] as $item)
                                @php
                                    $isActive = $isRouteActive($item['route'] ?? null);
                                @endphp
                                <li class="app-nav-list__item">
                                    <a
                                        href="{{ route($item['route']) }}"
                                        class="app-nav-link{{ $isActive ? ' is-active' : '' }}"
                                        @if ($isActive) aria-current="page" @endif
                                    >
                                        <span class="app-nav-link__copy">
                                            <span class="app-nav-link__label">{{ $item['label'] }}</span>
                                            @if (!empty($item['description']))
                                                <span class="app-nav-link__meta">{{ $item['description'] }}</span>
                                            @endif
                                        </span>
                                    </a>
                                </li>
                            @endforeach
                        </ul>
                    </section>
                @endforeach
            </nav>

            <div class="app-sidebar__footer">
                <div class="app-sidebar__utility">
                    <div class="app-sidebar__utility-copy">
                        <p class="app-sidebar__note">Current area</p>
                        <p class="app-sidebar__context">{{ $contextLabel }} <span aria-hidden="true" class="app-sidebar__symbol app-sidebar__symbol--footer">ﷻ</span></p>
                    </div>

                    @auth
                        <form method="POST" action="{{ route('logout') }}" class="app-sidebar__utility-action">
                            @csrf
                            <button type="submit" class="app-sidebar__logout">Logout</button>
                        </form>
                    @else
                        <a href="{{ route('login') }}" class="app-sidebar__login app-sidebar__utility-action">Login</a>
                    @endauth
                </div>
            </div>
        </aside>

        <div class="app-shell__main">
            <header class="app-topbar">
                <div class="app-topbar__primary">
                    <button
                        type="button"
                        class="app-menu-toggle app-menu-toggle--topbar"
                        data-shell-toggle
                        aria-controls="app-shell-sidebar"
                        aria-expanded="false"
                        aria-label="Open sidebar"
                        data-shell-tooltip="Open sidebar"
                    >
                        <i class="app-shell-control fa-regular fa-window-maximize" data-shell-toggle-icon aria-hidden="true"></i>
                    </button>

                    <div class="app-topbar__context">
                        <p class="app-topbar__eyebrow">{{ $contextLabel }}</p>
                        <h2 class="app-topbar__title">
                            {{ $pageTitle }}
                            <span aria-hidden="true" class="app-topbar__symbol">ﷺ</span>
                        </h2>
                    </div>
                </div>

                <div class="app-topbar__secondary">
                    @if ($activeItem && !empty($activeItem['description']))
                        <p class="app-topbar__summary">{{ $activeItem['description'] }}</p>
                    @endif

                    @auth
                        <span class="app-topbar__status">Signed in</span>
                    @else
                        <a href="{{ route('login') }}" class="app-topbar__status-link">Login</a>
                    @endauth
                </div>
            </header>

            <main class="app-main">
                @include('partials.ui.flash-message')
                @if (!empty($notices))
                    @include('partials.ui.info-card', ['title' => 'System Notices'])
                    @include('partials.ui.notice-list', ['items' => $notices])
                @endif
                @yield('content')
            </main>
        </div>
    </div>

    <script>
        (() => {
            const body = document.body;
            const toggleButtons = document.querySelectorAll('[data-shell-toggle]');
            const closeButtons = document.querySelectorAll('[data-shell-close]');

            if (!toggleButtons.length) {
                return;
            }

            const isDesktop = () => window.innerWidth >= 1100;

            const syncToggle = () => {
                const isExpanded = isDesktop()
                    ? !body.classList.contains('app-sidebar-collapsed')
                    : body.classList.contains('app-sidebar-open');

                toggleButtons.forEach((button) => {
                    button.setAttribute('aria-expanded', isExpanded ? 'true' : 'false');
                    button.setAttribute('aria-label', isExpanded ? 'Close sidebar' : 'Open sidebar');
                    button.setAttribute('data-shell-tooltip', isExpanded ? 'Close sidebar' : 'Open sidebar');

                    const icon = button.querySelector('[data-shell-toggle-icon]');
                    if (icon) {
                        icon.className = `app-shell-control fa-regular ${isExpanded ? 'fa-window-restore' : 'fa-window-maximize'}`;
                    }
                });
            };

            const setOpenState = (isOpen) => {
                body.classList.toggle('app-sidebar-open', isOpen);
                syncToggle();
            };

            const setDesktopSidebarState = (isExpanded) => {
                body.classList.toggle('app-sidebar-collapsed', !isExpanded);
                syncToggle();
            };

            toggleButtons.forEach((button) => {
                button.addEventListener('click', () => {
                    if (isDesktop()) {
                        setDesktopSidebarState(body.classList.contains('app-sidebar-collapsed'));
                        return;
                    }

                    setOpenState(!body.classList.contains('app-sidebar-open'));
                });
            });

            closeButtons.forEach((button) => {
                button.addEventListener('click', () => {
                    if (isDesktop()) {
                        setDesktopSidebarState(false);
                        return;
                    }

                    setOpenState(false);
                });
            });

            document.addEventListener('keydown', (event) => {
                if (event.key === 'Escape') {
                    if (isDesktop()) {
                        setDesktopSidebarState(false);
                        return;
                    }

                    setOpenState(false);
                }
            });

            window.addEventListener('resize', () => {
                if (window.innerWidth >= 1100) {
                    setOpenState(false);
                    syncToggle();
                    return;
                }

                body.classList.remove('app-sidebar-collapsed');
                syncToggle();
            });

            syncToggle();
        })();
    </script>
</body>
</html>
