
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

        @case('check')
            <path d="M5 12.5 10 17l9-10"/>
            @break

        @case('key')
            <circle cx="8" cy="8" r="4"/>
            <path d="m11 11 9 9m-4-4 3-3m-1 5 2-2"/>
            @break

        @case('file')
            <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/>
            <path d="M14 2v6h6M8 13h8M8 17h8"/>
            @break

        @case('users')
            <path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"/>
            <circle cx="9" cy="7" r="4"/>
            <path d="M22 21v-2a4 4 0 0 0-3-3.87M16 3.13a4 4 0 0 1 0 7.75"/>
            @break

        @case('chart')
            <path d="M3 3v18h18M7 17v-4M12 17V7M17 17v-7"/>
            @break

        @case('logout')
            <path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"/>
            <path d="m16 17 5-5-5-5m5 5H9"/>
            @break

        @case('search')
            <circle cx="11" cy="11" r="7"/>
            <path d="m20 20-4-4"/>
            @break


        @case('logout')
            <path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"/>
            <path d="m16 17 5-5-5-5m5 5H9"/>
            @break

        @default
            <circle cx="12" cy="12" r="9"/>
    @endswitch
</svg>
