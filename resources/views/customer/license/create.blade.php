<?php
// اعوز بالله من الشياطين و ان يحضرون بسم الله الرحمن الرحيم الله لا إله إلا هو الحي القيوم
// Bismillahi ar-Rahmani ar-Rahim Audhu billahi min ash-shayatin wa an yahdurun Bismillah ar-Rahman ar-Rahim Allah la ilaha illa huwa al-hayy al-qayyum. Tamsa Allahu ala ayunihim
// version: x
// ======================================================
// - App Name: bismel1.com
// - Gusgraph LLC -
// - Author: Gus Kazem
// - https://Gusgraph.com
// - File Path: resources/views/customer/license/create.blade.php
// ======================================================
?>
@extends('layouts.app')

@section('title', 'Add License Access')

@section('content')
    @include('partials.ui.page-shell', ['headerPartial' => 'customer.partials.page-header', 'page' => $page])

    <div class="customer-form-page">
        <section class="customer-page__hero">
            @include('partials.ui.info-card', ['title' => 'Add license access', 'body' => 'Save a license and API key for this workspace. The key stays masked after save, and only safe identifying details remain visible.', 'symbol' => 'ﷻ'])
        </section>

        <form class="ui-card ui-form-stack customer-form-card" method="POST" action="{{ route('customer.license.store') }}">
            @csrf

            <div class="ui-form-intro">
                <div>
                    <p class="ui-form-intro__eyebrow">License access</p>
                    <h2 class="ui-form-intro__title">Add license and API key details</h2>
                </div>
                <p class="ui-form-intro__body">Use clear labels so the saved license and key stay easy to recognize without exposing secret values.</p>
            </div>

            @if ($errors->any())
                <section class="ui-form-alert ui-form-alert--error" aria-labelledby="customer-license-errors-title">
                    <p class="ui-form-alert__title" id="customer-license-errors-title">Please correct the highlighted fields.</p>
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
                        <p class="ui-form-section__eyebrow">Naming</p>
                        <h3 class="ui-form-section__title">License and key labels</h3>
                    </div>
                    <p class="ui-form-section__body">These labels help you identify the saved access later while key values remain hidden.</p>
                </div>

                <div class="ui-form-grid">
                    @include('partials.ui.form-field', [
                        'name' => 'license_name',
                        'label' => 'License Name',
                        'value' => old('license_name', 'Primary License'),
                        'help' => 'Use a readable license label for this workspace.',
                        'autocomplete' => 'off',
                    ])
                    @include('partials.ui.form-field', [
                        'name' => 'key_name',
                        'label' => 'API Key Name',
                        'value' => old('key_name', 'Primary API Key'),
                        'help' => 'Use a clear name that identifies the key without exposing secret material.',
                        'autocomplete' => 'off',
                    ])
                </div>
            </div>

            <div class="ui-form-section">
                <div class="ui-form-section__header">
                    <div>
                        <p class="ui-form-section__eyebrow">Security</p>
                        <h3 class="ui-form-section__title">Token and lifecycle</h3>
                    </div>
                    <p class="ui-form-section__body">Add the token value and optionally note when it should expire.</p>
                </div>

                <div class="ui-form-grid">
                    @include('partials.ui.form-field', [
                        'name' => 'token_value',
                        'label' => 'Token Value',
                        'type' => 'password',
                        'value' => '',
                        'placeholder' => 'api-token',
                        'help' => 'This token is stored securely and later shown only as a masked suffix.',
                        'autocomplete' => 'new-password',
                    ])
                    @include('partials.ui.form-field', [
                        'name' => 'expires_at',
                        'label' => 'Expiry Date',
                        'type' => 'date',
                        'value' => old('expires_at', ''),
                        'help' => 'Leave blank when no expiry date needs to be tracked.',
                    ])
                </div>

                <p class="customer-form-note">Masked-only display after save <span aria-hidden="true">ﷺ</span></p>
            </div>

            @include('partials.ui.form-actions', [
                'submitLabel' => 'Save license access',
                'cancelRoute' => 'customer.license.index',
                'cancelLabel' => 'Back to license',
                'note' => 'Saving here adds one license and API key for this workspace while keeping the key masked after save.',
            ])
        </form>
    </div>
@endsection
