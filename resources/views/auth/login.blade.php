<x-guest-layout>
    <div class="auth-rgb-wrap">
    <section class="auth-card rounded-[calc(1.6rem-2.5px)] bg-white p-6 shadow-xl shadow-slate-900/5 sm:p-9">
        <div class="mb-8">
            <span class="inline-flex items-center gap-2 rounded-full bg-indigo-50 px-3 py-1.5 text-xs font-bold uppercase tracking-wider text-indigo-700">
                <svg class="h-4 w-4" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true"><path fill-rule="evenodd" d="M5 8V6a5 5 0 0 1 10 0v2h.5A1.5 1.5 0 0 1 17 9.5v7a1.5 1.5 0 0 1-1.5 1.5h-11A1.5 1.5 0 0 1 3 16.5v-7A1.5 1.5 0 0 1 4.5 8H5Zm2 0h6V6a3 3 0 1 0-6 0v2Z" clip-rule="evenodd" /></svg>
                Secure sign in
            </span>
            <h2 class="mt-5 text-3xl font-extrabold tracking-tight text-slate-950">Welcome back</h2>
            <p class="mt-2 text-sm leading-6 text-slate-600">Log in to book a trip, send a package, and manage your RiderHub account.</p>
        </div>

        <x-auth-session-status class="mb-5 rounded-xl bg-emerald-50 px-4 py-3 text-sm font-medium text-emerald-800" :status="session('status')" />

        <form method="POST" action="{{ route('login') }}" id="login-form" class="auth-form-stagger space-y-5">
            @csrf

            <div id="saved-accounts-wrap" class="hidden rounded-2xl border border-indigo-100 bg-indigo-50/70 p-3">
                <p class="text-[11px] font-bold uppercase tracking-wider text-indigo-700">Saved emails</p>
                <div id="saved-accounts-list" class="mt-2 flex flex-wrap gap-1.5"></div>
            </div>

            <div>
                <label for="email" class="block text-sm font-semibold text-slate-700">{{ __('Email address') }}</label>
                <div class="rgb-input-wrap mt-2">
                    <input id="email" name="email" type="email" value="{{ old('email') }}" required autofocus autocomplete="username" autocapitalize="none" autocorrect="off" spellcheck="false"
                           placeholder="you@example.com"
                           class="block w-full rounded-xl border-0 bg-white px-4 py-3 text-sm text-slate-900 placeholder:text-slate-400 focus:outline-none focus:ring-0">
                </div>
                <x-input-error :messages="$errors->get('email')" class="mt-2" />
            </div>

            <div>
                <x-password-input id="password" name="password" :label="__('Password')" autocomplete="current-password" />
                <x-input-error :messages="$errors->get('password')" class="mt-2" />
            </div>

            <div class="flex flex-wrap items-center justify-between gap-3">
                <label for="remember_me" class="inline-flex cursor-pointer items-center gap-2.5">
                    <input id="remember_me" type="checkbox" name="remember" value="1" @checked(old('remember', true)) class="h-4 w-4 rounded border-slate-300 text-indigo-600 shadow-sm focus:ring-indigo-500">
                    <span class="text-sm font-medium text-slate-600">{{ __('Remember me (Auto-login)') }}</span>
                </label>
                @if (Route::has('password.request'))
                    <a class="text-sm font-semibold text-indigo-600 transition hover:text-indigo-800 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2" href="{{ route('password.request') }}">
                        {{ __('Forgot password or account?') }}
                    </a>
                @endif
            </div>

            <button type="submit" class="inline-flex w-full items-center justify-center gap-2 rounded-xl bg-indigo-600 px-5 py-3.5 text-sm font-bold text-white shadow-sm transition hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2">
                {{ __('Log in to RiderHub') }}
                <svg class="h-4 w-4" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true"><path fill-rule="evenodd" d="M3 10a.75.75 0 0 1 .75-.75h10.69L10.22 5.03a.75.75 0 1 1 1.06-1.06l5.5 5.5a.75.75 0 0 1 0 1.06l-5.5 5.5a.75.75 0 1 1-1.06-1.06l4.22-4.22H3.75A.75.75 0 0 1 3 10Z" clip-rule="evenodd" /></svg>
            </button>
        </form>

        <div class="mt-7 border-t border-slate-100 pt-6 text-center">
            <p class="text-sm text-slate-600">Don't have an account?
                <a href="{{ route('register') }}" class="font-bold text-indigo-600 transition hover:text-indigo-800">Create one for free</a>
            </p>
        </div>
    </section>
    </div>

    <script>
        window.addEventListener('pageshow', (event) => {
            if (event.persisted) {
                window.location.reload();
            }
        });
        document.addEventListener('DOMContentLoaded', () => {
            const form = document.getElementById('login-form');
            const emailInput = document.getElementById('email');
            const passwordInput = document.getElementById('password');
            const rememberCheckbox = document.getElementById('remember_me');
            const savedWrap = document.getElementById('saved-accounts-wrap');
            const savedList = document.getElementById('saved-accounts-list');
            const KEY_LAST = 'riderhub-remembered-email';
            const KEY_LIST = 'riderhub-saved-emails';

            const getSavedEmails = () => {
                try {
                    const parsed = JSON.parse(localStorage.getItem(KEY_LIST) || '[]');
                    return Array.isArray(parsed) ? parsed.filter((v) => typeof v === 'string' && v.includes('@')) : [];
                } catch {
                    return [];
                }
            };

            const renderSavedEmails = () => {
                const emails = getSavedEmails();
                savedList.innerHTML = '';
                if (!emails.length) {
                    savedWrap.classList.add('hidden');
                    return;
                }
                savedWrap.classList.remove('hidden');
                emails.forEach((savedEmail) => {
                    const chip = document.createElement('div');
                    chip.className = 'inline-flex items-center gap-1.5 rounded-full border border-indigo-200 bg-white px-2.5 py-1 text-xs font-semibold text-slate-700 shadow-sm';

                    const pickBtn = document.createElement('button');
                    pickBtn.type = 'button';
                    pickBtn.textContent = savedEmail;
                    pickBtn.className = 'hover:text-indigo-600';
                    pickBtn.addEventListener('click', () => {
                        emailInput.value = savedEmail;
                        rememberCheckbox.checked = true;
                        passwordInput?.focus();
                    });

                    const removeBtn = document.createElement('button');
                    removeBtn.type = 'button';
                    removeBtn.textContent = '×';
                    removeBtn.setAttribute('aria-label', `Remove ${savedEmail}`);
                    removeBtn.className = 'ml-0.5 text-slate-400 hover:text-rose-600';
                    removeBtn.addEventListener('click', () => {
                        const updated = getSavedEmails().filter((e) => e !== savedEmail);
                        try {
                            localStorage.setItem(KEY_LIST, JSON.stringify(updated));
                            if (localStorage.getItem(KEY_LAST) === savedEmail) localStorage.removeItem(KEY_LAST);
                        } catch {}
                        renderSavedEmails();
                    });

                    chip.append(pickBtn, removeBtn);
                    savedList.append(chip);
                });
            };

            try {
                const rememberedEmail = localStorage.getItem(KEY_LAST);
                if (rememberedEmail && !emailInput.value) {
                    emailInput.value = rememberedEmail;
                    rememberCheckbox.checked = true;
                }
            } catch {}

            renderSavedEmails();

            form?.addEventListener('submit', () => {
                const email = emailInput.value.trim();
                try {
                    if (rememberCheckbox.checked && email) {
                        localStorage.setItem(KEY_LAST, email);
                        const existing = getSavedEmails().filter((e) => e.toLowerCase() !== email.toLowerCase());
                        localStorage.setItem(KEY_LIST, JSON.stringify([email, ...existing].slice(0, 4)));
                    } else {
                        localStorage.removeItem(KEY_LAST);
                    }
                } catch {}
            });
        });
    </script>
</x-guest-layout>
