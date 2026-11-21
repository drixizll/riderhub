<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $title }} — RiderHub</title>
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600,700,800&display=swap" rel="stylesheet" />
    <link rel="icon" type="image/svg+xml" href="{{ asset('icons/riderhub.svg') }}">
    <style>
        * { box-sizing: border-box; }
        body {
            margin: 0;
            min-height: 100vh;
            font-family: 'Figtree', system-ui, -apple-system, sans-serif;
            color: #0f172a;
            background:
                radial-gradient(circle at 0% 0%, rgba(124, 58, 237, 0.08), transparent 45%),
                radial-gradient(circle at 100% 100%, rgba(139, 92, 246, 0.08), transparent 45%),
                #f1f5f9;
            padding-bottom: 56px;
            -webkit-font-smoothing: antialiased;
        }
        .toolbar {
            position: sticky;
            top: 0;
            z-index: 30;
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 12px;
            padding: 12px 28px;
            background: rgba(255, 255, 255, 0.86);
            backdrop-filter: blur(16px);
            border-bottom: 1px solid rgba(226, 232, 240, 0.9);
        }
        .toolbar-left { display: flex; align-items: center; gap: 10px; font-size: 13px; font-weight: 700; color: #334155; }
        .toolbar-pill { padding: 3px 9px; border-radius: 999px; background: #f5f3ff; color: #6d28d9; font-size: 11px; font-weight: 800; }
        .toolbar-actions { display: flex; gap: 10px; }
        .btn { display: inline-flex; align-items: center; gap: 7px; padding: 9px 16px; border-radius: 11px; font-size: 13px; font-weight: 700; text-decoration: none; border: none; cursor: pointer; transition: all 180ms ease; }
        .btn-primary { background: linear-gradient(135deg, #7c3aed, #6d28d9); color: #fff; box-shadow: 0 6px 18px rgba(124, 58, 237, 0.28); }
        .btn-primary:hover { transform: translateY(-1px); box-shadow: 0 10px 24px rgba(124, 58, 237, 0.36); }
        .btn-ghost { background: #fff; color: #475569; border: 1px solid #e2e8f0; }
        .btn-ghost:hover { background: #f8fafc; color: #0f172a; border-color: #cbd5e1; }

        .sheet {
            max-width: 980px;
            margin: 32px auto;
            background: #ffffff;
            border-radius: 20px;
            border: 1px solid #e2e8f0;
            overflow: hidden;
            box-shadow: 0 24px 60px -20px rgba(15, 23, 42, 0.14), 0 4px 12px rgba(15, 23, 42, 0.04);
        }
        .accent-bar { height: 5px; background: linear-gradient(90deg, #6d28d9 0%, #8b5cf6 50%, #c084fc 100%); }
        .inner { padding: 40px 46px 42px; }

        .doc-header { display: flex; justify-content: space-between; align-items: flex-start; gap: 24px; padding-bottom: 28px; border-bottom: 1px solid #f1f5f9; }
        .brand { display: flex; align-items: center; gap: 14px; }
        .brand-mark { width: 46px; height: 46px; border-radius: 13px; background: linear-gradient(135deg, #8b5cf6, #6d28d9); display: flex; align-items: center; justify-content: center; box-shadow: 0 8px 18px rgba(124, 58, 237, 0.25); }
        .brand-name { font-size: 22px; font-weight: 800; letter-spacing: -0.025em; color: #0f172a; }
        .brand-name span { color: #7c3aed; }
        .brand-tag { font-size: 12px; color: #64748b; margin-top: 2px; }
        .doc-meta { text-align: right; }
        .doc-tag { display: inline-flex; align-items: center; gap: 6px; padding: 4px 11px; border-radius: 999px; background: #f5f3ff; border: 1px solid #ede9fe; color: #6d28d9; font-size: 10px; font-weight: 800; letter-spacing: 0.08em; text-transform: uppercase; }
        .doc-id { margin-top: 8px; font-size: 12px; font-weight: 700; color: #334155; font-variant-numeric: tabular-nums; }
        .doc-date { margin-top: 2px; font-size: 12px; color: #64748b; }

        .title-block { display: flex; justify-content: space-between; align-items: flex-end; gap: 20px; margin-top: 28px; }
        .title { margin: 0; font-size: 26px; font-weight: 800; letter-spacing: -0.025em; color: #0f172a; }
        .subtitle { margin: 4px 0 0; font-size: 13.5px; color: #64748b; }
        .prepared { font-size: 12px; color: #64748b; text-align: right; }
        .prepared strong { color: #0f172a; }

        .summary { display: grid; grid-template-columns: repeat(4, 1fr); gap: 14px; margin: 28px 0 30px; }
        .stat { background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 14px; padding: 16px 18px; }
        .stat:last-child { background: linear-gradient(135deg, #f5f3ff 0%, #faf5ff 100%); border-color: #ddd6fe; }
        .stat-label { font-size: 11px; font-weight: 700; color: #64748b; letter-spacing: 0.02em; }
        .stat:last-child .stat-label { color: #6d28d9; }
        .stat-value { margin-top: 8px; font-size: 24px; font-weight: 800; color: #0f172a; letter-spacing: -0.02em; font-variant-numeric: tabular-nums; }
        .stat:last-child .stat-value { color: #5b21b6; }

        .table-wrap { border: 1px solid #e2e8f0; border-radius: 14px; overflow: hidden; }
        table { width: 100%; border-collapse: collapse; font-size: 12.5px; }
        th { background: #f8fafc; color: #475569; text-align: left; padding: 12px 14px; font-size: 10.5px; font-weight: 800; text-transform: uppercase; letter-spacing: 0.06em; border-bottom: 1px solid #e2e8f0; }
        td { padding: 13px 14px; border-bottom: 1px solid #f1f5f9; vertical-align: top; color: #334155; }
        tr:last-child td { border-bottom: none; }
        .badge { display: inline-flex; align-items: center; gap: 5px; padding: 3px 9px; border-radius: 999px; font-size: 11px; font-weight: 700; text-transform: capitalize; }
        .badge-completed { background: #ecfdf5; color: #047857; }
        .badge-cancelled { background: #fff1f2; color: #be123c; }
        .badge-pending { background: #fffbeb; color: #b45309; }
        .badge-active { background: #f5f3ff; color: #6d28d9; }

        .empty-state { padding: 48px 24px; text-align: center; border: 1px dashed #cbd5e1; border-radius: 16px; background: #f8fafc; }
        .empty-icon { width: 44px; height: 44px; margin: 0 auto 12px; border-radius: 12px; background: #f5f3ff; color: #7c3aed; display: flex; align-items: center; justify-content: center; }

        .footer { margin-top: 34px; padding-top: 22px; border-top: 1px solid #f1f5f9; display: flex; justify-content: space-between; align-items: flex-end; gap: 20px; font-size: 11.5px; color: #64748b; }
        .sig { width: 230px; text-align: center; }
        .sig-line { height: 28px; border-bottom: 1px solid #cbd5e1; margin-bottom: 6px; }
        .sig-label { font-size: 11px; font-weight: 700; color: #475569; }

        @media print {
            body { background: #fff !important; padding: 0; }
            .toolbar { display: none !important; }
            .sheet { margin: 0; border: none; border-radius: 0; box-shadow: none; max-width: 100%; }
            .inner { padding: 24px 12px; }
            .accent-bar, .stat, th, .badge { -webkit-print-color-adjust: exact; print-color-adjust: exact; }
        }
        @media (max-width: 720px) {
            .inner { padding: 24px 20px; }
            .summary { grid-template-columns: repeat(2, 1fr); }
            .doc-header, .title-block, .footer { flex-direction: column; align-items: flex-start; }
            .doc-meta, .prepared { text-align: left; }
        }
    </style>
</head>
<body>
    <div class="toolbar">
        <div class="toolbar-left">
            <span class="toolbar-pill">PDF PREVIEW</span>
            <span>{{ $title }}</span>
        </div>
        <div class="toolbar-actions">
            <a href="{{ $backUrl }}" class="btn btn-ghost">← Back</a>
            <button type="button" onclick="window.print()" class="btn btn-primary">Download / Print PDF</button>
        </div>
    </div>

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
                    <span class="doc-tag">Official System Report</span>
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
                        <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12h6m-6 4h6m2 5H7a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h5.586a1 1 0 0 1 .707.293l5.414 5.414a1 1 0 0 1 .293.707V19a2 2 0 0 1-2 2Z" /></svg>
                    </div>
                    <div style="font-size: 14px; font-weight: 700; color: #0f172a;">No booking records yet</div>
                    <div style="margin-top: 4px; font-size: 12.5px; color: #64748b;">Completed and active bookings will automatically appear in this report table.</div>
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
</body>
</html>
