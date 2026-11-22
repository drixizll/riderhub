@props(['booking'])

@php
    $isRider = auth()->id() === $booking->rider_id;
    $partner = $isRider ? $booking->customer : $booking->rider;
    $partnerName = $partner?->name ?? ($isRider ? 'Customer' : 'Rider');
    $partnerInitials = \Illuminate\Support\Str::of($partnerName)->explode(' ')->filter()->take(2)->map(fn ($p) => mb_substr($p, 0, 1))->implode('') ?: 'U';
    $partnerAvatar = $partner?->profile_photo_path
        ? route('profile.photo.show', ['user' => $partner->id, 'v' => $partner->updated_at?->timestamp])
        : null;
    $partnerGender = in_array($partner?->gender, ['male', 'female'], true) ? ucfirst($partner->gender) : null;
    $quickReplies = $isRider
        ? ['Papunta na po ako!', 'Nandito na po ako sa pickup', '5 mins away na po', 'Salamat po!']
        : ['Nasa pickup na po ako', 'Nasaan na po kayo?', 'Naka-wait po ako sa labas', 'Salamat po!'];
@endphp

<div
    data-booking-chat
    data-fetch-url="{{ route('bookings.messages.index', $booking) }}"
    data-send-url="{{ route('bookings.messages.store', $booking) }}"
    data-csrf="{{ csrf_token() }}"
    data-partner-avatar="{{ $partnerAvatar ?? '' }}"
    data-partner-initials="{{ $partnerInitials }}"
    class="overflow-hidden rounded-2xl border border-indigo-100 bg-white shadow-sm"
>
    <div class="flex items-center justify-between gap-3 border-b border-slate-100 bg-gradient-to-r from-indigo-600 via-violet-600 to-indigo-700 px-4 py-3 text-white">
        <div class="flex min-w-0 items-center gap-3">
            <div class="relative h-10 w-10 shrink-0">
                @if ($partnerAvatar)
                    <img src="{{ $partnerAvatar }}" alt="{{ $partnerName }}" class="h-10 w-10 rounded-full border-2 border-white/40 object-cover shadow-sm">
                @else
                    <span class="flex h-10 w-10 items-center justify-center rounded-full border-2 border-white/30 bg-white/15 text-xs font-extrabold text-white backdrop-blur-sm">
                        {{ $partnerInitials }}
                    </span>
                @endif
                <span data-partner-dot class="absolute -bottom-0.5 -right-0.5 h-3 w-3 rounded-full border-2 border-indigo-700 bg-slate-300"></span>
            </div>
            <div class="min-w-0">
                <div class="flex items-center gap-1.5">
                    <p class="truncate text-sm font-extrabold">{{ $partnerName }}</p>
                    @if ($partnerGender)
                        <span class="shrink-0 rounded-full bg-white/20 px-2 py-0.5 text-[10px] font-bold text-indigo-50">{{ $partnerGender }}</span>
                    @endif
                </div>
                <p data-partner-presence class="truncate text-[11px] font-semibold text-indigo-100">Connecting…</p>
            </div>
        </div>
        <span data-chat-status class="shrink-0 rounded-full bg-white/15 px-2.5 py-1 text-[11px] font-bold text-indigo-50">Live Chat</span>
    </div>

    <div data-chat-list class="h-64 space-y-2.5 overflow-y-auto bg-slate-50/70 p-4 text-sm">
        <p data-chat-empty class="py-10 text-center text-xs text-slate-400">No messages yet. Say hi to {{ $partnerName }} below!</p>
    </div>

    <div class="border-t border-slate-100 bg-white px-3.5 pt-2.5">
        <div class="flex flex-wrap gap-1.5">
            @foreach ($quickReplies as $reply)
                <button
                    type="button"
                    data-quick-reply="{{ $reply }}"
                    class="rounded-full border border-indigo-100 bg-indigo-50/70 px-2.5 py-1 text-[11px] font-semibold text-indigo-700 transition hover:bg-indigo-100"
                >{{ $reply }}</button>
            @endforeach
        </div>
    </div>

    <form data-chat-form class="flex items-center gap-2 bg-white p-3.5">
        <input
            type="text"
            data-chat-input
            maxlength="500"
            autocomplete="off"
            placeholder="Message {{ $partnerName }}…"
            class="flex-1 rounded-xl border border-slate-200 bg-slate-50 px-3.5 py-2.5 text-sm text-slate-900 placeholder:text-slate-400 focus:border-indigo-500 focus:bg-white focus:outline-none focus:ring-2 focus:ring-indigo-500/20"
        >
        <button
            type="submit"
            data-chat-submit
            class="inline-flex shrink-0 items-center gap-1.5 rounded-xl bg-gradient-to-r from-indigo-600 to-violet-600 px-4 py-2.5 text-sm font-bold text-white shadow-sm transition hover:opacity-95 disabled:opacity-50"
        >
            Send
            <svg class="h-4 w-4" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true"><path d="M3.105 2.288a.75.75 0 0 0-.826.95l1.414 4.926A1.5 1.5 0 0 0 5.135 9.25h6.115a.75.75 0 0 1 0 1.5H5.135a1.5 1.5 0 0 0-1.442 1.086l-1.414 4.926a.75.75 0 0 0 .826.95 28.897 28.897 0 0 0 15.293-7.155.75.75 0 0 0 0-1.114A28.897 28.897 0 0 0 3.105 2.288Z" /></svg>
        </button>
    </form>
