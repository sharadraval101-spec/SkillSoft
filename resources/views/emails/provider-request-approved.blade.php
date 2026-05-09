<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Provider Request Approved</title>
</head>
<body style="font-family: Arial, sans-serif; background:#f6f8fb; margin:0; padding:24px;">
    <table role="presentation" cellpadding="0" cellspacing="0" width="100%" style="max-width:640px; margin:0 auto; background:#ffffff; border-radius:12px; border:1px solid #e5e7eb;">
        <tr>
            <td style="padding:24px;">
                <h2 style="margin:0 0 12px; color:#111827;">Your provider account is ready</h2>
                <p style="margin:0 0 12px; color:#374151;">Hello {{ $providerRequest->owner_name }},</p>
                <p style="margin:0 0 16px; color:#374151;">
                    Your request for <strong>{{ $providerRequest->business_name }}</strong> has been approved and a provider account has been created for you.
                </p>

                <div style="margin:16px 0; padding:16px; background:#f3f4f6; border-radius:10px;">
                    <p style="margin:0 0 8px; color:#111827; font-weight:600;">Login details</p>
                    <p style="margin:0 0 6px; color:#374151;"><strong>Email:</strong> {{ $user->email }}</p>
                    <p style="margin:0 0 6px; color:#374151;"><strong>Password:</strong> {{ $plainPassword }}</p>
                    <p style="margin:0; color:#374151;"><strong>Dashboard:</strong> <a href="{{ $loginUrl }}">{{ $loginUrl }}</a></p>
                </div>

                <p style="margin:0; color:#374151;">
                    Please sign in and change your password after your first login.
                </p>
            </td>
        </tr>
    </table>
</body>
</html>
