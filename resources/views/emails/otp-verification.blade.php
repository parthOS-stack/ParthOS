<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <title>{{ $brandName }} verification code</title>
</head>
<body style="margin:0;padding:0;background-color:{{ $background }};font-family:'Segoe UI',Roboto,'Helvetica Neue',Arial,sans-serif;-webkit-font-smoothing:antialiased;">
@php
    $digits = str_split(str_pad(preg_replace('/\D/', '', (string) $code), 6, '0', STR_PAD_LEFT));
@endphp
<table role="presentation" width="100%" cellspacing="0" cellpadding="0" border="0" style="background-color:{{ $background }};padding:40px 16px;">
    <tr>
        <td align="center">
            <table role="presentation" width="100%" cellspacing="0" cellpadding="0" border="0" style="max-width:560px;background-color:#ffffff;border-radius:16px;overflow:hidden;box-shadow:0 8px 30px rgba(20,20,22,0.08);">
                <tr>
                    <td style="background-color:{{ $primary }};padding:28px 32px;text-align:center;">
                        <span style="display:inline-block;font-size:18px;line-height:1.2;font-weight:700;letter-spacing:0.08em;color:#ffffff;text-transform:uppercase;">
                            {{ $brandName }}
                        </span>
                    </td>
                </tr>
                <tr>
                    <td style="padding:36px 32px 28px;">
                        <p style="margin:0 0 10px;font-size:11px;line-height:1.4;font-weight:700;letter-spacing:0.12em;text-transform:uppercase;color:{{ $muted }};">
                            Verification code
                        </p>
                        <h1 style="margin:0 0 14px;font-size:28px;line-height:1.25;font-weight:700;color:{{ $text }};">
                            Confirm it's you
                        </h1>
                        <p style="margin:0 0 28px;font-size:15px;line-height:1.7;color:{{ $muted }};">
                            Enter this code to complete your sign-in. It's valid for the next
                            <strong style="color:{{ $text }};">{{ $expiresMinutes }} minutes</strong>.
                        </p>

                        <table role="presentation" cellspacing="0" cellpadding="0" border="0" align="center" style="margin:0 auto 28px;">
                            <tr>
                                @foreach ($digits as $digit)
                                    <td style="padding:0 4px;">
                                        <div style="width:44px;height:52px;line-height:52px;background-color:{{ $primaryDark }};border-radius:10px;text-align:center;font-size:22px;font-weight:700;color:#ffffff;">
                                            {{ $digit }}
                                        </div>
                                    </td>
                                @endforeach
                            </tr>
                        </table>

                        <p style="margin:0;font-size:13px;line-height:1.7;color:{{ $muted }};">
                            Didn't request this code? You can safely ignore this email — no changes will be made to your account.
                        </p>
                    </td>
                </tr>
                <tr>
                    <td style="padding:0 32px 28px;">
                        <table role="presentation" width="100%" cellspacing="0" cellpadding="0" border="0">
                            <tr>
                                <td style="border-top:1px solid #eef0f4;padding-top:20px;text-align:center;">
                                    <p style="margin:0 0 6px;font-size:12px;line-height:1.6;color:{{ $muted }};">
                                        Sent by {{ $brandName }} &bull; Do not reply to this email
                                    </p>
                                    @if (!empty($footerAddress))
                                        <p style="margin:0;font-size:12px;line-height:1.6;color:{{ $muted }};">
                                            {{ $footerAddress }}
                                        </p>
                                    @endif
                                </td>
                            </tr>
                        </table>
                    </td>
                </tr>
            </table>

            <p style="margin:18px 0 0;font-size:12px;line-height:1.6;color:{{ $muted }};">
                &copy; {{ date('Y') }} {{ $brandName }}. All rights reserved.
            </p>
        </td>
    </tr>
</table>
</body>
</html>
