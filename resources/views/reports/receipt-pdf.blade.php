<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Receipt #{{ $booking->id }} — RiderHub</title>
    <link rel="icon" type="image/svg+xml" href="{{ asset('icons/riderhub.svg') }}">
    <style>
        * { box-sizing: border-box; }
        body {
            margin: 0;
            min-height: 100vh;
            font-family: 'Segoe UI', system-ui, -apple-system, sans-serif;
            color: #0f172a;
            background:
                radial-gradient(circle at 18% 20%, rgba(139, 92, 246, 0.25), transparent 45%),
                radial-gradient(circle at 82% 80%, rgba(124, 58, 237, 0.22), transparent 45%),
                linear-gradient(145deg, #1e1042 0%, #2e1065 48%, #0f172a 100%);
            padding-bottom: 48px;
        }
        .toolbar {
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 14px 28px;
            background: rgba(15, 23, 42, 0.78);
            backdrop-filter: blur(14px);
            border-bottom: 1px solid rgba(196, 181, 253, 0.16);
            color: #fff;
        }
        .btn { display: inline-flex; align-items: center; gap: 6px; padding: 9px 18px; border-radius: 12px; font-size: 13px; font-weight: 700; text-decoration: none; border: none; cursor: pointer; }
        .btn-primary { background: linear-gradient(135deg, #8b5cf6, #6d28d9); color: #fff; box-shadow: 0 8px 20px rgba(124, 58, 237, 0.4); }
        .btn-ghost { background: rgba(255,255,255,0.1); color: #e2e8f0; border: 1px solid rgba(255,255,255,0.16); }

        .receipt { max-width: 580px; margin: 34px auto; background: #fff; border-radius: 26px; overflow: hidden; box-shadow: 0 30px 70px rgba(10, 5, 30, 0.5); }
        .hero {
            position: relative;
            overflow: hidden;
            background: linear-gradient(135deg, #5b21b6 0%, #7c3aed 55%, #4c1d95 100%);
            color: #fff;
            padding: 30px 34px 28px;
        }
        .hero::before {
            content: "";
            position: absolute;
            inset: 0;
            background-image:
                linear-gradient(to right, rgba(255,255,255,0.07) 1px, transparent 1px),
                linear-gradient(to bottom, rgba(255,255,255,0.07) 1px, transparent 1px);
            background-size: 28px 28px;
        }
        .hero-top { position: relative; display: flex; justify-content: space-between; align-items: center; }
        .hero-brand { display: flex; align-items: center; gap: 10px; font-size: 20px; font-weight: 900; }
        .hero-amount { position: relative; margin-top: 20px; font-size: 38px; font-weight: 900; letter-spacing: -0.02em; }
        .hero-meta { position: relative; margin-top: 6px; font-size: 12px; color: #ddd6fe; }

        .tear { height: 14px; background: radial-gradient(circle at 10px 0, transparent 7px, #fff 7.5px) repeat-x; background-size: 20px 14px; margin-top: -7px; position: relative; z-index: 2; }
        .body { padding: 24px 34px 28px; }
        .route-box { background: linear-gradient(180deg, #faf5ff, #ffffff); border: 1px solid #e9d5ff; border-left: 4px solid #7c3aed; border-radius: 14px; padding: 15px 18px; margin-bottom: 18px; font-size: 13px; }
        .row { display: flex; justify-content: space-between; gap: 16px; padding: 11px 0; border-bottom: 1px dashed #e2e8f0; font-size: 13px; }
        .row:last-child { border-bottom: none; }
        .label { color: #64748b; }
        .val { font-weight: 700; text-align: right; color: #0f172a; }
        .verified-pill { display: inline-flex; align-items: center; gap: 6px; margin-top: 16px; padding: 6px 14px; border-radius: 999px; background: #ecfdf5; border: 1px solid #a7f3d0; color: #065f46; font-size: 11px; font-weight: 800; }
        .footer { background: #faf5ff; border-top: 1px solid #ede9fe; padding: 16px 34px; text-align: center; font-size: 11px; color: #64748b; }

        @media print {
            body { background: #fff !important; padding: 0; }
            .toolbar { display: none !important; }
            .receipt { margin: 0 auto; box-shadow: none; border: 1px solid #e2e8f0; }
            .hero, .route-box, .verified-pill, .footer { -webkit-print-color-adjust: exact; print-color-adjust: exact; }
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
                <div class="hero-brand">
                    <svg width="26" height="26" viewBox="0 0 48 48" fill="none" aria-hidden="true">
                        <path d="M8 20h7M6 25h6M9 30h5" stroke="#DDD6FE" stroke-width="2.8" stroke-linecap="round"/>
                        <path d="M28.5 9C22.1 9 17 14.1 17 20.5c0 7.8 9.2 16.5 10.8 18a1 1 0 0 0 1.4 0c1.6-1.5 10.8-10.2 10.8-18C40 14.1 34.9 9 28.5 9Z" fill="#FFFFFF"/>
                        <path d="M24.8 25.5v-9.6h4.2c2.4 0 4 1.3 4 3.2 0 1.5-.9 2.6-2.4 3l2.9 3.4h-3l-2.4-3.1h-.9v3.1h-2.4Zm2.4-5.1h1.6c1 0 1.7-.5 1.7-1.3s-.7-1.3-1.7-1.3h-1.6v2.6Z" fill="#6D28D9"/>
                    </svg>
                    <span>RiderHub</span>
                </div>
                <span style="font-size:11px; font-weight:700; padding:4px 10px; border-radius:999px; background:rgba(255,255,255,0.16); border:1px solid rgba(255,255,255,0.25);">#RIDERHUB-{{ $booking->id }}</span>
            </div>
            <div class="hero-amount">₱{{ number_format((float) $booking->fare, 2) }}</div>
            <div class="hero-meta">{{ ucfirst($booking->type) }} · {{ ucfirst(str_replace('_', ' ', $booking->status)) }} · {{ $booking->created_at->format('M j, Y · g:i A') }}</div>
        </header>

        <div class="tear"></div>

        <div class="body">
            <div class="route-box">
                <div style="font-size:10px; text-transform:uppercase; letter-spacing:0.08em; color:#6d28d9; font-weight:800;">Pickup</div>
                <div style="font-weight:700; margin-top:2px;">{{ $booking->pickup_address }}</div>
                <div style="font-size:10px; text-transform:uppercase; letter-spacing:0.08em; color:#d97706; font-weight:800; margin-top:12px;">Drop-off</div>
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
            <div class="row"><span class="label">Total Fare</span><span class="val" style="color:#6d28d9; font-size:15px;">₱{{ number_format((float) $booking->fare, 2) }}</span></div>

            <div style="text-align:center;">
                <span class="verified-pill">✓ Verified Official RiderHub Receipt</span>
            </div>
        </div>

        <footer class="footer">
            <strong>Thank you for riding with RiderHub Misamis Oriental!</strong><br>
            Generated {{ now()->timezone('Asia/Manila')->format('M j, Y · g:i A') }} (PHT)
        </footer>
    </article>
</body>
</html>
