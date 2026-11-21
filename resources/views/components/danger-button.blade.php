<button {{ $attributes->merge(['type' => 'submit', 'class' => 'inline-flex items-center justify-center gap-2 rounded-xl border border-transparent bg-gradient-to-r from-rose-600 to-pink-600 px-4 py-2.5 text-sm font-bold text-white shadow-lg shadow-rose-600/25 transition-all duration-200 hover:-translate-y-0.5 hover:shadow-xl hover:shadow-rose-600/35 active:translate-y-0 active:scale-[0.98] focus:outline-none focus:ring-2 focus:ring-rose-500 focus:ring-offset-2 disabled:opacity-60']) }}>
    {{ $slot }}
</button>
