<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>RiderHub — 6-Digit Verification Code</title>
</head>
<body style="margin:0;padding:0;background-color:#0f0a1f;font-family:-apple-system,BlinkMacSystemFont,'Segoe UI',Roboto,Helvetica,Arial,sans-serif;color:#0f172a;-webkit-font-smoothing:antialiased;">
    <table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="background:linear-gradient(145deg,#0f0a1f 0%,#1e1042 50%,#130d26 100%);background-color:#130d26;padding:40px 16px;">
        <tr>
            <td align="center">
                <!-- Main Aesthetic Card -->
                <table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="max-width:560px;background-color:#ffffff;border-radius:28px;overflow:hidden;border:1px solid rgba(167,139,250,0.35);box-shadow:0 28px 64px rgba(10,5,28,0.55);">
                    <!-- Top Shimmer Accent Strip -->
                    <tr>
                        <td style="height:6px;background:linear-gradient(90deg,#6d28d9 0%,#8b5cf6 35%,#d946ef 68%,#7c3aed 100%);font-size:0;line-height:0;">&nbsp;</td>
                    </tr>

                    <!-- Royal Violet Hero Header -->
                    <tr>
                        <td style="background:linear-gradient(135deg,#2e1065 0%,#5b21b6 48%,#7c3aed 100%);background-color:#5b21b6;padding:34px 36px 30px;text-align:center;color:#ffffff;">
                            <table role="presentation" cellpadding="0" cellspacing="0" align="center" style="margin:0 auto 14px;">
                                <tr>
                                    <td align="center" style="width:62px;height:62px;border-radius:18px;background-color:rgba(255,255,255,0.14);border:1.5px solid rgba(255,255,255,0.28);box-shadow:0 12px 26px rgba(15,6,40,0.35);">
                                        <img src="https://riderhub.online/icons/riderhub-logo.png" alt="RiderHub" width="44" height="44" style="display:block;margin:9px auto;border-radius:12px;border:0;">
                                    </td>
                                </tr>
                            </table>

                            <div style="display:inline-block;padding:5px 14px;border-radius:999px;background-color:rgba(255,255,255,0.14);border:1px solid rgba(255,255,255,0.24);font-size:10.5px;font-weight:800;letter-spacing:0.12em;text-transform:uppercase;color:#f3e8ff;margin-bottom:10px;">
                                &#128274; Official Security Verification
                            </div>

                            <div style="font-size:27px;font-weight:900;letter-spacing:-0.025em;color:#ffffff;line-height:1.2;">
                                Rider<span style="color:#ddd6fe;">Hub</span> Verification Code
                            </div>
                            <div style="font-size:13px;color:#e9d5ff;margin-top:6px;font-weight:500;">
                                Misamis Oriental &middot; Ride Booking &amp; Express Delivery
                            </div>
                        </td>
                    </tr>

                    <!-- Content Body -->
                    <tr>
                        <td style="padding:36px 36px 28px;background-color:#ffffff;">
                            <p style="margin:0 0 10px;font-size:18px;font-weight:800;color:#0f172a;">
                                Kumusta, {{ $firstName }}! &#128075;
                            </p>
                            <p style="margin:0 0 26px;font-size:14.5px;line-height:1.65;color:#475569;">
                                You requested a password reset for your <strong>RiderHub</strong> account. Enter the <strong>6-digit verification code</strong> below on the RiderHub recovery screen to verify your identity and set your new password:
                            </p>

                            @if ($otp)
                                @php
                                    $digits = str_split(preg_replace('/\D+/', '', (string) $otp) ?: '000000');
                                @endphp
                                <!-- Aesthetic 6-Digit Tile Container -->
                                <div style="background:linear-gradient(145deg,#faf5ff 0%,#f3e8ff 100%);background-color:#f5f3ff;border:1.5px solid #d8b4fe;border-radius:22px;padding:24px 16px 20px;text-align:center;margin:0 0 26px;box-shadow:inset 0 1px 0 #ffffff,0 10px 25px rgba(124,58,237,0.08);">
                                    <div style="font-size:11px;font-weight:800;text-transform:uppercase;letter-spacing:0.14em;color:#6d28d9;margin-bottom:14px;">
                                        Step 2 &middot; Enter This 6-Digit Code
                                    </div>

                                    <!-- Digit Tiles -->
                                    <table role="presentation" cellpadding="0" cellspacing="0" align="center" style="margin:0 auto;">
                                        <tr>
                                            @foreach ($digits as $d)
                                                <td style="padding:0 4px;">
                                                    <div style="width:46px;height:56px;line-height:56px;border-radius:14px;background-color:#ffffff;border:2px solid #8b5cf6;box-shadow:0 6px 14px rgba(109,40,217,0.12);font-size:26px;font-weight:900;color:#4c1d95;font-family:'Courier New',Consolas,monospace;text-align:center;">
                                                        {{ $d }}
                                                    </div>
                                                </td>
                                            @endforeach
                                        </tr>
                                    </table>

                                    <div style="margin-top:14px;font-size:12px;font-weight:600;color:#64748b;">
                                        &#9201; Expires in <strong style="color:#6d28d9;">15 minutes</strong> &middot; Generated {{ now()->timezone('Asia/Manila')->format('g:i A') }} (PHT)
                                    </div>
                                </div>
                            @endif

                            <!-- Direct Reset CTA -->
                            <div style="text-align:center;margin:0 0 26px;">
                                <p style="margin:0 0 14px;font-size:13px;color:#64748b;">
                                    Or tap the button below to open your password reset screen directly:
                                </p>
                                <a href="{{ $resetUrl }}" style="display:inline-block;background:linear-gradient(135deg,#7c3aed 0%,#6d28d9 50%,#5b21b6 100%);background-color:#6d28d9;color:#ffffff;text-decoration:none;font-size:14.5px;font-weight:800;padding:14px 32px;border-radius:16px;box-shadow:0 12px 24px rgba(109,40,217,0.32);">
                                    Verify &amp; Set New Password &rarr;
                                </a>
                            </div>

                            <!-- Security Info Pill -->
                            <table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="background-color:#f8fafc;border:1px solid #e2e8f0;border-radius:16px;padding:14px 18px;">
                                <tr>
                                    <td style="font-size:12px;line-height:1.55;color:#64748b;">
                                        <strong style="color:#334155;">&#128737; Security Tip:</strong> RiderHub staff will never ask for your 6-digit verification code. If you didn't request this reset, you can safely ignore this message—your password has not been changed.
                                    </td>
                                </tr>
                            </table>
                        </td>
                    </tr>

                    <!-- Official Company Footer -->
                    <tr>
                        <td style="background-color:#faf5ff;border-top:1px solid #ede9fe;padding:22px 32px;text-align:center;">
                            <div style="font-size:12.5px;font-weight:800;color:#5b21b6;">
                                RiderHub Mobility &amp; Express Delivery Platform
                            </div>
                            <div style="font-size:11.5px;color:#64748b;margin-top:4px;">
                                Tagoloan &amp; Misamis Oriental, Philippines &middot;
                                <a href="https://riderhub.online" style="color:#7c3aed;text-decoration:none;font-weight:700;">riderhub.online</a>
                            </div>
                            <div style="font-size:11px;color:#94a3b8;margin-top:6px;">
                                &copy; {{ date('Y') }} RiderHub Official Security &middot; Automated System Notification
                            </div>
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>
</body>
</html>
