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

    $isJunk = (function() use ($message, $folders) {
        foreach ($folders as $f) {
            if ($f['name'] !== $message['folder']) continue;
            $attrs = $f['attributes'] ?? [];
            $name  = strtoupper($f['name']);
            return in_array('\\junk', $attrs, true) || in_array('\\spam', $attrs, true)
                || str_contains($name, 'JUNK') || str_contains($name, 'SPAM');
        }
        return str_contains(strtoupper($message['folder']), 'JUNK')
            || str_contains(strtoupper($message['folder']), 'SPAM');
    })();

    $spamUrl    = route('message.spam',     ['folder' => rawurlencode($message['folder']), 'uid' => $message['uid']]);
    $notSpamUrl = route('message.notspam',  ['folder' => rawurlencode($message['folder']), 'uid' => $message['uid']]);
    $printUrl   = route('message.print',    ['folder' => rawurlencode($message['folder']), 'uid' => $message['uid']]);
    $receiptUrl = route('message.receipt',  ['folder' => rawurlencode($message['folder']), 'uid' => $message['uid']]);

    // Build reply-quoted body as plain text for the inline reply form
    $replyQuote = '';
    if ($message['body_text'] !== '') {
        $replyQuote = $message['body_text'];
    } elseif ($message['body_html'] !== '') {
        $replyQuote = strip_tags($message['body_html']);
    }
    $fromEmail  = $message['from']['email'];
    $fromName   = $message['from']['name'] ?: $fromEmail;
    $replyTo    = $fromName ? "{$fromName} <{$fromEmail}>" : $fromEmail;
    $replySubj  = preg_match('/^re:/i', $message['subject']) ? $message['subject'] : 'Re: ' . $message['subject'];
