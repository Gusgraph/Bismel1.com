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
    <style>
        .app-shell-mark {
            position: relative;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            width: 37px;
            height: 37px;
            border-radius: 13px;
            border: 1px solid rgba(255, 255, 255, 0.08);
            background: rgba(255, 255, 255, 0.03);
            box-shadow: inset 0 1px 0 rgba(255, 255, 255, 0.06);
            overflow: hidden;
        }

        .app-shell-mark__glyph,
        .app-shell-mark__halo {
            user-select: none;
            -webkit-user-select: none;
        }

        .app-shell-mark__glyph {
            position: relative;
            z-index: 1;
            font-size: 13px;
            line-height: 1;
            opacity: 0.84;
        }

        .app-shell-mark__halo {
            position: absolute;
            inset: 0;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 29px;
            line-height: 1;
            opacity: 0.045;
            transform: translateY(-1px);
        }

        .app-topbar__menu {
            position: relative;
            display: inline-flex;
            align-items: center;
        }

        .app-topbar__menu-trigger {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            width: 37px;
            height: 37px;
            border: 1px solid rgba(15, 23, 42, 0.09);
            border-radius: 13px;
            background: rgba(255, 255, 255, 0.92);
            color: inherit;
            cursor: pointer;
        }

        .app-topbar__menu-panel {
            position: absolute;
            top: calc(100% + 9px);
            right: 0;
            min-width: 191px;
            padding: 9px;
            border: 1px solid rgba(15, 23, 42, 0.09);
            border-radius: 17px;
            background: rgba(255, 255, 255, 0.98);
            box-shadow: 0 17px 39px rgba(15, 23, 42, 0.11);
            backdrop-filter: blur(11px);
            z-index: 40;
        }

        .app-topbar__menu-panel[hidden] {
            display: none;
        }

        .app-topbar__menu-list {
            list-style: none;
            margin: 0;
            padding: 0;
        }

        .app-topbar__menu-item + .app-topbar__menu-item {
            margin-top: 5px;
        }

        .app-topbar__menu-link,
        .app-topbar__menu-button {
            display: flex;
            align-items: center;
            width: 100%;
            min-height: 37px;
            padding: 9px 11px;
            border: 0;
            border-radius: 11px;
            background: transparent;
            color: inherit;
            text-align: left;
            text-decoration: none;
            cursor: pointer;
        }

        .app-topbar__menu-link:hover,
        .app-topbar__menu-link:focus-visible,
        .app-topbar__menu-button:hover,
        .app-topbar__menu-button:focus-visible,
        .app-topbar__menu-link.is-active {
            background: rgba(15, 23, 42, 0.06);
            outline: none;
        }
    </style>
