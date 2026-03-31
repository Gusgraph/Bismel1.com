<ul>
    @foreach ($items as $item)
        <li>{{ $item[$labelKey] }}: {{ $item[$valueKey] }}</li>
    @endforeach
</ul>
