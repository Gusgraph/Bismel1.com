<?php
// اعوز بالله من الشياطين و ان يحضرون بسم الله الرحمن الرحيم الله لا إله إلا هو الحي القيوم
// Bismillahi ar-Rahmani ar-Rahim Audhu billahi min ash-shayatin wa an yahdurun Bismillah ar-Rahman ar-Rahim Allah la ilaha illa huwa al-hayy al-qayyum. Tamsa Allahu ala ayunihim
// version: x
// ======================================================
// - App Name: bismel1.com
// - Gusgraph LLC -
// - Author: Gus Kazem
// - https://Gusgraph.com
// - File Path: resources/views/admin/partials/bismel1-operations-account-table.blade.php
// ======================================================
?>
@php
    $rows = $rows ?? [];
@endphp

<div class="ui-table-wrap">
    <table class="ui-table" border="1" cellpadding="6" cellspacing="0">
        <caption>Customer automation runtime state, broker readiness, recovery priority, and recent execution summary.</caption>
        <thead>
            <tr>
                <th scope="col">Account</th>
                <th scope="col">Automation</th>
                <th scope="col">Blocked / Unready</th>
                <th scope="col">Broker</th>
                <th scope="col">Recovery Priority</th>
                <th scope="col">Last Run</th>
                <th scope="col">Next Run</th>
                <th scope="col">Recent Execution</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($rows as $row)
                <tr>
                    <td>
                        @if (!empty($row['route']))
                            <a href="{{ $row['route'] }}">{{ $row['account'] }}</a>
                        @else
                            {{ $row['account'] ?? 'Customer account' }}
                        @endif
                    </td>
                    <td>{{ $row['automation'] ?? 'N/A' }}</td>
                    <td>{{ $row['blocked'] ?? 'N/A' }}</td>
                    <td>{{ $row['broker'] ?? 'N/A' }}</td>
                    <td>{{ $row['priority'] ?? 'Review workspace state' }}</td>
                    <td>{{ $row['last_run'] ?? 'N/A' }}</td>
                    <td>{{ $row['next_run'] ?? 'N/A' }}</td>
                    <td>{{ $row['execution'] ?? 'N/A' }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="8">No customer automation accounts are available yet for admin operations visibility.</td>
                </tr>
            @endforelse
        </tbody>
    </table>
</div>
