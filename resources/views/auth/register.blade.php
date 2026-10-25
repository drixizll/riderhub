<x-guest-layout>
    <section class="auth-card rounded-3xl border border-slate-200 bg-white p-6 shadow-xl shadow-slate-900/5 sm:p-9">
        <div class="mb-7">
            <span class="inline-flex items-center gap-2 rounded-full bg-indigo-50 px-3 py-1.5 text-xs font-bold uppercase tracking-wider text-indigo-700">
                <svg class="h-4 w-4" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true"><path d="M10 2a5 5 0 0 0-5 5v1H4a2 2 0 0 0-2 2v6a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2v-6a2 2 0 0 0-2-2h-1V7a5 5 0 0 0-5-5Zm3 6H7V7a3 3 0 0 1 6 0v1Z" /></svg>
                Join RiderHub
            </span>
            <h2 class="mt-5 text-3xl font-extrabold tracking-tight text-slate-950">Create your account</h2>
            <p class="mt-2 text-sm leading-6 text-slate-600">Sign up once to book rides and send deliveries with ease.</p>
        </div>

        <x-auth-session-status class="mb-5 rounded-xl bg-emerald-50 px-4 py-3 text-sm font-medium text-emerald-800" :status="session('status')" />

        <form method="POST" action="{{ route('register') }}" class="auth-form-stagger space-y-4">
            @csrf

            <fieldset>
                <legend class="block text-sm font-semibold text-slate-700">I want to join RiderHub as a</legend>
                <div class="mt-2 grid gap-3 sm:grid-cols-2">
                    <label class="role-choice relative cursor-pointer rounded-2xl border border-slate-200 bg-white p-4 transition hover:border-indigo-300 hover:bg-indigo-50/50">
                        <input class="peer sr-only" type="radio" name="role" value="customer" @checked(old('role', 'customer') === 'customer') required>
                        <span class="flex items-center gap-3">
                            <span class="flex h-10 w-10 items-center justify-center rounded-xl bg-indigo-50 text-indigo-600">
                                <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M5 17h2m10 0h2M7 17l2-6h6l2 6m-8-6 1-3h4l1 3M5 17a2 2 0 1 0 4 0m6 0a2 2 0 1 0 4 0" /></svg>
                            </span>
                            <span><span class="block text-sm font-bold text-slate-900">Customer</span><span class="mt-0.5 block text-xs text-slate-500">Book rides and deliveries</span></span>
                        </span>
                        <span class="role-choice-check absolute right-3 top-3 h-4 w-4 rounded-full border border-slate-300 transition"></span>
                    </label>
                    <label class="role-choice relative cursor-pointer rounded-2xl border border-slate-200 bg-white p-4 transition hover:border-indigo-300 hover:bg-indigo-50/50">
                        <input class="peer sr-only" type="radio" name="role" value="rider" @checked(old('role') === 'rider') required>
                        <span class="flex items-center gap-3">
                            <span class="flex h-10 w-10 items-center justify-center rounded-xl bg-emerald-50 text-emerald-600">
                                <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M4 15h16l-1.5-5h-13L4 15Zm2 0v3m12-3v3M8 10l1-3h6l1 3M7 18h.01M17 18h.01" /></svg>
                            </span>
                            <span><span class="block text-sm font-bold text-slate-900">Rider</span><span class="mt-0.5 block text-xs text-slate-500">Accept trips and earn</span></span>
                        </span>
                        <span class="role-choice-check absolute right-3 top-3 h-4 w-4 rounded-full border border-slate-300 transition"></span>
                    </label>
                </div>
                <x-input-error :messages="$errors->get('role')" class="mt-2" />
            </fieldset>

            <div>
                <label for="name" class="block text-sm font-semibold text-slate-700">{{ __('Full name') }}</label>
                <input id="name" name="name" type="text" value="{{ old('name') }}" required autofocus autocomplete="name"
                       placeholder="Your name"
                       class="mt-2 block w-full rounded-xl border border-slate-300 bg-white px-4 py-3 text-sm text-slate-900 shadow-sm placeholder:text-slate-400 focus:border-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-500/20">
                <x-input-error :messages="$errors->get('name')" class="mt-2" />
            </div>

            <div class="grid gap-4 sm:grid-cols-2">
                <div>
                    <label for="email" class="block text-sm font-semibold text-slate-700">{{ __('Email address') }}</label>
                    <input id="email" name="email" type="email" value="{{ old('email') }}" required autocomplete="username"
                           placeholder="you@example.com"
                           class="mt-2 block w-full rounded-xl border border-slate-300 bg-white px-4 py-3 text-sm text-slate-900 shadow-sm placeholder:text-slate-400 focus:border-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-500/20">
                    <x-input-error :messages="$errors->get('email')" class="mt-2" />
                </div>
                <div>
                    <label for="phone" class="block text-sm font-semibold text-slate-700">{{ __('Phone number') }} <span class="font-normal text-slate-400">(optional)</span></label>
                    <input id="phone" name="phone" type="tel" value="{{ old('phone') }}" autocomplete="tel"
                           placeholder="+63 9XX XXX XXXX"
                           class="mt-2 block w-full rounded-xl border border-slate-300 bg-white px-4 py-3 text-sm text-slate-900 shadow-sm placeholder:text-slate-400 focus:border-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-500/20">
                    <x-input-error :messages="$errors->get('phone')" class="mt-2" />
                </div>
            </div>

            <div>
                <x-password-input id="password" name="password" :label="__('Password')" autocomplete="new-password" data-password-input />
                <div data-password-strength class="mt-3 space-y-2" aria-live="polite">
                    <div class="flex items-center justify-between gap-3">
                        <span class="text-xs font-semibold text-slate-500">Password strength</span>
                        <span data-strength-label class="text-xs font-bold text-slate-500">Enter a password</span>
                    </div>
                    <div class="grid grid-cols-5 gap-1.5" aria-hidden="true">
                        @for ($segment = 1; $segment <= 5; $segment++)
                            <span data-strength-segment="{{ $segment }}" class="h-1.5 rounded-full bg-slate-200 transition-colors"></span>
                        @endfor
                    </div>
                    <ul class="grid grid-cols-2 gap-x-3 gap-y-1 text-[11px] text-slate-500 sm:grid-cols-3">
                        <li data-password-check="length">8+ characters</li>
                        <li data-password-check="lowercase">Lowercase letter</li>
                        <li data-password-check="uppercase">Uppercase letter</li>
                        <li data-password-check="number">Number</li>
                        <li data-password-check="symbol">Symbol</li>
                    </ul>
                </div>
                <x-input-error :messages="$errors->get('password')" class="mt-2" />
            </div>

            <div>
                <x-password-input id="password_confirmation" name="password_confirmation" :label="__('Confirm password')" autocomplete="new-password" />
                <x-input-error :messages="$errors->get('password_confirmation')" class="mt-2" />
            </div>

            <button type="submit" class="mt-2 inline-flex w-full items-center justify-center gap-2 rounded-xl bg-indigo-600 px-5 py-3.5 text-sm font-bold text-white shadow-sm transition hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2">
                {{ __('Create account') }}
                <svg class="h-4 w-4" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true"><path fill-rule="evenodd" d="M3 10a.75.75 0 0 1 .75-.75h10.69L10.22 5.03a.75.75 0 1 1 1.06-1.06l5.5 5.5a.75.75 0 0 1 0 1.06l-5.5 5.5a.75.75 0 1 1-1.06-1.06l4.22-4.22H3.75A.75.75 0 0 1 3 10Z" clip-rule="evenodd" /></svg>
            </button>
        </form>

        <div class="mt-6 border-t border-slate-100 pt-5 text-center">
            <p class="text-sm text-slate-600">Already registered?
                <a href="{{ route('login') }}" class="font-bold text-indigo-600 transition hover:text-indigo-800">Log in instead</a>
            </p>
        </div>
    </section>
</x-guest-layout>
