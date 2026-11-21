<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
    <title>{{ $title }} — RiderHub</title>
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600,700,800&display=swap" rel="stylesheet" />
    <link rel="icon" type="image/svg+xml" href="{{ asset('icons/riderhub.svg') }}">
    <style>
        * { box-sizing: border-box; }
        html, body { margin: 0; overflow-x: clip; -webkit-font-smoothing: antialiased; }
        body {
            position: relative;
            min-height: 100dvh;
            font-family: 'Figtree', system-ui, -apple-system, sans-serif;
            color: #0f172a;
            background:
                radial-gradient(circle at 12% 15%, rgba(139, 92, 246, 0.24), transparent 46%),
                radial-gradient(circle at 88% 20%, rgba(168, 85, 247, 0.22), transparent 46%),
                radial-gradient(circle at 50% 95%, rgba(124, 58, 237, 0.18), transparent 50%),
                linear-gradient(145deg, #160c33 0%, #22114b 45%, #0f172a 100%);
            background-size: 160% 160%;
            animation: report-bg-shift 18s ease-in-out infinite alternate;
            padding-bottom: 64px;
        }

        /* Live animated mobility grid + floating aurora orbs + interactive canvas */
        .bg-stage {
            position: fixed;
            inset: 0;
            z-index: 0;
            pointer-events: none;
            overflow: hidden;
        }
        .bg-grid {
            position: absolute;
            inset: 0;
            background-image:
                linear-gradient(to right, rgba(167, 139, 250, 0.1) 1px, transparent 1px),
                linear-gradient(to bottom, rgba(167, 139, 250, 0.1) 1px, transparent 1px);
            background-size: 42px 42px;
            mask-image: radial-gradient(ellipse 85% 75% at 50% 40%, #000 45%, transparent 100%);
            -webkit-mask-image: radial-gradient(ellipse 85% 75% at 50% 40%, #000 45%, transparent 100%);
            animation: report-grid-pan 26s linear infinite;
        }
        .orb {
            position: absolute;
            border-radius: 9999px;
            filter: blur(110px);
            opacity: 0.34;
            will-change: transform;
        }
        .orb-1 { top: -10%; left: -8%; width: min(46vw, 480px); height: min(46vw, 480px); background: radial-gradient(circle, #7c3aed, transparent 70%); animation: report-orb 16s ease-in-out infinite alternate; }
        .orb-2 { bottom: -12%; right: -8%; width: min(44vw, 460px); height: min(44vw, 460px); background: radial-gradient(circle, #c084fc, transparent 70%); animation: report-orb 20s ease-in-out -5s infinite alternate-reverse; }
        #report-canvas { position: absolute; inset: 0; width: 100%; height: 100%; }

        /* Frosted glass sticky toolbar */
        .toolbar {
            position: sticky;
            top: 0;
            z-index: 30;
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 12px;
            padding: 13px 26px;
            background: rgba(15, 10, 38, 0.72);
            backdrop-filter: blur(18px);
            -webkit-backdrop-filter: blur(18px);
            border-bottom: 1px solid rgba(196, 181, 253, 0.16);
            color: #f8fafc;
            animation: report-fade-down 500ms cubic-bezier(0.22, 1, 0.36, 1) both;
        }
        .toolbar-left { display: flex; align-items: center; gap: 10px; font-size: 13px; font-weight: 700; min-width: 0; }
        .toolbar-title { white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
        .toolbar-pill {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            padding: 4px 10px;
            border-radius: 999px;
            background: rgba(139, 92, 246, 0.24);
            border: 1px solid rgba(196, 181, 253, 0.3);
            color: #e9d5ff;
            font-size: 10.5px;
            font-weight: 800;
            letter-spacing: 0.06em;
            flex-shrink: 0;
        }
        .live-dot {
            width: 7px;
            height: 7px;
            border-radius: 999px;
            background: #34d399;
            box-shadow: 0 0 0 0 rgba(52, 211, 153, 0.7);
            animation: report-pulse 2s infinite;
        }
        .toolbar-actions { display: flex; gap: 10px; flex-shrink: 0; }
        .btn {
            display: inline-flex;
            align-items: center;
            gap: 7px;
            padding: 9px 16px;
            border-radius: 12px;
            font-size: 13px;
            font-weight: 700;
            text-decoration: none;
            border: none;
            cursor: pointer;
            transition: transform 200ms cubic-bezier(0.22, 1, 0.36, 1), box-shadow 200ms ease, background 200ms ease;
        }
        .btn-primary {
            background: linear-gradient(135deg, #8b5cf6 0%, #6d28d9 100%);
            color: #fff;
            box-shadow: 0 8px 22px rgba(124, 58, 237, 0.42);
        }
        .btn-primary:hover { transform: translateY(-2px); box-shadow: 0 14px 28px rgba(139, 92, 246, 0.52); }
        .btn-primary:active { transform: scale(0.97); }
        .btn-ghost {
            background: rgba(255, 255, 255, 0.09);
            color: #e2e8f0;
            border: 1px solid rgba(255, 255, 255, 0.16);
        }
        .btn-ghost:hover { background: rgba(255, 255, 255, 0.16); color: #fff; transform: translateY(-1px); }

        /* Main elevated glass-framed report sheet */
        .sheet-wrap { position: relative; z-index: 10; padding: 28px 16px 0; }
        .sheet {
            max-width: 980px;
            margin: 0 auto;
            background: rgba(255, 255, 255, 0.98);
            border-radius: 24px;
            border: 1px solid rgba(221, 214, 254, 0.7);
            overflow: hidden;
            box-shadow: 0 32px 80px -18px rgba(10, 5, 30, 0.55), 0 0 0 1px rgba(139, 92, 246, 0.18);
            animation: report-sheet-enter 680ms cubic-bezier(0.22, 1, 0.36, 1) both;
        }
        .accent-bar {
            height: 6px;
            background: linear-gradient(90deg, #6d28d9, #8b5cf6, #d946ef, #7c3aed, #6d28d9);
            background-size: 240% 100%;
            animation: report-shimmer 6s linear infinite;
        }
        .inner { padding: 38px 44px 40px; }

        .doc-header {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            gap: 24px;
            padding-bottom: 26px;
            border-bottom: 1px solid #ede9fe;
        }
        .brand { display: flex; align-items: center; gap: 14px; }
        .brand-mark {
            width: 50px;
            height: 50px;
            border-radius: 15px;
            background: linear-gradient(135deg, #8b5cf6, #6d28d9);
            display: flex;
            align-items: center;
            justify-content: center;
            box-shadow: 0 10px 24px rgba(124, 58, 237, 0.34);
            animation: report-float 5s ease-in-out infinite;
        }
        .brand-name { font-size: 23px; font-weight: 800; letter-spacing: -0.025em; color: #0f172a; }
        .brand-name span { color: #7c3aed; }
        .brand-tag { font-size: 12px; color: #64748b; margin-top: 2px; font-weight: 500; }
        .doc-meta { text-align: right; }
        .doc-tag {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            padding: 5px 12px;
            border-radius: 999px;
            background: linear-gradient(135deg, #f5f3ff, #ede9fe);
            border: 1px solid #ddd6fe;
            color: #6d28d9;
            font-size: 10px;
            font-weight: 800;
            letter-spacing: 0.08em;
            text-transform: uppercase;
        }
        .doc-id { margin-top: 8px; font-size: 12px; font-weight: 700; color: #334155; font-variant-numeric: tabular-nums; }
        .doc-date { margin-top: 2px; font-size: 12px; color: #64748b; }

        .title-block { display: flex; justify-content: space-between; align-items: flex-end; gap: 20px; margin-top: 26px; }
        .title { margin: 0; font-size: 27px; font-weight: 800; letter-spacing: -0.025em; color: #0f172a; }
        .subtitle { margin: 5px 0 0; font-size: 13.5px; color: #64748b; }
        .prepared { font-size: 12px; color: #64748b; text-align: right; }
        .prepared strong { color: #0f172a; }

        .summary { display: grid; grid-template-columns: repeat(4, 1fr); gap: 14px; margin: 26px 0 28px; }
        .stat {
            position: relative;
            overflow: hidden;
            background: linear-gradient(180deg, #ffffff 0%, #f8fafc 100%);
            border: 1px solid #e2e8f0;
            border-top: 3px solid #8b5cf6;
            border-radius: 16px;
            padding: 16px 18px;
            transition: transform 240ms cubic-bezier(0.22, 1, 0.36, 1), box-shadow 240ms ease, border-color 240ms ease;
            animation: report-stagger-up 560ms cubic-bezier(0.22, 1, 0.36, 1) both;
        }
        .stat:nth-child(1) { animation-delay: 80ms; }
        .stat:nth-child(2) { animation-delay: 150ms; border-top-color: #10b981; }
        .stat:nth-child(3) { animation-delay: 220ms; border-top-color: #f59e0b; }
        .stat:nth-child(4) {
            animation-delay: 290ms;
            background: linear-gradient(135deg, #f5f3ff 0%, #ede9fe 100%);
            border-color: #ddd6fe;
            border-top-color: #6d28d9;
        }
        .stat:hover {
            transform: translateY(-4px);
            box-shadow: 0 16px 32px -12px rgba(124, 58, 237, 0.22);
            border-color: #c4b5fd;
        }
        .stat-label { font-size: 11px; font-weight: 700; color: #64748b; letter-spacing: 0.03em; text-transform: uppercase; }
        .stat:last-child .stat-label { color: #6d28d9; }
        .stat-value { margin-top: 8px; font-size: 25px; font-weight: 800; color: #0f172a; letter-spacing: -0.02em; font-variant-numeric: tabular-nums; }
        .stat:last-child .stat-value { color: #5b21b6; }

        .table-wrap { border: 1px solid #e2e8f0; border-radius: 16px; overflow-x: auto; -webkit-overflow-scrolling: touch; animation: report-stagger-up 620ms cubic-bezier(0.22, 1, 0.36, 1) 240ms both; }
        table { width: 100%; border-collapse: collapse; font-size: 12.5px; min-width: 640px; }
        th { background: #f5f3ff; color: #5b21b6; text-align: left; padding: 12px 14px; font-size: 10.5px; font-weight: 800; text-transform: uppercase; letter-spacing: 0.06em; border-bottom: 1px solid #ede9fe; }
        td { padding: 13px 14px; border-bottom: 1px solid #f1f5f9; vertical-align: top; color: #334155; }
        tbody tr { transition: background 180ms ease; }
        tbody tr:hover { background: #faf5ff; }
        tr:last-child td { border-bottom: none; }
        .badge { display: inline-flex; align-items: center; gap: 5px; padding: 4px 10px; border-radius: 999px; font-size: 11px; font-weight: 700; text-transform: capitalize; }
        .badge-completed { background: #ecfdf5; color: #047857; }
        .badge-cancelled { background: #fff1f2; color: #be123c; }
        .badge-pending { background: #fffbeb; color: #b45309; }
        .badge-active { background: #f5f3ff; color: #6d28d9; }

        .empty-state {
            padding: 50px 24px;
            text-align: center;
            border: 1.5px dashed #c4b5fd;
            border-radius: 18px;
            background: linear-gradient(180deg, #faf5ff 0%, #ffffff 100%);
            animation: report-stagger-up 620ms cubic-bezier(0.22, 1, 0.36, 1) 220ms both;
        }
        .empty-icon {
            width: 52px;
            height: 52px;
            margin: 0 auto 14px;
            border-radius: 16px;
            background: linear-gradient(135deg, #8b5cf6, #6d28d9);
            color: #fff;
            display: flex;
            align-items: center;
            justify-content: center;
            box-shadow: 0 12px 24px rgba(124, 58, 237, 0.3);
            animation: report-float 4.5s ease-in-out infinite;
        }

        .footer {
            margin-top: 32px;
            padding-top: 22px;
            border-top: 1px solid #ede9fe;
            display: flex;
            justify-content: space-between;
            align-items: flex-end;
            gap: 20px;
            font-size: 11.5px;
            color: #64748b;
        }
        .sig { width: 230px; text-align: center; }
        .sig-line { height: 28px; border-bottom: 1.5px solid #c4b5fd; margin-bottom: 6px; }
        .sig-label { font-size: 11px; font-weight: 700; color: #475569; }

        @keyframes report-bg-shift { 0% { background-position: 0% 50%; } 100% { background-position: 100% 50%; } }
        @keyframes report-grid-pan { from { background-position: 0 0; } to { background-position: 84px 84px; } }
        @keyframes report-orb { from { transform: translate3d(0, 0, 0) scale(1); } to { transform: translate3d(6%, 6%, 0) scale(1.1); } }
        @keyframes report-shimmer { 0% { background-position: 0% 50%; } 100% { background-position: 240% 50%; } }
        @keyframes report-float { 0%, 100% { transform: translateY(0); } 50% { transform: translateY(-6px); } }
        @keyframes report-pulse { 0% { box-shadow: 0 0 0 0 rgba(52, 211, 153, 0.7); } 70% { box-shadow: 0 0 0 8px rgba(52, 211, 153, 0); } 100% { box-shadow: 0 0 0 0 rgba(52, 211, 153, 0); } }
        @keyframes report-fade-down { from { opacity: 0; transform: translateY(-10px); } to { opacity: 1; transform: translateY(0); } }
        @keyframes report-sheet-enter { from { opacity: 0; transform: translateY(22px) scale(0.985); } to { opacity: 1; transform: translateY(0) scale(1); } }
        @keyframes report-stagger-up { from { opacity: 0; transform: translateY(14px); } to { opacity: 1; transform: translateY(0); } }

        @media print {
            body { background: #fff !important; padding: 0; animation: none !important; }
            .bg-stage, .toolbar { display: none !important; }
            .sheet-wrap { padding: 0; }
            .sheet { margin: 0; border: none; border-radius: 0; box-shadow: none; max-width: 100%; animation: none !important; }
            .inner { padding: 24px 12px; }
            .stat, .empty-state, .table-wrap, .brand-mark, .empty-icon { animation: none !important; }
            .accent-bar, .stat, th, .badge, .brand-mark { -webkit-print-color-adjust: exact; print-color-adjust: exact; }
        }
        @media (max-width: 720px) {
            .toolbar { padding: 11px 14px; }
            .toolbar-title { display: none; }
            .inner { padding: 24px 18px; }
            .summary { grid-template-columns: repeat(2, 1fr); gap: 10px; }
            .doc-header, .title-block, .footer { flex-direction: column; align-items: flex-start; }
            .doc-meta, .prepared { text-align: left; }
            .title { font-size: 22px; }
        }
    </style>
</head>
<body>
    <div class="bg-stage" aria-hidden="true">
        <div class="bg-grid"></div>
        <div class="orb orb-1"></div>
        <div class="orb orb-2"></div>
        <canvas id="report-canvas"></canvas>
    </div>

    <div class="toolbar">
        <div class="toolbar-left">
            <span class="toolbar-pill"><span class="live-dot"></span>LIVE PDF PREVIEW</span>
            <span class="toolbar-title">{{ $title }}</span>
        </div>
        <div class="toolbar-actions">
            <a href="{{ $backUrl }}" class="btn btn-ghost">← Back</a>
            <button type="button" onclick="window.print()" class="btn btn-primary">Download / Print PDF</button>
        </div>
    </div>

    <div class="sheet-wrap">
        <main class="sheet">
            <div class="accent-bar"></div>
            <div class="inner">
                <header class="doc-header">
                    <div class="brand">
                        <div class="brand-mark">
                            <svg width="28" height="28" viewBox="0 0 48 48" fill="none" aria-hidden="true">
                                <path d="M8 20h7M6 25h6M9 30h5" stroke="#DDD6FE" stroke-width="2.6" stroke-linecap="round"/>
                                <path d="M28.5 9C22.1 9 17 14.1 17 20.5c0 7.8 9.2 16.5 10.8 18a1 1 0 0 0 1.4 0c1.6-1.5 10.8-10.2 10.8-18C40 14.1 34.9 9 28.5 9Z" fill="#FFFFFF"/>
                                <path d="M24.8 25.5v-9.6h4.2c2.4 0 4 1.3 4 3.2 0 1.5-.9 2.6-2.4 3l2.9 3.4h-3l-2.4-3.1h-.9v3.1h-2.4Zm2.4-5.1h1.6c1 0 1.7-.5 1.7-1.3s-.7-1.3-1.7-1.3h-1.6v2.6Z" fill="#6D28D9"/>
                            </svg>
                        </div>
                        <div>
                            <div class="brand-name">Rider<span>Hub</span></div>
                            <div class="brand-tag">Misamis Oriental · Rides &amp; Express Delivery</div>
                        </div>
                    </div>
                    <div class="doc-meta">
                        <span class="doc-tag"><span class="live-dot" style="width:6px;height:6px;"></span>Official System Report</span>
                        <div class="doc-id">Ref: RH-RPT-{{ now()->timezone('Asia/Manila')->format('Ymd-Hi') }}</div>
                        <div class="doc-date">{{ now()->timezone('Asia/Manila')->format('F j, Y · g:i A') }} (PHT)</div>
                    </div>
                </header>

                <section class="title-block">
                    <div>
                        <h1 class="title">{{ $title }}</h1>
                        <p class="subtitle">{{ $subtitle }}</p>
                    </div>
                    <div class="prepared">
                        <div>Prepared by <strong>{{ $generatedBy }}</strong></div>
                        <div style="margin-top: 2px;">{{ $bookings->count() }} {{ \Illuminate\Support\Str::plural('record', $bookings->count()) }} included</div>
                    </div>
                </section>

                <section class="summary">
                    @foreach ($summary as $item)
                        <div class="stat">
                            <div class="stat-label">{{ $item['label'] }}</div>
                            <div class="stat-value">{{ $item['value'] }}</div>
                        </div>
                    @endforeach
                </section>

                @if ($bookings->isEmpty())
                    <div class="empty-state">
                        <div class="empty-icon">
                            <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12h6m-6 4h6m2 5H7a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h5.586a1 1 0 0 1 .707.293l5.414 5.414a1 1 0 0 1 .293.707V19a2 2 0 0 1-2 2Z" /></svg>
                        </div>
                        <div style="font-size: 15px; font-weight: 800; color: #0f172a;">No booking records yet</div>
                        <div style="margin-top: 5px; font-size: 13px; color: #64748b;">Completed and active bookings will automatically appear in this report table.</div>
                    </div>
                @else
                    <div class="table-wrap">
                        <table>
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>Date</th>
                                    @if ($showCustomer)<th>Customer</th>@endif
                                    @if ($showRider)<th>Rider</th>@endif
                                    <th>Service &amp; Route</th>
                                    <th>Payment</th>
                                    <th>Status</th>
                                    <th style="text-align:right;">Fare</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($bookings as $booking)
                                    @php
                                        $badgeClass = match ($booking->status) {
                                            'completed' => 'badge-completed',
                                            'cancelled' => 'badge-cancelled',
                                            'accepted', 'picked_up' => 'badge-active',
                                            default => 'badge-pending',
                                        };
                                    @endphp
                                    <tr>
                                        <td><strong style="color:#6d28d9;">#{{ $booking->id }}</strong></td>
                                        <td style="white-space:nowrap;">{{ $booking->created_at->format('M j, Y') }}<br><span style="color:#94a3b8; font-size:11px;">{{ $booking->created_at->format('g:i A') }}</span></td>
                                        @if ($showCustomer)<td><strong style="color:#0f172a;">{{ $booking->customer?->name ?? 'N/A' }}</strong></td>@endif
                                        @if ($showRider)<td>{{ $booking->rider?->name ?? 'Unassigned' }}</td>@endif
                                        <td>
                                            <strong style="color:#0f172a;">{{ ucfirst($booking->type) }}</strong><br>
                                            <span style="color:#64748b;">{{ $booking->pickup_address }} → {{ $booking->dropoff_address }}</span>
                                        </td>
                                        <td>
                                            {{ ucfirst($booking->payment_method) }}<br>
                                            <span style="color:#94a3b8; font-size:11px;">{{ ucfirst($booking->payment?->status ?? 'pending') }}</span>
                                        </td>
                                        <td><span class="badge {{ $badgeClass }}">{{ str_replace('_', ' ', $booking->status) }}</span></td>
                                        <td style="text-align:right; font-weight:800; color:#0f172a; font-variant-numeric:tabular-nums; white-space:nowrap;">₱{{ number_format((float) $booking->fare, 2) }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @endif

                <footer class="footer">
                    <div>
                        <div style="font-weight:700; color:#334155;">RiderHub Platform · Misamis Oriental, Philippines</div>
                        <div style="margin-top:2px;">Digitally generated report · riderhub.online</div>
                    </div>
                    <div class="sig">
                        <div class="sig-line"></div>
                        <div class="sig-label">Authorized Signature / Verified By</div>
                    </div>
                </footer>
            </div>
        </main>
    </div>

    <script>
        (() => {
            const canvas = document.getElementById('report-canvas');
            if (!canvas || window.matchMedia('(prefers-reduced-motion: reduce)').matches) return;
            const ctx = canvas.getContext('2d');
            let w = 0, h = 0, nodes = [], pointer = { x: -999, y: -999 };
            const init = () => {
                const dpr = Math.min(window.devicePixelRatio || 1, 2);
                w = window.innerWidth; h = window.innerHeight;
                canvas.width = Math.floor(w * dpr); canvas.height = Math.floor(h * dpr);
                ctx.setTransform(dpr, 0, 0, dpr, 0, 0);
                const count = w < 640 ? 20 : 42;
                nodes = Array.from({ length: count }, () => ({
                    x: Math.random() * w, y: Math.random() * h,
                    vx: (Math.random() - 0.5) * 0.55, vy: (Math.random() - 0.5) * 0.55,
                    r: Math.random() * 2 + 1.3, p: Math.random() * 6.28
                }));
            };
            window.addEventListener('mousemove', e => { pointer = { x: e.clientX, y: e.clientY }; }, { passive: true });
            window.addEventListener('mouseleave', () => { pointer = { x: -999, y: -999 }; });
            window.addEventListener('resize', init, { passive: true });
            init();
            const loop = () => {
                if (!document.hidden) {
                    ctx.clearRect(0, 0, w, h);
                    for (let i = 0; i < nodes.length; i++) {
                        const a = nodes[i];
                        a.x += a.vx; a.y += a.vy; a.p += 0.03;
                        if (a.x < 0 || a.x > w) a.vx *= -1;
                        if (a.y < 0 || a.y > h) a.vy *= -1;
                        const md = Math.hypot(a.x - pointer.x, a.y - pointer.y);
                        if (md < 160) {
                            ctx.strokeStyle = `rgba(232,121,249,${(1 - md / 160) * 0.45})`;
                            ctx.lineWidth = 1.2; ctx.beginPath(); ctx.moveTo(a.x, a.y); ctx.lineTo(pointer.x, pointer.y); ctx.stroke();
                        }
                        for (let j = i + 1; j < nodes.length; j++) {
                            const b = nodes[j], d = Math.hypot(a.x - b.x, a.y - b.y);
                            if (d < 150) {
                                ctx.strokeStyle = `rgba(167,139,250,${(1 - d / 150) * 0.28})`;
                                ctx.lineWidth = 1; ctx.beginPath(); ctx.moveTo(a.x, a.y); ctx.lineTo(b.x, b.y); ctx.stroke();
                            }
                        }
                        ctx.fillStyle = 'rgba(216,180,254,0.82)';
                        ctx.beginPath(); ctx.arc(a.x, a.y, Math.max(1, a.r + Math.sin(a.p) * 0.5), 0, Math.PI * 2); ctx.fill();
                    }
                }
                requestAnimationFrame(loop);
            };
            loop();
        })();
    </script>
</body>
</html>
