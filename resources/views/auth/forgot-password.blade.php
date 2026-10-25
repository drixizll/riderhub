<x-guest-layout>
    <section class="rounded-3xl border border-slate-200 bg-white p-6 shadow-xl shadow-slate-900/5 sm:p-9">
        <span class="inline-flex h-12 w-12 items-center justify-center rounded-2xl bg-indigo-50 text-indigo-600">
            <svg class="h-6 w-6" viewBox="0 0 24 24" fill="none" aria-hidden="true">
                <path d="M12 15v2m-6.2 4h12.4A2.8 2.8 0 0 0 21 18.2v-1.4a2.8 2.8 0 0 0-2.8-2.8H5.8A2.8 2.8 0 0 0 3 16.8v1.4A2.8 2.8 0 0 0 5.8 21ZM7 14V8a5 5 0 0 1 10 0v6" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" />
            </svg>
        </span>
        <h2 class="mt-5 text-3xl font-extrabold tracking-tight text-slate-950">{{ __('Reset your password') }}</h2>
        <p class="mt-2 text-sm leading-6 text-slate-600">{{ __('Forgot your password? Enter the email address associated with your account and we will send you a secure reset link.') }}</p>

        <x-auth-session-status class="mt-5 rounded-xl bg-emerald-50 px-4 py-3 text-sm font-medium text-emerald-800" :status="session('status')" />

        <form method="POST" action="{{ route('password.email') }}" class="mt-7 space-y-5">
            @csrf
            <div>
                <label for="email" class="block text-sm font-semibold text-slate-700">{{ __('Email address') }}</label>
                <input id="email" name="email" type="email" value="{{ old('email') }}" required autofocus autocomplete="username"
                       placeholder="you@example.com"
                       class="mt-2 block w-full rounded-xl border border-slate-300 bg-white px-4 py-3 text-sm text-slate-900 shadow-sm placeholder:text-slate-400 focus:border-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-500/20">
                <x-input-error :messages="$errors->get('email')" class="mt-2" />
            </div>

            <button type="submit" class="inline-flex w-full items-center justify-center rounded-xl bg-indigo-600 px-5 py-3.5 text-sm font-bold text-white shadow-sm transition hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2">
                {{ __('Email password reset link') }}
            </button>
        </form>

        <p class="mt-6 text-center text-sm text-slate-600">
            Remembered your password?
            <a href="{{ route('login') }}" class="font-bold text-indigo-600 transition hover:text-indigo-800">Back to log in</a>
        </p>
    </section>
</x-guest-layout>
