<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>RiderHub — 6-Digit Verification Code</title>
</head>
<body style="margin:0;padding:0;background-color:#f5f3ff;font-family:-apple-system,BlinkMacSystemFont,'Segoe UI',Roboto,Helvetica,Arial,sans-serif;color:#0f172a;-webkit-font-smoothing:antialiased;">
    <table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="background-color:#f5f3ff;padding:36px 16px;">
        <tr>
            <td align="center">
                <!-- Main Aesthetic Card -->
                <table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="max-width:560px;background-color:#ffffff;border-radius:26px;overflow:hidden;border:1px solid #ddd6fe;box-shadow:0 22px 50px rgba(109,40,217,0.14);">
                    <!-- Top Accent Strip -->
                    <tr>
                        <td style="height:6px;background:linear-gradient(90deg,#6d28d9 0%,#8b5cf6 50%,#d946ef 100%);background-color:#7c3aed;font-size:0;line-height:0;">&nbsp;</td>
                    </tr>

                    <!-- Royal Violet Hero Header (Pure CSS Emblem for 100% Gmail Inbox Delivery) -->
                    <tr>
                        <td style="background:linear-gradient(135deg,#2e1065 0%,#5b21b6 50%,#7c3aed 100%);background-color:#5b21b6;padding:32px 36px 28px;text-align:center;color:#ffffff;">
                            <table role="presentation" cellpadding="0" cellspacing="0" align="center" style="margin:0 auto 14px;">
                                <tr>
                                    <td align="center" valign="middle" style="width:58px;height:58px;border-radius:18px;background-color:#7c3aed;border:2px solid #ddd6fe;box-shadow:0 10px 22px rgba(15,6,40,0.3);font-size:28px;font-weight:900;color:#ffffff;font-family:'Segoe UI',Roboto,Arial,sans-serif;">
                                        R
                                    </td>
                                </tr>
                            </table>

                            <div style="display:inline-block;padding:5px 14px;border-radius:999px;background-color:rgba(255,255,255,0.15);border:1px solid rgba(255,255,255,0.28);font-size:10.5px;font-weight:800;letter-spacing:0.12em;text-transform:uppercase;color:#f3e8ff;margin-bottom:10px;">
                                Password Reset Verification
                            </div>

                            <div style="font-size:26px;font-weight:900;letter-spacing:-0.025em;color:#ffffff;line-height:1.2;">
                                Rider<span style="color:#ddd6fe;">Hub</span> Verification Code
                            </div>
                            <div style="font-size:13px;color:#e9d5ff;margin-top:6px;font-weight:500;">
                                Misamis Oriental &middot; Ride Booking &amp; Express Delivery
                            </div>
                        </td>
                    </tr>

                    <!-- Content Body -->
                    <tr>
                        <td style="padding:34px 36px 28px;background-color:#ffffff;">
                            <p style="margin:0 0 10px;font-size:18px;font-weight:800;color:#0f172a;">
                                Hello {{ $firstName }},
                            </p>
                            <p style="margin:0 0 24px;font-size:14.5px;line-height:1.65;color:#475569;">
                                We received a request to reset the password for your <strong>RiderHub</strong> account. Enter the <strong>6-digit verification code</strong> below on the recovery screen to verify your account and set your new password:
                            </p>

                            @if ($otp)
                                @php
                                    $digits = str_split(preg_replace('/\D+/', '', (string) $otp) ?: '000000');
                                @endphp
                                <!-- Aesthetic 6-Digit Tile Container -->
                                <div style="background:linear-gradient(145deg,#faf5ff 0%,#f3e8ff 100%);background-color:#f5f3ff;border:1.5px solid #d8b4fe;border-radius:22px;padding:24px 16px 20px;text-align:center;margin:0 0 26px;">
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
                                        Expires in <strong style="color:#6d28d9;">15 minutes</strong> &middot; Generated {{ now()->timezone('Asia/Manila')->format('g:i A') }} (PHT)
                                    </div>
                                </div>
                            @endif

                            <!-- Direct Reset CTA -->
                            <div style="text-align:center;margin:0 0 26px;">
                                <p style="margin:0 0 14px;font-size:13px;color:#64748b;">
                                    Or tap the button below to open your password reset screen directly:
                                </p>
                                <a href="{{ $resetUrl }}" style="display:inline-block;background:linear-gradient(135deg,#7c3aed 0%,#6d28d9 50%,#5b21b6 100%);background-color:#6d28d9;color:#ffffff;text-decoration:none;font-size:14.5px;font-weight:800;padding:14px 32px;border-radius:16px;box-shadow:0 10px 22px rgba(109,40,217,0.28);">
                                    Verify &amp; Set New Password &rarr;
                                </a>
                            </div>

                            <!-- Security Info Pill -->
                            <table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="background-color:#f8fafc;border:1px solid #e2e8f0;border-radius:16px;padding:14px 18px;">
                                <tr>
                                    <td style="font-size:12px;line-height:1.55;color:#64748b;">
                                        <strong style="color:#334155;">Security Tip:</strong> RiderHub staff will never ask for your 6-digit verification code. If you did not request this reset, you can safely ignore this message.
                                    </td>
                                </tr>
                            </table>
                        </td>
                    </tr>

                    <!-- Official Company Footer -->
                    <tr>
                        <td style="background-color:#faf5ff;border-top:1px solid #ede9fe;padding:20px 32px;text-align:center;">
                            <div style="font-size:12.5px;font-weight:800;color:#5b21b6;">
                                RiderHub Mobility &amp; Express Delivery Platform
                            </div>
                            <div style="font-size:11.5px;color:#64748b;margin-top:4px;">
                                Tagoloan &amp; Misamis Oriental, Philippines
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
