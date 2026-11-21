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
                                    <td class="px-5 py-4"><span class="rounded-full px-2.5 py-1 text-xs font-bold capitalize {{ $user->role === 'admin' ? 'bg-violet-50 text-violet-700' : ($user->role === 'rider' ? 'bg-amber-50 text-amber-700' : 'bg-indigo-50 text-indigo-700') }}">{{ $user->role }}</span></td>
                                    <td class="whitespace-nowrap px-5 py-4 text-sm text-slate-500">{{ $user->created_at->format('M j, Y') }}</td>
                                    <td class="whitespace-nowrap px-5 py-4 text-right sm:px-6">
                                        @if ($user->id === Auth::id())
                                            <span class="inline-flex items-center rounded-full border border-slate-200 bg-slate-50 px-2.5 py-1 text-[11px] font-semibold text-slate-400">Protected</span>
                                        @else
                                            <div class="inline-flex items-center justify-end gap-2">
                                                <button
                                                    type="button"
                                                    @click="openEdit(@js($userData))"
                                                    class="rounded-lg border border-indigo-200 bg-indigo-50/70 px-3 py-1.5 text-xs font-bold text-indigo-700 transition hover:bg-indigo-100"
                                                >
                                                    Edit
                                                </button>
                                                <form method="POST" action="{{ route('admin.users.destroy', $user) }}" onsubmit="return confirm('Delete {{ addslashes($user->name) }}\'s account permanently?');">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" class="rounded-lg border border-rose-200 bg-rose-50 px-3 py-1.5 text-xs font-bold text-rose-700 transition hover:bg-rose-100">
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
