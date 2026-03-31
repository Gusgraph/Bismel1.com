<?php
// اعوز بالله من الشياطين و ان يحضرون بسم الله الرحمن الرحيم الله لا إله إلا هو الحي القيوم
// Bismillahi ar-Rahmani ar-Rahim Audhu billahi min ash-shayatin wa an yahdurun Bismillah ar-Rahman ar-Rahim Allah la ilaha illa huwa al-hayy al-qayyum. Tamsa Allahu ala ayunihim
// version: x
// ======================================================
// - App Name: gusgraph-trading
// - Gusgraph LLC -
// - Author: Gus Kazem
// - https://Gusgraph.com
// - File Path: resources/views/partials/ui/form-field.blade.php
// ======================================================
?>
@php
    $name = $name ?? 'field';
    $label = $label ?? ucfirst(str_replace('_', ' ', $name));
    $type = $type ?? 'text';
    $value = $value ?? old($name, '');
    $placeholder = $placeholder ?? null;
    $help = $help ?? null;
    $autocomplete = $autocomplete ?? null;
    $inputmode = $inputmode ?? null;
    $step = $step ?? null;
    $meta = $meta ?? null;
    $helpId = $help ? $name.'-help' : null;
    $errorId = $errors->has($name) ? $name.'-error' : null;
    $describedBy = trim(collect([$helpId, $errorId])->filter()->implode(' '));
@endphp

<div class="ui-form-field @error($name) ui-form-field--invalid @enderror">
    <div class="ui-form-field__header">
        <label class="ui-form-field__label" for="{{ $name }}">{{ $label }}</label>
        @if ($meta)
            <p class="ui-form-field__meta">{{ $meta }}</p>
        @endif
    </div>
    <input
        class="ui-input @error($name) ui-input--invalid @enderror"
        id="{{ $name }}"
        name="{{ $name }}"
        type="{{ $type }}"
        value="{{ $value }}"
        aria-invalid="@error($name) true @else false @enderror"
        @if ($describedBy !== '') aria-describedby="{{ $describedBy }}" @endif
        @if ($placeholder) placeholder="{{ $placeholder }}" @endif
        @if ($autocomplete) autocomplete="{{ $autocomplete }}" @endif
        @if ($inputmode) inputmode="{{ $inputmode }}" @endif
        @if ($step) step="{{ $step }}" @endif
    >
    @if ($help)
        <small class="ui-field-help" id="{{ $helpId }}">{{ $help }}</small>
    @endif
    @error($name)
        <small class="ui-field-error" id="{{ $errorId }}">{{ $message }}</small>
    @enderror
</div>
