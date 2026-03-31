<?php
// اعوز بالله من الشياطين و ان يحضرون بسم الله الرحمن الرحيم الله لا إله إلا هو الحي القيوم
// Bismillahi ar-Rahmani ar-Rahim Audhu billahi min ash-shayatin wa an yahdurun Bismillah ar-Rahman ar-Rahim Allah la ilaha illa huwa al-hayy al-qayyum. Tamsa Allahu ala ayunihim
// version: x
// ======================================================
// - App Name: bismel1.com
// - Gusgraph LLC -
// - Author: Gus Kazem
// - https://Gusgraph.com
// - File Path: resources/views/errors/layout.blade.php
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
            --error-bg: #06111f;
            --error-surface: rgba(12, 24, 41, 0.88);
            --error-line: rgba(141, 177, 222, 0.18);
            --error-line-strong: rgba(112, 182, 255, 0.34);
            --error-text: #ebf3ff;
            --error-muted: #91a9c7;
            --error-accent: #68d5ff;
            --error-accent-strong: #8c7bff;
            --error-shadow: 0 23px 91px rgba(0, 0, 0, 0.34);
        }

        body {
            margin: 0;
            min-height: 100vh;
            color: var(--error-text);
            background:
                radial-gradient(circle at top left, rgba(104, 213, 255, 0.16), transparent 28%),
                radial-gradient(circle at top right, rgba(140, 123, 255, 0.18), transparent 24%),
                linear-gradient(180deg, #050b14 0%, #07111f 48%, #04080f 100%);
            font-family: var(--font-sans);
        }

        .error-page {
            min-height: 100vh;
            padding: 1.55rem;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .error-shell {
            width: min(100%, 1119px);
        }

        .error-header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 0.95rem;
            margin-bottom: 0.95rem;
            padding: 0.95rem 1.11rem;
            border: 1px solid var(--error-line);
            border-radius: 999px;
            background: rgba(8, 16, 28, 0.74);
            backdrop-filter: blur(18px);
            box-shadow: 0 19px 61px rgba(1, 6, 14, 0.34);
        }

        .error-brand {
            display: flex;
            align-items: center;
            gap: 0.85rem;
            text-decoration: none;
            color: var(--error-text);
        }

        .error-brand__mark {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            width: 4.5625rem;
            height: 4.5625rem;
            border-radius: 1.1875rem;
            background: transparent;
            border: none;
        }

        .error-brand__mark img {
            display: block;
            height: 4.5625rem;
            width: auto;
            object-fit: contain;
        }

        .error-brand__eyebrow,
        .error-card__eyebrow {
            margin: 0 0 0.12rem;
            font-size: 0.73rem;
            line-height: 1.19;
            text-transform: uppercase;
            letter-spacing: 0.16em;
            color: var(--error-muted);
        }

        .error-brand__title {
            margin: 0;
            font-size: 1rem;
            line-height: 1.2;
            letter-spacing: -0.03em;
            color: var(--error-text);
        }

        .error-actions {
            display: flex;
            flex-wrap: wrap;
            gap: 0.73rem;
        }

        .error-cta {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            min-height: 2.95rem;
            padding: 0.85rem 1.15rem;
            border-radius: 999px;
            border: 1px solid var(--error-line);
            text-decoration: none;
            color: var(--error-text);
            background: rgba(255, 255, 255, 0.02);
            transition: transform 140ms ease, border-color 140ms ease, background 140ms ease;
        }

        .error-cta:hover,
        .error-cta:focus-visible {
            transform: translateY(-1px);
            border-color: var(--error-line-strong);
            background: rgba(255, 255, 255, 0.06);
        }

        .error-cta--primary {
            background: linear-gradient(135deg, rgba(104, 213, 255, 0.22), rgba(140, 123, 255, 0.28));
            border-color: rgba(104, 213, 255, 0.36);
        }

        .error-card {
            display: grid;
            grid-template-columns: minmax(0, 1.19fr) minmax(279px, 0.81fr);
            gap: 1.35rem;
            padding: 1.95rem;
            border: 1px solid var(--error-line);
            border-radius: 27px;
            background: var(--error-surface);
            backdrop-filter: blur(18px);
            box-shadow: var(--error-shadow);
        }

        .error-card__status {
            margin: 0 0 0.75rem;
            font-size: clamp(3.35rem, 10vw, 7.1rem);
            line-height: 0.9;
            letter-spacing: -0.08em;
        }

        .error-card__title {
            margin: 0 0 0.95rem;
            font-size: clamp(1.95rem, 5vw, 3.11rem);
            line-height: 0.96;
            letter-spacing: -0.05em;
        }

        .error-card__body,
        .error-card__note,
        .error-card__list li {
            margin: 0;
            color: var(--error-muted);
            font-size: 0.95rem;
            line-height: 1.69;
        }

        .error-card__note {
            margin-top: 0.95rem;
        }

        .error-card__list {
            margin: 0.95rem 0 0;
            padding-left: 1.11rem;
        }

        .error-side {
            padding: 1.35rem;
            border: 1px solid rgba(255, 255, 255, 0.08);
            border-radius: 23px;
            background: rgba(255, 255, 255, 0.03);
        }

        .error-side__quote {
            margin: 0.45rem 0 0;
            color: var(--error-text);
            font-size: 1.05rem;
            line-height: 1.69;
        }

        .error-side__caption {
            margin: 0.95rem 0 0;
            color: var(--error-muted);
            font-size: 0.95rem;
            line-height: 1.6;
        }

        @media (max-width: 960px) {
            .error-card {
                grid-template-columns: 1fr;
            }

            .error-header {
                border-radius: 27px;
                align-items: flex-start;
                flex-direction: column;
            }
        }
    </style>
</head>
<body>
    <main class="error-page">
        <div class="error-shell">
            <header class="error-header">
                <a href="{{ route('home') }}" class="error-brand">
                    <span class="error-brand__mark">
                        <img src="{{ asset('images/logo.png') }}" alt="Bismel1 logo" aria-hidden="true" />
                    </span>
                    <span>
                        <p class="error-brand__eyebrow">Public access</p>
                        <p class="error-brand__title">Bismel1</p>
                    </span>
                </a>
                <nav class="error-actions" aria-label="Error page navigation">
                    <a href="{{ route('home') }}" class="error-cta">Home</a>
                    <a href="{{ route('plans') }}" class="error-cta">Plans</a>
                    <a href="{{ route('login') }}" class="error-cta error-cta--primary">Login</a>
                </nav>
            </header>

            <section class="error-card" aria-labelledby="error-title">
                <article>
                    <p class="error-card__eyebrow">@yield('eyebrow', 'System response')</p>
                    <p class="error-card__status">@yield('code', 'Error')</p>
                    <h1 id="error-title" class="error-card__title">@yield('message', 'Something stepped out of line.')</h1>
                    <p class="error-card__body">@yield('body', 'The request did not finish the way it should have.')</p>
                    <ul class="error-card__list">
                        <li>@yield('tip_one', 'Return to the main page and try the step again.')</li>
                        <li>@yield('tip_two', 'If the issue keeps repeating, use a simpler path such as Home or Login first.')</li>
                    </ul>
                    <p class="error-card__note">@yield('note', 'Bismel1 keeps the message clear here and keeps the internals behind the curtain.')</p>
                </article>

                <aside class="error-side">
                    <p class="error-card__eyebrow">Juha moment</p>
                    <p class="error-side__quote">@yield('juha_quote', 'Juha asked the market for directions. The market said, “First decide where you were trying to go.”')</p>
                    <p class="error-side__caption">@yield('juha_caption', 'A short smile, then back to the clean path forward.')</p>
                </aside>
            </section>
        </div>
    </main>
</body>
</html>
