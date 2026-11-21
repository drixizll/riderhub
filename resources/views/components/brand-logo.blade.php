@props(['size' => 'md'])

@php
    $box = match ($size) {
        'sm' => 'h-8 w-8 rounded-lg',
        'lg' => 'h-11 w-11 rounded-2xl',
        default => 'h-10 w-10 rounded-xl',
    };
    $text = match ($size) {
        'sm' => 'text-base',
        'lg' => 'text-2xl',
        default => 'text-xl',
    };
@endphp

<span {{ $attributes->merge(['class' => 'inline-flex items-center gap-2.5 select-none']) }}>
    <span class="relative flex {{ $box }} shrink-0 items-center justify-center overflow-hidden bg-gradient-to-br from-violet-500 via-indigo-600 to-indigo-800 text-white shadow-md shadow-indigo-600/25 ring-1 ring-inset ring-white/20">
        <svg class="h-3/4 w-3/4" viewBox="0 0 48 48" fill="none" aria-hidden="true">
            <path d="M8 20h7M6 25h6M9 30h5" stroke="#DDD6FE" stroke-width="2.4" stroke-linecap="round" opacity="0.9"/>
            <path d="M28.5 9C22.1 9 17 14.1 17 20.5c0 7.8 9.2 16.5 10.8 18a1 1 0 0 0 1.4 0c1.6-1.5 10.8-10.2 10.8-18C40 14.1 34.9 9 28.5 9Z" fill="#FFFFFF"/>
            <path d="M24.8 25.5v-9.6h4.2c2.4 0 4 1.3 4 3.2 0 1.5-.9 2.6-2.4 3l2.9 3.4h-3l-2.4-3.1h-.9v3.1h-2.4Zm2.4-5.1h1.6c1 0 1.7-.5 1.7-1.3s-.7-1.3-1.7-1.3h-1.6v2.6Z" fill="#6D28D9"/>
        </svg>
    </span>
    <span class="{{ $text }} font-extrabold tracking-tight text-slate-900">Rider<span class="text-indigo-600">Hub</span></span>
</span>
