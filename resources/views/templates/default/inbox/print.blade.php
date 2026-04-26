{{--
 | Opterius Mail - Open source webmail.
 | Modern, fast and responsive webmail that works with any IMAP/SMTP server.
 | https://opterius.com
 |
 | Copyright (c) Host Server SRL (Opterius)
 | @license  AGPL-3.0  https://opensource.org/license/agpl-v3
 | @author   Iosif Gabriel Chimilevschi <office@opterius.com>
--}}
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $message['subject'] }} — Print</title>
    <style>
        * { box-sizing: border-box; margin: 0; padding: 0; }
        body {
            font-family: Georgia, 'Times New Roman', serif;
            font-size: 13px;
            color: #111;
            background: #fff;
            padding: 32px 40px;
            max-width: 760px;
            margin: 0 auto;
        }
        .header { border-bottom: 2px solid #111; padding-bottom: 16px; margin-bottom: 20px; }
        .logo { font-size: 11px; color: #555; letter-spacing: .05em; text-transform: uppercase; margin-bottom: 12px; }
        .subject { font-size: 20px; font-weight: bold; margin-bottom: 12px; line-height: 1.3; }
        .meta-row { font-size: 12px; color: #333; margin-bottom: 4px; }
        .meta-label { font-weight: bold; display: inline-block; width: 52px; color: #555; }
        .body { margin-top: 24px; line-height: 1.6; }
        .body pre { white-space: pre-wrap; font-family: inherit; font-size: 13px; }
        .attachments { margin-top: 20px; padding-top: 12px; border-top: 1px solid #ccc; }
        .attachments h3 { font-size: 11px; text-transform: uppercase; letter-spacing: .05em; color: #555; margin-bottom: 8px; }
        .attachment-item { font-size: 12px; color: #333; margin-bottom: 3px; }
        .footer { margin-top: 32px; padding-top: 12px; border-top: 1px solid #ccc; font-size: 11px; color: #888; }
        @media print {
            body { padding: 0; }
            .no-print { display: none !important; }
        }
    </style>
</head>
<body>

<div class="no-print" style="margin-bottom: 20px;">
    <button onclick="window.print()"
            style="padding: 8px 20px; background: #f97316; color: #fff; border: none; border-radius: 6px; font-size: 13px; cursor: pointer; margin-right: 8px;">
        Print
    </button>
    <button onclick="window.close()"
            style="padding: 8px 16px; background: #eee; color: #333; border: 1px solid #ccc; border-radius: 6px; font-size: 13px; cursor: pointer;">
        Close
    </button>
</div>

<div class="header">
    <div class="logo">Opterius Mail</div>
    <div class="subject">{{ $message['subject'] }}</div>

    <div class="meta-row">
        <span class="meta-label">From:</span>
        @php
            $fn = $message['from']['name'] ?: $message['from']['email'];
            $fe = $message['from']['email'];
        @endphp
        {{ $fn }}@if($fn !== $fe && $fe !== '') &lt;{{ $fe }}&gt;@endif
    </div>

    <div class="meta-row">
        <span class="meta-label">To:</span>
        {{ implode(', ', array_map(fn($a) => ($a['name'] ?: $a['email']) . ($a['name'] && $a['email'] ? ' <' . $a['email'] . '>' : ''), $message['to'])) ?: '—' }}
    </div>

    @if(!empty($message['cc']))
    <div class="meta-row">
        <span class="meta-label">Cc:</span>
        {{ implode(', ', array_map(fn($a) => ($a['name'] ?: $a['email']) . ($a['name'] && $a['email'] ? ' <' . $a['email'] . '>' : ''), $message['cc'])) }}
    </div>
    @endif

    <div class="meta-row">
        <span class="meta-label">Date:</span>
        {{ $message['date_formatted'] }}
    </div>
</div>

<div class="body">
    @if($message['body_html'] !== '')
        {!! $message['body_html'] !!}
    @elseif($message['body_text'] !== '')
        <pre>{{ $message['body_text'] }}</pre>
    @else
        <em style="color:#888">No message content.</em>
    @endif
</div>

@if(!empty($message['attachments']))
<div class="attachments">
    <h3>Attachments</h3>
    @foreach($message['attachments'] as $att)
        <div class="attachment-item">
            📎 {{ $att['name'] }}
            @php
                $sz = $att['size'];
                $sizeStr = $sz < 1024 ? $sz . ' B' : ($sz < 1048576 ? round($sz/1024,1) . ' KB' : round($sz/1048576,1) . ' MB');
            @endphp
            ({{ $sizeStr }})
        </div>
    @endforeach
</div>
@endif

<div class="footer">
    Printed from Opterius Mail · {{ now()->format('F j, Y \a\t g:i A') }}
</div>

<script>
    // Auto-print if opened as popup
    if (window.opener) {
        window.addEventListener('load', function() { window.print(); });
    }
</script>

</body>
</html>
