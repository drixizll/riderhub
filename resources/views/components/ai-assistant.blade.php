@php
    $quickTopics = [
        ['icon' => '🏍️', 'label' => 'Book a Ride', 'prompt' => 'How to book a ride?'],
        ['icon' => '💵', 'label' => 'Fare 5 km', 'prompt' => 'Estimate fare for 5 km'],
        ['icon' => '🎟️', 'label' => 'Promo Codes', 'prompt' => 'Promo codes'],
        ['icon' => '💳', 'label' => 'Payments', 'prompt' => 'Payment options'],
        ['icon' => '🌦️', 'label' => 'Live Weather', 'prompt' => 'Check Tagoloan weather'],
        ['icon' => '🛡️', 'label' => 'PIN & SOS', 'prompt' => 'Trip PIN and SOS safety'],
        ['icon' => '🪪', 'label' => 'Rider KYC', 'prompt' => 'Rider KYC verification'],
        ['icon' => '📱', 'label' => 'Install App', 'prompt' => 'How to install RiderHub app?'],
        ['icon' => '📍', 'label' => 'Booking Status', 'prompt' => 'Check my booking status'],
    ];
@endphp

<div
    x-data="riderHubAssistant(@js(route('assistant.chat')), @js(csrf_token()), @js($quickTopics))"
    x-init="init()"
    class="fixed right-4 z-50 sm:right-5 @auth bottom-20 lg:bottom-5 @else bottom-4 sm:bottom-5 @endauth"
