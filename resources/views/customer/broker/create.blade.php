<?php
// اعوز بالله من الشياطين و ان يحضرون بسم الله الرحمن الرحيم الله لا إله إلا هو الحي القيوم
// Bismillahi ar-Rahmani ar-Rahim Audhu billahi min ash-shayatin wa an yahdurun Bismillah ar-Rahman ar-Rahim Allah la ilaha illa huwa al-hayy al-qayyum. Tamsa Allahu ala ayunihim
// version: x
// ======================================================
// - App Name: bismel1.com
// - Gusgraph LLC -
// - Author: Gus Kazem
// - https://Gusgraph.com
// - File Path: resources/views/customer/broker/create.blade.php
// ======================================================
?>
@extends('layouts.app')

@section('title', 'Add Alpaca Connection')

@section('content')
    @include('partials.ui.page-shell', ['headerPartial' => 'customer.partials.page-header', 'page' => $page])

    <div class="customer-form-page">
        <section class="customer-page__hero">
            @include('partials.ui.info-card', ['title' => 'Connect Alpaca', 'body' => 'Save the Alpaca connection for this workspace, confirm whether it is paper or live, and return to the broker page to verify readiness. Secrets stay hidden after save and only masked identifiers remain visible.', 'symbol' => 'ﷺ'])
        </section>

        <form class="ui-card ui-form-stack customer-form-card" method="POST" action="{{ route('customer.broker.store') }}">
            @csrf

            <div class="ui-form-intro">
                <div>
                    <p class="ui-form-intro__eyebrow">Alpaca connection</p>
                    <h2 class="ui-form-intro__title">Add a linked Alpaca account</h2>
                </div>
                <p class="ui-form-intro__body">Use a clear account label, choose paper or live mode intentionally, and keep the saved connection easy to review later.</p>
            </div>

            @if ($errors->any())
                <section class="ui-form-alert ui-form-alert--error" aria-labelledby="customer-broker-errors-title">
                    <p class="ui-form-alert__title" id="customer-broker-errors-title">Please correct the fields below.</p>
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
                        <p class="ui-form-section__eyebrow">Connection setup</p>
                        <h3 class="ui-form-section__title">Provider and labeling</h3>
                    </div>
                    <p class="ui-form-section__body">Start with the readable connection details that help identify this broker record.</p>
                </div>

                <div class="ui-form-grid">
                    @include('partials.ui.form-field', [
                        'name' => 'provider',
                        'label' => 'Provider',
                        'value' => old('provider', 'alpaca'),
                        'help' => 'This flow is Alpaca-first and stores one provider value only.',
                        'meta' => 'Required',
                    ])
                    @include('partials.ui.form-field', [
                        'name' => 'account_label',
                        'label' => 'Connection Label',
                        'value' => old('account_label', 'Primary Alpaca Account'),
                        'help' => 'Choose a readable label so you can recognize this linked account later.',
                        'autocomplete' => 'off',
                    ])
                    @include('partials.ui.form-field', [
                        'name' => 'access_mode',
                        'label' => 'Access Mode',
                        'value' => old('access_mode', 'read_only'),
                        'help' => 'Keep this aligned with how this Alpaca account should be used in the workspace.',
                        'autocomplete' => 'off',
                    ])
                    @include('partials.ui.form-field', [
                        'name' => 'environment',
                        'label' => 'Environment',
                        'value' => old('environment', 'paper'),
                        'help' => 'Choose paper for setup and validation, or live only when you mean to connect a live account.',
                        'autocomplete' => 'off',
                    ])
                    @include('partials.ui.form-field', [
                        'name' => 'market_data_feed',
                        'label' => 'Market Data Feed',
                        'value' => old('market_data_feed', 'iex'),
                        'help' => 'Choose the feed that matches this account connection so readiness stays clear later.',
                        'autocomplete' => 'off',
                    ])
                </div>

                <p class="customer-form-note">Choose paper or live intentionally, then confirm the saved mode on the broker page before enabling automation. <span aria-hidden="true">ﷻ</span></p>
            </div>

            <div class="ui-form-section">
                <div class="ui-form-section__header">
                    <div>
                        <p class="ui-form-section__eyebrow">Credentials</p>
                        <h3 class="ui-form-section__title">Saved access values</h3>
                    </div>
                    <p class="ui-form-section__body">Alpaca key material is stored securely and is only shown back as masked metadata after save.</p>
                </div>

                <div class="ui-form-grid ui-form-grid--single">
                    @include('partials.ui.form-field', [
                        'name' => 'access_key_id',
                        'label' => 'Access Key ID',
                        'value' => '',
                        'placeholder' => 'alpaca-key-id',
                        'help' => 'This value is stored securely and shown back only as a masked suffix.',
                        'autocomplete' => 'off',
                    ])
                    @include('partials.ui.form-field', [
                        'name' => 'access_secret',
                        'label' => 'Access Secret',
                        'type' => 'password',
                        'value' => '',
                        'placeholder' => 'alpaca-secret',
                        'help' => 'This secret is stored securely and is never shown back in raw form.',
                        'autocomplete' => 'new-password',
                    ])
                </div>
            </div>

            @include('partials.ui.form-actions', [
                'submitLabel' => 'Save Alpaca access',
                'cancelRoute' => 'customer.broker.index',
                'cancelLabel' => 'Back to broker',
                'note' => 'After save, return to Broker to confirm the linked account, verify paper or live mode, and check readiness before automation.',
            ])
        </form>
    </div>
@endsection
