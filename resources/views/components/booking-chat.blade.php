@props(['booking'])

@php
    $isRider = auth()->id() === $booking->rider_id;
    $quickReplies = $isRider
        ? ['Papunta na po ako!', 'Nandito na po ako sa pickup', '5 mins away na po', 'Salamat po!']
        : ['Nasa pickup na po ako', 'Nasaan na po kayo?', 'Naka-wait po ako sa labas', 'Salamat po!'];
    $partnerName = $isRider ? ($booking->customer?->name ?? 'Customer') : ($booking->rider?->name ?? 'Rider');
@endphp

<div
    data-booking-chat
    data-fetch-url="{{ route('bookings.messages.index', $booking) }}"
    data-send-url="{{ route('bookings.messages.store', $booking) }}"
    data-csrf="{{ csrf_token() }}"
    class="overflow-hidden rounded-2xl border border-indigo-100 bg-white shadow-sm"
>
    <div class="flex items-center justify-between gap-3 border-b border-slate-100 bg-gradient-to-r from-indigo-600 via-violet-600 to-indigo-700 px-4 py-3 text-white">
        <div class="flex items-center gap-2.5 min-w-0">
            <span class="relative flex h-8 w-8 shrink-0 items-center justify-center rounded-xl bg-white/15 backdrop-blur-sm">
                <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M8 10h.01M12 10h.01M16 10h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 0 1-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8Z" />
                </svg>
                <span class="absolute -right-0.5 -top-0.5 flex h-2.5 w-2.5">
                    <span class="absolute inline-flex h-full w-full animate-ping rounded-full bg-emerald-300 opacity-75"></span>
                    <span class="relative inline-flex h-2.5 w-2.5 rounded-full bg-emerald-400"></span>
                </span>
            </span>
            <div class="min-w-0">
                <p class="text-[10px] font-bold uppercase tracking-wider text-indigo-100">Live Trip Chat</p>
                <p class="truncate text-sm font-extrabold">{{ $partnerName }}</p>
            </div>
        </div>
        <span data-chat-status class="rounded-full bg-white/15 px-2.5 py-1 text-[11px] font-bold text-indigo-50">Connected</span>
    </div>

    <div data-chat-list class="h-60 space-y-2.5 overflow-y-auto bg-slate-50/70 p-4 text-sm">
        <p data-chat-empty class="py-10 text-center text-xs text-slate-400">No messages yet. Send a quick update to {{ $partnerName }} below.</p>
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
            const listEl = root.querySelector('[data-chat-list]');
            const emptyEl = root.querySelector('[data-chat-empty]');
            const form = root.querySelector('[data-chat-form]');
            const input = root.querySelector('[data-chat-input]');
            const submitBtn = root.querySelector('[data-chat-submit]');
            const statusEl = root.querySelector('[data-chat-status]');
            let lastId = 0;
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

            const appendMessage = (m) => {
                if (m.id <= lastId) return;
                lastId = m.id;
                if (emptyEl) emptyEl.remove();

                const wrap = document.createElement('div');
                wrap.className = `flex flex-col ${m.is_mine ? 'items-end' : 'items-start'}`;

                const bubble = document.createElement('div');
                bubble.className = m.is_mine
                    ? 'max-w-[82%] rounded-2xl rounded-br-md bg-gradient-to-r from-indigo-600 to-violet-600 px-3.5 py-2 text-sm font-medium text-white shadow-sm'
                    : 'max-w-[82%] rounded-2xl rounded-bl-md border border-slate-200 bg-white px-3.5 py-2 text-sm font-medium text-slate-800 shadow-sm';
                bubble.textContent = m.body;

                const meta = document.createElement('span');
                meta.className = 'mt-1 px-1 text-[10px] font-semibold text-slate-400';
                meta.textContent = `${m.is_mine ? 'You' : m.sender_name} · ${m.time}`;

                wrap.appendChild(bubble);
                wrap.appendChild(meta);
                listEl.appendChild(wrap);
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
                    initialized = true;
                    if (statusEl) statusEl.textContent = data.can_send ? 'Live' : 'Trip ended';
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
                        if (data.message) appendMessage(data.message);
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