@endphp
@php
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

        <div class="flex items-center gap-1 ml-auto" x-data="{ replyOpen: false }">
            {{-- Reply (inline) --}}
            <button @click="replyOpen = !replyOpen; $nextTick(() => replyOpen && document.getElementById('inline-reply-editor')?.focus())"
                    :class="replyOpen ? 'bg-orange-50 dark:bg-orange-900/20 text-orange-600 dark:text-orange-400' : 'text-gray-600 dark:text-gray-400 hover:bg-gray-100 dark:hover:bg-gray-800'"
                    class="flex items-center gap-1.5 px-3 py-1.5 text-sm rounded-lg transition-colors"
                    title="Reply (r)">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                          d="M3 10h10a8 8 0 018 8v2M3 10l6 6m-6-6l6-6"/>
                </svg>
                Reply
            </button>

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

            {{-- Mark as unread --}}
            <button id="mark-unread-btn"
                    type="button"
                    title="Mark as unread (u)"
                    class="flex items-center gap-1.5 px-3 py-1.5 text-sm text-gray-600 dark:text-gray-400 hover:bg-gray-100 dark:hover:bg-gray-800 rounded-lg transition-colors">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                          d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
                </svg>
                Mark as unread
            </button>

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

            {{-- Spam / Not Spam --}}
            @if($isJunk)
            <button id="not-spam-btn"
                    type="button"
                    title="Not spam — move to Inbox"
                    class="flex items-center gap-1.5 px-3 py-1.5 text-sm text-gray-600 dark:text-gray-400 hover:bg-green-50 dark:hover:bg-green-900/20 hover:text-green-700 dark:hover:text-green-400 rounded-lg transition-colors">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
                Not spam
            </button>
            @else
            <button id="spam-btn"
                    type="button"
                    title="Mark as spam"
                    class="flex items-center gap-1.5 px-3 py-1.5 text-sm text-gray-600 dark:text-gray-400 hover:bg-red-50 dark:hover:bg-red-900/20 hover:text-red-600 dark:hover:text-red-400 rounded-lg transition-colors">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18.364 18.364A9 9 0 005.636 5.636m12.728 12.728A9 9 0 015.636 5.636m12.728 12.728L5.636 5.636"/>
                </svg>
                Spam
            </button>
            @endif

            {{-- Print --}}
            <a href="{{ $printUrl }}" target="_blank"
               class="flex items-center gap-1.5 px-3 py-1.5 text-sm text-gray-600 dark:text-gray-400 hover:bg-gray-100 dark:hover:bg-gray-800 rounded-lg transition-colors"
               title="Print">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                          d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/>
                </svg>
                Print
            </a>

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

                @if(!empty($message['attachments']))
                    <div class="flex flex-wrap gap-2 mt-2">
                        @foreach($message['attachments'] as $att)
                            @php
                                $dlUrl = route('attachment.download', [
                                    'folder' => rawurlencode($message['folder']),
                                    'uid'    => $message['uid'],
                                    'part'   => $att['index'],
                                ]);
                                $sz = $att['size'];
                                $sizeStr = $sz < 1024
                                    ? $sz . ' B'
                                    : ($sz < 1048576
                                        ? round($sz / 1024, 1) . ' KB'
                                        : round($sz / 1048576, 1) . ' MB');
                            @endphp
                            <a href="{{ $dlUrl }}"
                               class="flex items-center gap-1.5 px-3 py-1.5 bg-gray-100 dark:bg-gray-800 hover:bg-gray-200 dark:hover:bg-gray-700 rounded-lg text-sm transition-colors max-w-xs">
                                <svg class="w-3.5 h-3.5 text-gray-500 dark:text-gray-400 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                          d="M15.172 7l-6.586 6.586a2 2 0 102.828 2.828l6.414-6.586a4 4 0 00-5.656-5.656l-6.415 6.585a6 6 0 108.486 8.486L20.5 13"/>
                                </svg>
                                <span class="truncate text-gray-700 dark:text-gray-300">{{ $att['name'] }}</span>
                                <span class="flex-shrink-0 text-xs text-gray-400 dark:text-gray-500">{{ $sizeStr }}</span>
                                <svg class="w-3.5 h-3.5 text-gray-400 dark:text-gray-500 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                          d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/>
                                </svg>
                            </a>
                        @endforeach
                    </div>
                @endif
            </div>
        </div>
    </div>

    {{-- Read receipt banner --}}
    @if($receiptTo !== '' && $receiptSetting !== 'never')
    <div id="receipt-banner"
         x-data="{ visible: true, sent: false, sending: false }"
         x-show="visible"
         x-cloak
         class="flex items-center gap-3 px-6 py-2.5 bg-blue-50 dark:bg-blue-900/20 border-b border-blue-100 dark:border-blue-800 text-sm">
        <svg class="w-4 h-4 text-blue-500 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                  d="M3 19v-8.93a2 2 0 01.89-1.664l7-4.666a2 2 0 012.22 0l7 4.666A2 2 0 0121 10.07V19M3 19a2 2 0 002 2h14a2 2 0 002-2M3 19l6.75-4.5M21 19l-6.75-4.5M3 10l6.75 4.5M21 10l-6.75 4.5m0 0l-1.14.76a2 2 0 01-2.22 0L9 14.5"/>
        </svg>
        <span class="flex-1 text-blue-700 dark:text-blue-300">
            The sender requested a read receipt.
        </span>
        <span x-show="sent" x-cloak class="text-blue-600 dark:text-blue-400 font-medium">Receipt sent ✓</span>
        @if($receiptSetting === 'ask')
        <button x-show="!sent" @click="
                sending = true;
                fetch({{ Js::from($receiptUrl) }}, {
                    method: 'POST',
                    headers: { 'X-CSRF-TOKEN': {{ Js::from(csrf_token()) }}, 'Accept': 'application/json' }
                }).then(r => { sending = false; if (r.ok) sent = true; });
            "
            :disabled="sending"
            class="flex-shrink-0 px-3 py-1 text-xs font-medium bg-blue-500 hover:bg-blue-600 disabled:opacity-60 text-white rounded-lg transition-colors">
            <span x-text="sending ? 'Sending…' : 'Send receipt'"></span>
        </button>
        <button x-show="!sent" @click="visible = false"
                class="flex-shrink-0 text-xs text-blue-400 hover:text-blue-600 dark:hover:text-blue-300 transition-colors ml-1">
            Dismiss
        </button>
        @endif
        {{-- auto-send when setting is 'always' --}}
        @if($receiptSetting === 'always')
        <script>
        (function() {
            fetch({{ Js::from($receiptUrl) }}, {
                method: 'POST',
                headers: { 'X-CSRF-TOKEN': {{ Js::from(csrf_token()) }}, 'Accept': 'application/json' }
            });
        })();
        </script>
        @endif
    </div>
    @endif

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

    {{-- Inline Reply Form --}}
    <div x-show="replyOpen" x-cloak
         x-transition:enter="transition ease-out duration-150"
         x-transition:enter-start="opacity-0 translate-y-2"
         x-transition:enter-end="opacity-100 translate-y-0"
         class="border-t border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-900"
         x-data="{
             replySending: false,
             replyStatus: '',
             csrfToken: {{ Js::from(csrf_token()) }},
             sendUrl: {{ Js::from(route('compose.send')) }},
             async send() {
                 this.replySending = true;
                 this.replyStatus = '';
                 const body = document.getElementById('inline-reply-editor');
                 const fd = new FormData();
                 fd.append('to',      {{ Js::from($replyTo) }});
                 fd.append('subject', {{ Js::from($replySubj) }});
                 fd.append('body',    body ? body.innerHTML : '');
                 try {
                     const r = await fetch(this.sendUrl, {
                         method: 'POST',
                         headers: { 'X-CSRF-TOKEN': this.csrfToken, 'Accept': 'application/json' },
                         body: fd,
                     });
                     if (r.ok) {
                         this.replyStatus = 'sent';
                         this.$dispatch('reply-sent');
                         setTimeout(() => { replyOpen = false; }, 2000);
                     } else {
                         const j = await r.json().catch(() => ({}));
                         this.replyStatus = j.message || 'Could not send reply.';
                     }
                 } catch { this.replyStatus = 'Could not send reply.'; }
                 this.replySending = false;
             }
         }">

        {{-- Reply header --}}
        <div class="flex items-center justify-between px-6 py-3 border-b border-gray-100 dark:border-gray-800">
            <div class="text-sm text-gray-600 dark:text-gray-400">
                <span class="font-medium">Reply to:</span>
                <span class="ml-1 text-gray-800 dark:text-gray-200">{{ $replyTo }}</span>
                <span class="ml-2 text-gray-400">·</span>
                <span class="ml-2">{{ $replySubj }}</span>
            </div>
            <a href="{{ $replyHref }}"
               class="text-xs text-gray-400 hover:text-orange-500 dark:hover:text-orange-400 transition-colors">
                Open full editor
            </a>
        </div>

        {{-- Trix editor for inline reply --}}
        <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/trix@2/dist/trix.css">
        <script src="https://cdn.jsdelivr.net/npm/trix@2/dist/trix.umd.min.js"></script>
        <style>
            #inline-reply-editor trix-editor { min-height: 140px; border: none !important; font-size: 0.9rem; padding: 0.75rem 1.5rem; }
            #inline-reply-editor trix-toolbar .trix-button-group--file-tools { display: none; }
        </style>
        <div id="inline-reply-editor" class="min-h-[160px]">
            <input type="hidden" id="inline-body" value="">
            <trix-editor input="inline-body" placeholder="Write your reply…"></trix-editor>
        </div>

        {{-- Footer --}}
        <div class="flex items-center gap-3 px-6 py-3 border-t border-gray-100 dark:border-gray-800">
            <button @click="send()"
                    :disabled="replySending"
                    class="flex items-center gap-2 px-4 py-2 bg-orange-500 hover:bg-orange-600 disabled:opacity-60 text-white text-sm font-medium rounded-lg transition-colors">
                <svg x-show="!replySending" class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8"/>
                </svg>
                <svg x-show="replySending" class="w-4 h-4 animate-spin" fill="none" viewBox="0 0 24 24">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8H4z"></path>
                </svg>
                <span x-text="replySending ? 'Sending…' : 'Send Reply'"></span>
            </button>
            <button @click="replyOpen = false"
                    class="px-4 py-2 text-sm text-gray-500 hover:bg-gray-100 dark:hover:bg-gray-800 rounded-lg transition-colors">
                Cancel
            </button>
            <span x-show="replyStatus === 'sent'" x-cloak class="text-sm text-green-600 dark:text-green-400">
                Reply sent!
            </span>
            <span x-show="replyStatus !== '' && replyStatus !== 'sent'" x-cloak class="text-sm text-red-500" x-text="replyStatus"></span>
        </div>
    </div>

