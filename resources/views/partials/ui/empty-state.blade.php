@php
    $title = $title ?? 'Nothing is ready yet';
    $message = $message ?? 'This area will stay readable until enough product activity is available.';
@endphp

<section class="ui-card ui-empty-state">
    <h2 class="ui-empty-state__title"><strong>{{ $title }}</strong> <small aria-hidden="true">ﷻ</small></h2>
    <p>{{ $message }}</p>
    <p><small>This space will update automatically as the relevant account or platform activity becomes available.</small></p>
</section>
