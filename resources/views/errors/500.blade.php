<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>500 — Temporary Hiccup · RiderHub</title>
    <link rel="icon" type="image/svg+xml" href="{{ asset('icons/riderhub.svg') }}?v=3">
    <style>
        *{box-sizing:border-box;margin:0;padding:0}
        body{min-height:100dvh;display:flex;align-items:center;justify-content:center;padding:24px;font-family:system-ui,-apple-system,Segoe UI,Roboto,sans-serif;background:radial-gradient(circle at top,#24104f,#0a0616 65%);color:#f8fafc}
        .card{max-width:440px;width:100%;text-align:center;border-radius:28px;border:1px solid rgba(167,139,250,.28);background:rgba(17,10,36,.88);padding:40px 28px;box-shadow:0 30px 80px rgba(0,0,0,.55);backdrop-filter:blur(16px)}
        .code{font-size:64px;font-weight:900;line-height:1;background:linear-gradient(135deg,#f43f5e,#fbbf24);-webkit-background-clip:text;-webkit-text-fill-color:transparent}
        h1{margin-top:14px;font-size:22px;font-weight:800}
        p{margin-top:10px;font-size:14px;line-height:1.6;color:#cbd5e1}
        a{display:inline-flex;align-items:center;gap:8px;margin-top:24px;padding:12px 24px;border-radius:14px;background:linear-gradient(135deg,#7c3aed,#c026d3);color:#fff;font-weight:800;font-size:14px;text-decoration:none;box-shadow:0 12px 30px rgba(124,58,237,.4)}
        a:hover{filter:brightness(1.1)}
    </style>
</head>
<body>
    <main class="card">
        <div class="code">500</div>
        <h1>Something hit a bump</h1>
        <p>Our RiderHub server encountered a temporary issue. Please refresh or head back to the home page.</p>
        <a href="{{ url('/') }}">↻ Back to RiderHub</a>
    </main>
</body>
</html>
