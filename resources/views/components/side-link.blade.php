@props(['href', 'active' => false, 'icon' => 'circle'])

@php
    $icons = [
        'home' => 'M3 12l9-9 9 9M5 10v10a1 1 0 001 1h3v-6h6v6h3a1 1 0 001-1V10',
        'pos' => 'M3 4h18v12H3zM8 20h8M12 16v4M7 8h4M7 11h2',
        'receipt' => 'M6 2h12v20l-3-2-3 2-3-2-3 2V2zM9 7h6M9 11h6M9 15h4',
        'truck' => 'M1 4h14v11H1zM15 8h4l3 3v4h-7M5.5 18.5a2 2 0 100-4 2 2 0 000 4zM18.5 18.5a2 2 0 100-4 2 2 0 000 4z',
        'wallet' => 'M3 7a2 2 0 012-2h14v4M3 7v10a2 2 0 002 2h16V9H5a2 2 0 01-2-2zM16 14h.01',
        'box' => 'M21 8l-9-5-9 5 9 5 9-5zM3 8v8l9 5 9-5V8M12 13v8',
        'ruler' => 'M3 17L17 3l4 4L7 21zM7 13l2 2M10 10l2 2M13 7l2 2',
        'users' => 'M17 21v-2a4 4 0 00-4-4H5a4 4 0 00-4 4v2M9 11a4 4 0 100-8 4 4 0 000 8zM23 21v-2a4 4 0 00-3-3.87M16 3.13a4 4 0 010 7.75',
        'book' => 'M4 19.5A2.5 2.5 0 016.5 17H20V2H6.5A2.5 2.5 0 004 4.5v15zM4 19.5A2.5 2.5 0 006.5 22H20v-5',
        'ledger' => 'M4 4h16v16H4zM4 9h16M9 9v11',
        'scale' => 'M12 3v18M5 7h14M5 7l-3 7a3 3 0 006 0zM19 7l-3 7a3 3 0 006 0zM8 21h8',
        'chart' => 'M3 3v18h18M7 15l4-4 3 3 5-6',
        'bank' => 'M3 10l9-6 9 6M5 10v8M9 10v8M15 10v8M19 10v8M3 21h18',
        'factory' => 'M2 20h20V9l-6 4V9l-6 4V4H2z',
        'circle' => 'M12 21a9 9 0 100-18 9 9 0 000 18z',
    ];
@endphp

<a href="{{ $href }}" {{ $attributes->class(['side-link', 'active' => $active]) }} @if($active) aria-current="page" @endif>
    <svg class="h-[18px] w-[18px] shrink-0" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24">
        <path d="{{ $icons[$icon] ?? $icons['circle'] }}" />
    </svg>
    <span>{{ $slot }}</span>
</a>
