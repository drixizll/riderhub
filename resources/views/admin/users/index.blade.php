<x-app-layout>
    <x-slot name="header">
        <div>
            <p class="text-sm font-semibold text-indigo-600">Administration</p>
            <h1 class="mt-1 text-2xl font-extrabold tracking-tight text-slate-950 sm:text-3xl">People directory</h1>
            <p class="mt-1 text-sm text-slate-500">Search and review RiderHub accounts by role.</p>
        </div>
    </x-slot>

    <div class="mx-auto max-w-7xl space-y-6 px-4 py-8 sm:px-6 lg:px-8">
        <section class="rounded-2xl border border-slate-200 bg-white p-4 shadow-sm sm:p-5">
            <form method="GET" action="{{ route('admin.users.index') }}" class="grid gap-3 md:grid-cols-[1fr_220px_auto]">
                <label class="sr-only" for="search">Search people</label>
                <input id="search" name="search" type="search" value="{{ request('search') }}" placeholder="Search name, email, or phone"
                       class="w-full rounded-xl border border-slate-300 px-4 py-2.5 text-sm text-slate-900 placeholder:text-slate-400 focus:border-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-500/20">
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
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100">
                            @foreach ($users as $user)
                                <tr>
                                    <td class="whitespace-nowrap px-5 py-4 sm:px-6">
                                        <div class="flex items-center gap-3">
                                            <span class="flex h-9 w-9 items-center justify-center rounded-full bg-indigo-50 text-xs font-bold text-indigo-700">{{ \Illuminate\Support\Str::of($user->name)->explode(' ')->take(2)->map(fn ($part) => mb_substr($part, 0, 1))->implode('') }}</span>
                                            <span class="text-sm font-semibold text-slate-900">{{ $user->name }}</span>
                                        </div>
                                    </td>
                                    <td class="px-5 py-4 text-sm">
                                        <p class="text-slate-800">{{ $user->email }}</p>
                                        <p class="mt-1 text-xs text-slate-500">{{ $user->phone ?: 'No phone provided' }}</p>
                                    </td>
                                    <td class="px-5 py-4"><span class="rounded-full px-2.5 py-1 text-xs font-bold capitalize {{ $user->role === 'admin' ? 'bg-violet-50 text-violet-700' : ($user->role === 'rider' ? 'bg-amber-50 text-amber-700' : 'bg-indigo-50 text-indigo-700') }}">{{ $user->role }}</span></td>
                                    <td class="whitespace-nowrap px-5 py-4 text-sm text-slate-500">{{ $user->created_at->format('M j, Y') }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                <div class="border-t border-slate-100 px-5 py-4 sm:px-6">{{ $users->links() }}</div>
            @endif
        </section>
    </div>
</x-app-layout>
