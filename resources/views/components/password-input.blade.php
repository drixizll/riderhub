@props([
    'id',
    'name',
    'label' => 'Password',
    'autocomplete' => 'current-password',
    'required' => true,
])

<div x-data="{ visible: false }">
    <label for="{{ $id }}" class="block text-sm font-semibold text-slate-700">{{ $label }}</label>
    <div class="rgb-input-wrap relative mt-2">
        <input
            id="{{ $id }}"
            name="{{ $name }}"
            :type="visible ? 'text' : 'password'"
            autocomplete="{{ $autocomplete }}"
            @required($required)
            {{ $attributes->merge(['class' => 'block w-full rounded-xl border-0 bg-white py-3 pl-4 pr-12 text-sm text-slate-900 placeholder:text-slate-400 focus:outline-none focus:ring-0']) }}
        >
        <button
            type="button"
            @click="visible = !visible"
            :aria-label="visible ? 'Hide password' : 'Show password'"
            :aria-pressed="visible.toString()"
            class="absolute inset-y-0 right-0 inline-flex items-center rounded-r-xl px-3 text-slate-500 transition hover:text-indigo-600 focus:outline-none focus:ring-2 focus:ring-inset focus:ring-indigo-500"
        >
            <svg x-show="!visible" class="h-5 w-5" viewBox="0 0 24 24" fill="none" aria-hidden="true">
                <path d="M2.5 12s3.5-6 9.5-6 9.5 6 9.5 6-3.5 6-9.5 6-9.5-6-9.5-6Z" stroke="currentColor" stroke-width="1.7" stroke-linejoin="round" />
                <circle cx="12" cy="12" r="2.5" stroke="currentColor" stroke-width="1.7" />
            </svg>
            <svg x-show="visible" x-cloak class="h-5 w-5" viewBox="0 0 24 24" fill="none" aria-hidden="true">
                <path d="m3 3 18 18M10.6 6.2A10.8 10.8 0 0 1 12 6c6 0 9.5 6 9.5 6a16.5 16.5 0 0 1-3 3.6M6.2 6.2C3.8 7.7 2.5 12 2.5 12s3.5 6 9.5 6c1.2 0 2.3-.3 3.3-.7" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round" />
            </svg>
        </button>
    </div>
</div>
