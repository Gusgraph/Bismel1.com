<?php
// اعوز بالله من الشياطين و ان يحضرون بسم الله الرحمن الرحيم الله لا إله إلا هو الحي القيوم
// Bismillahi ar-Rahmani ar-Rahim Audhu billahi min ash-shayatin wa an yahdurun Bismillah ar-Rahman ar-Rahim Allah la ilaha illa huwa al-hayy al-qayyum. Tamsa Allahu ala ayunihim
// version: x
// ======================================================
// - App Name: bismel1.com
// - Gusgraph LLC -
// - Author: Gus Kazem
// - https://Gusgraph.com
// - File Path: resources/views/auth/signup.blade.php
// ======================================================
?>
@extends('layouts.guest')

@section('title', 'Bismel1 | Signup')

@section('content')
    <section class="guest-login guest-panel guest-auth-shell" aria-labelledby="signup-title">
        <p class="guest-section__eyebrow">Create account</p>
        <h1 id="signup-title" class="guest-login__title">Signup</h1>
        <p class="guest-login__body">
            Create your Bismel1 workspace and continue into your selected plan.
            @if (!empty($selectedPlan?->name))
                You are starting with <strong>{{ $selectedPlan->name }}</strong>.
            @endif
        </p>

        @if ($errors->any())
            <section class="guest-card" aria-labelledby="signup-errors-title" style="margin-top: 0.95rem; position: relative; overflow: hidden;">
                <span class="guest-symbol" aria-hidden="true" style="position: absolute; top: 19px; right: 19px; font-size: 11px;">ﷻ</span>
                <h2 id="signup-errors-title" class="guest-card__title">Signup issue</h2>
                <ul class="ui-list ui-list--tight">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </section>
        @endif

        <form method="POST" action="{{ route('signup.store') }}" class="ui-form">
            @csrf
            @if (!empty($selectedPlanCode))
                <input type="hidden" name="selected_plan_code" value="{{ $selectedPlanCode }}">
            @endif
            <div class="ui-form-field">
                <label for="name">Your name</label>
                <input id="name" name="name" type="text" value="{{ old('name') }}" required autocomplete="name" autofocus aria-invalid="{{ $errors->has('name') ? 'true' : 'false' }}">
                @error('name')
                    <p class="guest-form-error" role="alert">{{ $message }}</p>
                @enderror
            </div>

            <div class="ui-form-field">
                <label for="workspace_name">Workspace name</label>
                <input id="workspace_name" name="workspace_name" type="text" value="{{ old('workspace_name') }}" required autocomplete="organization" aria-invalid="{{ $errors->has('workspace_name') ? 'true' : 'false' }}">
                @error('workspace_name')
                    <p class="guest-form-error" role="alert">{{ $message }}</p>
                @enderror
            </div>

            <div class="ui-form-field">
                <label for="email">Email</label>
                <input id="email" name="email" type="email" value="{{ old('email') }}" required autocomplete="email" aria-invalid="{{ $errors->has('email') ? 'true' : 'false' }}">
                @error('email')
                    <p class="guest-form-error" role="alert">{{ $message }}</p>
                @enderror
            </div>

            <div class="ui-form-field">
                <label for="password">Password</label>
                <input id="password" name="password" type="password" required autocomplete="new-password" aria-invalid="{{ $errors->has('password') ? 'true' : 'false' }}">
                @error('password')
                    <p class="guest-form-error" role="alert">{{ $message }}</p>
                @enderror
            </div>

            <div class="ui-form-field">
                <label for="password_confirmation">Confirm password</label>
                <input id="password_confirmation" name="password_confirmation" type="password" required autocomplete="new-password" aria-invalid="{{ $errors->has('password') ? 'true' : 'false' }}">
            </div>

            @if (($selectedPlan?->plan_type ?? null) === 'addon')
                <div class="ui-form-field">
                    <label for="selected_base_plan_code">Base plan</label>
                    <select id="selected_base_plan_code" name="selected_base_plan_code" required aria-invalid="{{ $errors->has('selected_base_plan_code') ? 'true' : 'false' }}">
                        <option value="">Choose a base plan</option>
                        @foreach (($basePlans ?? []) as $plan)
                            <option value="{{ $plan->code }}" @selected(old('selected_base_plan_code') === $plan->code)>{{ $plan->name }}</option>
                        @endforeach
                    </select>
                    @error('selected_base_plan_code')
                        <p class="guest-form-error" role="alert">{{ $message }}</p>
                    @enderror
                </div>
            @endif

            <div class="ui-form-actions guest-login__actions">
                <button type="submit" class="guest-cta guest-cta--primary" style="appearance: none; display: inline-flex; align-items: center; justify-content: center; cursor: pointer; pointer-events: auto; position: relative; z-index: 2; border: 0;">Create account</button>
                <a href="{{ route('login') }}" class="guest-cta">Login</a>
            </div>
        </form>
    </section>
@endsection
