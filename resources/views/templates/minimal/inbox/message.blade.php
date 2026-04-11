{{--
 | Opterius Mail - Open source webmail.
 | Minimal template — message view.
 | https://opterius.com
 |
 | Copyright (c) Host Server SRL (Opterius)
 | @license  AGPL-3.0  https://opensource.org/license/agpl-v3
 | @author   Iosif Gabriel Chimilevschi <office@opterius.com>
--}}
@extends(mailView('layouts.app'))

@section('title', $message['subject'])

@section('content')
@php
    $backHref = strtoupper($message['folder']) === 'INBOX'
        ? route('inbox')
        : route('folder', ['folder' => rawurlencode($message['folder'])]);

    $replyHref   = route('compose.reply',   ['folder' => rawurlencode($message['folder']), 'uid' => $message['uid']]);
    $forwardHref = route('compose.forward', ['folder' => rawurlencode($message['folder']), 'uid' => $message['uid']]);

    $fromName  = $message['from']['name'] ?: $message['from']['email'];
    $fromEmail = $message['from']['email'];
@endphp

{{-- Actions bar --}}
<div class="flex items-center gap-2 mb-4">
    <a href="{{ $backHref }}"
       class="flex items-center gap-1 text-sm text-gray-500 hover:text-gray-800 transition-colors">
        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
        </svg>
        Back
    </a>
    <span class="text-gray-300">|</span>
    <a href="{{ $replyHref }}" class="text-sm text-gray-500 hover:text-gray-800 transition-colors" title="Reply (r)">Reply</a>
    <a href="{{ $forwardHref }}" class="text-sm text-gray-500 hover:text-gray-800 transition-colors" title="Forward (f)">Forward</a>
    <form id="delete-form" method="POST"
          action="{{ route('message.destroy', ['folder' => rawurlencode($message['folder']), 'uid' => $message['uid']]) }}"
          onsubmit="return confirm('Move this message to Trash?')" class="inline">
        @csrf
        @method('DELETE')
        <button type="submit" class="text-sm text-gray-500 hover:text-red-600 transition-colors" title="Delete (Del)">
            Delete
        </button>
    </form>
</div>

{{-- Message card --}}
<div class="bg-white border border-gray-200 rounded-lg overflow-hidden">

    {{-- Header --}}
    <div class="px-6 py-4 border-b border-gray-100">
        <h2 class="font-semibold text-gray-900 mb-3">{{ $message['subject'] }}</h2>
        <div class="flex items-start justify-between gap-4">
            <div>
                <span class="font-medium text-gray-800">{{ $fromName }}</span>
                @if($fromName !== $fromEmail && $fromEmail !== '')
                    <span class="text-gray-400 ml-1 text-xs">&lt;{{ $fromEmail }}&gt;</span>
                @endif
                <div class="text-xs text-gray-400 mt-0.5">
                    To: {{ implode(', ', array_map(fn($a) => $a['name'] ?: $a['email'], $message['to'])) ?: '—' }}
                    @if(!empty($message['cc']))
                        &nbsp;&middot;&nbsp;Cc: {{ implode(', ', array_map(fn($a) => $a['name'] ?: $a['email'], $message['cc'])) }}
                    @endif
                </div>
            </div>
            <span class="text-xs text-gray-400 flex-shrink-0">{{ $message['date_formatted'] }}</span>
        </div>
    </div>

    {{-- Body --}}
    <div>
        @if($message['body_html'] !== '')
            @php
                $html = $message['body_html'];
                if (!str_contains($html, '<base')) {
                    $html = (stripos($html, '<head>') !== false)
                        ? str_ireplace('<head>', '<head><base target="_blank">', $html)
                        : '<base target="_blank">' . $html;
                }
            @endphp
            <iframe srcdoc="{!! htmlspecialchars($html, ENT_QUOTES, 'UTF-8') !!}"
                    sandbox="allow-same-origin allow-popups allow-popups-to-escape-sandbox"
                    class="w-full border-0" style="min-height:400px"
                    onload="this.style.height = Math.max(400, this.contentDocument.documentElement.scrollHeight) + 'px'">
            </iframe>
        @elseif($message['body_text'] !== '')
            <div class="px-6 py-5">
                <pre class="whitespace-pre-wrap text-sm text-gray-800 font-sans leading-relaxed">{{ $message['body_text'] }}</pre>
            </div>
        @else
            <div class="px-6 py-10 text-center text-sm text-gray-400">No message content.</div>
        @endif
    </div>
</div>

@endsection

@push('scripts')
<script>
(function () {
    var replyHref   = {{ Js::from($replyHref) }};
    var forwardHref = {{ Js::from($forwardHref) }};
    var backHref    = {{ Js::from($backHref) }};

    function isTyping(el) {
        var t = el.tagName;
        return t === 'INPUT' || t === 'TEXTAREA' || t === 'SELECT' || el.isContentEditable;
    }
    document.addEventListener('keydown', function (e) {
        if (isTyping(e.target) || e.ctrlKey || e.altKey || e.metaKey) return;
        switch (e.key) {
            case 'r': window.location.href = replyHref;   break;
            case 'f': window.location.href = forwardHref; break;
            case 'u': case 'Escape': window.location.href = backHref; break;
            case 'Delete': case 'Backspace':
                e.preventDefault();
                if (confirm('Move this message to Trash?')) document.getElementById('delete-form').submit();
                break;
        }
    });
})();
</script>
@endpush
