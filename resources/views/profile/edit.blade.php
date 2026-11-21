<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col gap-2 sm:flex-row sm:items-end sm:justify-between">
            <div>
                <p class="text-sm font-semibold text-indigo-600">Account</p>
                <h1 class="mt-1 text-2xl font-extrabold tracking-tight text-slate-950 sm:text-3xl">{{ __('Account settings') }}</h1>
                <p class="mt-1 text-sm text-slate-500">Manage your profile photo, contact details, password, and account preferences.</p>
            </div>
            <span class="inline-flex w-fit items-center gap-2 rounded-full border border-emerald-200 bg-emerald-50 px-3 py-1 text-xs font-bold text-emerald-700">
                <span class="h-2 w-2 rounded-full bg-emerald-500"></span>
                Account active
            </span>
        </div>
    </x-slot>

    <div class="mx-auto max-w-5xl space-y-6 px-4 py-8 sm:px-6 lg:px-8">
        @if (in_array(session('status'), ['profile-updated', 'profile-photo-updated', 'password-updated'], true))
            @php
                $statusMessage = match (session('status')) {
                    'profile-photo-updated' => 'Your profile photo has been updated.',
                    'password-updated' => 'Your password has been updated securely.',
                    default => 'Your account details have been saved.',
                };
            @endphp
            <div x-data="{ show: true }" x-show="show" x-transition.opacity.duration.300ms role="status"
                 class="success-banner-enter flex items-center justify-between gap-4 rounded-2xl border border-emerald-200 bg-emerald-50/95 px-5 py-4 text-sm font-semibold text-emerald-900 shadow-lg shadow-emerald-900/5">
                <div class="flex items-center gap-3">
                    <span class="success-check-circle flex h-9 w-9 shrink-0 items-center justify-center rounded-xl bg-emerald-500 text-white shadow-md shadow-emerald-500/30">
                        <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                            <path class="success-check-path" d="M5 13l4 4L19 7" />
                        </svg>
                    </span>
                    <div>
                        <p class="font-bold text-emerald-950">Saved successfully!</p>
                        <p class="text-xs font-medium text-emerald-700">{{ $statusMessage }}</p>
                    </div>
                </div>
                <button type="button" @click="show = false" class="rounded-lg p-1 text-emerald-600 transition hover:bg-emerald-100 hover:text-emerald-900" aria-label="Dismiss message">
                    <svg class="h-4 w-4" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true"><path d="M6.28 5.22a.75.75 0 0 0-1.06 1.06L8.94 10l-3.72 3.72a.75.75 0 1 0 1.06 1.06L10 11.06l3.72 3.72a.75.75 0 1 0 1.06-1.06L11.06 10l3.72-3.72a.75.75 0 0 0-1.06-1.06L10 8.94 6.28 5.22Z" /></svg>
                </button>
            </div>
        @endif

        @if ($errors->has('profile_photo'))
            <div role="alert" class="success-banner-enter rounded-2xl border border-rose-200 bg-rose-50 px-5 py-4 text-sm font-semibold text-rose-800">{{ $errors->first('profile_photo') }}</div>
        @endif

        <section class="reveal-on-scroll overflow-hidden rounded-3xl border border-slate-200 bg-white shadow-lg shadow-indigo-950/5" data-revealed="true">
            <div class="relative overflow-hidden bg-gradient-to-br from-indigo-700 via-violet-700 to-indigo-900 px-6 py-8 text-white sm:px-9 sm:py-10">
                <div class="hero-orb absolute -right-12 -top-24 h-64 w-64 rounded-full bg-cyan-300/20 blur-3xl" aria-hidden="true"></div>
                <div class="hero-orb-delayed absolute -bottom-24 left-10 h-56 w-56 rounded-full bg-fuchsia-400/20 blur-3xl" aria-hidden="true"></div>
                <div class="relative flex flex-col gap-6 sm:flex-row sm:items-center sm:justify-between">
                    <div class="flex items-center gap-5">
                        <div class="group relative shrink-0">
                            @if ($user->profile_photo_path)
                                <img data-profile-photo-preview src="{{ route('profile.photo.show', ['user' => $user->id, 'v' => $user->updated_at?->timestamp]) }}" alt="{{ $user->name }} profile photo" class="h-20 w-20 rounded-2xl border-2 border-white/40 object-cover shadow-xl transition duration-300 group-hover:scale-105 sm:h-24 sm:w-24">
                            @else
                                <span data-profile-photo-preview class="flex h-20 w-20 items-center justify-center rounded-2xl border-2 border-white/30 bg-white/15 text-2xl font-extrabold shadow-xl backdrop-blur-sm transition duration-300 group-hover:scale-105 sm:h-24 sm:w-24">{{ \Illuminate\Support\Str::of($user->name)->explode(' ')->take(2)->map(fn ($part) => mb_substr($part, 0, 1))->implode('') }}</span>
                            @endif
                            <span class="absolute -bottom-2 -right-2 flex h-8 w-8 items-center justify-center rounded-xl bg-emerald-400 text-emerald-950 shadow-lg ring-4 ring-indigo-800" aria-hidden="true">
                                <svg class="h-4 w-4" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M16.704 5.29a1 1 0 0 1 .006 1.414l-7.25 7.31a1 1 0 0 1-1.42.004l-3.75-3.75a1 1 0 0 1 1.414-1.414l3.04 3.04 6.544-6.598a1 1 0 0 1 1.416-.006Z" clip-rule="evenodd" /></svg>
                            </span>
                        </div>
                        <div class="min-w-0">
                            <span class="inline-flex items-center gap-1.5 rounded-full border border-white/25 bg-white/10 px-3 py-1 text-[11px] font-bold uppercase tracking-wider text-indigo-50 backdrop-blur-sm">
                                <span class="h-1.5 w-1.5 rounded-full bg-emerald-400"></span>
                                {{ $user->role }} account
                            </span>
                            <h2 class="mt-2 truncate text-2xl font-extrabold tracking-tight sm:text-3xl">{{ $user->name }}</h2>
                            <p class="mt-1 truncate text-sm text-indigo-100">{{ $user->email }}</p>
                            <p class="mt-1 text-xs text-indigo-200/90">RiderHub member since {{ $user->created_at->format('F Y') }}</p>
                        </div>
                    </div>
                    <form method="POST" action="{{ route('profile.photo') }}" enctype="multipart/form-data" class="flex flex-wrap items-center gap-3">
                        @csrf
                        <label for="profile_photo" class="relative inline-flex cursor-pointer items-center gap-2 overflow-hidden rounded-xl border border-white/30 bg-white/15 px-4 py-2.5 text-sm font-bold text-white backdrop-blur-md transition duration-200 hover:-translate-y-0.5 hover:bg-white/25 active:scale-95 focus-within:ring-2 focus-within:ring-white">
                            <svg class="h-4 w-4 shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M4 7h3l2-3h6l2 3h3a2 2 0 0 1 2 2v10a2 2 0 0 1-2 2H4a2 2 0 0 1-2-2V9a2 2 0 0 1 2-2Zm8 11a4 4 0 1 0 0-8 4 4 0 0 0 0 8Z" /></svg>
                            <span data-profile-photo-label>{{ $user->profile_photo_path ? 'Change photo' : 'Add profile photo' }}</span>
                            <input id="profile_photo" name="profile_photo" type="file" accept="image/*" data-profile-photo-input class="absolute inset-0 h-full w-full cursor-pointer opacity-0">
                        </label>
                        <button type="submit" class="rounded-xl bg-white px-4 py-2.5 text-sm font-extrabold text-indigo-700 shadow-lg shadow-indigo-950/20 transition duration-200 hover:-translate-y-0.5 hover:bg-indigo-50 active:scale-95">Save photo</button>
                    </form>
                </div>
            </div>
            <div class="grid divide-y divide-slate-100 sm:grid-cols-2 sm:divide-x sm:divide-y-0">
                @foreach ($profileStats as $stat)
                    <div class="group px-6 py-5 transition hover:bg-slate-50/70 sm:px-9">
                        <p class="text-xs font-bold uppercase tracking-wider text-slate-400">{{ $stat['label'] }}</p>
                        <p class="mt-1 text-2xl font-extrabold text-slate-900 transition group-hover:text-indigo-600">{{ $stat['value'] }}</p>
                    </div>
                @endforeach
            </div>
        </section>

        <div class="reveal-stagger space-y-6">
            <section class="reveal-on-scroll card-interactive rounded-3xl border border-slate-200 bg-white p-6 shadow-sm sm:p-8" data-revealed="true">
                <div class="mb-6 flex items-start gap-4 border-b border-slate-100 pb-5">
                    <span class="flex h-11 w-11 shrink-0 items-center justify-center rounded-2xl bg-gradient-to-br from-indigo-500 to-violet-600 text-white shadow-md shadow-indigo-600/20">
                        <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" aria-hidden="true"><circle cx="12" cy="8" r="4" stroke-width="1.8" /><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M4 21a8 8 0 0 1 16 0" /></svg>
                    </span>
                    <div><h2 class="text-lg font-extrabold text-slate-950">Personal information</h2><p class="mt-1 text-sm text-slate-500">Keep your contact details current for a smoother trip.</p></div>
                </div>
                <div class="max-w-2xl">@include('profile.partials.update-profile-information-form')</div>
            </section>

            <section class="reveal-on-scroll card-interactive rounded-3xl border border-slate-200 bg-white p-6 shadow-sm sm:p-8">
                <div class="mb-6 flex items-start gap-4 border-b border-slate-100 pb-5">
                    <span class="flex h-11 w-11 shrink-0 items-center justify-center rounded-2xl bg-gradient-to-br from-emerald-500 to-teal-600 text-white shadow-md shadow-emerald-600/20">
                        <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M12 3 5 6v5c0 4.5 3 8.2 7 10 4-1.8 7-5.5 7-10V6l-7-3Zm-3 9 2 2 4-4" /></svg>
                    </span>
                    <div><h2 class="text-lg font-extrabold text-slate-950">Password &amp; security</h2><p class="mt-1 text-sm text-slate-500">Update your password anytime to keep your account protected.</p></div>
                </div>
                <div class="max-w-2xl">@include('profile.partials.update-password-form')</div>
            </section>

            <section class="reveal-on-scroll card-interactive rounded-3xl border border-rose-200/80 bg-white p-6 shadow-sm sm:p-8">
                <div class="max-w-2xl">@include('profile.partials.delete-user-form')</div>
            </section>
        </div>
    </div>
</x-app-layout>
