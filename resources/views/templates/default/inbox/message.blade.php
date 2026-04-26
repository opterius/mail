{{--
 | Opterius Mail - Open source webmail.
 | Modern, fast and responsive webmail that works with any IMAP/SMTP server.
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
    $initial   = mb_strtoupper(mb_substr($fromName, 0, 1, 'UTF-8'), 'UTF-8');

    $folderLabel = static function (array $f): string {
        $attrs = $f['attributes'] ?? [];
        $upper = strtoupper($f['name']);
        if ($upper === 'INBOX')                         return 'Inbox';
        if (in_array('\\sent',   $attrs, true))         return 'Sent';
        if (in_array('\\drafts', $attrs, true))         return 'Drafts';
        if (in_array('\\trash',  $attrs, true))         return 'Trash';
        if (in_array('\\junk',   $attrs, true))         return 'Spam';
        if (str_contains($upper, 'SENT'))               return 'Sent';
        if (str_contains($upper, 'DRAFT'))              return 'Drafts';
        if (str_contains($upper, 'TRASH') || str_contains($upper, 'DELETED')) return 'Trash';
        if (str_contains($upper, 'JUNK')  || str_contains($upper, 'SPAM'))    return 'Spam';
        return $f['name'];
    };

    $moveFolders = array_values(array_filter(
        $folders,
        fn($f) => $f['name'] !== $message['folder']
    ));
    $moveUrl = route('message.move', ['folder' => rawurlencode($message['folder']), 'uid' => $message['uid']]);
@endphp

<div class="flex flex-col h-full">

    {{-- Toolbar --}}
    <div class="flex items-center gap-2 px-6 py-3 border-b border-gray-100 dark:border-gray-700 bg-white dark:bg-gray-900 sticky top-0 z-10">
        <a href="{{ $backHref }}"
           class="flex items-center gap-1.5 px-3 py-1.5 text-sm text-gray-600 dark:text-gray-400 hover:bg-gray-100 dark:hover:bg-gray-800 rounded-lg transition-colors">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
            </svg>
            Back
        </a>

        <div class="flex items-center gap-1 ml-auto">
            {{-- Reply --}}
            <a href="{{ $replyHref }}"
               class="flex items-center gap-1.5 px-3 py-1.5 text-sm text-gray-600 dark:text-gray-400 hover:bg-gray-100 dark:hover:bg-gray-800 rounded-lg transition-colors"
               title="Reply (r)">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                          d="M3 10h10a8 8 0 018 8v2M3 10l6 6m-6-6l6-6"/>
                </svg>
                Reply
            </a>

            {{-- Forward --}}
            <a href="{{ $forwardHref }}"
               class="flex items-center gap-1.5 px-3 py-1.5 text-sm text-gray-600 dark:text-gray-400 hover:bg-gray-100 dark:hover:bg-gray-800 rounded-lg transition-colors"
               title="Forward (f)">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                          d="M21 10H11a8 8 0 00-8 8v2m18-10l-6-6m6 6l-6 6"/>
                </svg>
                Forward
            </a>

            {{-- Move to folder --}}
            @if(count($moveFolders) > 0)
            <div x-data="{ open: false }" class="relative">
                <button @click="open = !open" @keydown.escape.window="open = false"
                        type="button"
                        class="flex items-center gap-1.5 px-3 py-1.5 text-sm text-gray-600 dark:text-gray-400 hover:bg-gray-100 dark:hover:bg-gray-800 rounded-lg transition-colors"
                        title="Move to folder">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                              d="M3 7a2 2 0 012-2h4l2 2h8a2 2 0 012 2v8a2 2 0 01-2 2H5a2 2 0 01-2-2V7z"/>
                    </svg>
                    Move to
                    <svg class="w-3 h-3 ml-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                    </svg>
                </button>

                <div x-show="open" @click.outside="open = false"
                     x-transition:enter="transition ease-out duration-100"
                     x-transition:enter-start="opacity-0 scale-95"
                     x-transition:enter-end="opacity-100 scale-100"
                     x-transition:leave="transition ease-in duration-75"
                     x-transition:leave-start="opacity-100 scale-100"
                     x-transition:leave-end="opacity-0 scale-95"
                     class="absolute right-0 mt-1 w-52 bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-lg shadow-lg z-50 py-1 max-h-72 overflow-y-auto"
                     style="display:none; top: 100%;">
                    @foreach($moveFolders as $f)
                    <form method="POST" action="{{ $moveUrl }}">
                        @csrf
                        <input type="hidden" name="target" value="{{ $f['name'] }}">
                        <button type="submit"
                                class="w-full text-left px-4 py-2 text-sm text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700 transition-colors truncate">
                            {{ $folderLabel($f) }}
                        </button>
                    </form>
                    @endforeach
                </div>
            </div>
            @endif

            {{-- Delete --}}
            <form id="delete-form" method="POST"
                  action="{{ route('message.destroy', ['folder' => rawurlencode($message['folder']), 'uid' => $message['uid']]) }}"
                  onsubmit="return confirm('Move this message to Trash?')">
                @csrf
                @method('DELETE')
                <button type="submit"
                        class="flex items-center gap-1.5 px-3 py-1.5 text-sm text-gray-600 dark:text-gray-400 hover:bg-red-50 dark:hover:bg-red-900/20 hover:text-red-600 dark:hover:text-red-400 rounded-lg transition-colors"
                        title="Delete (Del)">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                              d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                    </svg>
                    Delete
                </button>
            </form>
        </div>
    </div>

    {{-- Message header --}}
    <div class="px-6 py-5 border-b border-gray-100 dark:border-gray-700">
        <h2 class="text-lg font-semibold text-gray-900 dark:text-gray-100 mb-4">{{ $message['subject'] }}</h2>

        <div class="flex items-start gap-3">
            {{-- Avatar --}}
            <div class="flex-shrink-0 w-10 h-10 rounded-full bg-orange-100 dark:bg-orange-900/30 text-orange-600 dark:text-orange-400
                        flex items-center justify-center text-sm font-semibold select-none">
                {{ $initial }}
            </div>

            <div class="flex-1 min-w-0">
                <div class="flex items-baseline justify-between gap-4">
                    <div class="min-w-0">
                        <span class="font-medium text-sm text-gray-900 dark:text-gray-100">{{ $fromName }}</span>
                        @if($fromName !== $fromEmail && $fromEmail !== '')
                            <span class="text-sm text-gray-400 dark:text-gray-500 ml-1">&lt;{{ $fromEmail }}&gt;</span>
                        @endif
                    </div>
                    <span class="flex-shrink-0 text-xs text-gray-400 dark:text-gray-500">{{ $message['date_formatted'] }}</span>
                </div>

                <div class="mt-1 text-xs text-gray-500 dark:text-gray-400">
                    <span class="text-gray-400 dark:text-gray-500">To:</span>
                    {{ implode(', ', array_map(fn($a) => $a['name'] ?: $a['email'], $message['to'])) ?: '—' }}
                </div>
                @if(!empty($message['cc']))
                    <div class="text-xs text-gray-500 dark:text-gray-400">
                        <span class="text-gray-400 dark:text-gray-500">Cc:</span>
                        {{ implode(', ', array_map(fn($a) => $a['name'] ?: $a['email'], $message['cc'])) }}
                    </div>
                @endif

                @if($message['has_attachments'])
                    <div class="flex items-center gap-1 mt-1.5 text-xs text-gray-500 dark:text-gray-400">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                  d="M15.172 7l-6.586 6.586a2 2 0 102.828 2.828l6.414-6.586a4 4 0 00-5.656-5.656l-6.415 6.585a6 6 0 108.486 8.486L20.5 13"/>
                        </svg>
                        Has attachments
                    </div>
                @endif
            </div>
        </div>
    </div>

    {{-- Message body --}}
    <div class="flex-1 overflow-auto">
        @if($message['body_html'] !== '')
            @php
                $html = $message['body_html'];
                if (!str_contains($html, '<base')) {
                    if (stripos($html, '<head>') !== false) {
                        $html = str_ireplace('<head>', '<head><base target="_blank">', $html);
                    } elseif (stripos($html, '<html') !== false) {
                        $html = preg_replace('/(<html[^>]*>)/i', '$1<head><base target="_blank"></head>', $html);
                    } else {
                        $html = '<base target="_blank">' . $html;
                    }
                }
            @endphp
            <iframe
                id="msg-body"
                srcdoc="{!! htmlspecialchars($html, ENT_QUOTES, 'UTF-8') !!}"
                sandbox="allow-same-origin allow-popups allow-popups-to-escape-sandbox"
                class="w-full border-0"
                style="min-height: 400px"
                onload="this.style.height = Math.max(400, this.contentDocument.documentElement.scrollHeight) + 'px'">
            </iframe>
        @elseif($message['body_text'] !== '')
            <div class="px-6 py-6">
                <pre class="whitespace-pre-wrap text-sm text-gray-800 dark:text-gray-200 font-sans leading-relaxed">{{ $message['body_text'] }}</pre>
            </div>
        @else
            <div class="flex items-center justify-center h-40 text-sm text-gray-400 dark:text-gray-500">
                No message content.
            </div>
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
        var tag = el.tagName;
        return tag === 'INPUT' || tag === 'TEXTAREA' || tag === 'SELECT' || el.isContentEditable;
    }

    document.addEventListener('keydown', function (e) {
        if (isTyping(e.target)) return;
        if (e.ctrlKey || e.altKey || e.metaKey) return;

        switch (e.key) {
            case 'r': window.location.href = replyHref;   break;
            case 'f': window.location.href = forwardHref; break;
            case 'u':
            case 'Escape': window.location.href = backHref; break;
            case 'Delete':
            case 'Backspace':
                e.preventDefault();
                if (confirm('Move this message to Trash?')) {
                    document.getElementById('delete-form').submit();
                }
                break;
        }
    });
})();
</script>
@endpush
