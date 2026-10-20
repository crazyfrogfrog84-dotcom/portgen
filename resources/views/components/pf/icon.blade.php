@props(['name', 'class' => ''])

<svg class="pf-icon {{ $class }}" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true" focusable="false">
    @switch($name)
        @case('home')
            <path d="m3 10 9-7 9 7" /><path d="M5 9v11h14V9M9 20v-7h6v7" />
            @break
        @case('user')
            <circle cx="12" cy="8" r="3.5" /><path d="M4 21a8 8 0 0 1 16 0" />
            @break
        @case('code')
            <path d="m8 8-4 4 4 4m8-8 4 4-4 4m-3-11-2 14" />
            @break
        @case('folder')
            <path d="M3 7.5A2.5 2.5 0 0 1 5.5 5H10l2 2h6.5A2.5 2.5 0 0 1 21 9.5v8a2.5 2.5 0 0 1-2.5 2.5h-13A2.5 2.5 0 0 1 3 17.5z" />
            @break
        @case('briefcase')
            <rect x="3" y="7" width="18" height="14" rx="2" /><path d="M8 7V5a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2m-13 5h18m-11-1v3h4v-3" />
            @break
        @case('graduation')
            <path d="m2 9 10-5 10 5-10 5z" /><path d="M6 11v5c3.5 3 8.5 3 12 0v-5m4-2v7" />
            @break
        @case('pin')
            <path d="M20 10c0 5.5-8 12-8 12S4 15.5 4 10a8 8 0 1 1 16 0Z" /><circle cx="12" cy="10" r="2.5" />
            @break
        @case('heart')
            <path d="M20.8 8.7c0 5.1-8.8 11-8.8 11s-8.8-5.9-8.8-11a4.7 4.7 0 0 1 8.8-2.1 4.7 4.7 0 0 1 8.8 2.1Z" />
            @break
        @case('mail')
            <rect x="3" y="5" width="18" height="14" rx="2" /><path d="m4 7 8 6 8-6" />
            @break
        @case('link')
            <path d="M10 13.5a4.5 4.5 0 0 0 6.4 0l3-3a4.5 4.5 0 0 0-6.4-6.4l-1.7 1.7" /><path d="M14 10.5a4.5 4.5 0 0 0-6.4 0l-3 3a4.5 4.5 0 0 0 6.4 6.4l1.7-1.7" />
            @break
        @case('external')
            <path d="M14 4h6v6m0-6-9 9" /><path d="M18 13v5a2 2 0 0 1-2 2H6a2 2 0 0 1-2-2V8a2 2 0 0 1 2-2h5" />
            @break
        @case('sparkle')
            <path d="m12 2 1.8 7.2L21 12l-7.2 2.8L12 22l-1.8-7.2L3 12l7.2-2.8z" /><path d="m19 2 .7 2.3L22 5l-2.3.7L19 8l-.7-2.3L16 5l2.3-.7z" />
            @break
        @case('html5')
            <path d="m4 3 1.5 16L12 21l6.5-2L20 3z" /><path d="M8 7h8l-.3 3H8.3l.3 3h7.1l-.5 3-3.2 1-3.1-1-.2-2" />
            @break
        @case('css3')
            <path d="m4 3 1.5 16L12 21l6.5-2L20 3z" /><path d="M8 7h8l-.3 3H8.3m-.1 3h7.1l-.5 3-3.2 1-3.1-1" />
            @break
        @case('javascript')
            <rect x="3" y="3" width="18" height="18" rx="3" /><path d="M12 8v7a2 2 0 0 1-4 0m7-1a2 2 0 1 0 4 0c0-2-4-1-4-4a2 2 0 0 1 4 0" />
            @break
        @case('react')
            <circle cx="12" cy="12" r="2" /><ellipse cx="12" cy="12" rx="10" ry="4.2" /><ellipse cx="12" cy="12" rx="10" ry="4.2" transform="rotate(60 12 12)" /><ellipse cx="12" cy="12" rx="10" ry="4.2" transform="rotate(120 12 12)" />
            @break
        @case('tailwind')
            <path d="M3 10c2.7-4 6.7-4 9.3 0 1.3 2 3.3 2 4.7 0 1.3-2 2.7-2.7 4-2.7-2.7 0-4 1.3-5.3 3.3-1.4 2-3.4 2-4.7 0-2.7-4-6.7-4-9.3 0Zm0 7c2.7-4 6.7-4 9.3 0 1.3 2 3.3 2 4.7 0 1.3-2 2.7-2.7 4-2.7-2.7 0-4 1.3-5.3 3.3-1.4 2-3.4 2-4.7 0-2.7-4-6.7-4-9.3 0Z" />
            @break
        @case('git')
            <path d="m12 2 10 10-10 10L2 12z" /><path d="M9 9l6 6m0-6-6 6" />
            @break
        @case('figma')
            <path d="M9 2h6a3 3 0 0 1 0 6H9a3 3 0 0 1 0-6Zm0 6h6a3 3 0 0 1 0 6h-3a3 3 0 0 1-3-3zm3 6v3a3 3 0 1 1-3-3zm3-6a3 3 0 1 1 0 6" />
            @break
    @endswitch
</svg>
