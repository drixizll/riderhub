@auth
    @php
        $role = Auth::user()->role;
        $openRiderRequests = 0;
        if ($role === 'rider') {
            $openRiderRequests = \App\Models\Booking::query()
                ->whereNull('rider_id')
                ->where('status', 'pending')
                ->where(fn ($q) => $q->where('payment_method', 'cash')->orWhereHas('payment', fn ($p) => $p->where('status', 'paid')))
                ->count();
        }
    @endphp

    <nav
        aria-label="Mobile quick navigation"
        class="fixed inset-x-0 bottom-0 z-40 border-t border-slate-200/80 bg-white/92 px-2 pb-[max(0.5rem,env(safe-area-inset-bottom))] pt-1.5 shadow-[0_-10px_30px_-10px_rgba(15,23,42,0.12)] backdrop-blur-xl lg:hidden"
    >
        <div class="mx-auto grid max-w-md grid-cols-4 items-end gap-1">
            @if ($role === 'customer')
                <a href="{{ route('customer.dashboard') }}" class="group flex flex-col items-center justify-center gap-0.5 rounded-xl py-1.5 text-[10px] font-bold transition {{ request()->routeIs('customer.dashboard') ? 'text-indigo-600' : 'text-slate-500 hover:text-indigo-600' }}">
                    <svg class="h-5 w-5 transition group-active:scale-90" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="m3 10.5 9-7.5 9 7.5V20a1 1 0 0 1-1 1h-5.5v-6.5h-5V21H4a1 1 0 0 1-1-1v-9.5Z" /></svg>
                    <span>Home</span>
                    @if (request()->routeIs('customer.dashboard'))
                        <span class="h-1 w-4 rounded-full bg-indigo-600"></span>
                    @endif
                </a>

                <a href="{{ route('customer.bookings.create') }}" class="group -mt-4 flex flex-col items-center justify-center gap-0.5 text-[10px] font-extrabold text-indigo-700">
                    <span class="flex h-12 w-12 items-center justify-center rounded-2xl bg-gradient-to-br from-indigo-600 via-violet-600 to-fuchsia-600 text-white shadow-lg shadow-indigo-600/35 ring-4 ring-white transition group-active:scale-95">
                        <svg class="h-6 w-6" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.4" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M12 5v14m-7-7h14" /></svg>
                    </span>
                    <span>Book</span>
                </a>

                <a href="{{ route('customer.bookings.index') }}" class="group flex flex-col items-center justify-center gap-0.5 rounded-xl py-1.5 text-[10px] font-bold transition {{ request()->routeIs('customer.bookings.index', 'customer.bookings.show') ? 'text-indigo-600' : 'text-slate-500 hover:text-indigo-600' }}">
                    <svg class="h-5 w-5 transition group-active:scale-90" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M9 5H7a2 2 0 0 0-2 2v12a2 2 0 0 0 2 2h10a2 2 0 0 0 2-2V7a2 2 0 0 0-2-2h-2M9 5a2 2 0 0 0 2 2h2a2 2 0 0 0 2-2M9 5a2 2 0 0 1 2-2h2a2 2 0 0 1 2 2m-6 9 2 2 4-4" /></svg>
                    <span>Trips</span>
                    @if (request()->routeIs('customer.bookings.index', 'customer.bookings.show'))
                        <span class="h-1 w-4 rounded-full bg-indigo-600"></span>
                    @endif
                </a>

                <a href="{{ route('profile.edit') }}" class="group flex flex-col items-center justify-center gap-0.5 rounded-xl py-1.5 text-[10px] font-bold transition {{ request()->routeIs('profile.edit') ? 'text-indigo-600' : 'text-slate-500 hover:text-indigo-600' }}">
                    <svg class="h-5 w-5 transition group-active:scale-90" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M15.75 6a3.75 3.75 0 1 1-7.5 0 3.75 3.75 0 0 1 7.5 0ZM4.5 20.118a7.5 7.5 0 0 1 15 0A17.933 17.933 0 0 1 12 21.75c-2.676 0-5.216-.584-7.5-1.632Z" /></svg>
                    <span>Profile</span>
                    @if (request()->routeIs('profile.edit'))
                        <span class="h-1 w-4 rounded-full bg-indigo-600"></span>
                    @endif
                </a>
            @elseif ($role === 'rider')
                <a href="{{ route('rider.dashboard') }}" class="group flex flex-col items-center justify-center gap-0.5 rounded-xl py-1.5 text-[10px] font-bold transition {{ request()->routeIs('rider.dashboard') ? 'text-indigo-600' : 'text-slate-500 hover:text-indigo-600' }}">
                    <svg class="h-5 w-5 transition group-active:scale-90" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="m3 10.5 9-7.5 9 7.5V20a1 1 0 0 1-1 1h-5.5v-6.5h-5V21H4a1 1 0 0 1-1-1v-9.5Z" /></svg>
                    <span>Home</span>
                    @if (request()->routeIs('rider.dashboard'))
                        <span class="h-1 w-4 rounded-full bg-indigo-600"></span>
                    @endif
                </a>

                <a href="{{ route('rider.bookings.index') }}" class="group relative -mt-4 flex flex-col items-center justify-center gap-0.5 text-[10px] font-extrabold text-indigo-700">
                    <span class="relative flex h-12 w-12 items-center justify-center rounded-2xl bg-gradient-to-br from-indigo-600 via-violet-600 to-fuchsia-600 text-white shadow-lg shadow-indigo-600/35 ring-4 ring-white transition group-active:scale-95">
                        <svg class="h-6 w-6" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.1" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M12 21s7-4.5 7-11a7 7 0 1 0-14 0c0 6.5 7 11 7 11Z" /><circle cx="12" cy="10" r="2.5" /></svg>
                        @if ($openRiderRequests > 0)
                            <span class="absolute -right-1 -top-1 flex h-5 min-w-5 items-center justify-center rounded-full bg-amber-400 px-1 text-[10px] font-black text-slate-950 ring-2 ring-white">{{ $openRiderRequests > 9 ? '9+' : $openRiderRequests }}</span>
                        @endif
                    </span>
                    <span>Radar</span>
                </a>

                <a href="{{ route('rider.dashboard') }}#rider-earnings" class="group flex flex-col items-center justify-center gap-0.5 rounded-xl py-1.5 text-[10px] font-bold text-slate-500 transition hover:text-indigo-600">
                    <svg class="h-5 w-5 transition group-active:scale-90" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M12 6v12m-3-2.818.879.659c1.171.879 3.07.879 4.242 0 1.172-.879 1.172-2.303 0-3.182C13.536 12.219 12.768 12 12 12c-.725 0-1.45-.22-2.003-.659-1.106-.879-1.106-2.303 0-3.182s2.9-.879 4.006 0l.415.33M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" /></svg>
                    <span>Earnings</span>
                </a>

                <a href="{{ route('profile.edit') }}" class="group flex flex-col items-center justify-center gap-0.5 rounded-xl py-1.5 text-[10px] font-bold transition {{ request()->routeIs('profile.edit') ? 'text-indigo-600' : 'text-slate-500 hover:text-indigo-600' }}">
                    <svg class="h-5 w-5 transition group-active:scale-90" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M15.75 6a3.75 3.75 0 1 1-7.5 0 3.75 3.75 0 0 1 7.5 0ZM4.5 20.118a7.5 7.5 0 0 1 15 0A17.933 17.933 0 0 1 12 21.75c-2.676 0-5.216-.584-7.5-1.632Z" /></svg>
                    <span>Profile</span>
                    @if (request()->routeIs('profile.edit'))
                        <span class="h-1 w-4 rounded-full bg-indigo-600"></span>
                    @endif
                </a>
            @else
                <a href="{{ route('admin.dashboard') }}" class="group flex flex-col items-center justify-center gap-0.5 rounded-xl py-1.5 text-[10px] font-bold transition {{ request()->routeIs('admin.dashboard') ? 'text-indigo-600' : 'text-slate-500 hover:text-indigo-600' }}">
                    <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M3 13.125C3 12.504 3.504 12 4.125 12h2.25c.621 0 1.125.504 1.125 1.125v6.75C7.5 20.496 6.996 21 6.375 21h-2.25A1.125 1.125 0 0 1 3 19.875v-6.75ZM9.75 8.625c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125v11.25c0 .621-.504 1.125-1.125 1.125h-2.25a1.125 1.125 0 0 1-1.125-1.125V8.625ZM16.5 4.125c0-.621.504-1.125 1.125-1.125h2.25C20.496 3 21 3.504 21 4.125v15.75c0 .621-.504 1.125-1.125 1.125h-2.25a1.125 1.125 0 0 1-1.125-1.125V4.125Z" /></svg>
                    <span>Overview</span>
                </a>
                <a href="{{ route('admin.bookings.index') }}" class="group flex flex-col items-center justify-center gap-0.5 rounded-xl py-1.5 text-[10px] font-bold transition {{ request()->routeIs('admin.bookings.*') ? 'text-indigo-600' : 'text-slate-500 hover:text-indigo-600' }}">
                    <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M9 5H7a2 2 0 0 0-2 2v12a2 2 0 0 0 2 2h10a2 2 0 0 0 2-2V7a2 2 0 0 0-2-2h-2M9 5a2 2 0 0 0 2 2h2a2 2 0 0 0 2-2" /></svg>
                    <span>Bookings</span>
                </a>
                <a href="{{ route('admin.users.index') }}" class="group flex flex-col items-center justify-center gap-0.5 rounded-xl py-1.5 text-[10px] font-bold transition {{ request()->routeIs('admin.users.*') ? 'text-indigo-600' : 'text-slate-500 hover:text-indigo-600' }}">
                    <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M15 19.128a9.38 9.38 0 0 0 2.625.372 9.337 9.337 0 0 0 4.121-.952 4.125 4.125 0 0 0-7.533-2.493M15 19.128v-.003c0-1.113-.285-2.16-.786-3.07M15 19.128v.106A12.318 12.318 0 0 1 8.624 21c-2.331 0-4.512-.645-6.374-1.766l-.001-.109a6.375 6.375 0 0 1 11.964-3.07M12 6.375a3.375 3.375 0 1 1-6.75 0 3.375 3.375 0 0 1 6.75 0Zm8.25 2.25a2.625 2.625 0 1 1-5.25 0 2.625 2.625 0 0 1 5.25 0Z" /></svg>
                    <span>Users</span>
                </a>
                <a href="{{ route('profile.edit') }}" class="group flex flex-col items-center justify-center gap-0.5 rounded-xl py-1.5 text-[10px] font-bold transition {{ request()->routeIs('profile.edit') ? 'text-indigo-600' : 'text-slate-500 hover:text-indigo-600' }}">
                    <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M15.75 6a3.75 3.75 0 1 1-7.5 0 3.75 3.75 0 0 1 7.5 0ZM4.5 20.118a7.5 7.5 0 0 1 15 0A17.933 17.933 0 0 1 12 21.75c-2.676 0-5.216-.584-7.5-1.632Z" /></svg>
                    <span>Profile</span>
                </a>
            @endif
        </div>
    </nav>
@endauth
