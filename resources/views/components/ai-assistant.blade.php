<div
    x-data="riderhubAssistant()"
    @riderhub-open-ai.window="open = true; $nextTick(() => scrollToBottom())"
    class="fixed bottom-5 right-5 z-50 flex flex-col items-end"
    aria-live="polite"
>
    <div
        x-show="open"
        x-cloak
        x-transition:enter="transition ease-out duration-250"
        x-transition:enter-start="opacity-0 translate-y-4 scale-95"
        x-transition:enter-end="opacity-100 translate-y-0 scale-100"
        x-transition:leave="transition ease-in duration-180"
        x-transition:leave-start="opacity-100 translate-y-0 scale-100"
        x-transition:leave-end="opacity-0 translate-y-4 scale-95"
        class="mb-3 flex h-[32rem] max-h-[78vh] w-[calc(100vw-2.5rem)] max-w-sm flex-col overflow-hidden rounded-3xl border border-slate-200 bg-white shadow-2xl shadow-indigo-950/20"
        role="dialog"
        aria-label="RiderHub AI Assistant"
    >
        <div class="flex items-center justify-between bg-gradient-to-r from-indigo-600 via-violet-600 to-indigo-700 px-5 py-4 text-white">
            <div class="flex items-center gap-3">
                <span class="relative flex h-10 w-10 items-center justify-center rounded-2xl bg-white/15 backdrop-blur-sm">
                    <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M12 3l1.9 5.1L19 10l-5.1 1.9L12 17l-1.9-5.1L5 10l5.1-1.9L12 3z" />
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M19 16l.9 2.1L22 19l-2.1.9L19 22l-.9-2.1L16 19l2.1-.9L19 16z" />
                    </svg>
                    <span class="absolute -bottom-0.5 -right-0.5 h-3 w-3 rounded-full border-2 border-indigo-600 bg-emerald-400"></span>
                </span>
                <div>
                    <p class="text-sm font-extrabold tracking-tight">RiderHub AI</p>
                    <p class="text-[11px] text-indigo-100">Smart ride, weather &amp; fare assistant</p>
                </div>
            </div>
            <div class="flex items-center gap-1">
                <button
                    type="button"
                    @click="resetChat()"
                    title="Clear conversation"
                    class="rounded-xl p-1.5 text-indigo-100 transition hover:bg-white/15 hover:text-white focus:outline-none focus:ring-2 focus:ring-white/60"
                    aria-label="Reset conversation"
                >
                    <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M4 4v5h5M20 20v-5h-5M5.64 18.36A9 9 0 0 0 20 12M18.36 5.64A9 9 0 0 0 4 12" /></svg>
                </button>
                <button
                    type="button"
                    @click="open = false"
                    class="rounded-xl p-1.5 text-indigo-100 transition hover:bg-white/15 hover:text-white focus:outline-none focus:ring-2 focus:ring-white/60"
                    aria-label="Close AI Assistant"
                >
                    <svg class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                        <path d="M6.28 5.22a.75.75 0 0 0-1.06 1.06L8.94 10l-3.72 3.72a.75.75 0 1 0 1.06 1.06L10 11.06l3.72 3.72a.75.75 0 1 0 1.06-1.06L11.06 10l3.72-3.72a.75.75 0 0 0-1.06-1.06L10 8.94 6.28 5.22Z" />
                    </svg>
                </button>
            </div>
        </div>

        <div x-ref="messages" class="flex-1 space-y-3 overflow-y-auto bg-slate-50/70 p-4">
            <template x-for="(msg, idx) in messages" :key="idx">
                <div :class="msg.role === 'user' ? 'flex justify-end' : 'flex justify-start'">
                    <div
                        class="max-w-[85%] rounded-2xl px-4 py-2.5 text-sm leading-relaxed shadow-sm"
                        :class="msg.role === 'user'
                            ? 'rounded-br-md bg-indigo-600 text-white'
                            : 'rounded-bl-md border border-slate-200 bg-white text-slate-800'"
                    >
                        <p class="whitespace-pre-line" x-text="msg.text"></p>
                        <template x-if="msg.actions && msg.actions.length">
                            <div class="mt-2.5 flex flex-wrap gap-1.5">
                                <template x-for="action in msg.actions" :key="action.url + action.label">
                                    <a :href="action.url" class="inline-flex items-center gap-1 rounded-lg bg-indigo-50 px-2.5 py-1 text-xs font-bold text-indigo-700 transition hover:bg-indigo-100">
                                        <span x-text="action.label"></span>
                                        <span aria-hidden="true">→</span>
                                    </a>
                                </template>
                            </div>
                        </template>
                    </div>
                </div>
            </template>

            <div x-show="sending" x-cloak class="flex justify-start">
                <div class="inline-flex items-center gap-2 rounded-2xl rounded-bl-md border border-slate-200 bg-white px-4 py-2.5 text-xs font-semibold text-slate-500 shadow-sm">
                    <span class="inline-flex items-center gap-1">
                        <span class="ai-typing-dot h-1.5 w-1.5 rounded-full bg-indigo-500"></span>
                        <span class="ai-typing-dot h-1.5 w-1.5 rounded-full bg-indigo-500" style="animation-delay: 140ms"></span>
                        <span class="ai-typing-dot h-1.5 w-1.5 rounded-full bg-indigo-500" style="animation-delay: 280ms"></span>
                    </span>
                    <span>Thinking…</span>
                </div>
            </div>
        </div>

        <div class="border-t border-slate-100 bg-white px-3 pt-2.5 pb-2">
            <div class="mb-2 flex flex-wrap gap-1.5">
                <template x-for="chip in suggestions" :key="chip">
                    <button
                        type="button"
                        @click="send(chip)"
                        :disabled="sending"
                        class="rounded-full border border-indigo-100 bg-indigo-50/70 px-2.5 py-1 text-[11px] font-semibold text-indigo-700 transition hover:bg-indigo-100 disabled:opacity-50"
                        x-text="chip"
                    ></button>
                </template>
            </div>

            <form @submit.prevent="send(input)" data-no-loading="true" class="flex items-center gap-2">
                <input
                    type="text"
                    x-model="input"
                    placeholder="Ask about fares, weather, tracking…"
                    maxlength="500"
                    class="min-w-0 flex-1 rounded-xl border border-slate-200 bg-slate-50 px-3.5 py-2.5 text-sm text-slate-900 placeholder:text-slate-400 focus:border-indigo-500 focus:bg-white focus:outline-none focus:ring-2 focus:ring-indigo-500/20"
                >
                <button
                    type="submit"
                    :disabled="sending || !input.trim()"
                    class="inline-flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-indigo-600 text-white shadow-sm transition hover:bg-indigo-700 disabled:opacity-40"
                    aria-label="Send message"
                >
                    <svg class="h-4 w-4" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                        <path d="M3.105 2.288a.75.75 0 0 0-.826.95l1.414 4.926A1.5 1.5 0 0 0 5.135 9.25h6.115a.75.75 0 0 1 0 1.5H5.135a1.5 1.5 0 0 0-1.442 1.086l-1.414 4.926a.75.75 0 0 0 .826.95 28.897 28.897 0 0 0 15.293-7.155.75.75 0 0 0 0-1.114A28.897 28.897 0 0 0 3.105 2.288Z" />
                    </svg>
                </button>
            </form>
        </div>
    </div>

    <button
        type="button"
        @click="open = !open; if (open) $nextTick(() => scrollToBottom())"
        class="group relative inline-flex items-center gap-2.5 rounded-full bg-gradient-to-r from-indigo-600 to-violet-600 px-4 py-3.5 text-sm font-bold text-white shadow-xl shadow-indigo-600/30 transition duration-200 hover:-translate-y-0.5 hover:shadow-2xl hover:shadow-indigo-600/40 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2"
        :aria-expanded="open.toString()"
        aria-label="Toggle RiderHub AI Assistant"
    >
        <span class="relative flex h-6 w-6 items-center justify-center">
            <span class="absolute inline-flex h-full w-full animate-ping rounded-full bg-white/40 opacity-75"></span>
            <svg class="relative h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" aria-hidden="true">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 3l1.9 5.1L19 10l-5.1 1.9L12 17l-1.9-5.1L5 10l5.1-1.9L12 3z" />
            </svg>
        </span>
        <span class="hidden sm:inline" x-text="open ? 'Close AI' : 'Ask RiderHub AI'"></span>
    </button>
