<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $title }} — RiderHub</title>
    <link rel="icon" type="image/svg+xml" href="{{ asset('icons/riderhub.svg') }}">
    <style>
        * { box-sizing: border-box; }
        body {
            margin: 0;
            min-height: 100vh;
            font-family: 'Segoe UI', system-ui, -apple-system, sans-serif;
            color: #0f172a;
            background:
                radial-gradient(circle at 12% 15%, rgba(139, 92, 246, 0.22), transparent 45%),
                radial-gradient(circle at 88% 82%, rgba(124, 58, 237, 0.2), transparent 48%),
                linear-gradient(145deg, #1e1042 0%, #2e1065 45%, #0f172a 100%);
            padding-bottom: 48px;
        }
        .toolbar {
            position: sticky;
            top: 0;
            z-index: 30;
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 12px;
            padding: 14px 28px;
            background: rgba(15, 23, 42, 0.78);
            backdrop-filter: blur(14px);
            border-bottom: 1px solid rgba(196, 181, 253, 0.16);
            color: #fff;
        }
        .toolbar-title { display: flex; align-items: center; gap: 10px; font-size: 14px; font-weight: 700; }
        .toolbar-dot { width: 9px; height: 9px; border-radius: 999px; background: #34d399; box-shadow: 0 0 10px #34d399; }
        .toolbar-actions { display: flex; gap: 10px; }
        .btn { display: inline-flex; align-items: center; gap: 8px; padding: 9px 18px; border-radius: 12px; font-size: 13px; font-weight: 700; text-decoration: none; border: none; cursor: pointer; transition: transform 160ms ease, box-shadow 160ms ease, background 160ms ease; }
        .btn-primary { background: linear-gradient(135deg, #8b5cf6, #6d28d9); color: #fff; box-shadow: 0 8px 20px rgba(124, 58, 237, 0.4); }
        .btn-primary:hover { transform: translateY(-1px); box-shadow: 0 12px 24px rgba(124, 58, 237, 0.55); }
        .btn-ghost { background: rgba(255,255,255,0.1); color: #e2e8f0; border: 1px solid rgba(255,255,255,0.16); }
        .btn-ghost:hover { background: rgba(255,255,255,0.18); color: #fff; }

        .sheet {
            position: relative;
            max-width: 1040px;
            margin: 30px auto;
            background: #ffffff;
            border-radius: 24px;
            overflow: hidden;
            box-shadow: 0 30px 70px rgba(10, 5, 30, 0.45);
        }

        /* Executive Royal Violet Banner */
        .hero {
            position: relative;
            overflow: hidden;
            padding: 34px 40px 30px;
            background: linear-gradient(135deg, #5b21b6 0%, #7c3aed 55%, #4c1d95 100%);
            color: #fff;
        }
        .hero::before {
            content: "";
            position: absolute;
            inset: 0;
            background-image:
                linear-gradient(to right, rgba(255,255,255,0.07) 1px, transparent 1px),
                linear-gradient(to bottom, rgba(255,255,255,0.07) 1px, transparent 1px);
            background-size: 32px 32px;
            pointer-events: none;
        }
        .hero-orb {
            position: absolute;
            right: -60px;
            top: -80px;
            width: 280px;
            height: 280px;
            border-radius: 999px;
            background: radial-gradient(circle, rgba(216, 180, 254, 0.35), transparent 70%);
            pointer-events: none;
        }
        .hero-top { position: relative; display: flex; justify-content: space-between; align-items: flex-start; gap: 20px; }
        .brand { display: flex; align-items: center; gap: 14px; }
        .brand-logo { width: 48px; height: 48px; border-radius: 14px; background: rgba(255,255,255,0.16); border: 1px solid rgba(255,255,255,0.28); display: flex; align-items: center; justify-content: center; box-shadow: 0 8px 20px rgba(0,0,0,0.15); }
        .brand-name { font-size: 24px; font-weight: 900; letter-spacing: -0.02em; }
        .brand-sub { font-size: 11px; color: #ddd6fe; letter-spacing: 0.04em; text-transform: uppercase; margin-top: 2px; }
        .doc-badge { display: inline-flex; align-items: center; gap: 6px; padding: 5px 12px; border-radius: 999px; background: rgba(255,255,255,0.16); border: 1px solid rgba(255,255,255,0.26); font-size: 10px; font-weight: 800; letter-spacing: 0.1em; text-transform: uppercase; color: #f5f3ff; }
        .hero-meta { position: relative; text-align: right; font-size: 12px; color: #ede9fe; line-height: 1.7; }
        .hero-title-row { position: relative; margin-top: 22px; padding-top: 18px; border-top: 1px solid rgba(255,255,255,0.18); display: flex; justify-content: space-between; align-items: flex-end; gap: 16px; }
        .title { margin: 0; font-size: 25px; font-weight: 900; letter-spacing: -0.02em; }
        .subtitle { margin: 4px 0 0; font-size: 13px; color: #ddd6fe; }

        .content { padding: 30px 40px 36px; }

        .summary { display: grid; grid-template-columns: repeat(4, 1fr); gap: 14px; margin-bottom: 26px; }
        .stat {
            position: relative;
            background: linear-gradient(180deg, #faf5ff 0%, #ffffff 100%);
            border: 1px solid #e9d5ff;
            border-top: 3px solid #7c3aed;
            border-radius: 14px;
            padding: 15px 16px;
        }
        .stat-label { font-size: 10px; text-transform: uppercase; letter-spacing: 0.08em; color: #6d28d9; font-weight: 800; }
        .stat-value { margin-top: 6px; font-size: 22px; font-weight: 900; color: #0f172a; letter-spacing: -0.02em; }

        .table-wrap { border: 1px solid #e2e8f0; border-radius: 14px; overflow: hidden; }
        table { width: 100%; border-collapse: collapse; font-size: 12px; }
        th { background: #2e1065; color: #f5f3ff; text-align: left; padding: 11px 13px; font-size: 10px; font-weight: 800; text-transform: uppercase; letter-spacing: 0.07em; }
        td { padding: 11px 13px; border-bottom: 1px solid #f1f5f9; vertical-align: top; }
        tr:last-child td { border-bottom: none; }
        tr:nth-child(even) td { background: #faf8ff; }

        .badge { display: inline-block; padding: 3px 9px; border-radius: 999px; font-size: 10px; font-weight: 800; text-transform: capitalize; }
        .badge-completed { background: #d1fae5; color: #065f46; }
        .badge-cancelled { background: #ffe4e6; color: #9f1239; }
        .badge-pending { background: #fef3c7; color: #92400e; }
        .badge-active { background: #ede9fe; color: #5b21b6; }

        .empty-box { padding: 40px 20px; text-align: center; color: #64748b; background: #faf5ff; border: 1px dashed #c4b5fd; border-radius: 16px; font-size: 13px; }

        .signoff { margin-top: 30px; padding-top: 20px; border-top: 1px solid #e2e8f0; display: flex; justify-content: space-between; align-items: flex-end; gap: 24px; font-size: 11px; color: #64748b; }
        .sig-line { width: 220px; border-top: 1px solid #94a3b8; padding-top: 6px; text-align: center; font-weight: 700; color: #334155; }

        @media print {
            body { background: #fff !important; padding: 0; }
            .toolbar { display: none !important; }
            .sheet { margin: 0; box-shadow: none; border-radius: 0; max-width: 100%; }
            .hero, .stat, th, .badge, tr:nth-child(even) td { -webkit-print-color-adjust: exact; print-color-adjust: exact; }
        }
        @media (max-width: 720px) {
            .summary { grid-template-columns: repeat(2, 1fr); }
            .hero, .content { padding: 22px; }
            .hero-top, .hero-title-row, .signoff { flex-direction: column; align-items: flex-start; }
            .hero-meta { text-align: left; }
        }
    </style>
</head>
<body>
    <div class="toolbar">
        <div class="toolbar-title">
            <span class="toolbar-dot"></span>
            <span>RiderHub Executive PDF Report</span>
        </div>
        <div class="toolbar-actions">
            <a href="{{ $backUrl }}" class="btn btn-ghost">← Back</a>
            <button type="button" onclick="window.print()" class="btn btn-primary">Download / Print PDF</button>
        </div>
    </div>

    <main class="sheet">
        <header class="hero">
            <div class="hero-orb"></div>
            <div class="hero-top">
                <div class="brand">
                    <div class="brand-logo">
                        <svg width="30" height="30" viewBox="0 0 48 48" fill="none" aria-hidden="true">
                            <path d="M8 20h7M6 25h6M9 30h5" stroke="#DDD6FE" stroke-width="2.6" stroke-linecap="round"/>
                            <path d="M28.5 9C22.1 9 17 14.1 17 20.5c0 7.8 9.2 16.5 10.8 18a1 1 0 0 0 1.4 0c1.6-1.5 10.8-10.2 10.8-18C40 14.1 34.9 9 28.5 9Z" fill="#FFFFFF"/>
                            <path d="M24.8 25.5v-9.6h4.2c2.4 0 4 1.3 4 3.2 0 1.5-.9 2.6-2.4 3l2.9 3.4h-3l-2.4-3.1h-.9v3.1h-2.4Zm2.4-5.1h1.6c1 0 1.7-.5 1.7-1.3s-.7-1.3-1.7-1.3h-1.6v2.6Z" fill="#6D28D9"/>
                        </svg>
                    </div>
                    <div>
                        <div class="brand-name">RiderHub</div>
                        <div class="brand-sub">Misamis Oriental · Mobility &amp; Delivery Platform</div>
                    </div>
                </div>
                <div class="hero-meta">
                    <span class="doc-badge">Official System Report</span>
                    <div style="margin-top: 8px;"><strong>Generated:</strong> {{ now()->timezone('Asia/Manila')->format('M j, Y · g:i A') }} (PHT)</div>
                    <div><strong>Prepared by:</strong> {{ $generatedBy }}</div>
                </div>
            </div>

            <div class="hero-title-row">
                <div>
                    <h1 class="title">{{ $title }}</h1>
                    <p class="subtitle">{{ $subtitle }}</p>
                </div>
                <div style="font-size: 12px; color: #ddd6fe; font-weight: 700;">Total Records: {{ $bookings->count() }}</div>
            </div>
        </header>

        <div class="content">
            <section class="summary">
                @foreach ($summary as $item)
                    <div class="stat">
                        <div class="stat-label">{{ $item['label'] }}</div>
                        <div class="stat-value">{{ $item['value'] }}</div>
                    </div>
                @endforeach
            </section>

            @if ($bookings->isEmpty())
                <div class="empty-box">
                    <strong style="color:#4c1d95; font-size:14px;">No booking records found</strong>
                    <div style="margin-top:4px;">Once rides or deliveries are booked, they will automatically appear in this official report.</div>
                </div>
            @else
                <div class="table-wrap">
                    <table>
                        <thead>
                            <tr>
                                <th>#</th>
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
                                    <td><strong style="color:#5b21b6;">#{{ $booking->id }}</strong></td>
                                    <td style="white-space:nowrap;">{{ $booking->created_at->format('M j, Y') }}<br><span style="color:#94a3b8;">{{ $booking->created_at->format('g:i A') }}</span></td>
                                    @if ($showCustomer)<td><strong>{{ $booking->customer?->name ?? 'N/A' }}</strong></td>@endif
                                    @if ($showRider)<td>{{ $booking->rider?->name ?? 'Unassigned' }}</td>@endif
                                    <td>
                                        <strong>{{ ucfirst($booking->type) }}</strong><br>
                                        <span style="color:#475569;">{{ $booking->pickup_address }} → {{ $booking->dropoff_address }}</span>
                                    </td>
                                    <td>
                                        {{ ucfirst($booking->payment_method) }}<br>
                                        <span style="color:#64748b;">{{ ucfirst($booking->payment?->status ?? 'pending') }}</span>
                                    </td>
                                    <td><span class="badge {{ $badgeClass }}">{{ str_replace('_', ' ', $booking->status) }}</span></td>
                                    <td style="text-align:right; font-weight:800; color:#0f172a; white-space:nowrap;">₱{{ number_format((float) $booking->fare, 2) }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif

            <footer class="signoff">
                <div>
                    <div style="font-weight:700; color:#334155;">RiderHub Operations · Misamis Oriental, Philippines</div>
                    <div style="margin-top:3px;">System-generated document · Verified via RiderHub Platform</div>
                </div>
                <div class="sig-line">Authorized Signature / Verified By</div>
            </footer>
        </div>
    </main>
</body>
</html>
