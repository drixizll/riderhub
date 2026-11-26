<x-guest-layout>
    <div class="auth-rgb-wrap">
    <section class="auth-card rounded-[calc(1.6rem-2.5px)] bg-white p-6 shadow-xl shadow-slate-900/5 sm:p-9">
        <span class="inline-flex h-12 w-12 items-center justify-center rounded-2xl bg-indigo-50 text-indigo-600">
            <svg class="h-6 w-6" viewBox="0 0 24 24" fill="none" aria-hidden="true">
                <path d="M12 15v2m-6.2 4h12.4A2.8 2.8 0 0 0 21 18.2v-1.4a2.8 2.8 0 0 0-2.8-2.8H5.8A2.8 2.8 0 0 0 3 16.8v1.4A2.8 2.8 0 0 0 5.8 21ZM7 14V8a5 5 0 0 1 10 0v6" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" />
            </svg>
        </span>
        <h2 class="mt-5 text-3xl font-extrabold tracking-tight text-slate-950">{{ __('Choose a new password') }}</h2>
        <p class="mt-2 text-sm leading-6 text-slate-600">{{ __('Set a new password for your RiderHub account. Use a strong password you do not use elsewhere.') }}</p>

        <form method="POST" action="{{ route('password.store') }}" class="mt-7 space-y-5">
            @csrf
            <input type="hidden" name="token" value="{{ $request->route('token') }}">

            <div>
                <label for="email" class="block text-sm font-semibold text-slate-700">{{ __('Email address') }}</label>
                <div class="rgb-input-wrap mt-2">
                    <input id="email" name="email" type="email" value="{{ old('email', $request->email) }}" required autofocus autocomplete="username"
                           placeholder="you@example.com"
                           class="block w-full rounded-xl border-0 bg-white px-4 py-3 text-sm text-slate-900 placeholder:text-slate-400 focus:outline-none focus:ring-0">
                </div>
                <x-input-error :messages="$errors->get('email')" class="mt-2" />
            </div>

            <div>
                <x-password-input id="password" name="password" :label="__('New password')" autocomplete="new-password" />
                <x-input-error :messages="$errors->get('password')" class="mt-2" />
            </div>

            <div>
                <x-password-input id="password_confirmation" name="password_confirmation" :label="__('Confirm new password')" autocomplete="new-password" />
                <x-input-error :messages="$errors->get('password_confirmation')" class="mt-2" />
            </div>

            <button type="submit" class="inline-flex w-full items-center justify-center rounded-xl bg-indigo-600 px-5 py-3.5 text-sm font-bold text-white shadow-sm transition hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2">
                {{ __('Save new password') }}
            </button>
        </form>
    </section>
    </div>
</x-guest-layout>