</div>

@once
    <script>
        window.riderhubAssistant = function () {
            return {
                open: false,
                sending: false,
                input: '',
                messages: [
                    {
                        role: 'assistant',
                        text: 'Kumusta! I’m RiderHub AI. Ask me about live weather, trip fares (e.g. "Estimate fare for 5 km"), PayMongo payments, or your booking status!',
                        actions: [],
                    },
                ],
                suggestions: [
                    'Check current weather',
                    'Estimate fare for 5 km',
                    @json(auth()->check() ? 'Check my booking status' : 'About RiderHub'),
                    'Payment options',
                ],
                scrollToBottom() {
                    const el = this.$refs.messages;
                    if (el) el.scrollTop = el.scrollHeight;
                },
                resetChat() {
                    this.messages = [this.messages[0]];
                    this.input = '';
                },
                async send(text) {
                    const clean = (text || '').trim();
                    if (!clean || this.sending) return;

                    this.messages.push({ role: 'user', text: clean });
                    this.input = '';
                    this.sending = true;
                    this.$nextTick(() => this.scrollToBottom());

                    try {
                        const token = document.querySelector('meta[name="csrf-token"]')?.content || @json(csrf_token());
                        const history = this.messages.slice(-8, -1).map((m) => ({ role: m.role, text: m.text }));
                        const response = await fetch(@json(route('assistant.chat')), {
                            method: 'POST',
                            headers: {
                                'Accept': 'application/json',
                                'Content-Type': 'application/json',
                                'X-CSRF-TOKEN': token,
                            },
                            body: JSON.stringify({ message: clean, history }),
                        });

                        if (!response.ok) throw new Error('Assistant unavailable');
                        const data = await response.json();

                        this.messages.push({
                            role: 'assistant',
                            text: data.reply || 'Sorry, I did not catch that.',
                            actions: data.actions || [],
                        });
                        if (Array.isArray(data.suggestions) && data.suggestions.length) {
                            this.suggestions = data.suggestions;
                        }
                    } catch (e) {
                        this.messages.push({
                            role: 'assistant',
                            text: 'I could not reach the server right now. Please try again in a moment.',
                            actions: [],
                        });
                    } finally {
                        this.sending = false;
                        this.$nextTick(() => this.scrollToBottom());
                    }
                },
            };
        };
    </script>
@endonce
