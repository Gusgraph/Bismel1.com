<?php
// اعوز بالله من الشياطين و ان يحضرون بسم الله الرحمن الرحيم الله لا إله إلا هو الحي القيوم
// Bismillahi ar-Rahmani ar-Rahim Audhu billahi min ash-shayatin wa an yahdurun Bismillah ar-Rahman ar-Rahim Allah la ilaha illa huwa al-hayy al-qayyum. Tamsa Allahu ala ayunihim
// version: x
// ======================================================
// - App Name: bismel1.com
// - Gusgraph LLC -
// - Author: Gus Kazem
// - https://Gusgraph.com
// - File Path: resources/views/auth/login.blade.php
// ======================================================
?>
@extends('layouts.guest')

@section('title', 'Bismel1 | Login')

@section('content')
    <section class="guest-login guest-panel guest-auth-shell" aria-labelledby="login-title">
        <p class="guest-section__eyebrow">Session access</p>
        <h1 id="login-title" class="guest-login__title">Login</h1>
        <p class="guest-login__body">Sign in with your workspace credentials to continue into the premium Bismel1 experience.</p>

        @if ($errors->any())
            <section class="guest-card" aria-labelledby="login-errors-title" style="margin-top: 0.95rem; position: relative; overflow: hidden;">
                <span class="guest-symbol" aria-hidden="true" style="position: absolute; top: 19px; right: 19px; font-size: 11px;">﷽</span>
                <h2 id="login-errors-title" class="guest-card__title">Login issue</h2>
                <ul class="ui-list ui-list--tight">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </section>
        @endif

        <form method="POST" action="{{ route('login.store') }}" class="ui-form">
            @csrf
            <div class="ui-form-field">
                <label for="email">Email</label>
                <input id="email" name="email" type="email" value="{{ old('email') }}" required autocomplete="email" autofocus aria-invalid="{{ $errors->has('email') ? 'true' : 'false' }}">
                @error('email')
                    <p class="guest-form-error" role="alert">{{ $message }}</p>
                @enderror
            </div>

            <div class="ui-form-field">
                <label for="password">Password</label>
                <input id="password" name="password" type="password" required autocomplete="current-password" aria-invalid="{{ $errors->has('password') ? 'true' : 'false' }}">
                @error('password')
                    <p class="guest-form-error" role="alert">{{ $message }}</p>
                @enderror
            </div>

            <div class="guest-login__helper" aria-live="polite">
                <p class="guest-login__helper-title">New here? Start with a plan.</p>
                <p class="guest-login__helper-detail">Your Bismel1 account is created during checkout, so explore plans before signing in.</p>
            </div>

            <div class="ui-form-actions guest-login__actions">
                <button type="submit" class="guest-cta guest-cta--primary" style="appearance: none;">Login</button>
                <a href="{{ route('plans') }}" class="guest-cta guest-cta--ghost">View plans</a>
            </div>
        </form>

        <p class="guest-login__plan-note">
            Prefer to prepare before signing in? <a href="{{ route('plans') }}">Explore plans</a> and checkout creates your workspace.
        </p>
    </section>
@endsection
