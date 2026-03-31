<ul class="ui-list ui-record-list">
    @foreach ($onboardingChecklist as $item)
        <li>
            <div class="ui-list__stack">
                <div class="ui-inline-copy">
                    <strong>{{ $item['label'] }}</strong>
                    <span>{{ $item['value'] }}</span>
                </div>
            </div>
        </li>
    @endforeach
</ul>
