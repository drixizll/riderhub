<section>
    <header>
        <h2 class="text-lg font-bold text-slate-950">
            {{ __('Profile Information') }}
        </h2>

        <p class="mt-1 text-sm leading-6 text-slate-500">
            {{ __("Update your account's profile information and email address.") }}
        </p>
    </header>

    <form id="send-verification" method="post" action="{{ route('verification.send') }}">
        @csrf
    </form>

    <form method="post" action="{{ route('profile.update') }}" class="mt-6 space-y-5">
        @csrf
        @method('patch')

        <div>
            <x-input-label for="name" :value="__('Full name')" />
            <x-text-input id="name" name="name" type="text" class="mt-2 block w-full px-4 py-3 text-sm" :value="old('name', $user->name)" required autofocus autocomplete="name" />
            <x-input-error class="mt-2" :messages="$errors->get('name')" />
        </div>

        <div>
            <x-input-label for="email" :value="__('Email address')" />
            <x-text-input id="email" name="email" type="email" class="mt-2 block w-full px-4 py-3 text-sm" :value="old('email', $user->email)" required autocomplete="username" />
            <x-input-error class="mt-2" :messages="$errors->get('email')" />

            @if ($user instanceof \Illuminate\Contracts\Auth\MustVerifyEmail && ! $user->hasVerifiedEmail())
                <div>
                    <p class="mt-2 text-sm text-amber-800">
                        {{ __('Your email address is unverified.') }}

                        <button form="send-verification" class="font-semibold text-indigo-700 underline hover:text-indigo-900 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2">
                            {{ __('Click here to re-send the verification email.') }}
                        </button>
                    </p>

                    @if (session('status') === 'verification-link-sent')
                        <p class="mt-2 text-sm font-medium text-emerald-700">
                            {{ __('A new verification link has been sent to your email address.') }}
                        </p>
                    @endif
                </div>
            @endif
        </div>

        <div>
            <x-input-label for="phone" :value="__('Phone number')" />
            <x-text-input id="phone" name="phone" type="tel" class="mt-2 block w-full px-4 py-3 text-sm" :value="old('phone', $user->phone)" autocomplete="tel" />
            <x-input-error class="mt-2" :messages="$errors->get('phone')" />
        </div>

        @if ($user->role === 'rider')
            <div class="grid gap-4 rounded-2xl border border-indigo-100 bg-indigo-50/40 p-4 sm:grid-cols-2">
                <div>
                    <x-input-label for="vehicle_model" :value="__('Motorcycle / Vehicle Model')" />
                    <x-text-input id="vehicle_model" name="vehicle_model" type="text" placeholder="e.g. Honda Click 125i" class="mt-2 block w-full px-4 py-3 text-sm" :value="old('vehicle_model', $user->vehicle_model)" />
                    <x-input-error class="mt-2" :messages="$errors->get('vehicle_model')" />
                </div>
                <div>
                    <x-input-label for="plate_number" :value="__('Plate Number')" />
                    <x-text-input id="plate_number" name="plate_number" type="text" placeholder="e.g. 123 ABC" class="mt-2 block w-full px-4 py-3 text-sm uppercase" :value="old('plate_number', $user->plate_number)" />
                    <x-input-error class="mt-2" :messages="$errors->get('plate_number')" />
                </div>
            </div>
        @endif

        <div class="flex items-center gap-4">
            <x-primary-button>{{ __('Save') }}</x-primary-button>

            @if (session('status') === 'profile-updated')
                <p
                    x-data="{ show: true }"
                    x-show="show"
                    x-transition
                    x-init="setTimeout(() => show = false, 2000)"
                    class="text-sm font-medium text-emerald-700"
                >{{ __('Saved.') }}</p>
            @endif
        </div>
    </form>

    @if ($user->role === 'rider')
        <div class="mt-8 border-t border-slate-200 pt-6">
            <h3 class="text-base font-bold text-slate-950">Driver's License (KYC Verification)</h3>
            <p class="mt-1 text-xs leading-5 text-slate-500">Upload a clear photo of your valid Philippine Driver's License. Only you and RiderHub Administrators can view this document.</p>

            @if ($user->license_photo_path)
                <div class="mt-3 flex items-center gap-3 rounded-xl border border-emerald-200 bg-emerald-50/70 p-3">
                    <img src="{{ route('profile.license.show', $user) }}" alt="Driver's license preview" class="h-14 w-20 rounded-lg border border-emerald-200 object-cover">
                    <div class="text-xs">
                        <p class="font-bold text-emerald-800">License uploaded ✓</p>
                        <a href="{{ route('profile.license.show', $user) }}" target="_blank" class="font-semibold text-indigo-700 underline hover:text-indigo-900">View full image</a>
                    </div>
                </div>
            @endif

            <form method="POST" action="{{ route('profile.license') }}" enctype="multipart/form-data" class="mt-4 flex flex-wrap items-center gap-3">
                @csrf
                <input type="file" name="license_photo" accept="image/jpeg,image/png,image/webp" required class="block text-xs text-slate-600 file:mr-3 file:rounded-xl file:border-0 file:bg-indigo-50 file:px-4 file:py-2 file:text-xs file:font-bold file:text-indigo-700 hover:file:bg-indigo-100">
                <x-primary-button>{{ $user->license_photo_path ? 'Replace License' : 'Upload License' }}</x-primary-button>
                @if (session('status') === 'license-photo-updated')
                    <span class="text-xs font-bold text-emerald-700">License uploaded!</span>
                @endif
                <x-input-error class="w-full" :messages="$errors->get('license_photo')" />
            </form>
        </div>
    @endif
</section>
