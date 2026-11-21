<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $title }} — RiderHub</title>
    <link rel="icon" type="image/svg+xml" href="{{ asset('icons/riderhub.svg') }}">
    <style>
        * { box-sizing: border-box; }
        body { margin: 0; font-family: 'Segoe UI', system-ui, -apple-system, sans-serif; color: #0f172a; background: #f5f3ff; }
        .toolbar { position: sticky; top: 0; z-index: 20; display: flex; align-items: center; justify-content: space-between; gap: 12px; padding: 14px 24px; background: #1e1b4b; color: #fff; box-shadow: 0 4px 16px rgba(15, 23, 42, 0.2); }
        .toolbar-actions { display: flex; gap: 10px; }
        .btn { display: inline-flex; align-items: center; gap: 8px; padding: 9px 16px; border-radius: 10px; font-size: 13px; font-weight: 700; text-decoration: none; border: none; cursor: pointer; }
        .btn-primary { background: #7c3aed; color: #fff; }
        .btn-primary:hover { background: #6d28d9; }
        .btn-ghost { background: rgba(255,255,255,0.14); color: #fff; }
        .sheet { max-width: 1020px; margin: 28px auto; background: #fff; border-radius: 18px; padding: 36px 40px; box-shadow: 0 18px 40px rgba(46, 16, 101, 0.08); }
        .header { display: flex; justify-content: space-between; align-items: flex-start; gap: 20px; border-bottom: 2px solid #ede9fe; padding-bottom: 20px; }
        .brand { display: flex; align-items: center; gap: 12px; }
        .brand-mark { width: 42px; height: 42px; border-radius: 12px; background: linear-gradient(135deg, #8b5cf6, #6d28d9); display: flex; align-items: center; justify-content: center; color: #fff; font-weight: 900; font-size: 20px; }
        .brand-name { font-size: 22px; font-weight: 800; letter-spacing: -0.02em; }
        .brand-name span { color: #7c3aed; }
        .meta { text-align: right; font-size: 12px; color: #64748b; line-height: 1.6; }
        .title { margin: 22px 0 4px; font-size: 24px; font-weight: 800; color: #1e1b4b; }
        .subtitle { margin: 0; font-size: 13px; color: #64748b; }
        .summary { display: grid; grid-template-columns: repeat(4, 1fr); gap: 14px; margin: 24px 0; }
        .stat { background: #faf5ff; border: 1px solid #ede9fe; border-radius: 12px; padding: 14px 16px; }
        .stat-label { font-size: 11px; text-transform: uppercase; letter-spacing: 0.06em; color: #6d28d9; font-weight: 700; }
        .stat-value { margin-top: 6px; font-size: 20px; font-weight: 800; color: #0f172a; }
        table { width: 100%; border-collapse: collapse; margin-top: 12px; font-size: 12px; }
        th { background: #6d28d9; color: #fff; text-align: left; padding: 10px 12px; font-size: 11px; text-transform: uppercase; letter-spacing: 0.05em; }
        td { padding: 10px 12px; border-bottom: 1px solid #f1f5f9; vertical-align: top; }
        tr:nth-child(even) td { background: #fcfaff; }
        .badge { display: inline-block; padding: 2px 8px; border-radius: 999px; font-size: 10px; font-weight: 700; text-transform: capitalize; }
        .badge-completed { background: #d1fae5; color: #065f46; }
        .badge-cancelled { background: #ffe4e6; color: #9f1239; }
        .badge-pending { background: #fef3c7; color: #92400e; }
        .badge-active { background: #ede9fe; color: #5b21b6; }
        .footer { margin-top: 28px; padding-top: 16px; border-top: 1px solid #e2e8f0; display: flex; justify-content: space-between; font-size: 11px; color: #64748b; }
        @media print {
            body { background: #fff; }
            .toolbar { display: none !important; }
            .sheet { margin: 0; padding: 0; box-shadow: none; border-radius: 0; max-width: 100%; }
            th { -webkit-print-color-adjust: exact; print-color-adjust: exact; }
            .stat, .badge { -webkit-print-color-adjust: exact; print-color-adjust: exact; }
        }
        @media (max-width: 720px) {
            .summary { grid-template-columns: repeat(2, 1fr); }
            .sheet { margin: 12px; padding: 20px; }
        }
    </style>
</head>
<body>
    <div class="toolbar">
        <strong>RiderHub PDF Report Preview</strong>
        <div class="toolbar-actions">
            <a href="{{ $backUrl }}" class="btn btn-ghost">← Back</a>
            <button type="button" onclick="window.print()" class="btn btn-primary">Download / Print PDF</button>
        </div>
    </div>

    <main class="sheet">
        <div class="header">
            <div class="brand">
                <div class="brand-mark">R</div>
                <div>
                    <div class="brand-name">Rider<span>Hub</span></div>
                    <div style="font-size: 11px; color: #64748b;">Misamis Oriental · Rides &amp; Express Delivery</div>
                </div>
            </div>
            <div class="meta">
                <div><strong>Generated:</strong> {{ now()->timezone('Asia/Manila')->format('M j, Y · g:i A') }} (PHT)</div>
                <div><strong>Prepared by:</strong> {{ $generatedBy }}</div>
            </div>
        </div>

        <h1 class="title">{{ $title }}</h1>
        <p class="subtitle">{{ $subtitle }}</p>

        <section class="summary">
            @foreach ($summary as $item)
                <div class="stat">
                    <div class="stat-label">{{ $item['label'] }}</div>
                    <div class="stat-value">{{ $item['value'] }}</div>
                </div>
            @endforeach
        </section>

        @if ($bookings->isEmpty())
            <p style="padding: 24px 0; text-align: center; color: #64748b;">No bookings matched the selected filters.</p>
        @else
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
                            <td><strong>#{{ $booking->id }}</strong></td>
                            <td style="white-space:nowrap;">{{ $booking->created_at->format('M j, Y') }}<br><span style="color:#94a3b8;">{{ $booking->created_at->format('g:i A') }}</span></td>
                            @if ($showCustomer)<td>{{ $booking->customer?->name ?? 'N/A' }}</td>@endif
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
                            <td style="text-align:right; font-weight:700; white-space:nowrap;">₱{{ number_format((float) $booking->fare, 2) }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        @endif

        <div class="footer">
            <span>RiderHub Official System Report · Misamis Oriental, Philippines</span>
            <span>Total Records: {{ $bookings->count() }}</span>
        </div>
    </main>
</body>
</html>
