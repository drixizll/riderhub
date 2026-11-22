<x-app-layout>
    <x-slot name="header">
        <div>
            <p class="text-sm font-semibold text-indigo-600">Administration</p>
            <h1 class="mt-1 text-2xl font-extrabold tracking-tight text-slate-950 sm:text-3xl">People directory</h1>
            <p class="mt-1 text-sm text-slate-500">Search, update roles or contact info, and manage RiderHub accounts.</p>
        </div>
    </x-slot>

    <div
        x-data="{
            editing: null,
            openEdit(user) {
                this.editing = { ...user };
            }
        }"
        class="mx-auto max-w-7xl space-y-6 px-4 py-8 sm:px-6 lg:px-8"
    >
        @if (session('error'))
            <div role="alert" class="rounded-xl border border-rose-200 bg-rose-50 px-4 py-3 text-sm font-semibold text-rose-800">{{ session('error') }}</div>
        @endif

        @if ($errors->any())
            <div role="alert" class="rounded-xl border border-rose-200 bg-rose-50 px-4 py-3 text-sm text-rose-800">
                <ul class="list-inside list-disc space-y-1">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <section class="rounded-2xl border border-slate-200 bg-white p-4 shadow-sm sm:p-5">
            <form method="GET" action="{{ route('admin.users.index') }}" class="grid gap-3 md:grid-cols-[1fr_220px_auto]">
                <label class="sr-only" for="search">Search people</label>
                <input id="search" name="search" type="search" value="{{ request('search') }}" placeholder="Search name, email, or phone"
                       class="w-full rounded-xl border border-slate-300 bg-white px-4 py-2.5 text-sm text-slate-900 placeholder:text-slate-400 focus:border-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-500/20">
                <label class="sr-only" for="role">Filter by role</label>
                <select id="role" name="role" class="rounded-xl border border-slate-300 bg-white px-3 py-2.5 text-sm text-slate-700 focus:border-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-500/20">
                    <option value="">All roles</option>
                    @foreach (['customer', 'rider', 'admin'] as $role)
                        <option value="{{ $role }}" @selected(request('role') === $role)>{{ ucfirst($role) }}</option>
                    @endforeach
                </select>
                <div class="flex gap-2">
                    <button type="submit" class="flex-1 rounded-xl bg-indigo-600 px-4 py-2.5 text-sm font-bold text-white transition hover:bg-indigo-700">Search</button>
                    @if (request()->hasAny(['search', 'role']))
                        <a href="{{ route('admin.users.index') }}" class="inline-flex items-center rounded-xl border border-slate-200 px-3 py-2.5 text-sm font-semibold text-slate-600 hover:bg-slate-50">Clear</a>
                    @endif
                </div>
            </form>
        </section>

        <section class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm">
            <div class="border-b border-slate-100 px-5 py-4 sm:px-6">
                <h2 class="font-bold text-slate-950">Registered accounts</h2>
                <p class="mt-1 text-xs text-slate-500">{{ $users->total() }} results</p>
            </div>
            @if ($users->isEmpty())
                <div class="px-6 py-14 text-center">
                    <h3 class="font-bold text-slate-900">No matching accounts</h3>
                    <p class="mt-2 text-sm text-slate-500">Try changing your search or role filter.</p>
                </div>
            @else
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-slate-100">
                        <thead class="bg-slate-50">
                            <tr class="text-left text-xs font-bold uppercase tracking-wider text-slate-500">
                                <th scope="col" class="px-5 py-3 sm:px-6">Name</th>
                                <th scope="col" class="px-5 py-3">Contact</th>
                                <th scope="col" class="px-5 py-3">Role</th>
                                <th scope="col" class="px-5 py-3">Joined</th>
                                <th scope="col" class="px-5 py-3 text-right sm:px-6">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100">
                            @foreach ($users as $user)
                                @php
                                    $userData = [
                                        'id' => $user->id,
                                        'name' => $user->name,
                                        'email' => $user->email,
                                        'phone' => $user->phone ?? '',
                                        'role' => $user->role,
                                        'rider_status' => $user->rider_status ?? 'verified',
                                        'vehicle_model' => $user->vehicle_model ?? '',
                                        'plate_number' => $user->plate_number ?? '',
                                        'updateUrl' => route('admin.users.update', $user),
                                    ];
                                @endphp
                                <tr class="transition hover:bg-slate-50/70">
                                    <td class="whitespace-nowrap px-5 py-4 sm:px-6">
                                        <div class="flex items-center gap-3">
                                            <span class="flex h-9 w-9 items-center justify-center rounded-full bg-indigo-50 text-xs font-bold text-indigo-700">{{ \Illuminate\Support\Str::of($user->name)->explode(' ')->take(2)->map(fn ($part) => mb_substr($part, 0, 1))->implode('') }}</span>
                                            <div>
                                                <span class="text-sm font-semibold text-slate-900">{{ $user->name }}</span>
                                                @if ($user->id === Auth::id())
                                                    <span class="ml-1.5 rounded-full bg-emerald-50 px-2 py-0.5 text-[10px] font-bold text-emerald-700">You</span>
                                                @endif
                                            </div>
                                        </div>
                                    </td>
                                    <td class="px-5 py-4 text-sm">
                                        <p class="text-slate-800">{{ $user->email }}</p>
                                        <p class="mt-1 text-xs text-slate-500">{{ $user->phone ?: 'No phone provided' }}</p>
                                    </td>
                                    <td class="px-5 py-4">
                                        <div class="flex flex-wrap items-center gap-1.5">
                                            <span class="rounded-full px-2.5 py-1 text-xs font-bold capitalize {{ $user->role === 'admin' ? 'bg-violet-50 text-violet-700' : ($user->role === 'rider' ? 'bg-amber-50 text-amber-700' : 'bg-indigo-50 text-indigo-700') }}">{{ $user->role }}</span>
                                            @if ($user->role === 'rider')
                                                @php $rStatus = $user->rider_status ?? 'verified'; @endphp
                                                <span class="rounded-full px-2 py-0.5 text-[10px] font-bold {{ $rStatus === 'verified' ? 'bg-emerald-50 text-emerald-700' : ($rStatus === 'pending' ? 'bg-amber-100 text-amber-800' : 'bg-rose-100 text-rose-700') }}">
                                                    {{ $rStatus === 'verified' ? '✓ Verified' : ucfirst($rStatus) }}
                                                </span>
                                                <span class="rounded-full px-2 py-0.5 text-[10px] font-semibold {{ ($user->is_online ?? true) ? 'bg-emerald-50 text-emerald-700' : 'bg-slate-100 text-slate-500' }}">
                                                    {{ ($user->is_online ?? true) ? '● Online' : '○ Offline' }}
                                                </span>
                                            @endif
                                        </div>
                                        @if ($user->role === 'rider' && ($user->vehicle_model || $user->plate_number || $user->license_photo_path))
                                            <div class="mt-1 flex flex-wrap items-center gap-2 text-[11px] text-slate-500">
                                                @if ($user->vehicle_model || $user->plate_number)
                                                    <span>{{ $user->vehicle_model }} {{ $user->plate_number ? "({$user->plate_number})" : '' }}</span>
                                                @endif
                                                @if ($user->license_photo_path)
                                                    <a href="{{ route('profile.license.show', $user) }}" target="_blank" class="inline-flex items-center rounded-md bg-indigo-50 px-1.5 py-0.5 font-bold text-indigo-700 hover:bg-indigo-100">📄 View License</a>
                                                @endif
                                            </div>
                                        @endif
                                    </td>
                                    <td class="whitespace-nowrap px-5 py-4 text-sm text-slate-500">{{ $user->created_at->format('M j, Y') }}</td>
                                    <td class="whitespace-nowrap px-5 py-4 text-right sm:px-6">
                                        @if ($user->id === Auth::id())
                                            <span class="inline-flex items-center rounded-full border border-slate-200 bg-slate-50 px-2.5 py-1 text-[11px] font-semibold text-slate-400">Protected</span>
                                        @else
                                            <div class="inline-flex items-center justify-end gap-2">
                                                <button
                                                    type="button"
                                                    @click="openEdit(@js($userData))"
                                                    class="inline-flex items-center gap-1.5 rounded-xl bg-gradient-to-r from-indigo-600 to-violet-600 px-3.5 py-1.5 text-xs font-bold text-white shadow-sm shadow-indigo-600/25 transition duration-200 hover:-translate-y-0.5 hover:shadow-md active:scale-95"
                                                >
                                                    <svg class="h-3.5 w-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="m16.862 4.487 1.687-1.688a1.875 1.875 0 1 1 2.652 2.652L10.582 16.07a4.5 4.5 0 0 1-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 0 1 1.13-1.897l8.932-8.931Z" /></svg>
                                                    Edit
                                                </button>
                                                <form method="POST"
                                                      action="{{ route('admin.users.destroy', $user) }}"
                                                      data-confirm-title="Delete account?"
                                                      data-confirm-message="Are you sure you want to permanently delete {{ $user->name }}'s account? This action cannot be undone."
                                                      data-confirm-button="Yes, delete"
                                                      data-confirm-tone="danger">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" class="inline-flex items-center gap-1.5 rounded-xl bg-gradient-to-r from-rose-600 to-pink-600 px-3.5 py-1.5 text-xs font-bold text-white shadow-sm shadow-rose-600/25 transition duration-200 hover:-translate-y-0.5 hover:shadow-md active:scale-95">
                                                        <svg class="h-3.5 w-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="m14.74 9-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 0 1-2.244 2.077H8.084a2.25 2.25 0 0 1-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 0 0-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 0 1 3.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 0 0-3.32 0c-1.18.037-2.09 1.022-2.09 2.201v.916m7.5 0a48.667 48.667 0 0 0-7.5 0" /></svg>
                                                        Delete
                                                    </button>
                                                </form>
                                            </div>
                                        @endif
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                <div class="border-t border-slate-100 px-5 py-4 sm:px-6">{{ $users->links() }}</div>
            @endif
        </section>

        <!-- Edit User Modal -->
        <div
            x-show="editing !== null"
            x-cloak
            x-transition.opacity.duration.200ms
            class="fixed inset-0 z-50 flex items-center justify-center bg-slate-950/55 p-4 backdrop-blur-xs"
            @keydown.escape.window="editing = null"
        >
            <div
                @click.outside="editing = null"
                x-show="editing !== null"
                x-transition:enter="transition ease-out duration-200"
                x-transition:enter-start="opacity-0 scale-95 translate-y-2"
                x-transition:enter-end="opacity-100 scale-100 translate-y-0"
                class="w-full max-w-md overflow-hidden rounded-3xl border border-slate-200 bg-white shadow-2xl"
            >
                <div class="flex items-center justify-between bg-gradient-to-r from-indigo-600 to-violet-700 px-6 py-4 text-white">
                    <div>
                        <p class="text-[11px] font-bold uppercase tracking-wider text-indigo-100">Admin Management</p>
                        <h3 class="text-lg font-extrabold">Update account</h3>
                    </div>
                    <button type="button" @click="editing = null" class="rounded-xl p-1.5 text-indigo-100 transition hover:bg-white/15 hover:text-white" aria-label="Close modal">
                        <svg class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor"><path d="M6.28 5.22a.75.75 0 0 0-1.06 1.06L8.94 10l-3.72 3.72a.75.75 0 1 0 1.06 1.06L10 11.06l3.72 3.72a.75.75 0 1 0 1.06-1.06L11.06 10l3.72-3.72a.75.75 0 0 0-1.06-1.06L10 8.94 6.28 5.22Z" /></svg>
                    </button>
                </div>

                <template x-if="editing">
                    <form method="POST" :action="editing.updateUrl" class="space-y-4 p-6">
                        @csrf
                        @method('PATCH')

                        <div>
                            <label class="block text-xs font-bold uppercase tracking-wider text-slate-500">Full name</label>
                            <input type="text" name="name" x-model="editing.name" required class="mt-1.5 block w-full rounded-xl border border-slate-300 bg-white px-4 py-2.5 text-sm text-slate-900 focus:border-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-500/20">
                        </div>

                        <div>
                            <label class="block text-xs font-bold uppercase tracking-wider text-slate-500">Email address</label>
                            <input type="email" name="email" x-model="editing.email" required class="mt-1.5 block w-full rounded-xl border border-slate-300 bg-white px-4 py-2.5 text-sm text-slate-900 focus:border-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-500/20">
                        </div>

                        <div>
                            <label class="block text-xs font-bold uppercase tracking-wider text-slate-500">Phone number</label>
                            <input type="tel" name="phone" x-model="editing.phone" class="mt-1.5 block w-full rounded-xl border border-slate-300 bg-white px-4 py-2.5 text-sm text-slate-900 focus:border-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-500/20">
                        </div>

                        <div>
                            <label class="block text-xs font-bold uppercase tracking-wider text-slate-500">Role</label>
                            <select name="role" x-model="editing.role" class="mt-1.5 block w-full rounded-xl border border-slate-300 bg-white px-3 py-2.5 text-sm text-slate-900 focus:border-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-500/20">
                                <option value="customer">Customer</option>
                                <option value="rider">Rider</option>
                                <option value="admin">Admin</option>
                            </select>
                        </div>

                        <div x-show="editing.role === 'rider'" class="space-y-3 rounded-2xl border border-indigo-100 bg-indigo-50/40 p-3.5">
                            <div>
                                <label class="block text-xs font-bold uppercase tracking-wider text-indigo-700">Rider KYC Verification</label>
                                <select name="rider_status" x-model="editing.rider_status" class="mt-1.5 block w-full rounded-xl border border-slate-300 bg-white px-3 py-2 text-sm text-slate-900 focus:border-indigo-500 focus:outline-none">
                                    <option value="verified">Verified (Can Accept Trips)</option>
                                    <option value="pending">Pending Review (Blocked)</option>
                                    <option value="suspended">Suspended (Blocked)</option>
                                </select>
                            </div>
                            <div class="grid grid-cols-2 gap-2.5">
                                <div>
                                    <label class="block text-[11px] font-bold uppercase tracking-wider text-slate-500">Vehicle Model</label>
                                    <input type="text" name="vehicle_model" x-model="editing.vehicle_model" placeholder="Honda Click 125" class="mt-1 block w-full rounded-xl border border-slate-300 bg-white px-3 py-2 text-xs text-slate-900">
                                </div>
                                <div>
                                    <label class="block text-[11px] font-bold uppercase tracking-wider text-slate-500">Plate Number</label>
                                    <input type="text" name="plate_number" x-model="editing.plate_number" placeholder="123 ABC" class="mt-1 block w-full rounded-xl border border-slate-300 bg-white px-3 py-2 text-xs uppercase text-slate-900">
                                </div>
                            </div>
                        </div>

                        <div class="flex justify-end gap-2 pt-2">
                            <x-secondary-button type="button" @click="editing = null">Cancel</x-secondary-button>
                            <x-primary-button>Save changes</x-primary-button>
                        </div>
                    </form>
                </template>
            </div>
        </div>
    </div>
</x-app-layout>
