<x-app-layout>
    <x-slot name="header">
        <div>
            <p class="text-sm font-semibold text-indigo-600">Rider workspace</p>
            <h1 class="mt-1 text-2xl font-extrabold tracking-tight text-slate-950 sm:text-3xl">Booking requests</h1>
            <p class="mt-1 text-sm text-slate-500">Review open rides and deliveries available to accept.</p>
        </div>
    </x-slot>

    <div class="mx-auto max-w-7xl space-y-6 px-4 py-8 sm:px-6 lg:px-8">
        @if (session('success'))
            <div role="status" class="rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm font-medium text-emerald-800">{{ session('success') }}</div>
        @endif

        @if ($activeBooking)
            <section class="rounded-2xl border border-indigo-100 bg-indigo-50/70 p-5 sm:p-6">
                <p class="text-sm font-bold text-indigo-800">You have an active booking</p>
                <p class="mt-1 text-sm text-indigo-900">{{ ucfirst($activeBooking->type) }} to {{ $activeBooking->dropoff_address }} · {{ str_replace('_', ' ', $activeBooking->status) }}</p>
                <a href="{{ route('rider.dashboard') }}" class="mt-3 inline-flex text-sm font-bold text-indigo-700 hover:text-indigo-900">Manage active booking <span class="ml-1" aria-hidden="true">→</span></a>
            </section>
        @endif

        <section class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm">
            <div class="border-b border-slate-100 px-5 py-5 sm:px-6">
                <h2 class="text-lg font-bold text-slate-950">Available now</h2>
                <p class="mt-1 text-sm text-slate-500">{{ $bookings->total() }} open {{ \Illuminate\Support\Str::plural('request', $bookings->total()) }}</p>
            </div>
            @include('rider.bookings.partials.list', ['bookings' => $bookings, 'activeBooking' => $activeBooking])
        </section>
    </div>
</x-app-layout>
