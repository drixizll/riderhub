@props(['align' => 'right', 'width' => '48', 'contentClasses' => 'py-1 bg-white'])

@php
$alignmentClasses = match ($align) {
    'left' => 'ltr:origin-top-left rtl:origin-top-right start-0',
    'top' => 'origin-top',
    default => 'ltr:origin-top-right rtl:origin-top-left end-0',
};

$width = match ($width) {
    '48' => 'w-48',
    '64' => 'w-64',
    '72' => 'w-72',
    default => $width,
};
@endphp

<div class="relative" x-data="{ open: false }" @click.outside="open = false" @close.stop="open = false">
    <div @click="open = ! open">
        {{ $trigger }}
    </div>

    <div x-show="open"
            x-transition:enter="transition ease-out duration-220"
            x-transition:enter-start="opacity-0 translate-y-1.5 scale-95"
            x-transition:enter-end="opacity-100 translate-y-0 scale-100"
            x-transition:leave="transition ease-in duration-120"
            x-transition:leave-start="opacity-100 translate-y-0 scale-100"
            x-transition:leave-end="opacity-0 translate-y-1 scale-95"
            class="absolute z-50 mt-2.5 {{ $width }} overflow-hidden rounded-2xl shadow-2xl shadow-slate-900/15 {{ $alignmentClasses }}"
            style="display: none;"
            @click="open = false">
        <div class="overflow-hidden rounded-2xl border border-slate-200/80 {{ $contentClasses }}">
            {{ $content }}
        </div>
    </div>
</div>
