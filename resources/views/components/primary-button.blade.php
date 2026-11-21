<button {{ $attributes->merge(['type' => 'submit', 'class' => 'inline-flex items-center justify-center gap-2 rounded-xl border border-transparent bg-gradient-to-r from-indigo-600 via-violet-600 to-indigo-600 bg-[length:200%_auto] px-5 py-3 text-sm font-bold text-white shadow-lg shadow-indigo-600/25 transition-all duration-200 hover:-translate-y-0.5 hover:bg-right hover:shadow-xl hover:shadow-indigo-600/35 active:translate-y-0 active:scale-[0.98] focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 disabled:opacity-60']) }}>
    {{ $slot }}
</button>
