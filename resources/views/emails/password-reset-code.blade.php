<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Password Reset Code</title>
</head>
<body style="font-family: Arial, sans-serif; background: #0f172a; color: #e2e8f0; padding: 24px;">
    <div style="max-width: 560px; margin: 0 auto; background: #111827; border: 1px solid #1f2937; border-radius: 16px; padding: 24px;">
        <h1 style="margin: 0 0 16px; color: #ffffff; font-size: 22px;">Password Reset Verification</h1>
        <p style="margin: 0 0 16px;">Hi {{ $user->name }},</p>
        <p style="margin: 0 0 20px;">
            Use this code to reset your SkillSlot password:
        </p>

        <div style="display: inline-block; font-size: 28px; letter-spacing: 6px; font-weight: 700; color: #22d3ee; background: #0b1120; border: 1px solid #1e293b; border-radius: 10px; padding: 10px 16px; margin-bottom: 20px;">
            {{ $code }}
        </div>

        <p style="margin: 0 0 8px;">This code expires at <strong>{{ $expiresAtText }}</strong>.</p>
    </div>
</body>
</html>
