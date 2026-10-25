<x-app-layout>
    <x-slot name="header">
        <div>
            <p class="text-sm font-semibold text-indigo-600">Account</p>
            <h1 class="mt-1 text-2xl font-extrabold tracking-tight text-slate-950">{{ __('Account settings') }}</h1>
            <p class="mt-1 text-sm text-slate-500">Manage your contact information, password, and account preferences.</p>
        </div>
    </x-slot>

    <div class="mx-auto max-w-5xl space-y-6 px-4 py-8 sm:px-6 lg:px-8">
        @if (session('status') === 'profile-updated')
            <div role="status" class="rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm font-medium text-emerald-800">Your account details have been saved.</div>
        @endif
        @if (session('status') === 'profile-photo-updated')
            <div role="status" class="rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm font-medium text-emerald-800">Your profile photo has been updated.</div>
        @endif
        @if ($errors->has('profile_photo'))
            <div role="alert" class="rounded-xl border border-rose-200 bg-rose-50 px-4 py-3 text-sm font-medium text-rose-800">{{ $errors->first('profile_photo') }}</div>
        @endif

        <section class="overflow-hidden rounded-3xl border border-slate-200 bg-white shadow-sm">
            <div class="relative overflow-hidden bg-gradient-to-br from-indigo-700 via-indigo-700 to-violet-800 px-6 py-8 text-white sm:px-9 sm:py-10">
                <div class="absolute -right-12 -top-24 h-64 w-64 rounded-full bg-white/10 blur-3xl" aria-hidden="true"></div>
                <div class="relative flex flex-col gap-6 sm:flex-row sm:items-center sm:justify-between">
                    <div class="flex items-center gap-5">
                        <div class="relative shrink-0">
                            @if ($user->profile_photo_path)
                                <img data-profile-photo-preview src="{{ \Illuminate\Support\Facades\Storage::url($user->profile_photo_path) }}" alt="{{ $user->name }} profile photo" class="h-20 w-20 rounded-2xl border-2 border-white/40 object-cover shadow-lg sm:h-24 sm:w-24">
                            @else
                                <span data-profile-photo-preview class="flex h-20 w-20 items-center justify-center rounded-2xl border-2 border-white/30 bg-white/15 text-2xl font-extrabold shadow-lg sm:h-24 sm:w-24">{{ \Illuminate\Support\Str::of($user->name)->explode(' ')->take(2)->map(fn ($part) => mb_substr($part, 0, 1))->implode('') }}</span>
                            @endif
                            <span class="absolute -bottom-2 -right-2 flex h-8 w-8 items-center justify-center rounded-xl bg-emerald-400 text-emerald-950 ring-4 ring-indigo-700" aria-hidden="true">
                                <svg class="h-4 w-4" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M16.704 5.29a1 1 0 0 1 .006 1.414l-7.25 7.31a1 1 0 0 1-1.42.004l-3.75-3.75a1 1 0 0 1 1.414-1.414l3.04 3.04 6.544-6.598a1 1 0 0 1 1.416-.006Z" clip-rule="evenodd" /></svg>
                            </span>
                        </div>
                        <div class="min-w-0">
                            <span class="inline-flex rounded-full border border-white/20 bg-white/10 px-2.5 py-1 text-[11px] font-bold uppercase tracking-wider text-indigo-100">{{ $user->role }} account</span>
                            <h2 class="mt-2 truncate text-2xl font-extrabold tracking-tight sm:text-3xl">{{ $user->name }}</h2>
                            <p class="mt-1 truncate text-sm text-indigo-100">{{ $user->email }}</p>
                            <p class="mt-1 text-xs text-indigo-200">RiderHub member since {{ $user->created_at->format('F Y') }}</p>
                        </div>
                    </div>
                    <form method="POST" action="{{ route('profile.photo') }}" enctype="multipart/form-data" class="flex flex-wrap items-center gap-3">
                        @csrf
                        <label for="profile_photo" class="inline-flex cursor-pointer items-center gap-2 rounded-xl border border-white/25 bg-white/10 px-4 py-2.5 text-sm font-bold text-white transition hover:bg-white/20 focus-within:ring-2 focus-within:ring-white">
                            <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M4 7h3l2-3h6l2 3h3a2 2 0 0 1 2 2v10a2 2 0 0 1-2 2H4a2 2 0 0 1-2-2V9a2 2 0 0 1 2-2Zm8 11a4 4 0 1 0 0-8 4 4 0 0 0 0 8Z" /></svg>
                            <span>{{ $user->profile_photo_path ? 'Change photo' : 'Add profile photo' }}</span>
                        </label>
                        <input id="profile_photo" name="profile_photo" type="file" accept="image/jpeg,image/png,image/webp" data-profile-photo-input class="sr-only">
                        <button type="submit" class="rounded-xl bg-white px-4 py-2.5 text-sm font-extrabold text-indigo-700 shadow-sm transition hover:bg-indigo-50">Save photo</button>
                    </form>
                </div>
            </div>
            <div class="grid divide-y divide-slate-100 sm:grid-cols-2 sm:divide-x sm:divide-y-0">
                @foreach ($profileStats as $stat)
                    <div class="px-6 py-5 sm:px-9">
                        <p class="text-xs font-bold uppercase tracking-wider text-slate-400">{{ $stat['label'] }}</p>
                        <p class="mt-1 text-2xl font-extrabold text-slate-900">{{ $stat['value'] }}</p>
                    </div>
                @endforeach
            </div>
        </section>

        <section class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm sm:p-7">
            <div class="mb-5 flex items-start gap-3">
                <span class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-indigo-50 text-indigo-600">
                    <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" aria-hidden="true"><circle cx="12" cy="8" r="4" stroke-width="1.8" /><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M4 21a8 8 0 0 1 16 0" /></svg>
                </span>
                <div><h2 class="text-lg font-bold text-slate-950">Personal information</h2><p class="mt-1 text-sm text-slate-500">Keep your contact details current for a smoother trip.</p></div>
            </div>
            <div class="max-w-2xl">@include('profile.partials.update-profile-information-form')</div>
        </section>

        <section class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm sm:p-7">
            <div class="mb-5 flex items-start gap-3">
                <span class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-emerald-50 text-emerald-600">
                    <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M12 3 5 6v5c0 4.5 3 8.2 7 10 4-1.8 7-5.5 7-10V6l-7-3Zm-3 9 2 2 4-4" /></svg>
                </span>
                <div><h2 class="text-lg font-bold text-slate-950">Password & security</h2><p class="mt-1 text-sm text-slate-500">Update your password to help keep your account safe.</p></div>
            </div>
            <div class="max-w-2xl">@include('profile.partials.update-password-form')</div>
        </section>

        <section class="rounded-2xl border border-rose-200 bg-white p-5 shadow-sm sm:p-7">
            <div class="max-w-2xl">@include('profile.partials.delete-user-form')</div>
        </section>
    </div>
</x-app-layout>