</div>
@endsection

@push('scripts')
<script>
(function () {
    var replyHref   = {{ Js::from($replyHref) }};
    var forwardHref = {{ Js::from($forwardHref) }};
    var backHref    = {{ Js::from($backHref) }};
    var printUrl    = {{ Js::from($printUrl) }};
    var flagUrl     = {{ Js::from(route('message.flag',    ['folder' => rawurlencode($message['folder']), 'uid' => $message['uid']])) }};
    var spamUrl     = {{ Js::from(route('message.spam',    ['folder' => rawurlencode($message['folder']), 'uid' => $message['uid']])) }};
    var notSpamUrl  = {{ Js::from(route('message.notspam', ['folder' => rawurlencode($message['folder']), 'uid' => $message['uid']])) }};
    var csrfToken   = {{ Js::from(csrf_token()) }};

    function markUnread() {
        fetch(flagUrl, {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': csrfToken,
                'Content-Type': 'application/json',
                'Accept': 'application/json',
            },
            body: JSON.stringify({ flag: '\\Seen', add: false }),
        }).then(function (r) {
            if (r.ok) { window.location.href = backHref; }
        });
    }

    document.getElementById('mark-unread-btn').addEventListener('click', markUnread);

    var spamBtn = document.getElementById('spam-btn');
    if (spamBtn) {
        spamBtn.addEventListener('click', function () {
            fetch(spamUrl, {
                method: 'POST',
                headers: { 'X-CSRF-TOKEN': csrfToken, 'Accept': 'application/json' },
            }).then(function (r) {
                if (r.ok) { window.location.href = backHref; }
            });
        });
    }

    var notSpamBtn = document.getElementById('not-spam-btn');
    if (notSpamBtn) {
        notSpamBtn.addEventListener('click', function () {
            fetch(notSpamUrl, {
                method: 'POST',
                headers: { 'X-CSRF-TOKEN': csrfToken, 'Accept': 'application/json' },
            }).then(function (r) {
                if (r.ok) { window.location.href = {{ Js::from(route('inbox')) }}; }
            });
        });
    }

    function isTyping(el) {
        var tag = el.tagName;
        return tag === 'INPUT' || tag === 'TEXTAREA' || tag === 'SELECT' || el.isContentEditable;
    }

    document.addEventListener('keydown', function (e) {
        if (isTyping(e.target)) return;
        if (e.ctrlKey || e.altKey || e.metaKey) return;

        switch (e.key) {
            case 'r':
                // Toggle inline reply (Alpine toggles replyOpen)
                var btn = document.querySelector('[\\@click*="replyOpen"]');
                if (btn) btn.click();
                break;
            case 'f': window.location.href = forwardHref; break;
            case 'p': window.open(printUrl, '_blank'); break;
            case 'u': markUnread(); break;
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
