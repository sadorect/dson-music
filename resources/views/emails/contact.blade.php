<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Contact Form Submission</title>
    <style>
        body { font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif; background: #f3f4f6; margin: 0; padding: 0; }
        .wrapper { max-width: 600px; margin: 40px auto; background: #ffffff; border-radius: 12px; overflow: hidden; box-shadow: 0 2px 8px rgba(0,0,0,.08); }
        .header { background: linear-gradient(135deg, #ef4444, #f97316); padding: 28px 32px; }
        .header h1 { color: #fff; margin: 0; font-size: 22px; font-weight: 700; }
        .header p { color: rgba(255,255,255,.85); margin: 4px 0 0; font-size: 14px; }
        .body { padding: 32px; }
        .field-label { font-size: 11px; font-weight: 700; text-transform: uppercase; letter-spacing: .08em; color: #9ca3af; margin-bottom: 4px; }
        .field-value { font-size: 15px; color: #111827; margin-bottom: 20px; }
        .message-box { background: #f9fafb; border: 1px solid #e5e7eb; border-radius: 8px; padding: 16px; font-size: 15px; color: #111827; line-height: 1.6; white-space: pre-wrap; margin-bottom: 0; }
        .footer { background: #f9fafb; border-top: 1px solid #e5e7eb; padding: 16px 32px; font-size: 12px; color: #9ca3af; text-align: center; }
        .reply-hint { background: #eff6ff; border: 1px solid #bfdbfe; border-radius: 8px; padding: 12px 16px; font-size: 13px; color: #1d4ed8; margin-top: 20px; }
    </style>
</head>
<body>
<div class="wrapper">
    <div class="header">
        <h1>New Contact Message</h1>
        <p>Submitted via the GrinMuzik contact form</p>
    </div>
    <div class="body">
        <div class="field-label">From</div>
        <div class="field-value">{{ $senderName }} &lt;{{ $senderEmail }}&gt;</div>

        <div class="field-label">Subject</div>
        <div class="field-value">{{ $subject }}</div>

        <div class="field-label">Message</div>
        <div class="message-box">{{ $body }}</div>

        <div class="reply-hint">
            💡 Hit <strong>Reply</strong> to respond directly to {{ $senderName }}.
        </div>
    </div>
    <div class="footer">
        GrinMuzik &mdash; {{ now()->format('d M Y, H:i') }} UTC
    </div>
</div>
</body>
</html>
