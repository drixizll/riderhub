<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Receipt #{{ $booking->id }} — RiderHub</title>
    <link rel="icon" type="image/svg+xml" href="{{ asset('icons/riderhub.svg') }}">
    <style>
        * { box-sizing: border-box; }
        body { margin: 0; font-family: 'Segoe UI', system-ui, -apple-system, sans-serif; color: #0f172a; background: #f5f3ff; }
        .toolbar { display: flex; align-items: center; justify-content: space-between; padding: 14px 24px; background: #1e1b4b; color: #fff; }
        .btn { display: inline-flex; align-items: center; gap: 6px; padding: 9px 16px; border-radius: 10px; font-size: 13px; font-weight: 700; text-decoration: none; border: none; cursor: pointer; }
        .btn-primary { background: #7c3aed; color: #fff; }
        .btn-ghost { background: rgba(255,255,255,0.14); color: #fff; }
        .receipt { max-width: 560px; margin: 32px auto; background: #fff; border-radius: 22px; overflow: hidden; box-shadow: 0 20px 45px rgba(46, 16, 101, 0.12); }
        .hero { background: linear-gradient(135deg, #7c3aed, #5b21b6); color: #fff; padding: 28px 32px; }
        .hero-top { display: flex; justify-content: space-between; align-items: center; }
        .hero-amount { margin-top: 18px; font-size: 36px; font-weight: 900; letter-spacing: -0.02em; }
        .body { padding: 28px 32px; }
        .row { display: flex; justify-content: space-between; gap: 16px; padding: 11px 0; border-bottom: 1px dashed #e2e8f0; font-size: 13px; }
        .row:last-child { border-bottom: none; }
        .label { color: #64748b; }
        .val { font-weight: 700; text-align: right; color: #0f172a; }
        .route-box { background: #faf5ff; border: 1px solid #ede9fe; border-radius: 14px; padding: 14px 16px; margin-bottom: 18px; font-size: 13px; }
        .footer { background: #f8fafc; padding: 16px 32px; text-align: center; font-size: 11px; color: #64748b; }
        @media print {
            body { background: #fff; }
            .toolbar { display: none !important; }
            .receipt { margin: 0 auto; box-shadow: none; border: 1px solid #e2e8f0; }
            .hero, .route-box { -webkit-print-color-adjust: exact; print-color-adjust: exact; }
        }
    </style>
</head>
<body>
    <div class="toolbar">
        <strong>Official Trip Receipt #{{ $booking->id }}</strong>
        <div style="display:flex; gap:10px;">
            <a href="{{ $backUrl }}" class="btn btn-ghost">← Back</a>
            <button type="button" onclick="window.print()" class="btn btn-primary">Download / Print PDF</button>
        </div>
    </div>

    <article class="receipt">
        <header class="hero">
            <div class="hero-top">
                <strong style="font-size:20px;">RiderHub</strong>
                <span style="font-size:12px; opacity:0.9;">Receipt #RIDERHUB-{{ $booking->id }}</span>
            </div>
            <div class="hero-amount">₱{{ number_format((float) $booking->fare, 2) }}</div>
            <div style="margin-top:6px; font-size:12px; opacity:0.9;">{{ ucfirst($booking->type) }} · {{ ucfirst(str_replace('_', ' ', $booking->status)) }} · {{ $booking->created_at->format('M j, Y · g:i A') }}</div>
        </header>

        <div class="body">
            <div class="route-box">
                <div style="font-size:11px; text-transform:uppercase; color:#6d28d9; font-weight:700;">Pickup</div>
                <div style="font-weight:700; margin-top:2px;">{{ $booking->pickup_address }}</div>
                <div style="font-size:11px; text-transform:uppercase; color:#d97706; font-weight:700; margin-top:12px;">Drop-off</div>
                <div style="font-weight:700; margin-top:2px;">{{ $booking->dropoff_address }}</div>
            </div>

            <div class="row"><span class="label">Customer</span><span class="val">{{ $booking->customer?->name }}</span></div>
            <div class="row"><span class="label">Assigned Rider</span><span class="val">{{ $booking->rider?->name ?? 'Unassigned' }}</span></div>
            <div class="row"><span class="label">Service Type</span><span class="val">{{ ucfirst($booking->type) }}</span></div>
            @if ($booking->item_description)
                <div class="row"><span class="label">Package Details</span><span class="val">{{ $booking->item_description }}</span></div>
            @endif
            <div class="row"><span class="label">Payment Method</span><span class="val">{{ ucfirst($booking->payment_method) }}</span></div>
            <div class="row"><span class="label">Payment Status</span><span class="val">{{ ucfirst($booking->payment?->status ?? 'pending') }}</span></div>
            @if ($booking->payment?->paymongo_reference)
                <div class="row"><span class="label">PayMongo Ref</span><span class="val">{{ $booking->payment->paymongo_reference }}</span></div>
            @endif
            <div class="row"><span class="label">Total Fare</span><span class="val" style="color:#6d28d9;">₱{{ number_format((float) $booking->fare, 2) }}</span></div>
        </div>

        <footer class="footer">
            Thank you for riding with RiderHub Misamis Oriental!<br>
            Generated {{ now()->timezone('Asia/Manila')->format('M j, Y · g:i A') }} (PHT)
        </footer>
    </article>
</body>
</html>
