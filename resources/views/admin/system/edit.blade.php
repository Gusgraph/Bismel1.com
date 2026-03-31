<?php
// اعوز بالله من الشياطين و ان يحضرون بسم الله الرحمن الرحيم الله لا إله إلا هو الحي القيوم
// Bismillahi ar-Rahmani ar-Rahim Audhu billahi min ash-shayatin wa an yahdurun Bismillah ar-Rahman ar-Rahim Allah la ilaha illa huwa al-hayy al-qayyum. Tamsa Allahu ala ayunihim
// version: x
// ======================================================
// - App Name: bismel1.com
// - Gusgraph LLC -
// - Author: Gus Kazem
// - https://Gusgraph.com
// - File Path: resources/views/admin/system/edit.blade.php
// ======================================================
?>
@extends('layouts.app')

@section('title', 'Edit Platform Settings')

@section('content')
    @include('partials.ui.page-shell', ['headerPartial' => 'admin.partials.page-header', 'page' => $page])

    <div class="admin-page admin-page--system-edit">
        <section class="admin-page__hero">
            @include('partials.ui.info-card', ['title' => 'Platform Settings Form', 'body' => 'Update the runtime, review, and status settings that define current platform posture in the admin area.', 'symbol' => 'ﷻ'])
        </section>

        <div class="customer-form-page admin-form-page">
            <section class="admin-section">
                <header class="admin-section__header">
                    <div class="admin-section__heading">
                        @include('partials.ui.icon', ['icon' => 'fa-solid fa-sliders', 'tone' => 'violet', 'size' => 'lg'])
                        <div>
                            <p class="admin-section__eyebrow">Configuration</p>
                            <h2 class="admin-section__title">Runtime and review controls</h2>
                        </div>
                    </div>
                    <p class="admin-section__body">These fields keep the platform status readable and consistent while remaining limited to the supported runtime, review, and status values.</p>
                </header>

                <form class="ui-card ui-form-stack customer-form-card" method="POST" action="{{ route('admin.system.update') }}" enctype="multipart/form-data">
                    @csrf
                    @method('PUT')
                    <div class="ui-form-intro">
                        <div>
                            <p class="ui-form-intro__eyebrow">Admin system settings</p>
                            <h2 class="ui-form-intro__title">Runtime status controls</h2>
                        </div>
                        <p class="ui-form-intro__body">Keep these values compact and intentional so the current platform state stays easy to review.</p>
                    </div>

                    @if ($errors->any())
                        <section class="ui-form-alert ui-form-alert--error" aria-labelledby="admin-system-errors-title">
                            <p class="ui-form-alert__title" id="admin-system-errors-title">Please review the highlighted system fields.</p>
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
                                <p class="ui-form-section__eyebrow">Configuration</p>
                                <h3 class="ui-form-section__title">Runtime, review, and status</h3>
                            </div>
                            <p class="ui-form-section__body">Keep these values aligned with the current platform posture values.</p>
                        </div>

                        <div class="ui-form-grid">
                            @include('partials.ui.form-field', [
                                'name' => 'runtime_mode',
                                'label' => 'Runtime Mode',
                                'value' => old('runtime_mode', $form['runtime_mode'] ?? 'local'),
                                'help' => 'Example: review, paper, or live.',
                                'autocomplete' => 'off',
                            ])
                            @include('partials.ui.form-field', [
                                'name' => 'review_channel',
                                'label' => 'Review Channel',
                                'value' => old('review_channel', $form['review_channel'] ?? 'manual'),
                                'help' => 'Describe the review flow currently in place.',
                                'autocomplete' => 'off',
                            ])
                            @include('partials.ui.form-field', [
                                'name' => 'status_level',
                                'label' => 'Status Level',
                                'value' => old('status_level', $form['status_level'] ?? 'medium'),
                                'help' => 'Choose one of the supported status levels.',
                                'autocomplete' => 'off',
                            ])
                        </div>
                    </div>

                    <div class="ui-form-section">
                        <div class="ui-form-section__header">
                            <div>
                                <p class="ui-form-section__eyebrow">Branding Images</p>
                                <h3 class="ui-form-section__title">Header Image</h3>
                            </div>
                            <p class="ui-form-section__body">Upload one header or logo image for site branding.</p>
                        </div>

                    <div class="admin-header-image-grid">
                        <figure class="admin-header-image-preview" aria-live="polite">
                            @if ($headerImage = $form['header_image_preview'] ?? null)
                                <img src="{{ $headerImage }}" alt="Current header image">
                                <figcaption class="admin-header-image-preview__label">Current header image</figcaption>
                            @else
                                <div class="admin-header-image-preview__empty">No header image uploaded</div>
                            @endif
                        </figure>

                        <div class="admin-header-image-input">
                            <p class="admin-header-image-input__heading">Upload or replace your header image</p>
                            <div class="admin-header-image-input__controls">
                                <label for="header_image" class="admin-header-image-input__button" role="button">Upload image</label>
                                <span id="header-image-filename" class="admin-header-image-input__filename">No file selected</span>
                            </div>
                            <input id="header_image" name="header_image" type="file" accept="image/*" class="admin-header-image-input__file" onchange="document.getElementById('header-image-filename').textContent = this.files && this.files[0] ? this.files[0].name : 'No file selected';">
                            <p class="admin-header-image-input__help">JPEG or PNG only, max 2MB.</p>
                            @error('header_image')
                                <p class="guest-form-error" role="alert">{{ $message }}</p>
                            @enderror

                            @if (!empty($form['header_image_path']))
                                <label class="ui-checkbox admin-header-image-input__remove">
                                    <input name="remove_header_image" type="checkbox" value="1" {{ old('remove_header_image') ? 'checked' : '' }}>
                                    Remove current header image
                                </label>
                            @endif
                        </div>
                    </div>
                    </div>

                    @include('partials.ui.form-actions', [
                        'submitLabel' => 'Save platform settings',
                        'cancelRoute' => 'admin.system.index',
                        'cancelLabel' => 'Back to system',
                        'note' => 'This updates the current system settings record only.',
                    ])
                </form>
            </section>
        </div>
    </div>
@endsection
