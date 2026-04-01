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
   @vite(['resources/css/app.css', 'resources/js/app.js', 'resources/js/market-background.js'])
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
            content: ;
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
            /* Removed max-width and margin: 0 auto; */
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

        .guest-page small[aria-hidden=true],
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

        .guest-hero {
            display: grid;
            grid-template-columns: minmax(0, 1.19fr) minmax(279px, 0.81fr);
            gap: 1.35rem;
            align-items: stretch;
            margin-bottom: 1.55rem;
        }

        .guest-panel,
        .guest-card,
        .guest-story,
        .guest-tape {
            border: 1px solid var(--guest-line);
            background: var(--guest-surface);
            border-radius: var(--guest-radius);
            box-shadow: var(--guest-shadow);
            backdrop-filter: blur(18px);
        }

        .guest-plan-flow {
            padding: 15px 19px;
            margin-bottom: 0.95rem;
            border-radius: 19px;
            border: 1px solid rgba(255, 255, 255, 0.1);
            background: rgba(255, 255, 255, 0.03);
        }

        .guest-plan-flow__steps {
            margin: 0;
            padding-left: 1.2rem;
            display: grid;
            gap: 0.95rem;
            color: var(--guest-muted);
            line-height: 1.6;
            list-style: decimal;
            list-style-position: inside;
        }

        .guest-panel {
            padding: 1.95rem;
            position: relative;
            overflow: hidden;
        }

        .guest-panel::after {
            content: ;
            position: absolute;
            inset: auto auto -3.95rem -3.95rem;
            width: 15rem;
            height: 15rem;
            border-radius: 999px;
            background: radial-gradient(circle, rgba(104, 213, 255, 0.11), transparent 70%);
            pointer-events: none;
            z-index: 0;
        }

        .guest-section__eyebrow {
            margin-bottom: 0.55rem;
        }

        .guest-hero__title,
        .guest-login__title {
            margin: 0 0 0.95rem;
            font-size: clamp(2.55rem, 6vw, 4.95rem);
            line-height: 0.95;
            letter-spacing: -0.06em;
            max-width: 11ch;
        }

        .guest-hero__body,
        .guest-card__body,
        .guest-story__body,
        .guest-login__body {
            margin: 0;
            color: var(--guest-muted);
            font-size: 0.95rem;
            line-height: 1.69;
        }

        .guest-hero__split {
            display: grid;
            grid-template-columns: repeat(3, minmax(0, 1fr));
            gap: 0.85rem;
            margin: 1.55rem 0 1.35rem;
        }

        .guest-pill {
            padding: 0.95rem 0.95rem;
            border-radius: 1.11rem;
            background: rgba(255, 255, 255, 0.03);
            border: 1px solid rgba(255, 255, 255, 0.07);
        }

        .guest-pill__label {
            display: block;
            margin-bottom: 0.35rem;
            font-size: 0.74rem;
            letter-spacing: 0.11em;
            text-transform: uppercase;
            color: var(--guest-muted);
        }

        .guest-pill__value {
            display: block;
            font-size: 1.11rem;
            letter-spacing: -0.03em;
            color: var(--guest-text);
        }

        .guest-hero__actions,
        .guest-login__actions {
            display: flex;
            flex-wrap: wrap;
            gap: 0.85rem;
            margin-top: 1.35rem;
        }

        .guest-cta,
        .guest-story__link {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 0.45rem;
            min-height: 3rem;
            padding: 0.85rem 1.15rem;
            border-radius: 999px;
            border: 1px solid var(--guest-line);
            text-decoration: none;
            color: var(--guest-text);
            background: rgba(255, 255, 255, 0.02);
            transition: transform 140ms ease, border-color 140ms ease, background 140ms ease;
        }

        .guest-card-grid {
            display: grid;
            grid-template-columns: repeat(3, minmax(0, 1fr));
            gap: 0.95rem;
            margin-bottom: 0.95rem;
        }

        .guest-card {
            padding: 1.35rem;
        }

        .guest-top-notice {
            margin-bottom: 1.19rem;
            border-width: 1px;
            border-style: solid;
            background: linear-gradient(180deg, rgba(17, 30, 48, 0.98), rgba(11, 20, 34, 0.95));
        }

        .guest-top-notice--success {
            border-color: rgba(74, 222, 128, 0.46);
            box-shadow: 0 19px 61px rgba(16, 185, 129, 0.13);
        }

        .guest-top-notice--warning {
            border-color: rgba(251, 191, 36, 0.44);
            box-shadow: 0 19px 61px rgba(245, 158, 11, 0.13);
        }

        .guest-top-notice--danger {
            border-color: rgba(251, 113, 133, 0.45);
            box-shadow: 0 19px 61px rgba(244, 63, 94, 0.13);
        }

        .guest-card__title,
        .guest-story__title,
        .guest-login__title {
            margin: 0 0 0.75rem;
            font-size: 1.19rem;
            letter-spacing: -0.04em;
            color: var(--guest-text);
        }

        .guest-card__list,
        .guest-story__list,
        .guest-plans__list {
            list-style: none;
            padding: 0;
            margin: 0.95rem 0 0;
            display: grid;
            gap: 0.55rem;
        }

        .guest-card__list li,
        .guest-story__list li,
        .guest-plans__list li {
            position: relative;
            padding-left: 0.95rem;
            color: var(--guest-muted);
        }

        .guest-card__list li::before,
        .guest-story__list li::before,
        .guest-plans__list li::before {
            content: ;
            position: absolute;
            top: 0.66rem;
            left: 0;
            width: 0.35rem;
            height: 0.35rem;
            border-radius: 999px;
            background: linear-gradient(135deg, var(--guest-accent), var(--guest-accent-strong));
            box-shadow: 0 0 19px rgba(104, 213, 255, 0.44);
        }

        .guest-story-wrap {
            display: grid;
            grid-template-columns: minmax(0, 1.15fr) minmax(0, 0.85fr);
            gap: 0.95rem;
            margin: 0.95rem 0 1.35rem;
        }

        .guest-story {
            padding: 1.55rem;
        }

        .guest-story__label {
            display: inline-flex;
            align-items: center;
            gap: 0.55rem;
            padding: 0.35rem 0.75rem;
            margin-bottom: 0.95rem;
            border-radius: 999px;
            border: 1px solid rgba(251, 191, 36, 0.22);
            background: rgba(251, 191, 36, 0.08);
            color: #fde68a;
            font-size: 0.73rem;
            letter-spacing: 0.08em;
            text-transform: uppercase;
        }

        .guest-plan-grid {
            display: grid;
            grid-template-columns: repeat(3, minmax(0, 1fr));
            gap: 0.95rem;
            margin-bottom: 1.35rem;
        }

        .guest-plan {
            padding: 1.35rem;
            border-radius: 1.45rem;
            border: 1px solid rgba(255, 255, 255, 0.08);
            background: var(--guest-surface-strong);
        }

        .guest-plan__header {
            display: flex;
            align-items: flex-start;
            justify-content: space-between;
            gap: 0.75rem;
            margin-bottom: 0.95rem;
        }

        .guest-plan__title {
            margin: 0 0 0.35rem;
            font-size: 1.11rem;
            line-height: 1.19;
            letter-spacing: -0.03em;
            color: var(--guest-text);
        }

        .guest-plan__price {
            margin: 0 0 0.95rem;
            font-size: 1.25rem;
            font-weight: 700;
            color: var(--guest-text);
        }

        .guest-plan--featured {
            border-color: rgba(104, 213, 255, 0.42);
            background: linear-gradient(180deg, rgba(28, 49, 81, 0.98), rgba(17, 32, 54, 0.96));
            box-shadow: 0 27px 93px rgba(6, 12, 24, 0.56);
        }

        .guest-plan--addon {
            border-style: dashed;
            background: rgba(255, 255, 255, 0.01);
            opacity: 0.8;
        }

        .guest-plan--demo {
            border-color: rgba(255, 255, 255, 0.12);
            background: rgba(255, 255, 255, 0.02);
            opacity: 0.94;
        }

        .guest-plans-intro {
            margin-bottom: 1.35rem;
            padding: 15px 19px;
            border-radius: 19px;
            border: 1px solid rgba(255, 255, 255, 0.08);
            background: rgba(255, 255, 255, 0.04);
        }

        .guest-plans-intro h1 {
            margin: 0;
            font-size: clamp(1.85rem, 4vw, 2.6rem);
        }

        .guest-plans-intro p {
            margin: 11px 0 0;
            color: var(--guest-muted);
        }

        .guest-plans-comparison__header {
            margin-bottom: 1.35rem;
        }

        .guest-plans-comparison__grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(210px, 1fr));
            gap: 0.95rem;
            margin-bottom: 1.35rem;
        }

        .guest-plans-comparison__plan {
            min-height: 100%;
            border-radius: 19px;
            padding: 1.35rem;
            border: 1px solid rgba(255, 255, 255, 0.09);
            background: rgba(255, 255, 255, 0.02);
            display: flex;
            flex-direction: column;
            justify-content: space-between;
            gap: 0.95rem;
        }

        .guest-plans-comparison__plan:hover {
            border-color: rgba(104, 213, 255, 0.35);
            box-shadow: 0 19px 45px rgba(3, 7, 18, 0.4);
        }

        .guest-plan__subtitle {
            margin: 0;
            color: var(--guest-muted);
            font-size: 0.94rem;
        }

        .guest-plans-comparison__features {
            margin: 0;
            padding: 0;
            list-style: none;
            display: grid;
            gap: 0.45rem;
            color: var(--guest-muted);
            font-size: 0.9rem;
        }

        .guest-plans-comparison__features li::before {
            display: inline-block;
            content: ;
            margin-right: 0.45rem;
            color: var(--guest-accent);
        }

        .guest-plans-comparison__cta {
            align-self: flex-start;
        }

        .guest-plan__tag {
            display: inline-flex;
            align-items: center;
            min-height: 1.95rem;
            padding: 0.35rem 0.75rem;
            border-radius: 999px;
            border: 1px solid rgba(255, 255, 255, 0.1);
            color: var(--guest-text);
            background: rgba(255, 255, 255, 0.04);
            font-size: 0.75rem;
            text-transform: uppercase;
            letter-spacing: 0.09em;
        }

        .guest-plan__body {
            margin: 0;
            color: var(--guest-muted);
            line-height: 1.64;
        }

        .guest-tape {
            position: relative;
            overflow: hidden;
            padding: 0.95rem 0;
        }

        .guest-tape__header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 0.95rem;
            padding: 0 1.35rem 0.85rem;
        }

        .guest-tape__subtle {
            margin: 0;
            color: var(--guest-muted);
            font-size: 0.89rem;
        }

        .guest-tape__track {
            display: flex;
            width: max-content;
            animation: guestTapeScroll 34s linear infinite;
        }

        .guest-tape:hover .guest-tape__track {
            animation-play-state: paused;
        }

        .guest-tape__group {
            display: flex;
            align-items: stretch;
            gap: 0.85rem;
            padding: 0 0.85rem;
        }

        .guest-tape__item {
            display: grid;
            grid-template-columns: auto auto auto;
            gap: 0.75rem;
            align-items: center;
            min-width: 231px;
            padding: 0.95rem 0.95rem;
            border-radius: 0.95rem;
            border: 1px solid rgba(255, 255, 255, 0.08);
            background: rgba(255, 255, 255, 0.03);
            color: var(--guest-text);
        }

        .guest-tape__symbol {
            font-weight: 700;
            letter-spacing: -0.02em;
        }

        .guest-tape__company,
        .guest-tape__price {
            color: var(--guest-muted);
            font-size: 0.89rem;
        }

        .guest-tape__move {
            justify-self: end;
            font-weight: 700;
        }

        .is-up {
            color: var(--guest-success);
        }

        .is-flat {
            color: var(--guest-warning);
        }

        .is-down {
            color: var(--guest-danger);
        }

        .guest-login {
            max-width: 579px;
            margin: 1.25rem auto 0;
            padding: 1.7rem;
        }

        .guest-auth-shell {
            position: relative;
        }

        .guest-auth-shell::before {
            content: ;
            position: absolute;
            inset: 19px 19px auto auto;
            width: 73px;
            height: 73px;
            border-radius: 27px;
            border: 1px solid rgba(104, 213, 255, 0.11);
            background: linear-gradient(180deg, rgba(104, 213, 255, 0.05), rgba(140, 123, 255, 0.03));
            opacity: 0.9;
            pointer-events: none;
        }

        .guest-login__title {
            font-size: clamp(1.95rem, 5vw, 2.95rem);
        }

        .guest-login .ui-form-field label,
        .guest-login .ui-form-field input,
        .guest-login .ui-form-field select {
            color: var(--guest-text);
        }

        .guest-login .ui-form-field input,
        .guest-login .ui-form-field select {
            width: 100%;
            min-height: 2.95rem;
            padding: 0.85rem 0.95rem;
            border-radius: 0.95rem;
            border: 1px solid rgba(255, 255, 255, 0.12);
            background: rgba(255, 255, 255, 0.03);
        }

        .guest-login .ui-form-field input[aria-invalid=true],
        .guest-login .ui-form-field select[aria-invalid=true] {
            border-color: rgba(251, 113, 133, 0.72);
            box-shadow: 0 0 0 0.18rem rgba(251, 113, 133, 0.14);
            background: rgba(66, 15, 25, 0.28);
        }

        .guest-form-error {
            display: inline-flex;
            align-items: center;
            width: fit-content;
            max-width: 100%;
            padding: 0.45rem 0.75rem;
            border-radius: 999px;
            border: 1px solid rgba(251, 113, 133, 0.26);
            background: rgba(66, 15, 25, 0.48);
            color: #ffd6dc;
            font-size: 0.82rem;
            font-weight: 700;
            line-height: 1.35;
        }

        .guest-login .ui-form-actions button {
            min-height: 2.95rem;
            padding: 0.85rem 1.11rem;
            border: 1px solid rgba(104, 213, 255, 0.32);
            border-radius: 999px;
            color: var(--guest-text);
            background: linear-gradient(135deg, rgba(104, 213, 255, 0.2), rgba(140, 123, 255, 0.24));
            cursor: pointer;
        }

        .guest-login__helper {
            margin: 19px 0 15px;
            padding: 15px;
            border-radius: 15px;
            background: rgba(255, 255, 255, 0.04);
            border: 1px solid rgba(255, 255, 255, 0.08);
            color: var(--guest-muted);
        }

        .guest-login__helper-title {
            margin: 0 0 5px;
            font-size: 1rem;
            letter-spacing: 0.02em;
            font-weight: 600;
            color: var(--guest-text);
        }

        .guest-login__helper-detail {
            margin: 0;
            font-size: 0.95rem;
            line-height: 1.55;
        }

        .guest-login__plan-note {
            margin-top: 19px;
            font-size: 0.9rem;
            color: var(--guest-muted);
        }

        .guest-login__plan-note a {
            color: var(--guest-accent);
            font-weight: 600;
            text-decoration: none;
        }

        .guest-cta--ghost {
            background: rgba(255, 255, 255, 0.04);
            border-color: rgba(255, 255, 255, 0.2);
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

        @keyframes guestTapeScroll {
            from {
                transform: translateX(0);
            }

            to {
                transform: translateX(-50%);
            }
        }

        @media (max-width: 960px) {
            .guest-hero,
            .guest-card-grid,
            .guest-story-wrap,
            .guest-plan-grid {
                grid-template-columns: 1fr;
            }

            .guest-hero__split {
                grid-template-columns: 1fr;
            }

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

            .guest-panel,
            .guest-card,
            .guest-story,
            .guest-login {
                padding: 1.19rem;
            }

            .guest-nav {
                width: 100%;
                justify-content: space-between;
            }

            .guest-nav__link {
                flex: 1 1 0;
            }

            .guest-tape__item {
                min-width: 199px;
            }

            .guest-auth-shell::before {
                inset: 15px 15px auto auto;
                width: 27px;
                height: 27px;
                border-radius: 15px;
            }
        }
    </style>
</head>
<body class="guest-body">
        <div class="market-bg-shell" aria-hidden="true">
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
                <p>Bismel1 brings AI market context, disciplined automation, and operator-grade clarity into one product.</p>
                <p>Login and plans stay simple here. The trading workspace starts after sign-in.</p>
            </div>
        </footer>
    </div>
</body>
</html>
