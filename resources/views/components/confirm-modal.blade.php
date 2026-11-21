<div
    x-data="{
        open: false,
        title: 'Are you sure?',
        message: 'This action cannot be undone.',
        confirmText: 'Yes, continue',
        tone: 'danger',
        targetForm: null,
        ask(detail) {
            this.title = detail.title || 'Are you sure?';
            this.message = detail.message || 'Please confirm this action.';
            this.confirmText = detail.confirmText || 'Yes, continue';
            this.tone = detail.tone || 'danger';
            this.targetForm = detail.form || null;
            this.open = true;
        },
        proceed() {
            const form = this.targetForm;
            this.open = false;
            this.targetForm = null;
            if (form) {
                form.dataset.confirmed = 'true';
                form.requestSubmit ? form.requestSubmit() : form.submit();
            }
        }
    }"
    @riderhub-confirm.window="ask($event.detail)"
    @keydown.escape.window="open = false"
    x-show="open"
    x-cloak
    x-transition:enter="transition ease-out duration-200"
    x-transition:enter-start="opacity-0"
    x-transition:enter-end="opacity-100"
    x-transition:leave="transition ease-in duration-150"
    x-transition:leave-start="opacity-100"
    x-transition:leave-end="opacity-0"
    class="fixed inset-0 z-[90] flex items-center justify-center bg-slate-950/60 p-4 backdrop-blur-sm"
    role="dialog"
    aria-modal="true"
    aria-labelledby="confirm-modal-title"
>
    <div
        @click.outside="open = false"
        x-show="open"
        x-transition:enter="transition ease-out duration-250"
        x-transition:enter-start="opacity-0 scale-90 translate-y-3"
        x-transition:enter-end="opacity-100 scale-100 translate-y-0"
        x-transition:leave="transition ease-in duration-150"
        x-transition:leave-start="opacity-100 scale-100 translate-y-0"
        x-transition:leave-end="opacity-0 scale-95 translate-y-2"
        class="w-full max-w-md overflow-hidden rounded-3xl border border-slate-200/80 bg-white shadow-2xl shadow-indigo-950/30"
    >
        <div class="p-6 text-center sm:p-7">
            <div class="mx-auto flex h-16 w-16 items-center justify-center rounded-2xl shadow-lg"
                 :class="tone === 'danger'
                    ? 'bg-gradient-to-br from-rose-500 to-pink-600 text-white shadow-rose-500/30'
                    : 'bg-gradient-to-br from-indigo-500 to-violet-600 text-white shadow-indigo-500/30'">
                <svg class="h-8 w-8" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m9-.75a9 9 0 1 1-18 0 9 9 0 0 1 18 0Zm-9 3.75h.008v.008H12v-.008Z" />
                </svg>
            </div>

            <h3 id="confirm-modal-title" class="mt-5 text-xl font-extrabold tracking-tight text-slate-950" x-text="title"></h3>
            <p class="mt-2 text-sm leading-6 text-slate-600" x-text="message"></p>

            <div class="mt-7 grid grid-cols-2 gap-3">
                <button
                    type="button"
                    @click="open = false"
                    class="inline-flex items-center justify-center rounded-xl border border-slate-200 bg-white px-4 py-3 text-sm font-bold text-slate-700 shadow-sm transition duration-200 hover:-translate-y-0.5 hover:bg-slate-50 active:scale-[0.98]"
                >
                    Cancel
                </button>
                <button
                    type="button"
                    @click="proceed()"
                    class="inline-flex items-center justify-center rounded-xl px-4 py-3 text-sm font-bold text-white shadow-lg transition duration-200 hover:-translate-y-0.5 active:scale-[0.98]"
                    :class="tone === 'danger'
                        ? 'bg-gradient-to-r from-rose-600 to-pink-600 shadow-rose-600/30 hover:shadow-xl hover:shadow-rose-600/40'
                        : 'bg-gradient-to-r from-indigo-600 to-violet-600 shadow-indigo-600/30 hover:shadow-xl hover:shadow-indigo-600/40'"
                    x-text="confirmText"
                ></button>
            </div>
        </div>
    </div>
</div>

@once
    <script>
        document.addEventListener('submit', (event) => {
            const form = event.target;
            if (!(form instanceof HTMLFormElement) || !form.dataset.confirmMessage) return;
            if (form.dataset.confirmed === 'true') {
                delete form.dataset.confirmed;
                return;
            }
            event.preventDefault();
            event.stopImmediatePropagation();
            window.dispatchEvent(new CustomEvent('riderhub-confirm', {
                detail: {
                    form,
                    title: form.dataset.confirmTitle || 'Are you sure?',
                    message: form.dataset.confirmMessage,
                    confirmText: form.dataset.confirmButton || 'Yes, continue',
                    tone: form.dataset.confirmTone || 'danger',
                },
            }));
        }, true);
    </script>
@endonce
