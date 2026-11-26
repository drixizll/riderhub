@props(['user'])

@if ($user && $user->role === 'rider')
    @php
        $riderCode = 'RH-RDR-'.str_pad((string) $user->id, 4, '0', STR_PAD_LEFT);
        $status = $user->rider_status ?? 'verified';
        $avgRating = $user->ratingsReceived()->avg('rating');
        $ratingText = $avgRating ? number_format((float) $avgRating, 1).' ★' : 'New Partner';
        $initials = \Illuminate\Support\Str::of($user->name)->explode(' ')->filter()->take(2)->map(fn ($p) => mb_substr($p, 0, 1))->implode('') ?: 'R';
        $qrPayload = urlencode("RiderHub Verified Partner | {$riderCode} | {$user->name} | Plate: ".($user->plate_number ?: 'N/A'));
        $qrUrl = "https://api.qrserver.com/v1/create-qr-code/?size=140x140&margin=6&data={$qrPayload}";
    @endphp

    <section class="reveal-on-scroll overflow-hidden rounded-3xl border border-violet-300/40 bg-gradient-to-br from-[#120826] via-[#211047] to-[#0b0618] p-6 text-white shadow-2xl shadow-indigo-950/30 sm:p-8" data-revealed="true">
        <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <span class="inline-flex items-center gap-1.5 rounded-full border border-amber-300/35 bg-amber-400/15 px-3 py-1 text-[10px] font-extrabold uppercase tracking-widest text-amber-200">
                    🪪 Official Partner Credential
                </span>
                <h2 class="mt-2 text-xl font-extrabold tracking-tight text-white">RiderHub Verified Partner ID</h2>
                <p class="mt-0.5 text-xs text-violet-200/80">Show this digital badge to passengers for instant verification or print a copy for your lanyard.</p>
            </div>
            <button
                type="button"
                onclick="window.printRiderHubIdCard()"
                class="inline-flex shrink-0 items-center justify-center gap-2 rounded-xl bg-gradient-to-r from-violet-500 via-fuchsia-500 to-amber-400 px-4 py-2.5 text-xs font-extrabold text-white shadow-lg shadow-fuchsia-600/30 transition hover:brightness-110 active:scale-95"
            >
                <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M6 9V2h12v7M6 18H4a2 2 0 0 1-2-2v-5a2 2 0 0 1 2-2h16a2 2 0 0 1 2 2v5a2 2 0 0 1-2 2h-2M6 14h12v8H6v-8Z" /></svg>
                Print Partner ID
            </button>
        </div>

        <!-- Printable Digital ID Card -->
        <div id="riderhub-partner-id-card" class="relative mt-6 overflow-hidden rounded-2xl border border-white/20 bg-gradient-to-br from-[#1a0d38] via-[#2c1561] to-[#110826] p-5 shadow-inner sm:p-6">
            <div class="pointer-events-none absolute -right-12 -top-12 h-40 w-40 rounded-full bg-fuchsia-500/20 blur-2xl" aria-hidden="true"></div>
            <div class="pointer-events-none absolute -bottom-12 left-10 h-36 w-36 rounded-full bg-amber-400/15 blur-2xl" aria-hidden="true"></div>

            <div class="relative flex flex-col gap-5 sm:flex-row sm:items-center sm:justify-between">
                <div class="flex items-center gap-4">
                    <div class="relative h-20 w-20 shrink-0">
                        @if ($user->profile_photo_path)
                            <img src="{{ route('profile.photo.show', ['user' => $user->id, 'v' => $user->updated_at?->timestamp]) }}" alt="{{ $user->name }}" class="h-20 w-20 rounded-2xl border-2 border-amber-300/60 object-cover shadow-lg">
                        @else
                            <span class="flex h-20 w-20 items-center justify-center rounded-2xl border-2 border-amber-300/60 bg-violet-700 text-2xl font-black text-white shadow-lg">{{ $initials }}</span>
                        @endif
                        <span class="absolute -bottom-1.5 -right-1.5 rounded-lg px-1.5 py-0.5 text-[9px] font-black uppercase {{ $status === 'verified' ? 'bg-emerald-400 text-emerald-950' : 'bg-amber-400 text-slate-950' }}">
                            {{ $status === 'verified' ? '✓ KYC' : 'Pending' }}
                        </span>
                    </div>
                    <div class="min-w-0">
                        <p class="font-mono text-[11px] font-extrabold tracking-widest text-amber-300">{{ $riderCode }}</p>
                        <h3 class="mt-0.5 truncate text-lg font-black text-white sm:text-xl">{{ $user->name }}</h3>
                        <p class="text-xs font-semibold text-violet-200">{{ $user->municipality ?: 'Tagoloan' }}, Misamis Oriental</p>
                        <div class="mt-2 flex flex-wrap items-center gap-1.5 text-[11px] font-bold">
                            <span class="rounded-lg bg-white/10 px-2 py-0.5 text-white">🏍 {{ $user->vehicle_model ?: 'Motorcycle' }}</span>
                            <span class="rounded-lg bg-amber-400/20 px-2 py-0.5 font-mono text-amber-200">{{ $user->plate_number ?: 'PLATE PENDING' }}</span>
                            <span class="rounded-lg bg-emerald-400/20 px-2 py-0.5 text-emerald-200">{{ $ratingText }}</span>
                        </div>
                    </div>
                </div>

                <div class="flex shrink-0 items-center gap-3 self-start rounded-2xl border border-white/15 bg-white p-2.5 text-slate-900 shadow-lg sm:self-center">
                    <img src="{{ $qrUrl }}" alt="RiderHub Partner Verification QR" width="84" height="84" class="h-[84px] w-[84px] rounded-lg">
                    <div class="pr-1 text-left sm:hidden">
                        <p class="text-[10px] font-black uppercase tracking-wider text-indigo-700">Scan to Verify</p>
                        <p class="mt-0.5 text-[11px] font-semibold text-slate-600">Official RiderHub Misamis Oriental Partner</p>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <script>
        window.printRiderHubIdCard = () => {
            const card = document.getElementById('riderhub-partner-id-card');
            if (!card) return;
            const win = window.open('', '_blank', 'width=720,height=480');
            if (!win) {
                window.print();
                return;
            }
            win.document.write(`<!doctype html><html><head><title>RiderHub Partner ID — {{ e($user->name) }}</title><style>body{margin:0;padding:32px;font-family:system-ui,-apple-system,sans-serif;background:#f8fafc;display:flex;justify-content:center;align-items:center;min-height:100vh}.card{width:540px;border-radius:20px;background:linear-gradient(135deg,#1a0d38,#2c1561,#110826);color:#fff;padding:24px;box-shadow:0 20px 50px rgba(0,0,0,.25);display:flex;justify-content:space-between;align-items:center;gap:20px}.left{display:flex;align-items:center;gap:16px}.avatar{width:76px;height:76px;border-radius:16px;object-fit:cover;border:2px solid #fcd34d;background:#6d28d9;display:flex;align-items:center;justify-content:center;font-size:24px;font-weight:900;color:#fff}.code{font-family:monospace;font-size:11px;font-weight:800;letter-spacing:.12em;color:#fcd34d;margin:0}.name{font-size:20px;font-weight:900;margin:4px 0}.sub{font-size:12px;color:#ddd6fe;margin:0 0 8px}.pills span{display:inline-block;font-size:11px;font-weight:700;padding:3px 8px;border-radius:8px;background:rgba(255,255,255,.14);margin-right:6px}.qr{background:#fff;padding:8px;border-radius:14px;text-align:center}.qr img{width:88px;height:88px;display:block}</style></head><body><div class="card"><div class="left">@if ($user->profile_photo_path)<img class="avatar" src="{{ route('profile.photo.show', ['user' => $user->id, 'v' => $user->updated_at?->timestamp]) }}">@else<div class="avatar">{{ $initials }}</div>@endif<div><p class="code">{{ $riderCode }} · VERIFIED PARTNER</p><h2 class="name">{{ e($user->name) }}</h2><p class="sub">{{ e($user->municipality ?: 'Tagoloan') }}, Misamis Oriental</p><div class="pills"><span>🏍 {{ e($user->vehicle_model ?: 'Motorcycle') }}</span><span>{{ e($user->plate_number ?: 'PLATE PENDING') }}</span><span>{{ $ratingText }}</span></div></div></div><div class="qr"><img src="{{ $qrUrl }}"></div></div><script>window.onload=()=>setTimeout(()=>window.print(),300);<\/script></body></html>`);
            win.document.close();
        };
    </script>
@endif
