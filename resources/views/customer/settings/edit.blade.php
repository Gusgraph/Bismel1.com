<?php
// اعوز بالله من الشياطين و ان يحضرون بسم الله الرحمن الرحيم الله لا إله إلا هو الحي القيوم
// Bismillahi ar-Rahmani ar-Rahim Audhu billahi min ash-shayatin wa an yahdurun Bismillah ar-Rahman ar-Rahim Allah la ilaha illa huwa al-hayy al-qayyum. Tamsa Allahu ala ayunihim
// version: x
// ======================================================
// - App Name: bismel1.com
// - Gusgraph LLC -
// - Author: Gus Kazem
// - https://Gusgraph.com
// - File Path: resources/views/customer/settings/edit.blade.php
// ======================================================
?>
@extends('layouts.app')

@section('title', 'Edit Profile Settings')

@section('content')
    @include('partials.ui.page-shell', [
        'headerPartial' => 'customer.partials.page-header',
        'page' => $page,
        'summary' => [
            'eyebrow' => 'Profile update',
            'title' => 'Update your profile',
            'body' => 'Keep the visible account details used across your workspace current. Broker access, billing, and protected credentials are not changed here.',
            'icon' => 'fa-solid fa-id-card',
            'tone' => 'amber',
        ],
    ])

    <div class="customer-form-page">
        <form class="ui-card ui-form-stack customer-form-card" method="POST" action="{{ route('customer.settings.update') }}">
            @csrf
            @method('PUT')

            <div class="ui-form-intro">
                <div>
                    <p class="ui-form-intro__eyebrow">Customer settings</p>
                    <h2 class="ui-form-intro__title">Profile details</h2>
                </div>
                <p class="ui-form-intro__body">Keep your visible profile details current so the workspace stays clear and recognizable.</p>
            </div>

            @if ($errors->any())
                <section class="ui-form-alert ui-form-alert--error" aria-labelledby="customer-settings-errors-title">
                    <p class="ui-form-alert__title" id="customer-settings-errors-title">Please review the highlighted fields.</p>
                    <ul class="ui-form-alert__list">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </section>
            @endif

            <div class="ui-form-section">
                <div class="ui-form-section__header">
                    <div>
                        <p class="ui-form-section__eyebrow">Profile</p>
                        <h3 class="ui-form-section__title">Identity and contact</h3>
                    </div>
                    <p class="ui-form-section__body">These details shape how your account appears throughout the customer experience.</p>
                </div>

                <div class="ui-form-grid">
                    @include('partials.ui.form-field', [
                        'name' => 'name',
                        'label' => 'Display Name',
                        'value' => old('name', $form['name'] ?? ''),
                        'help' => 'Use the name that should appear across the customer workspace.',
                        'autocomplete' => 'name',
                    ])
                    @include('partials.ui.form-field', [
                        'name' => 'email',
                        'label' => 'Email Address',
                        'type' => 'email',
                        'value' => old('email', $form['email'] ?? ''),
                        'help' => 'Use the current contact email for this account.',
                        'autocomplete' => 'email',
                    ])
                </div>
            </div>

            @include('partials.ui.form-actions', [
                'submitLabel' => 'Save profile settings',
                'cancelRoute' => 'customer.settings.index',
                'cancelLabel' => 'Back to settings',
                'note' => 'Only your visible profile details are updated here.',
            ])
        </form>
    </div>
@endsection
