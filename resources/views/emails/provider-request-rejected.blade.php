<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Provider Request Update</title>
</head>
<body style="font-family: Arial, sans-serif; background:#f6f8fb; margin:0; padding:24px;">
    <table role="presentation" cellpadding="0" cellspacing="0" width="100%" style="max-width:640px; margin:0 auto; background:#ffffff; border-radius:12px; border:1px solid #e5e7eb;">
        <tr>
            <td style="padding:24px;">
                <h2 style="margin:0 0 12px; color:#111827;">Provider application update</h2>
                <p style="margin:0 0 12px; color:#374151;">Hello {{ $providerRequest->owner_name }},</p>
                <p style="margin:0 0 16px; color:#374151;">
                    We reviewed your provider application for <strong>{{ $providerRequest->business_name }}</strong>, but we are unable to approve it at this time.
                </p>

                @if($reason)
                    <div style="margin:16px 0; padding:16px; background:#fef2f2; border-radius:10px; border:1px solid #fecaca;">
                        <p style="margin:0 0 8px; color:#991b1b; font-weight:600;">Reason</p>
                        <p style="margin:0; color:#7f1d1d;">{{ $reason }}</p>
                    </div>
                @endif

                <p style="margin:0; color:#374151;">
                    You can submit a new request later with updated business details or supporting documents.
                </p>
            </td>
        </tr>
    </table>
</body>
</html>
