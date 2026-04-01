<?php
// اعوز بالله من الشياطين و ان يحضرون بسم الله الرحمن الرحيم الله لا إله إلا هو الحي القيوم
// Bismillahi ar-Rahmani ar-Rahim Audhu billahi min ash-shayatin wa an yahdurun Bismillah ar-Rahman ar-Rahim Allah la ilaha illa huwa al-hayy al-qayyum. Tamsa Allahu ala ayunihim
// version: x
// ======================================================
// - App Name: bismel1.com
// - Gusgraph LLC -
// - Author: Gus Kazem
// - https://Gusgraph.com
// - File Path: resources/views/layouts/guest.blade.php
// ======================================================
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('title', 'Bismel1')</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])

    <style>
        :root {
            --guest-bg: #06111f;
            --guest-bg-soft: #0b182b;
            --guest-surface: rgba(12, 24, 41, 0.82);
            --guest-surface-strong: rgba(19, 35, 58, 0.94);
            --guest-line: rgba(141, 177, 222, 0.18);
            --guest-line-strong: rgba(112, 182, 255, 0.34);
            --guest-text: #ebf3ff;
            --guest-muted: #91a9c7;
            --guest-accent: #68d5ff;
            --guest-accent-strong: #8c7bff;
            --guest-success: #4ade80;
            --guest-danger: #fb7185;
            --guest-warning: #fbbf24;
            --guest-shadow: 0 23px 91px rgba(0, 0, 0, 0.34);
            --guest-radius: 27px;
            --guest-width: 1179px;
        }

        body.guest-body {
            margin: 0;
            min-height: 100vh;
            color: var(--guest-text);
            background:
                radial-gradient(circle at top left, rgba(104, 213, 255, 0.16), transparent 28%),
                radial-gradient(circle at top right, rgba(140, 123, 255, 0.18), transparent 24%),
                linear-gradient(180deg, #050b14 0%, #07111f 48%, #04080f 100%);
            font-family: var(--font-sans);
        }

        .guest-page {
            position: relative;
            min-height: 100vh;
            overflow: hidden;
        }

        .guest-buzles {
            position: absolute;
            inset: 0;
            pointer-events: none;
            z-index: 0;
            overflow: hidden;
        }

        .guest-buzle {
            position: absolute;
            border-radius: 999px;
            opacity: 0.55;
            filter: blur(0.3px);
        }

        .guest-buzle--one {
            top: 73px;
            right: 11%;
            width: 11px;
            height: 11px;
            background: radial-gradient(circle, rgba(104, 213, 255, 0.95), rgba(104, 213, 255, 0));
            box-shadow: 0 0 19px rgba(104, 213, 255, 0.28);
        }

        .guest-buzle--two {
            top: 27%;
            left: 73px;
            width: 15px;
            height: 15px;
            background: radial-gradient(circle, rgba(140, 123, 255, 0.88), rgba(140, 123, 255, 0));
            box-shadow: 0 0 27px rgba(140, 123, 255, 0.22);
        }

        .guest-buzle--three {
            right: 19%;
            bottom: 111px;
            width: 19px;
            height: 19px;
            background: radial-gradient(circle, rgba(104, 213, 255, 0.42), rgba(104, 213, 255, 0));
            box-shadow: 0 0 27px rgba(104, 213, 255, 0.18);
        }

        .guest-buzle--four {
            left: 15%;
            bottom: 73px;
            width: 11px;
            height: 11px;
            background: radial-gradient(circle, rgba(251, 191, 36, 0.72), rgba(251, 191, 36, 0));
            box-shadow: 0 0 15px rgba(251, 191, 36, 0.15);
        }

        .guest-page::before,
        .guest-page::after {
            content: "";
            position: fixed;
            inset: auto;
            pointer-events: none;
            z-index: 0;
        }

        .guest-page::before {
            top: 11rem;
            right: -11rem;
            width: 27rem;
            height: 27rem;
            border-radius: 999px;
            background: radial-gradient(circle, rgba(104, 213, 255, 0.15) 0%, rgba(104, 213, 255, 0) 70%);
        }

        .guest-page::after {
            left: -11rem;
            bottom: 7rem;
            width: 23rem;
            height: 23rem;
            border-radius: 999px;
            background: radial-gradient(circle, rgba(140, 123, 255, 0.14) 0%, rgba(140, 123, 255, 0) 72%);
        }

        .guest-header,
        .guest-main,
        .guest-footer {
            position: relative;
            z-index: 1;
        }

        .guest-header {
            width: 100%;
            padding: 0;
            border-bottom: 1px solid rgba(255, 255, 255, 0.12);
            background: rgba(8, 16, 28, 0.84);
            backdrop-filter: blur(10px);
        }

        .guest-header__inner {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 1rem;
            padding: 0.95rem 1.35rem;
        }

        .guest-brand {
            display: flex;
            align-items: center;
            gap: 0.85rem;
            text-decoration: none;
            color: var(--guest-text);
        }

        .guest-brand__mark {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            width: 73px;
            height: 73px;
        }

        .guest-brand__mark img {
            display: block;
            height: 73px;
            width: auto;
            object-fit: contain;
        }

        .guest-brand__eyebrow,
        .guest-nav__label,
        .guest-section__eyebrow,
        .guest-card__eyebrow,
        .guest-tape__label {
            margin: 0 0 0.12rem;
            font-size: 0.73rem;
            line-height: 1.19;
            text-transform: uppercase;
            letter-spacing: 0.16em;
            color: var(--guest-muted);
        }

        .guest-page small[aria-hidden="true"],
        .guest-symbol {
            color: color-mix(in srgb, var(--guest-bg-soft) 73%, var(--guest-text) 27%);
            user-select: none;
            -webkit-user-select: none;
            pointer-events: none;
        }

        .guest-brand__title {
            margin: 0;
            font-size: 1rem;
            line-height: 1.2;
            letter-spacing: -0.03em;
            color: var(--guest-text);
        }

        .guest-nav {
            display: flex;
            align-items: center;
            gap: 0.75rem;
        }

        .guest-nav__label {
            margin: 0;
        }

        .guest-nav__link {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            padding: 0.75rem 1.05rem;
            border-radius: 999px;
            border: 1px solid var(--guest-line);
            text-decoration: none;
            color: var(--guest-text);
            background: rgba(255, 255, 255, 0.02);
            transition: transform 140ms ease, border-color 140ms ease, background 140ms ease;
        }

        .guest-nav__link:hover,
        .guest-nav__link:focus-visible,
        .guest-cta:hover,
        .guest-cta:focus-visible,
        .guest-story__link:hover,
        .guest-story__link:focus-visible {
            transform: translateY(-1px);
            border-color: var(--guest-line-strong);
            background: rgba(255, 255, 255, 0.06);
        }

        .guest-nav__link--primary,
        .guest-cta--primary {
            background: linear-gradient(135deg, rgba(104, 213, 255, 0.22), rgba(140, 123, 255, 0.28));
            border-color: rgba(104, 213, 255, 0.36);
        }

        .guest-main {
            width: 100%;
            padding: 0.95rem 1.35rem 3.95rem;
        }

        .guest-footer {
            width: 100%;
            padding: 0 1.35rem 1.95rem;
        }

        .guest-footer__inner {
            display: flex;
            justify-content: space-between;
            gap: 0.95rem;
            padding-top: 1.11rem;
            border-top: 1px solid var(--guest-line);
            color: var(--guest-muted);
            font-size: 0.95rem;
        }

        @media (max-width: 960px) {
            .guest-header__inner {
                flex-direction: column;
                align-items: flex-start;
                gap: 0.75rem;
            }

            .guest-footer__inner {
                flex-direction: column;
                align-items: flex-start;
                border-radius: 1.5rem;
            }
        }

        @media (max-width: 640px) {
            .guest-header,
            .guest-main,
            .guest-footer {
                padding-left: 0.95rem;
                padding-right: 0.95rem;
            }

            .guest-nav {
                width: 100%;
                justify-content: space-between;
            }

            .guest-nav__link {
                flex: 1 1 0;
            }
        }
    </style>
</head>
<body class="guest-body">
    <div class="market-bg-shell" aria-hidden="true" style="background: rgba(255, 0, 0, 0.18) !important;">
        <canvas class="market-bg-canvas" data-market-background></canvas>
    </div>

    <div class="guest-page">
        <div class="guest-buzles" aria-hidden="true">
            <span class="guest-buzle guest-buzle--one"></span>
            <span class="guest-buzle guest-buzle--two"></span>
            <span class="guest-buzle guest-buzle--three"></span>
            <span class="guest-buzle guest-buzle--four"></span>
        </div>

        <header class="guest-header">
            <div class="guest-header__inner">
                <a href="{{ route('home') }}" class="guest-brand">
                    <span class="guest-brand__mark">
                        <img src="{{ asset('images/logo.png') }}" alt="Bismel1 logo" aria-hidden="true" />
                    </span>

                    <span>
                        <p class="guest-brand__eyebrow">AI markets and automation</p>
                        <p class="guest-brand__title">Bismel1</p>
                    </span>
                </a>

                <nav class="guest-nav" aria-label="Guest navigation">
                    <p class="guest-nav__label">Trader Access</p>
                    <a href="{{ route('plans') }}" class="guest-nav__link">Plans</a>
                    <a href="{{ route('login') }}" class="guest-nav__link guest-nav__link--primary">Login</a>
                </nav>
            </div>
        </header>

        <main class="guest-main">
            @include('partials.ui.flash-message')
            @yield('content')
        </main>

        <footer class="guest-footer">
    <div class="guest-footer__inner">
        <p>Copyright © {{ date('Y') }} Bismel1. All rights reserved.</p>
    </div>
</footer>
    </div>
</body>
</html>