</head>
<body class="app-body">
    @php
        $routeName = request()->route()?->getName();
        $pageTitle = trim($__env->yieldContent('title', 'Workspace'));
        $currentSection = 'Public';
        $contextLabel = 'Public Area';

        $publicItems = [
            [
                'label' => 'Home',
                'route' => 'home',
                'description' => 'Return to the public starting point for the app.',
            ],
        ];

        $customerItems = \App\Support\Navigation\CustomerNavigation::items();
        $adminItems = \App\Support\Navigation\AdminNavigation::items();
        $user = request()->user();
        $canSeeCustomerWorkspace = $user?->hasCustomerAccess() ?? false;
        $canSeeAdminWorkspace = $user?->hasAdminAccess() ?? false;

        if (request()->routeIs('customer.*')) {
            $currentSection = 'Customer';
            $contextLabel = 'Customer Workspace';
        } elseif (request()->routeIs('admin.*')) {
            $currentSection = 'Admin';
            $contextLabel = 'Admin Workspace';
        } elseif (request()->routeIs('login*')) {
            $contextLabel = 'Session Access';
        }

        $navigationGroups = array_values(array_filter([
            request()->routeIs('customer.*') ? null : ['title' => 'General', 'items' => $publicItems],
            $canSeeCustomerWorkspace ? ['title' => 'Customer Workspace', 'items' => $customerItems] : null,
            $canSeeAdminWorkspace ? ['title' => 'Admin Workspace', 'items' => $adminItems] : null,
        ]));

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
                ['route' => 'customer.automation.index', 'icon' => 'fa-solid fa-robot', 'tooltip' => 'Automation'],
                ['route' => 'customer.billing.index', 'icon' => 'fa-solid fa-credit-card', 'tooltip' => 'Plans & Billing'],
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

        $shellMenuItems = array_values(array_filter([
            $canSeeCustomerWorkspace ? ['label' => 'Profile', 'route' => 'customer.settings.edit'] : null,
            $canSeeCustomerWorkspace ? ['label' => 'Billing', 'route' => 'customer.billing.index'] : null,
            ($canSeeCustomerWorkspace || request()->routeIs('customer.*'))
                ? ['label' => 'Settings', 'route' => 'customer.settings.index']
                : ($canSeeAdminWorkspace ? ['label' => 'Settings', 'route' => 'admin.system.index'] : null),
        ]));
    @endphp

    <div class="app-shell">
        <button type="button" class="app-shell__overlay" data-shell-close aria-label="Close navigation"></button>

        <aside class="app-sidebar" id="app-shell-sidebar" aria-label="Primary navigation">
            <div class="app-sidebar__header">
                <div class="app-sidebar__header-main">
                    <div class="app-sidebar__brand">
                        <span class="app-shell-mark" aria-label="Workspace">
                            <span aria-hidden="true" class="app-shell-mark__halo">﷽</span>
                            <span aria-hidden="true" class="app-shell-mark__glyph">﷽</span>
                        </span>
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
                        <div class="app-topbar__menu" data-shell-menu>
                            <button
                                type="button"
                                class="app-topbar__menu-trigger"
                                data-shell-menu-trigger
                                aria-haspopup="menu"
                                aria-expanded="false"
                                aria-label="Open account menu"
                            >
                                <i class="fa-solid fa-ellipsis" aria-hidden="true"></i>
                            </button>

                            <div class="app-topbar__menu-panel" data-shell-menu-panel hidden>
                                <ul class="app-topbar__menu-list" role="menu" aria-label="Account menu">
                                    @foreach ($shellMenuItems as $shellMenuItem)
                                        @php
                                            $isShellMenuItemActive = $isRouteActive($shellMenuItem['route'] ?? null);
                                        @endphp
                                        <li class="app-topbar__menu-item" role="none">
                                            <a
                                                href="{{ route($shellMenuItem['route']) }}"
                                                class="app-topbar__menu-link{{ $isShellMenuItemActive ? ' is-active' : '' }}"
                                                role="menuitem"
                                            >
                                                {{ $shellMenuItem['label'] }}
                                            </a>
                                        </li>
                                    @endforeach
                                    <li class="app-topbar__menu-item" role="none">
                                        <form method="POST" action="{{ route('logout') }}">
                                            @csrf
                                            <button type="submit" class="app-topbar__menu-button" role="menuitem">Logout</button>
                                        </form>
                                    </li>
                                </ul>
                            </div>
                        </div>
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
            const shellMenus = document.querySelectorAll('[data-shell-menu]');

            if (!toggleButtons.length) {
                return;
            }

            const closeMenus = () => {
                shellMenus.forEach((menu) => {
                    const trigger = menu.querySelector('[data-shell-menu-trigger]');
                    const panel = menu.querySelector('[data-shell-menu-panel]');

                    if (!trigger || !panel) {
                        return;
                    }

                    panel.hidden = true;
                    trigger.setAttribute('aria-expanded', 'false');
                });
            };

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

            shellMenus.forEach((menu) => {
                const trigger = menu.querySelector('[data-shell-menu-trigger]');
                const panel = menu.querySelector('[data-shell-menu-panel]');

                if (!trigger || !panel) {
                    return;
                }

                trigger.addEventListener('click', (event) => {
                    event.stopPropagation();

                    const shouldOpen = panel.hidden;
                    closeMenus();
                    panel.hidden = !shouldOpen;
                    trigger.setAttribute('aria-expanded', shouldOpen ? 'true' : 'false');
                });

                panel.addEventListener('click', (event) => {
                    event.stopPropagation();
                });
            });

            document.addEventListener('keydown', (event) => {
                if (event.key === 'Escape') {
                    closeMenus();

                    if (isDesktop()) {
                        setDesktopSidebarState(false);
                        return;
                    }

                    setOpenState(false);
                }
            });

            document.addEventListener('click', () => {
                closeMenus();
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
