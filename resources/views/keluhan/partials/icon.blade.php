@php($size = $size ?? 20)

<svg
    width="{{ $size }}"
    height="{{ $size }}"
    viewBox="0 0 24 24"
    fill="none"
    stroke="currentColor"
    stroke-width="1.8"
    stroke-linecap="round"
    stroke-linejoin="round"
    aria-hidden="true"
>
    @switch($name ?? '')

        @case('home')
            <path d="m3 10 9-7 9 7v10a1 1 0 0 1-1 1h-5v-7H9v7H4a1 1 0 0 1-1-1z"/>
            @break

        @case('wallet')
            <rect x="3" y="5" width="18" height="15" rx="2"/>
            <path d="M16 13h5M3 9h18"/>
            @break

        @case('chat')
            <path d="M20 11.5a8.5 8.5 0 0 1-8.5 8.5 9 9 0 0 1-3.4-.7L3 21l1.7-5.1A8.5 8.5 0 1 1 20 11.5Z"/>
            <path d="M8 11h8M8 14h5"/>
            @break

        @case('bell')
            <path d="M18 8a6 6 0 0 0-12 0c0 7-3 7-3 9h18c0-2-3-2-3-9ZM10 21h4"/>
            @break

        @case('upload')
            <path d="M12 16V3m0 0L7 8m5-5 5 5"/>
            <path d="M4 16v3a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2v-3"/>
            @break

        @case('check')
            <path d="M5 12.5 10 17l9-10"/>
            @break

        @case('alert')
            <path d="m12 3 10 18H2L12 3Z"/>
            <path d="M12 9v5m0 3v.01"/>
            @break

        @case('info')
            <circle cx="12" cy="12" r="9"/>
            <path d="M12 11v6m0-10v.01"/>
            @break

        @default
            <circle cx="12" cy="12" r="9"/>
    @endswitch
</svg>