</div>

<script>
    (() => {
        const initChat = (root) => {
            if (root.dataset.chatBound) return;
            root.dataset.chatBound = '1';

            const fetchUrl = root.dataset.fetchUrl;
            const sendUrl = root.dataset.sendUrl;
            const csrf = root.dataset.csrf;
            const partnerAvatarFallback = root.dataset.partnerAvatar || '';
            const partnerInitialsFallback = root.dataset.partnerInitials || 'U';
            const listEl = root.querySelector('[data-chat-list]');
            const emptyEl = root.querySelector('[data-chat-empty]');
            const form = root.querySelector('[data-chat-form]');
            const input = root.querySelector('[data-chat-input]');
            const submitBtn = root.querySelector('[data-chat-submit]');
            const statusEl = root.querySelector('[data-chat-status]');
            const presenceEl = root.querySelector('[data-partner-presence]');
            const dotEl = root.querySelector('[data-partner-dot]');

            let lastId = 0;
            let lastMyMsgId = 0;
            let seenUpToId = 0;
            let partnerOnline = false;
            let partnerAvatar = partnerAvatarFallback;
            let partnerInitials = partnerInitialsFallback;
            let initialized = false;

            const playPing = () => {
                try {
                    const AudioCtx = window.AudioContext || window.webkitAudioContext;
                    if (!AudioCtx) return;
                    const ctx = new AudioCtx();
                    const osc = ctx.createOscillator();
                    const gain = ctx.createGain();
                    osc.type = 'sine';
                    osc.frequency.setValueAtTime(880, ctx.currentTime);
                    osc.frequency.exponentialRampToValueAtTime(1174, ctx.currentTime + 0.12);
                    gain.gain.setValueAtTime(0.08, ctx.currentTime);
                    gain.gain.exponentialRampToValueAtTime(0.001, ctx.currentTime + 0.22);
                    osc.connect(gain);
                    gain.connect(ctx.destination);
                    osc.start();
                    osc.stop(ctx.currentTime + 0.24);
                } catch {}
            };

            const updateReceipts = () => {
                root.querySelectorAll('[data-receipt-for]').forEach((el) => {
                    const id = Number(el.dataset.receiptFor || 0);
                    if (id !== lastMyMsgId) {
                        el.innerHTML = '';
                        return;
                    }
                    if (id <= seenUpToId) {
                        const mini = partnerAvatar
                            ? `<img src="${partnerAvatar}" alt="" class="inline-block h-3.5 w-3.5 rounded-full object-cover ring-1 ring-indigo-300">`
                            : `<span class="inline-flex h-3.5 w-3.5 items-center justify-center rounded-full bg-indigo-500 text-[8px] font-bold text-white">${partnerInitials}</span>`;
                        el.innerHTML = `<span class="inline-flex items-center gap-1 text-indigo-600 font-bold">Seen ✓✓ ${mini}</span>`;
                    } else if (partnerOnline) {
                        el.innerHTML = `<span class="text-emerald-600 font-semibold">Delivered ✓✓</span>`;
                    } else {
                        el.innerHTML = `<span class="text-slate-400 font-semibold">Sent ✓</span>`;
                    }
                });
            };

            const appendMessage = (m) => {
                if (m.id <= lastId) return;
                lastId = m.id;
                if (m.is_mine) lastMyMsgId = Math.max(lastMyMsgId, m.id);
                if (emptyEl) emptyEl.remove();

                const row = document.createElement('div');
                row.className = `flex items-end gap-2 ${m.is_mine ? 'justify-end' : 'justify-start'}`;

                if (!m.is_mine) {
                    const av = document.createElement(m.sender_avatar ? 'img' : 'span');
                    if (m.sender_avatar) {
                        av.src = m.sender_avatar;
                        av.alt = m.sender_name;
                        av.className = 'mb-5 h-7 w-7 shrink-0 rounded-full object-cover ring-1 ring-slate-200';
                    } else {
                        av.className = 'mb-5 flex h-7 w-7 shrink-0 items-center justify-center rounded-full bg-indigo-100 text-[10px] font-extrabold text-indigo-700';
                        av.textContent = m.sender_initials || 'U';
                    }
                    row.appendChild(av);
                }

                const wrap = document.createElement('div');
                wrap.className = `flex max-w-[80%] flex-col ${m.is_mine ? 'items-end' : 'items-start'}`;

                const bubble = document.createElement('div');
                bubble.className = m.is_mine
                    ? 'rounded-2xl rounded-br-md bg-gradient-to-r from-indigo-600 to-violet-600 px-3.5 py-2 text-sm font-medium text-white shadow-sm'
                    : 'rounded-2xl rounded-bl-md border border-slate-200 bg-white px-3.5 py-2 text-sm font-medium text-slate-800 shadow-sm';
                bubble.textContent = m.body;

                const meta = document.createElement('div');
                meta.className = 'mt-1 flex items-center gap-1.5 px-1 text-[10px] font-semibold text-slate-400';
                meta.textContent = `${m.is_mine ? 'You' : m.sender_name} · ${m.time}`;

                wrap.appendChild(bubble);
                wrap.appendChild(meta);

                if (m.is_mine) {
                    const receipt = document.createElement('div');
                    receipt.dataset.receiptFor = String(m.id);
                    receipt.className = 'mt-0.5 px-1 text-[10px]';
                    wrap.appendChild(receipt);
                }

                row.appendChild(wrap);
                listEl.appendChild(row);
                listEl.scrollTop = listEl.scrollHeight;
            };

            const poll = async () => {
                try {
                    const res = await fetch(`${fetchUrl}?after_id=${lastId}`, {
                        headers: { 'Accept': 'application/json' },
                    });
                    if (!res.ok) return;
                    const data = await res.json();
                    let incomingFromOther = false;
                    (data.messages || []).forEach((m) => {
                        if (initialized && !m.is_mine && m.id > lastId) {
                            incomingFromOther = true;
                        }
                        appendMessage(m);
                    });
                    if (incomingFromOther) playPing();

                    if (typeof data.seen_up_to_id === 'number') {
                        seenUpToId = Math.max(seenUpToId, data.seen_up_to_id);
                    }

                    if (data.partner) {
                        partnerOnline = Boolean(data.partner.is_online);
                        if (data.partner.avatar) partnerAvatar = data.partner.avatar;
                        if (data.partner.initials) partnerInitials = data.partner.initials;
                        if (presenceEl) presenceEl.textContent = data.partner.status_text || 'Offline';
                        if (dotEl) {
                            dotEl.className = partnerOnline
                                ? 'absolute -bottom-0.5 -right-0.5 h-3 w-3 rounded-full border-2 border-indigo-700 bg-emerald-400 animate-pulse'
                                : 'absolute -bottom-0.5 -right-0.5 h-3 w-3 rounded-full border-2 border-indigo-700 bg-slate-400';
                        }
                    }

                    updateReceipts();
                    initialized = true;
                    if (statusEl) statusEl.textContent = data.can_send ? 'Live Chat' : 'Trip ended';
                } catch {}
            };

            const sendBody = async (text) => {
                const body = (text || '').trim();
                if (!body) return;
                submitBtn.disabled = true;
                try {
                    const res = await fetch(sendUrl, {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'Accept': 'application/json',
                            'X-CSRF-TOKEN': csrf,
                        },
                        body: JSON.stringify({ body }),
                    });
                    if (res.ok) {
                        const data = await res.json();
                        if (data.message) {
                            appendMessage(data.message);
                            updateReceipts();
                        }
                        input.value = '';
                    }
                } finally {
                    submitBtn.disabled = false;
                    input.focus();
                }
            };

            form.addEventListener('submit', (e) => {
                e.preventDefault();
                sendBody(input.value);
            });

            root.querySelectorAll('[data-quick-reply]').forEach((btn) => {
                btn.addEventListener('click', () => sendBody(btn.dataset.quickReply));
            });

            poll();
            setInterval(poll, 4000);
        };

        document.addEventListener('DOMContentLoaded', () => {
            document.querySelectorAll('[data-booking-chat]').forEach(initChat);
        });
    })();
</script>
