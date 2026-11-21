<x-guest-layout>
    <section
        x-data="{ tab: @js(session('recovery_tab', $errors->has('phone') ? 'phone' : 'email')) }"
        class="auth-card rounded-3xl border border-slate-200 bg-white p-6 shadow-xl shadow-slate-900/5 sm:p-9"
    >
        <span class="inline-flex h-12 w-12 items-center justify-center rounded-2xl bg-indigo-50 text-indigo-600">
            <svg class="h-6 w-6" viewBox="0 0 24 24" fill="none" aria-hidden="true">
                <path d="M12 15v2m-6.2 4h12.4A2.8 2.8 0 0 0 21 18.2v-1.4a2.8 2.8 0 0 0-2.8-2.8H5.8A2.8 2.8 0 0 0 3 16.8v1.4A2.8 2.8 0 0 0 5.8 21ZM7 14V8a5 5 0 0 1 10 0v6" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" />
            </svg>
        </span>
        <h2 class="mt-5 text-3xl font-extrabold tracking-tight text-slate-950">{{ __('Account & password recovery') }}</h2>
        <p class="mt-2 text-sm leading-6 text-slate-600">{{ __('Reset your password using your email, or retrieve a forgotten account using your registered phone number.') }}</p>

        <div class="mt-6 grid grid-cols-2 gap-1.5 rounded-2xl bg-slate-100 p-1" role="tablist" aria-label="Recovery method">
            <button
                type="button"
                role="tab"
                :aria-selected="(tab === 'email').toString()"
                @click="tab = 'email'"
                class="rounded-xl px-3 py-2.5 text-xs font-bold transition sm:text-sm"
                :class="tab === 'email' ? 'bg-white text-indigo-700 shadow-sm' : 'text-slate-600 hover:text-slate-900'"
            >
                Reset via Email
            </button>
            <button
                type="button"
                role="tab"
                :aria-selected="(tab === 'phone').toString()"
                @click="tab = 'phone'"
                class="rounded-xl px-3 py-2.5 text-xs font-bold transition sm:text-sm"
                :class="tab === 'phone' ? 'bg-white text-indigo-700 shadow-sm' : 'text-slate-600 hover:text-slate-900'"
            >
                Find Account by Phone
            </button>
        </div>

        <x-auth-session-status class="mt-5 rounded-xl bg-emerald-50 px-4 py-3 text-sm font-medium text-emerald-800" :status="session('status')" />

        @if (session('local_reset_url'))
            <div class="mt-4 rounded-2xl border border-indigo-200 bg-indigo-50 p-4 text-sm text-indigo-900">
                <p class="font-bold">Local preview link (email is in log mode)</p>
                <p class="mt-1 text-xs text-indigo-700">Click below to open your password reset page directly:</p>
                <a href="{{ session('local_reset_url') }}" class="mt-3 inline-flex items-center gap-1.5 rounded-xl bg-indigo-600 px-4 py-2 text-xs font-bold text-white shadow-sm transition hover:bg-indigo-700">
                    Open password reset page
                    <span aria-hidden="true">→</span>
                </a>
            </div>
        @endif

        <form x-show="tab === 'email'" method="POST" action="{{ route('password.email') }}" class="mt-6 space-y-5">
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

        <form x-show="tab === 'phone'" x-cloak method="POST" action="{{ route('account.recover') }}" class="mt-6 space-y-5">
            @csrf
            <div>
                <label for="phone" class="block text-sm font-semibold text-slate-700">{{ __('Registered phone number') }}</label>
                <input id="phone" name="phone" type="tel" value="{{ old('phone') }}" required autocomplete="tel"
                       placeholder="0917 123 4567 or +639171234567"
                       class="mt-2 block w-full rounded-xl border border-slate-300 bg-white px-4 py-3 text-sm text-slate-900 shadow-sm placeholder:text-slate-400 focus:border-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-500/20">
                <p class="mt-1.5 text-xs text-slate-500">We will show a masked hint of your email and send a reset link there.</p>
                <x-input-error :messages="$errors->get('phone')" class="mt-2" />
            </div>

            <button type="submit" class="inline-flex w-full items-center justify-center rounded-xl bg-indigo-600 px-5 py-3.5 text-sm font-bold text-white shadow-sm transition hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2">
                {{ __('Find my account & send reset link') }}
            </button>
        </form>

        <p class="mt-6 text-center text-sm text-slate-600">
            Remembered your account?
            <a href="{{ route('login') }}" class="font-bold text-indigo-600 transition hover:text-indigo-800">Back to log in</a>
        </p>
    </section>
</x-guest-layout>
