<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $title }}</title>
</head>
<body style="font-family: Arial, sans-serif; background:#f6f8fb; margin:0; padding:24px;">
    <table role="presentation" cellpadding="0" cellspacing="0" width="100%" style="max-width:640px; margin:0 auto; background:#ffffff; border-radius:12px; border:1px solid #e5e7eb;">
        <tr>
            <td style="padding:24px;">
                <h2 style="margin:0 0 12px; color:#111827;">{{ $title }}</h2>
                <p style="margin:0 0 12px; color:#374151;">Hello {{ $user->name }},</p>
                <p style="margin:0; color:#374151;">{{ $message ?: 'You have a new notification in your account.' }}</p>

                @if(!empty($data))
                    <div style="margin-top:16px; padding:12px; background:#f3f4f6; border-radius:8px;">
                        <p style="margin:0 0 8px; color:#111827; font-weight:600;">Details</p>
                        <pre style="margin:0; white-space:pre-wrap; color:#374151; font-size:12px;">{{ json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES) }}</pre>
                    </div>
                @endif
            </td>
        </tr>
    </table>
</body>
</html>
