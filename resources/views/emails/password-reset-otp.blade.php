<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>RiderHub Password Reset Verification Code</title>
</head>
<body style="margin:0;padding:0;background-color:#f3f0ff;font-family:'Segoe UI',Roboto,Helvetica,Arial,sans-serif;color:#0f172a;">
    <table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="background-color:#f3f0ff;padding:32px 16px;">
        <tr>
            <td align="center">
                <table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="max-width:540px;background-color:#ffffff;border-radius:24px;overflow:hidden;border:1px solid #ddd6fe;box-shadow:0 20px 45px rgba(109,40,217,0.12);">
                    <!-- Royal Violet Company Header with Logo -->
                    <tr>
                        <td style="background:linear-gradient(135deg,#5b21b6 0%,#7c3aed 55%,#4c1d95 100%);padding:28px 32px;text-align:center;color:#ffffff;">
                            <div style="display:inline-block;width:54px;height:54px;line-height:54px;border-radius:16px;background-color:rgba(255,255,255,0.18);border:1px solid rgba(255,255,255,0.3);font-size:26px;font-weight:900;color:#ffffff;margin-bottom:10px;">
                                R
                            </div>
                            <div style="font-size:24px;font-weight:800;letter-spacing:-0.02em;color:#ffffff;">
                                Rider<span style="color:#ddd6fe;">Hub</span>
                            </div>
                            <div style="font-size:12px;color:#e9d5ff;margin-top:4px;font-weight:600;letter-spacing:0.04em;text-transform:uppercase;">
                                Misamis Oriental · Official Security Notice
                            </div>
                        </td>
                    </tr>

                    <!-- Body -->
                    <tr>
                        <td style="padding:32px 32px 24px;">
                            <p style="margin:0 0 12px;font-size:17px;font-weight:800;color:#0f172a;">
                                Hello {{ $firstName }},
                            </p>
                            <p style="margin:0 0 22px;font-size:14.5px;line-height:1.6;color:#475569;">
                                We received a request to reset the password for your <strong>RiderHub</strong> account. Enter the 6-digit verification code below on the recovery screen to continue:
                            </p>

                            @if ($otp)
                                <div style="background:linear-gradient(135deg,#f5f3ff 0%,#ede9fe 100%);border:2px dashed #8b5cf6;border-radius:18px;padding:22px 16px;text-align:center;margin:0 0 24px;">
                                    <div style="font-size:11px;font-weight:800;text-transform:uppercase;letter-spacing:0.12em;color:#6d28d9;margin-bottom:8px;">
                                        Your 6-Digit Verification Code
                                    </div>
                                    <div style="font-size:36px;font-weight:900;letter-spacing:0.32em;color:#4c1d95;font-family:'Courier New',monospace;">
                                        {{ $otp }}
                                    </div>
                                    <div style="font-size:12px;color:#64748b;margin-top:8px;">
                                        Valid for 15 minutes · Do not share this code with anyone
                                    </div>
                                </div>
                            @endif

                            <p style="margin:0 0 18px;font-size:13.5px;line-height:1.6;color:#64748b;text-align:center;">
                                Or click the button below to open the password reset page directly:
                            </p>

                            <div style="text-align:center;margin:0 0 24px;">
                                <a href="{{ $resetUrl }}" style="display:inline-block;background:linear-gradient(135deg,#7c3aed 0%,#6d28d9 100%);color:#ffffff;text-decoration:none;font-size:14px;font-weight:800;padding:13px 28px;border-radius:14px;box-shadow:0 8px 20px rgba(124,58,237,0.3);">
                                    Reset RiderHub Password →
                                </a>
                            </div>

                            <p style="margin:0;font-size:12.5px;line-height:1.5;color:#94a3b8;text-align:center;">
                                If you did not request a password reset, you can safely ignore this email. Your account remains secure.
                            </p>
                        </td>
                    </tr>

                    <!-- Footer -->
                    <tr>
                        <td style="background-color:#faf5ff;border-top:1px solid #ede9fe;padding:18px 32px;text-align:center;font-size:11.5px;color:#64748b;">
                            <strong style="color:#5b21b6;">RiderHub Mobility &amp; Express Delivery Platform</strong><br>
                            Tagoloan &amp; Misamis Oriental, Philippines · <a href="https://riderhub.online" style="color:#7c3aed;text-decoration:none;font-weight:700;">riderhub.online</a>
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>
</body>
</html>
