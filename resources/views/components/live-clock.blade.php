<div
    x-data="{
        dateStr: '',
        timeStr: '',
        tick() {
            const now = new Date();
            try {
                this.dateStr = new Intl.DateTimeFormat('en-PH', {
                    timeZone: 'Asia/Manila',
                    weekday: 'short',
                    month: 'short',
                    day: 'numeric',
                }).format(now);
                this.timeStr = new Intl.DateTimeFormat('en-PH', {
                    timeZone: 'Asia/Manila',
                    hour: 'numeric',
                    minute: '2-digit',
                    second: '2-digit',
                    hour12: true,
                }).format(now);
            } catch {
                this.dateStr = now.toLocaleDateString('en-US', { weekday: 'short', month: 'short', day: 'numeric' });
                this.timeStr = now.toLocaleTimeString('en-US', { hour: 'numeric', minute: '2-digit', second: '2-digit', hour12: true });
            }
        },
        init() {
            this.tick();
            setInterval(() => this.tick(), 1000);
        }
    }"
    class="hidden items-center gap-2 rounded-full border border-slate-200 bg-slate-50/90 px-3 py-1.5 text-xs font-semibold text-slate-700 sm:inline-flex"
    aria-label="Current Philippine date and time"
>
    <svg class="h-3.5 w-3.5 shrink-0 text-indigo-500" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.1" aria-hidden="true">
        <circle cx="12" cy="12" r="9" />
        <path stroke-linecap="round" stroke-linejoin="round" d="M12 7v5l3 2" />
    </svg>
    <span class="hidden text-slate-500 lg:inline" x-text="dateStr">{{ now()->timezone('Asia/Manila')->format('D, M j') }}</span>
    <span class="hidden text-slate-300 lg:inline" aria-hidden="true">·</span>
    <span class="tabular-nums font-bold text-slate-800" x-text="timeStr">{{ now()->timezone('Asia/Manila')->format('g:i:s A') }}</span>
</div>