>
    <!-- Ultra-Luxury Luminous Gradient Frame with Silky Spring Transition -->
    <div
        x-show="open"
        x-cloak
        @keydown.escape.window="open = false"
        x-transition:enter="transition-all duration-300 ease-[cubic-bezier(0.22,1,0.36,1)]"
        x-transition:enter-start="opacity-0 translate-y-5 scale-90"
        x-transition:enter-end="opacity-100 translate-y-0 scale-100"
        x-transition:leave="transition-all duration-200 ease-[cubic-bezier(0.4,0,0.2,1)]"
        x-transition:leave-start="opacity-100 translate-y-0 scale-100"
        x-transition:leave-end="opacity-0 translate-y-4 scale-90"
        class="mb-3 w-[calc(100vw-2rem)] max-w-[410px] origin-bottom-right will-change-transform rounded-[1.85rem] bg-gradient-to-br from-violet-500/70 via-fuchsia-500/40 to-amber-400/60 p-[1.5px] shadow-[0_30px_90px_-15px_rgba(88,28,135,0.75)]"
        role="dialog"
        aria-label="RiderHub AI Concierge"
    >
        <div class="relative flex flex-col overflow-hidden rounded-[calc(1.85rem-1.5px)] bg-[#080514]/95 text-slate-100 backdrop-blur-2xl">
            <!-- Ambient Aurora Orbs -->
            <div class="pointer-events-none absolute -left-16 -top-16 h-44 w-44 rounded-full bg-violet-600/25 blur-3xl" aria-hidden="true"></div>
            <div class="pointer-events-none absolute -right-14 top-10 h-36 w-36 rounded-full bg-fuchsia-500/20 blur-3xl" aria-hidden="true"></div>
            <div class="pointer-events-none absolute bottom-16 left-1/2 h-28 w-44 -translate-x-1/2 rounded-full bg-amber-400/10 blur-3xl" aria-hidden="true"></div>

            <!-- Executive Concierge Header -->
            <div class="relative overflow-hidden border-b border-white/10 bg-gradient-to-r from-[#1c1038]/95 via-[#2a1454]/95 to-[#160b2c]/95 px-4 py-3.5">
                <div class="pointer-events-none absolute inset-x-0 top-0 h-[2px] bg-gradient-to-r from-violet-400 via-fuchsia-400 to-amber-300 opacity-90" aria-hidden="true"></div>
                <div class="relative flex items-center justify-between gap-2">
                    <div class="flex items-center gap-3">
                        <!-- Glowing AI Emblem -->
                        <div class="relative flex h-11 w-11 shrink-0 items-center justify-center rounded-2xl bg-gradient-to-br from-violet-500 via-fuchsia-500 to-amber-400 p-[1.5px] shadow-lg shadow-fuchsia-600/30">
                            <div class="flex h-full w-full items-center justify-center rounded-[14px] bg-[#120926]">
                                <svg class="h-5 w-5 text-amber-300 drop-shadow-[0_0_8px_rgba(251,191,36,0.75)]" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true">
                                    <path d="M12 2l2.1 6.3L20.5 10.5l-6.4 2.1L12 19l-2.1-6.4-6.4-2.1 6.4-2.2L12 2zm7 13l.9 2.6L22.5 18.5l-2.6.9L19 22l-.9-2.6-2.6-.9 2.6-.9L19 15z"/>
                                </svg>
                            </div>
                            <span class="absolute -bottom-0.5 -right-0.5 flex h-3.5 w-3.5 items-center justify-center rounded-full bg-[#120926]">
                                <span class="h-2.5 w-2.5 animate-pulse rounded-full bg-emerald-400 shadow-[0_0_8px_rgba(52,211,153,0.95)]"></span>
                            </span>
                        </div>
                        <div class="min-w-0">
                            <div class="flex items-center gap-1.5">
                                <p class="truncate text-sm font-extrabold tracking-wide text-white">RiderHub AI</p>
                                <span class="inline-flex items-center gap-1 rounded-full border border-emerald-400/35 bg-emerald-500/15 px-2 py-0.5 text-[9px] font-extrabold uppercase tracking-wider text-emerald-300">
                                    <span class="h-1.5 w-1.5 rounded-full bg-emerald-400"></span>
                                    Live
                                </span>
                            </div>
                            <p class="mt-0.5 truncate text-[11px] font-medium text-violet-200/80">
                                <span x-text="greetingIcon"></span>
                                <span x-text="liveGreeting"></span>
                                <span class="mx-1 opacity-40">·</span>
                                <span class="font-mono text-amber-300/90" x-text="liveClock"></span>
                            </p>
                        </div>
                    </div>

                    <div class="flex items-center gap-1.5">
                        <button
                            type="button"
                            @click="resetChat()"
                            title="New conversation"
                            class="flex h-8 w-8 items-center justify-center rounded-xl border border-white/10 bg-white/5 text-violet-200 transition hover:border-amber-300/40 hover:bg-white/10 hover:text-amber-200"
                            aria-label="Reset conversation"
                        >
                            <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M4 4v5h.582m15.356 2A8.001 8.001 0 0 0 4.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 0 1-15.357-2m15.357 2H15" /></svg>
                        </button>
                        <button
                            type="button"
                            @click="open = false"
                            class="flex h-8 w-8 items-center justify-center rounded-xl border border-white/10 bg-white/5 text-violet-200 transition hover:border-rose-400/40 hover:bg-rose-500/20 hover:text-white"
                            aria-label="Close RiderHub AI"
                        >
                            <svg class="h-4 w-4" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true"><path d="M6.28 5.22a.75.75 0 0 0-1.06 1.06L8.94 10l-3.72 3.72a.75.75 0 1 0 1.06 1.06L10 11.06l3.72 3.72a.75.75 0 1 0 1.06-1.06L11.06 10l3.72-3.72a.75.75 0 0 0-1.06-1.06L10 8.94 6.28 5.22Z" /></svg>
                        </button>
                    </div>
                </div>
            </div>

            <!-- Messages Viewport -->
            <div
                x-ref="messages"
                class="riderhub-ai-scroll relative h-[350px] space-y-3.5 overflow-y-auto px-4 py-4 text-[13px] leading-relaxed"
            >
                <!-- Interactive Quick-Action Bento Card (visible on fresh chat) -->
                <div x-show="messages.length <= 1" class="grid grid-cols-2 gap-2">
                    <button type="button" @click="send('Estimate fare for 5 km')" class="group flex items-center gap-2.5 rounded-2xl border border-violet-400/20 bg-gradient-to-br from-violet-950/70 to-[#140a29]/80 p-2.5 text-left transition hover:-translate-y-0.5 hover:border-amber-300/50 hover:shadow-lg hover:shadow-violet-900/40">
                        <span class="flex h-8 w-8 shrink-0 items-center justify-center rounded-xl bg-amber-400/15 text-base ring-1 ring-amber-300/30">💵</span>
                        <span class="min-w-0">
                            <span class="block truncate text-[11px] font-extrabold text-white group-hover:text-amber-200">Fare Calculator</span>
                            <span class="block truncate text-[10px] text-violet-300/75">₱55 + ₱18.50/km</span>
                        </span>
                    </button>
                    <button type="button" @click="send('Promo codes')" class="group flex items-center gap-2.5 rounded-2xl border border-violet-400/20 bg-gradient-to-br from-fuchsia-950/60 to-[#140a29]/80 p-2.5 text-left transition hover:-translate-y-0.5 hover:border-amber-300/50 hover:shadow-lg hover:shadow-fuchsia-900/40">
                        <span class="flex h-8 w-8 shrink-0 items-center justify-center rounded-xl bg-fuchsia-400/15 text-base ring-1 ring-fuchsia-300/30">🎟️</span>
                        <span class="min-w-0">
                            <span class="block truncate text-[11px] font-extrabold text-white group-hover:text-amber-200">Promo Codes</span>
                            <span class="block truncate text-[10px] text-violet-300/75">Save up to 20%</span>
                        </span>
                    </button>
                    <button type="button" @click="send('Check Tagoloan weather')" class="group flex items-center gap-2.5 rounded-2xl border border-violet-400/20 bg-gradient-to-br from-indigo-950/70 to-[#140a29]/80 p-2.5 text-left transition hover:-translate-y-0.5 hover:border-amber-300/50 hover:shadow-lg hover:shadow-indigo-900/40">
                        <span class="flex h-8 w-8 shrink-0 items-center justify-center rounded-xl bg-sky-400/15 text-base ring-1 ring-sky-300/30">🌦️</span>
                        <span class="min-w-0">
                            <span class="block truncate text-[11px] font-extrabold text-white group-hover:text-amber-200">Live Weather</span>
                            <span class="block truncate text-[10px] text-violet-300/75">Tagoloan &amp; MisOr</span>
                        </span>
                    </button>
                    <button type="button" @click="send('How to install RiderHub app?')" class="group flex items-center gap-2.5 rounded-2xl border border-violet-400/20 bg-gradient-to-br from-emerald-950/50 to-[#140a29]/80 p-2.5 text-left transition hover:-translate-y-0.5 hover:border-amber-300/50 hover:shadow-lg hover:shadow-emerald-900/30">
                        <span class="flex h-8 w-8 shrink-0 items-center justify-center rounded-xl bg-emerald-400/15 text-base ring-1 ring-emerald-300/30">📱</span>
                        <span class="min-w-0">
                            <span class="block truncate text-[11px] font-extrabold text-white group-hover:text-amber-200">Mobile App</span>
                            <span class="block truncate text-[10px] text-violet-300/75">Android APK &amp; iOS</span>
                        </span>
                    </button>
                </div>

                <!-- Chat Message Stream -->
                <template x-for="(msg, idx) in messages" :key="idx">
                    <div class="riderhub-ai-msg" :class="msg.role === 'user' ? 'flex flex-col items-end' : 'flex items-start gap-2.5'">
                        <!-- AI Mini Avatar -->
                        <template x-if="msg.role !== 'user'">
                            <span class="mt-0.5 flex h-7 w-7 shrink-0 items-center justify-center rounded-xl bg-gradient-to-br from-violet-600 to-fuchsia-600 text-[11px] text-amber-200 shadow-md shadow-violet-900/40 ring-1 ring-white/15">✦</span>
                        </template>

                        <div :class="msg.role === 'user' ? 'flex max-w-[84%] flex-col items-end' : 'flex max-w-[85%] flex-col items-start'">
                            <div
                                :class="msg.role === 'user'
                                    ? 'rounded-2xl rounded-br-sm bg-gradient-to-br from-violet-600 via-purple-600 to-fuchsia-600 px-3.5 py-2.5 text-white shadow-lg shadow-purple-900/40 ring-1 ring-white/15'
                                    : 'rounded-2xl rounded-tl-sm border border-violet-400/25 bg-gradient-to-br from-[#170e2e]/95 via-[#120b24]/95 to-[#0d081a]/95 px-4 py-3 text-slate-100 shadow-xl shadow-black/30'"
                            >
                                <div class="whitespace-pre-line break-words" x-html="formatMessage(msg.text)"></div>

                                <template x-if="msg.actions && msg.actions.length">
                                    <div class="mt-3 flex flex-wrap gap-1.5 border-t border-white/10 pt-2.5">
                                        <template x-for="act in msg.actions" :key="act.url + act.label">
                                            <a
                                                :href="act.url"
                                                class="inline-flex items-center gap-1 rounded-xl border border-amber-300/35 bg-gradient-to-r from-amber-400/15 to-violet-500/20 px-2.5 py-1 text-[11px] font-bold text-amber-200 transition hover:border-amber-300/70 hover:bg-amber-400/25"
                                            >
                                                <span x-text="act.label"></span>
                                                <span aria-hidden="true">→</span>
                                            </a>
                                        </template>
                                    </div>
                                </template>
                            </div>
                            <span class="mt-1 px-1 text-[10px] font-semibold tracking-wide text-violet-300/50" x-text="msg.time"></span>
                        </div>
                    </div>
                </template>

                <!-- Typing Indicator -->
                <div x-show="loading" class="flex items-start gap-2.5">
                    <span class="mt-0.5 flex h-7 w-7 shrink-0 items-center justify-center rounded-xl bg-gradient-to-br from-violet-600 to-fuchsia-600 text-[11px] text-amber-200 ring-1 ring-white/15">✦</span>
                    <div class="inline-flex items-center gap-1.5 rounded-2xl rounded-tl-sm border border-violet-400/25 bg-[#150c2b] px-4 py-3">
                        <span class="h-2 w-2 animate-bounce rounded-full bg-amber-300 [animation-delay:-0.3s]"></span>
                        <span class="h-2 w-2 animate-bounce rounded-full bg-fuchsia-400 [animation-delay:-0.15s]"></span>
                        <span class="h-2 w-2 animate-bounce rounded-full bg-violet-400"></span>
                    </div>
                </div>
            </div>

            <!-- Horizontal Scrollable Quick-Question Strip -->
            <div class="relative border-t border-white/10 bg-[#0d071e]/95 px-3.5 pt-2.5 pb-2">
                <div class="mb-2 flex items-center justify-between gap-2">
                    <span class="inline-flex items-center gap-1.5 text-[10px] font-extrabold uppercase tracking-[0.14em] text-amber-300/95">
                        <span>✨</span>
                        <span>Ask a Question (Scroll to View):</span>
                    </span>
                    <div class="flex items-center gap-1">
                        <button
                            type="button"
                            @click="scrollTopics(-170)"
                            class="flex h-6 w-6 items-center justify-center rounded-lg border border-white/10 bg-white/5 text-violet-200 transition hover:border-amber-300/50 hover:bg-amber-400/15 hover:text-amber-200"
                            aria-label="Scroll questions left"
                        >
                            <svg class="h-3.5 w-3.5" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true"><path fill-rule="evenodd" d="M12.79 5.23a.75.75 0 0 1-.02 1.06L8.832 10l3.938 3.71a.75.75 0 1 1-1.04 1.08l-4.5-4.25a.75.75 0 0 1 0-1.08l4.5-4.25a.75.75 0 0 1 1.06.02Z" clip-rule="evenodd" /></svg>
                        </button>
                        <button
                            type="button"
                            @click="scrollTopics(170)"
                            class="flex h-6 w-6 items-center justify-center rounded-lg border border-white/10 bg-white/5 text-violet-200 transition hover:border-amber-300/50 hover:bg-amber-400/15 hover:text-amber-200"
                            aria-label="Scroll questions right"
                        >
                            <svg class="h-3.5 w-3.5" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true"><path fill-rule="evenodd" d="M7.21 14.77a.75.75 0 0 1 .02-1.06L11.168 10 7.23 6.29a.75.75 0 1 1 1.04-1.08l4.5 4.25a.75.75 0 0 1 0 1.08l-4.5 4.25a.75.75 0 0 1-1.06-.02Z" clip-rule="evenodd" /></svg>
                        </button>
                    </div>
                </div>

                <div
                    x-ref="topicRail"
                    class="riderhub-ai-rail flex items-center gap-1.5 overflow-x-auto pb-2 scroll-smooth"
                >
                    <template x-for="topic in topics" :key="topic.label">
                        <button
                            type="button"
                            @click="send(topic.prompt)"
                            :disabled="loading"
                            class="inline-flex shrink-0 items-center gap-1.5 rounded-full border border-violet-400/25 bg-[#180e33] px-3 py-1.5 text-[11px] font-bold text-violet-100 shadow-sm transition hover:border-amber-300/60 hover:bg-violet-600/30 hover:text-amber-200 active:scale-95 disabled:opacity-50"
                        >
                            <span x-text="topic.icon"></span>
                            <span x-text="topic.label"></span>
                        </button>
                    </template>
                </div>
            </div>

            <!-- Elevated Neon Input Bar -->
            <form @submit.prevent="send()" class="border-t border-white/10 bg-[#090516] p-3">
                <div class="flex items-center gap-2 rounded-2xl border border-violet-400/25 bg-[#130b29] p-1.5 shadow-inner transition focus-within:border-amber-300/60 focus-within:ring-2 focus-within:ring-violet-500/30">
                    <input
                        x-model="draft"
                        type="text"
                        maxlength="500"
                        placeholder="Ask RiderHub AI about fares, rides, promos…"
                        class="w-full border-0 bg-transparent px-2.5 py-1.5 text-sm text-white placeholder:text-violet-300/45 focus:outline-none focus:ring-0"
                    >
                    <button
                        type="submit"
                        :disabled="loading || !draft.trim()"
                        class="flex h-9 w-9 shrink-0 items-center justify-center rounded-xl bg-gradient-to-br from-violet-500 via-fuchsia-500 to-amber-400 text-white shadow-lg shadow-fuchsia-600/35 transition hover:scale-105 hover:brightness-110 active:scale-95 disabled:opacity-35 disabled:hover:scale-100"
                        aria-label="Send message"
                    >
                        <svg class="h-4 w-4" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true"><path d="M3.105 2.288a.75.75 0 0 0-.826.95l1.414 4.926A1.5 1.5 0 0 0 5.135 9.25h6.115a.75.75 0 0 1 0 1.5H5.135a1.5 1.5 0 0 0-1.442 1.086l-1.414 4.926a.75.75 0 0 0 .826.95 28.897 28.897 0 0 0 15.293-7.155.75.75 0 0 0 0-1.114A28.897 28.897 0 0 0 3.105 2.288Z" /></svg>
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Ultra-Bongga Floating Concierge Launcher -->
    <div class="flex justify-end">
        <button
            type="button"
            @click="open = !open; if (open) scrollBottom();"
            :aria-expanded="open.toString()"
            class="group relative inline-flex items-center gap-2.5 rounded-full bg-gradient-to-r from-violet-600 via-fuchsia-600 to-amber-500 p-[2px] shadow-[0_14px_38px_-6px_rgba(147,51,234,0.75)] transition-all duration-300 ease-[cubic-bezier(0.22,1,0.36,1)] hover:-translate-y-1 hover:shadow-[0_18px_45px_-6px_rgba(217,70,239,0.85)] active:scale-95"
            aria-label="Toggle RiderHub AI assistant"
        >
            <span class="inline-flex items-center gap-2.5 rounded-full bg-[#110826]/95 px-4 py-2.5 text-sm font-extrabold text-white backdrop-blur-md transition-colors duration-300 group-hover:bg-[#190c36]">
                <span class="relative flex h-6 w-6 items-center justify-center rounded-full bg-gradient-to-br from-violet-500 to-fuchsia-500 text-amber-200 shadow-inner transition-transform duration-300 ease-[cubic-bezier(0.22,1,0.36,1)]" :class="open ? 'rotate-90 scale-95' : 'rotate-0 scale-100'">
                    <svg x-show="!open" class="h-3.5 w-3.5" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true">
                        <path d="M12 2l2.1 6.3L20.5 10.5l-6.4 2.1L12 19l-2.1-6.4-6.4-2.1 6.4-2.2L12 2z"/>
                    </svg>
                    <svg x-show="open" x-cloak class="h-3.5 w-3.5" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                        <path d="M6.28 5.22a.75.75 0 0 0-1.06 1.06L8.94 10l-3.72 3.72a.75.75 0 1 0 1.06 1.06L10 11.06l3.72 3.72a.75.75 0 1 0 1.06-1.06L11.06 10l3.72-3.72a.75.75 0 0 0-1.06-1.06L10 8.94 6.28 5.22Z" />
                    </svg>
                    <span class="absolute -right-0.5 -top-0.5 h-2.5 w-2.5 animate-ping rounded-full bg-emerald-400 opacity-75"></span>
                    <span class="absolute -right-0.5 -top-0.5 h-2.5 w-2.5 rounded-full bg-emerald-400 ring-2 ring-[#110826]"></span>
                </span>
                <span class="bg-gradient-to-r from-white via-violet-100 to-amber-200 bg-clip-text text-transparent transition-all duration-200" x-text="open ? 'Close AI' : 'RiderHub AI'"></span>
                <span x-show="!open" class="hidden rounded-full border border-emerald-400/35 bg-emerald-500/15 px-2 py-0.5 text-[10px] font-extrabold uppercase tracking-wider text-emerald-300 sm:inline-block">Concierge</span>
            </span>
        </button>
    </div>
</div>

@once
    <style>
        .riderhub-ai-scroll::-webkit-scrollbar { width: 5px; }
        .riderhub-ai-scroll::-webkit-scrollbar-track { background: rgba(255, 255, 255, 0.03); border-radius: 9999px; }
        .riderhub-ai-scroll::-webkit-scrollbar-thumb { background: linear-gradient(180deg, rgba(167, 139, 250, 0.65), rgba(251, 191, 36, 0.55)); border-radius: 9999px; }

        .riderhub-ai-rail::-webkit-scrollbar { height: 5px; }
        .riderhub-ai-rail::-webkit-scrollbar-track { background: rgba(255, 255, 255, 0.05); border-radius: 9999px; }
        .riderhub-ai-rail::-webkit-scrollbar-thumb { background: linear-gradient(90deg, #a855f7, #f59e0b); border-radius: 9999px; }

        @keyframes riderhubMsgPop {
            from { opacity: 0; transform: translateY(8px) scale(0.98); }
            to { opacity: 1; transform: translateY(0) scale(1); }
        }
        .riderhub-ai-msg { animation: riderhubMsgPop 0.24s cubic-bezier(0.22, 1, 0.36, 1) both; }
    </style>

    <script>
        function riderHubAssistant(endpoint, csrf, quickTopics) {
            return {
                open: false,
                loading: false,
                draft: '',
                topics: quickTopics || [],
                messages: [],
                liveGreeting: 'Good evening',
                greetingIcon: '🌙',
                liveClock: '',
                init() {
                    this.updateClock();
                    setInterval(() => this.updateClock(), 1000);
                    this.resetChat();
                },
                updateClock() {
                    try {
                        const now = new Date();
                        const hour = Number(new Intl.DateTimeFormat('en-US', {
                            timeZone: 'Asia/Manila',
                            hour: 'numeric',
                            hour12: false,
                        }).format(now)) % 24;

                        this.liveClock = new Intl.DateTimeFormat('en-PH', {
                            timeZone: 'Asia/Manila',
                            hour: 'numeric',
                            minute: '2-digit',
                            hour12: true,
                        }).format(now) + ' PHT';

                        if (hour >= 5 && hour < 12) {
                            this.liveGreeting = 'Good morning';
                            this.greetingIcon = '☀️';
                        } else if (hour >= 12 && hour < 18) {
                            this.liveGreeting = 'Good afternoon';
                            this.greetingIcon = '🌤️';
                        } else {
                            this.liveGreeting = 'Good evening';
                            this.greetingIcon = '🌙';
                        }
                    } catch {}
                },
                nowTime() {
                    try {
                        return new Date().toLocaleTimeString('en-PH', {
                            timeZone: 'Asia/Manila',
                            hour: '2-digit',
                            minute: '2-digit',
                            hour12: true,
                        });
                    } catch {
                        return '';
                    }
                },
                resetChat() {
                    this.messages = [
                        {
                            role: 'assistant',
                            text: `${this.liveGreeting}! I am **RiderHub AI**, your dedicated assistant for **RiderHub Misamis Oriental**.\n\nTap any quick card above or ask me about **rides, parcel deliveries, fares, promos, live weather, or mobile app install**.`,
                            actions: [],
                            time: this.nowTime(),
                        },
                    ];
                },
                formatMessage(text) {
                    const safe = String(text || '')
                        .replace(/&/g, '&amp;')
                        .replace(/</g, '&lt;')
                        .replace(/>/g, '&gt;');
                    return safe.replace(/\*\*(.+?)\*\*/g, '<strong class="font-extrabold text-amber-300">$1</strong>');
                },
                scrollTopics(amount) {
                    const rail = this.$refs.topicRail;
                    if (rail) rail.scrollBy({ left: amount, behavior: 'smooth' });
                },
                scrollBottom() {
                    this.$nextTick(() => {
                        const box = this.$refs.messages;
                        if (box) box.scrollTop = box.scrollHeight;
                    });
                },
                async send(preset = null) {
                    const text = (preset ?? this.draft).trim();
                    if (!text || this.loading) return;

                    const history = this.messages.slice(-8).map((m) => ({
                        role: m.role,
                        text: m.text,
                    }));

                    this.messages.push({ role: 'user', text, actions: [], time: this.nowTime() });
                    this.draft = '';
                    this.loading = true;
                    this.scrollBottom();

                    try {
                        const res = await fetch(endpoint, {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'Accept': 'application/json',
                                'X-CSRF-TOKEN': csrf,
                            },
                            body: JSON.stringify({ message: text, history }),
                        });
                        const data = await res.json();
                        this.messages.push({
                            role: 'assistant',
                            text: data.reply || 'Sorry, I could not process that request right now.',
                            actions: data.actions || [],
                            time: this.nowTime(),
                        });
                    } catch {
                        this.messages.push({
                            role: 'assistant',
                            text: 'Unable to reach **RiderHub AI** right now. Please check your connection and try again.',
                            actions: [],
                            time: this.nowTime(),
                        });
                    } finally {
                        this.loading = false;
                        this.scrollBottom();
                    }
                },
            };
        }
    </script>
@endonce
