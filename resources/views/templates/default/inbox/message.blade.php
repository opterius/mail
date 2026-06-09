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

    $replyQuote = $message['body_text'] !== '' ? $message['body_text'] : strip_tags($message['body_html']);
    $fromEmail  = $message['from']['email'];
    $fromName   = $message['from']['name'] ?: $fromEmail;
    $replyTo    = $fromName ? "{$fromName} <{$fromEmail}>" : $fromEmail;
    $replySubj  = preg_match('/^re:/i', $message['subject']) ? $message['subject'] : 'Re: ' . $message['subject'];
    $initial    = mb_strtoupper(mb_substr($fromName, 0, 1, 'UTF-8'), 'UTF-8');

    $folderLabel = static function (array $f): string {
        $attrs = $f['attributes'] ?? [];
        $upper = strtoupper($f['name']);
        if ($upper === 'INBOX')                          return 'Inbox';
        if (in_array('\\sent',   $attrs, true))          return 'Sent';
        if (in_array('\\drafts', $attrs, true))          return 'Drafts';
        if (in_array('\\trash',  $attrs, true))          return 'Trash';
        if (in_array('\\junk',   $attrs, true))          return 'Spam';
        if (str_contains($upper, 'SENT'))                return 'Sent';
        if (str_contains($upper, 'DRAFT'))               return 'Drafts';
        if (str_contains($upper, 'TRASH') || str_contains($upper, 'DELETED')) return 'Trash';
        if (str_contains($upper, 'JUNK')  || str_contains($upper, 'SPAM'))    return 'Spam';
        return $f['name'];
    };

    $moveFolders = array_values(array_filter($folders, fn($f) => $f['name'] !== $message['folder']));
    $moveUrl = route('message.move', ['folder' => rawurlencode($message['folder']), 'uid' => $message['uid']]);

    // Unsubscribe link extraction
    $unsubscribeUrl = '';
    $unsubMail = '';
    $listUnsub = $message['list_unsubscribe'] ?? '';
    if ($listUnsub) {
        if (preg_match('/<(https?:[^>]+)>/', $listUnsub, $m)) $unsubscribeUrl = $m[1];
        if (preg_match('/<mailto:([^>]+)>/', $listUnsub, $m)) $unsubMail = $m[1];
    }
@endphp

<div class="flex flex-col h-full"
     x-data="{
        replyOpen: false,
        snoozeOpen: false,
        noteOpen: {{ $note ? 'true' : 'false' }},
        noteText: {{ Js::from($note?->note ?? '') }},
        noteSaving: false,
        isSnoozed: {{ $isSnoozed ? 'true' : 'false' }},
        isSetAside: {{ $isSetAside ? 'true' : 'false' }},
        isReplyLater: {{ $isReplyLater ? 'true' : 'false' }},
        csrf: {{ Js::from(csrf_token()) }},
        uid: {{ $message['uid'] }},
        mailbox: {{ Js::from($message['folder']) }},
        subject: {{ Js::from($message['subject']) }},
        fromEmail: {{ Js::from($fromEmail) }},
        fromName: {{ Js::from($fromName) }},

        async postAction(url, extra = {}) {
            const fd = new FormData();
            fd.append('_token', this.csrf);
            fd.append('imap_uid', this.uid);
            fd.append('mailbox', this.mailbox);
            fd.append('subject', this.subject);
            fd.append('from_email', this.fromEmail);
            fd.append('from_name', this.fromName);
            Object.entries(extra).forEach(([k,v]) => fd.append(k, v));
            return fetch(url, { method: 'POST', headers: { 'Accept': 'application/json' }, body: fd });
        },
        async deleteAction(url) {
            const fd = new FormData();
            fd.append('_token', this.csrf);
            fd.append('_method', 'DELETE');
            fd.append('imap_uid', this.uid);
            fd.append('mailbox', this.mailbox);
            return fetch(url, { method: 'POST', headers: { 'Accept': 'application/json' }, body: fd });
        },

        async toggleSetAside() {
            if (this.isSetAside) {
                await this.deleteAction({{ Js::from(route('set-aside.destroy')) }});
                this.isSetAside = false;
            } else {
                await this.postAction({{ Js::from(route('set-aside.store')) }});
                this.isSetAside = true;
            }
        },
        async toggleReplyLater() {
            if (this.isReplyLater) {
                await this.deleteAction({{ Js::from(route('reply-later.destroy')) }});
                this.isReplyLater = false;
            } else {
                await this.postAction({{ Js::from(route('reply-later.store')) }});
                this.isReplyLater = true;
            }
        },
        async saveNote() {
            this.noteSaving = true;
            if (this.noteText.trim()) {
                await this.postAction({{ Js::from(route('note.store')) }}, { note: this.noteText });
            } else {
                await this.deleteAction({{ Js::from(route('note.destroy')) }});
            }
            this.noteSaving = false;
        },
        async snoozeFor(snoozeUntil) {
            await this.postAction({{ Js::from(route('snooze.store')) }}, { snooze_until: snoozeUntil });
            this.isSnoozed = true;
            this.snoozeOpen = false;
        },
        async unsnooze() {
            await this.deleteAction({{ Js::from(route('snooze.destroy')) }});
            this.isSnoozed = false;
        },
     }">

    {{-- ================================================================ --}}
    {{-- Toolbar                                                            --}}
    {{-- ================================================================ --}}
    <div class="flex items-center gap-1 px-4 py-2.5 border-b border-gray-100 dark:border-gray-700 bg-white dark:bg-gray-900 sticky top-0 z-10 flex-wrap">
        <a href="{{ $backHref }}"
           class="flex items-center gap-1.5 px-3 py-1.5 text-sm text-gray-600 dark:text-gray-400 hover:bg-gray-100 dark:hover:bg-gray-800 rounded-lg transition-colors mr-1">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
            </svg>
            Back
        </a>

        {{-- Reply --}}
        <button @click="replyOpen = !replyOpen; $nextTick(() => replyOpen && document.getElementById('inline-reply-editor')?.focus())"
                :class="replyOpen ? 'bg-orange-50 dark:bg-orange-900/20 text-orange-600' : 'text-gray-600 dark:text-gray-400 hover:bg-gray-100 dark:hover:bg-gray-800'"
                class="flex items-center gap-1.5 px-3 py-1.5 text-sm rounded-lg transition-colors" title="Reply (r)">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h10a8 8 0 018 8v2M3 10l6 6m-6-6l6-6"/>
            </svg>
            Reply
        </button>

        {{-- Forward --}}
        <a href="{{ $forwardHref }}"
           class="flex items-center gap-1.5 px-3 py-1.5 text-sm text-gray-600 dark:text-gray-400 hover:bg-gray-100 dark:hover:bg-gray-800 rounded-lg transition-colors" title="Forward (f)">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 10H11a8 8 0 00-8 8v2m18-10l-6-6m6 6l-6 6"/>
            </svg>
            Forward
        </a>

        <div class="w-px h-5 bg-gray-200 dark:bg-gray-700 mx-0.5"></div>

        {{-- Snooze --}}
        <div class="relative" x-data="{}">
            <button @click="snoozeOpen = !snoozeOpen; isSnoozed && unsnooze()"
                    :class="isSnoozed ? 'text-amber-600 bg-amber-50 dark:bg-amber-900/20' : 'text-gray-600 dark:text-gray-400 hover:bg-gray-100 dark:hover:bg-gray-800'"
                    class="flex items-center gap-1.5 px-3 py-1.5 text-sm rounded-lg transition-colors"
                    :title="isSnoozed ? 'Snoozed — click to unsnooze' : 'Snooze'">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
                <span x-text="isSnoozed ? 'Snoozed' : 'Snooze'"></span>
            </button>
            <div x-show="snoozeOpen && !isSnoozed" @click.outside="snoozeOpen = false" x-cloak
                 class="absolute left-0 mt-1 w-52 bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-xl shadow-lg z-50 py-1"
                 style="display:none; top:100%">
                @php
                    $snoozeOptions = [
                        ['label' => 'Later today (3 hours)',  'fn' => "snoozeFor(new Date(Date.now()+3*3600000).toISOString())"],
                        ['label' => 'Tomorrow morning',       'fn' => "snoozeFor((() => { let d = new Date(); d.setDate(d.getDate()+1); d.setHours(8,0,0,0); return d.toISOString(); })())"],
                        ['label' => 'This weekend',           'fn' => "snoozeFor((() => { let d = new Date(); let diff = 6-d.getDay(); if(diff<=0) diff+=7; d.setDate(d.getDate()+diff); d.setHours(9,0,0,0); return d.toISOString(); })())"],
                        ['label' => 'Next week',              'fn' => "snoozeFor((() => { let d = new Date(); d.setDate(d.getDate()+(8-d.getDay())); d.setHours(8,0,0,0); return d.toISOString(); })())"],
                    ];
                @endphp
                @foreach($snoozeOptions as $opt)
                <button @click="{{ $opt['fn'] }}"
                        class="w-full text-left px-4 py-2 text-sm text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700 transition-colors">
                    {{ $opt['label'] }}
                </button>
                @endforeach
                <div class="border-t border-gray-100 dark:border-gray-700 mt-1 pt-1 px-3 pb-2">
                    <label class="text-[13px] text-gray-400 block mb-1">Custom date & time</label>
                    <input type="datetime-local" id="snooze-custom"
                           class="w-full text-sm border border-gray-200 dark:border-gray-600 rounded-lg px-2 py-1.5 bg-white dark:bg-gray-700 text-gray-800 dark:text-gray-200 focus:outline-none focus:ring-2 focus:ring-orange-400">
                    <button @click="snoozeFor(document.getElementById('snooze-custom').value)"
                            class="mt-1.5 w-full py-1.5 text-sm bg-orange-500 hover:bg-orange-600 text-white rounded-lg transition-colors">
                        Set snooze
                    </button>
                </div>
            </div>
        </div>

        {{-- Set Aside --}}
        <button @click="toggleSetAside()"
                :class="isSetAside ? 'text-purple-600 bg-purple-50 dark:bg-purple-900/20' : 'text-gray-600 dark:text-gray-400 hover:bg-gray-100 dark:hover:bg-gray-800'"
                class="flex items-center gap-1.5 px-3 py-1.5 text-sm rounded-lg transition-colors"
                :title="isSetAside ? 'Remove from Set Aside' : 'Set Aside'">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 5a2 2 0 012-2h10a2 2 0 012 2v16l-7-3.5L5 21V5z"/>
            </svg>
            <span x-text="isSetAside ? 'Aside ✓' : 'Set Aside'"></span>
        </button>

        {{-- Reply Later --}}
        <button @click="toggleReplyLater()"
                :class="isReplyLater ? 'text-blue-600 bg-blue-50 dark:bg-blue-900/20' : 'text-gray-600 dark:text-gray-400 hover:bg-gray-100 dark:hover:bg-gray-800'"
                class="flex items-center gap-1.5 px-3 py-1.5 text-sm rounded-lg transition-colors"
                :title="isReplyLater ? 'Remove from Reply Later' : 'Reply Later'">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 10h.01M12 10h.01M16 10h.01M9 16H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-5l-5 5v-5z"/>
            </svg>
            <span x-text="isReplyLater ? 'Reply Later ✓' : 'Reply Later'"></span>
        </button>

        {{-- Note --}}
        <button @click="noteOpen = !noteOpen"
                :class="noteOpen || noteText ? 'text-yellow-600 bg-yellow-50 dark:bg-yellow-900/20' : 'text-gray-600 dark:text-gray-400 hover:bg-gray-100 dark:hover:bg-gray-800'"
                class="flex items-center gap-1.5 px-3 py-1.5 text-sm rounded-lg transition-colors" title="Add note">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
            </svg>
            Note
        </button>

        <div class="w-px h-5 bg-gray-200 dark:bg-gray-700 mx-0.5"></div>

        {{-- Mark unread --}}
        <button id="mark-unread-btn" type="button"
                class="flex items-center gap-1.5 px-3 py-1.5 text-sm text-gray-600 dark:text-gray-400 hover:bg-gray-100 dark:hover:bg-gray-800 rounded-lg transition-colors" title="Mark as unread (u)">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
            </svg>
            Unread
        </button>

        {{-- Move to folder --}}
        @if(count($moveFolders) > 0)
        <div x-data="{ open: false }" class="relative">
            <button @click="open = !open" @keydown.escape.window="open = false" type="button"
                    class="flex items-center gap-1.5 px-3 py-1.5 text-sm text-gray-600 dark:text-gray-400 hover:bg-gray-100 dark:hover:bg-gray-800 rounded-lg transition-colors">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 7a2 2 0 012-2h4l2 2h8a2 2 0 012 2v8a2 2 0 01-2 2H5a2 2 0 01-2-2V7z"/>
                </svg>
                Move
                <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
            </button>
            <div x-show="open" @click.outside="open = false" x-cloak
                 class="absolute right-0 mt-1 w-52 bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-xl shadow-lg z-50 py-1 max-h-72 overflow-y-auto"
                 style="display:none; top:100%">
                @foreach($moveFolders as $f)
                <form method="POST" action="{{ $moveUrl }}">
                    @csrf
                    <input type="hidden" name="target" value="{{ $f['name'] }}">
                    <button type="submit" class="w-full text-left px-4 py-2 text-sm text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700 transition-colors truncate">
                        {{ $folderLabel($f) }}
                    </button>
                </form>
                @endforeach
            </div>
        </div>
        @endif

        {{-- Spam / Not spam --}}
        @if($isJunk)
        <button id="not-spam-btn" type="button"
                class="flex items-center gap-1.5 px-3 py-1.5 text-sm text-gray-600 dark:text-gray-400 hover:bg-green-50 dark:hover:bg-green-900/20 hover:text-green-700 rounded-lg transition-colors">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
            </svg>
            Not spam
        </button>
        @else
        <button id="spam-btn" type="button"
                class="flex items-center gap-1.5 px-3 py-1.5 text-sm text-gray-600 dark:text-gray-400 hover:bg-red-50 dark:hover:bg-red-900/20 hover:text-red-600 rounded-lg transition-colors">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18.364 18.364A9 9 0 005.636 5.636m12.728 12.728A9 9 0 015.636 5.636m12.728 12.728L5.636 5.636"/>
            </svg>
            Spam
        </button>
        @endif

        {{-- Print --}}
        <a href="{{ $printUrl }}" target="_blank"
           class="flex items-center gap-1.5 px-3 py-1.5 text-sm text-gray-600 dark:text-gray-400 hover:bg-gray-100 dark:hover:bg-gray-800 rounded-lg transition-colors">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/>
            </svg>
        </a>

        {{-- Delete --}}
        <form id="delete-form" method="POST"
              action="{{ route('message.destroy', ['folder' => rawurlencode($message['folder']), 'uid' => $message['uid']]) }}">
            @csrf @method('DELETE')
            <button type="submit"
                    class="flex items-center gap-1.5 px-3 py-1.5 text-sm text-gray-600 dark:text-gray-400 hover:bg-red-50 dark:hover:bg-red-900/20 hover:text-red-600 rounded-lg transition-colors">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                </svg>
                Delete
            </button>
        </form>
    </div>

    {{-- ================================================================ --}}
    {{-- Screener banner — unknown sender                                   --}}
    {{-- ================================================================ --}}
    @if(!$isKnownSender && $fromEmail)
    <div class="flex items-center gap-3 px-6 py-2.5 bg-orange-50 dark:bg-orange-900/20 border-b border-orange-100 dark:border-orange-800 text-sm" id="screener-banner">
        <svg class="w-4 h-4 text-orange-500 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8.228 9c.549-1.165 2.03-2 3.772-2 2.21 0 4 1.343 4 3 0 1.4-1.278 2.575-3.006 2.907-.542.104-.994.54-.994 1.093m0 3h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
        </svg>
        <span class="flex-1 text-orange-800 dark:text-orange-200">
            <span class="font-medium">{{ $fromEmail }}</span> is a new sender you haven't approved yet.
        </span>
        <form method="POST" action="{{ route('screener.approve') }}" class="inline">
            @csrf <input type="hidden" name="sender_email" value="{{ $fromEmail }}">
            <button type="submit" class="px-3 py-1 text-[13px] bg-green-500 hover:bg-green-600 text-white rounded-lg transition-colors font-medium">Approve</button>
        </form>
        <form method="POST" action="{{ route('screener.block') }}" class="inline">
            @csrf <input type="hidden" name="sender_email" value="{{ $fromEmail }}">
            <button type="submit" class="px-3 py-1 text-[13px] bg-red-500 hover:bg-red-600 text-white rounded-lg transition-colors font-medium">Block</button>
        </form>
    </div>
    @endif

    {{-- ================================================================ --}}
    {{-- Spy Pixel / Unsubscribe banners                                    --}}
    {{-- ================================================================ --}}
    <div id="spy-banner" class="hidden flex items-center gap-3 px-6 py-2 bg-gray-50 dark:bg-gray-800/50 border-b border-gray-100 dark:border-gray-700 text-sm">
        <svg class="w-4 h-4 text-gray-400 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l3.59 3.59m0 0A9.953 9.953 0 0112 5c4.478 0 8.268 2.943 9.543 7a10.025 10.025 0 01-4.132 5.411m0 0L21 21"/>
        </svg>
        <span class="text-gray-500 dark:text-gray-400"><span id="spy-count" class="font-medium text-gray-700 dark:text-gray-300"></span> tracking pixel(s) blocked.</span>
    </div>

    @if($unsubscribeUrl || $unsubMail)
    <div class="flex items-center gap-3 px-6 py-2 bg-blue-50 dark:bg-blue-900/20 border-b border-blue-100 dark:border-blue-800 text-sm" x-data="{ done: false }">
        <svg class="w-4 h-4 text-blue-400 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18.364 18.364A9 9 0 005.636 5.636m12.728 12.728A9 9 0 015.636 5.636m12.728 12.728L5.636 5.636"/>
        </svg>
        <span class="flex-1 text-blue-700 dark:text-blue-300">This is a newsletter or mailing list.</span>
        <span x-show="done" x-cloak class="text-green-600 dark:text-green-400 text-[13px] font-medium">Unsubscribed ✓</span>
        @if($unsubscribeUrl)
        <a x-show="!done" href="{{ $unsubscribeUrl }}" target="_blank" @click="done = true"
           class="px-3 py-1 text-[13px] bg-blue-500 hover:bg-blue-600 text-white rounded-lg transition-colors font-medium">
            Unsubscribe
        </a>
        @endif
        <button x-show="!done" @click="done = true"
                class="px-3 py-1 text-[13px] text-blue-500 hover:text-blue-700 transition-colors">
            Move to Feed
        </button>
    </div>
    @endif

    {{-- Read receipt banner --}}
    @if($receiptTo !== '' && $receiptSetting !== 'never')
    <div id="receipt-banner" x-data="{ visible: true, sent: false, sending: false }" x-show="visible" x-cloak
         class="flex items-center gap-3 px-6 py-2 bg-indigo-50 dark:bg-indigo-900/20 border-b border-indigo-100 dark:border-indigo-800 text-sm">
        <svg class="w-4 h-4 text-indigo-400 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 19v-8.93a2 2 0 01.89-1.664l7-4.666a2 2 0 012.22 0l7 4.666A2 2 0 0121 10.07V19M3 19a2 2 0 002 2h14a2 2 0 002-2"/>
        </svg>
        <span class="flex-1 text-indigo-700 dark:text-indigo-300">The sender requested a read receipt.</span>
        <span x-show="sent" x-cloak class="text-indigo-600 font-medium">Receipt sent ✓</span>
        @if($receiptSetting === 'ask')
        <button x-show="!sent" @click="sending=true; fetch({{ Js::from($receiptUrl) }},{method:'POST',headers:{'X-CSRF-TOKEN':{{ Js::from(csrf_token()) }},'Accept':'application/json'}}).then(r=>{sending=false;if(r.ok)sent=true;})"
                :disabled="sending"
                class="px-3 py-1 text-[13px] bg-indigo-500 hover:bg-indigo-600 disabled:opacity-60 text-white rounded-lg transition-colors font-medium">
            <span x-text="sending ? 'Sending…' : 'Send receipt'"></span>
        </button>
        <button x-show="!sent" @click="visible=false" class="text-[13px] text-indigo-400 hover:text-indigo-600 transition-colors ml-1">Dismiss</button>
        @endif
        @if($receiptSetting === 'always')
        <script>fetch({{ Js::from($receiptUrl) }},{method:'POST',headers:{'X-CSRF-TOKEN':{{ Js::from(csrf_token()) }},'Accept':'application/json'}});</script>
        @endif
    </div>
    @endif

    {{-- ================================================================ --}}
    {{-- Note panel                                                         --}}
    {{-- ================================================================ --}}
    <div x-show="noteOpen" x-cloak
         x-transition:enter="transition ease-out duration-150"
         x-transition:enter-start="opacity-0 -translate-y-1"
         x-transition:enter-end="opacity-100 translate-y-0"
         class="px-6 py-3 bg-yellow-50 dark:bg-yellow-900/20 border-b border-yellow-200 dark:border-yellow-800">
        <label class="block text-[13px] font-medium text-yellow-700 dark:text-yellow-300 mb-1.5">Private note (only visible to you)</label>
        <textarea x-model="noteText" rows="2"
                  class="w-full text-sm border border-yellow-200 dark:border-yellow-700 rounded-lg px-3 py-2 bg-white dark:bg-gray-800 text-gray-800 dark:text-gray-200 resize-none focus:outline-none focus:ring-2 focus:ring-yellow-400"
                  placeholder="Add a private note about this email…"></textarea>
        <div class="flex items-center gap-3 mt-2">
            <button @click="saveNote()" :disabled="noteSaving"
                    class="px-3 py-1.5 text-[13px] bg-yellow-500 hover:bg-yellow-600 disabled:opacity-60 text-white rounded-lg transition-colors font-medium">
                <span x-text="noteSaving ? 'Saving…' : 'Save note'"></span>
            </button>
            <button @click="noteOpen = false" class="text-[13px] text-yellow-600 hover:text-yellow-800 transition-colors">Close</button>
            <button x-show="noteText" @click="noteText=''; saveNote()" class="text-[13px] text-red-400 hover:text-red-600 transition-colors ml-auto">Delete note</button>
        </div>
    </div>

    {{-- ================================================================ --}}
    {{-- Message header                                                     --}}
    {{-- ================================================================ --}}
    <div class="px-6 py-5 border-b border-gray-100 dark:border-gray-700">
        <h2 class="text-lg font-semibold text-gray-900 dark:text-gray-100 mb-4">{{ $message['subject'] }}</h2>

        <div class="flex items-start gap-3">
            <div class="flex-shrink-0 w-10 h-10 rounded-full bg-orange-100 dark:bg-orange-900/30 text-orange-600 dark:text-orange-400
                        flex items-center justify-center text-sm font-semibold select-none">
                {{ $initial }}
            </div>
            <div class="flex-1 min-w-0">
                <div class="flex items-baseline justify-between gap-4">
                    <div class="min-w-0">
                        <span class="font-medium text-base text-gray-900 dark:text-gray-100">{{ $fromName }}</span>
                        @if($fromName !== $fromEmail && $fromEmail !== '')
                            <span class="text-sm text-gray-400 dark:text-gray-500 ml-1">&lt;{{ $fromEmail }}&gt;</span>
                        @endif
                    </div>
                    <span class="flex-shrink-0 text-sm text-gray-400 dark:text-gray-500">{{ $message['date_formatted'] }}</span>
                </div>
                <div class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                    <span class="text-gray-400 dark:text-gray-500">To:</span>
                    {{ implode(', ', array_map(fn($a) => $a['name'] ?: $a['email'], $message['to'])) ?: '—' }}
                </div>
                @if(!empty($message['cc']))
                    <div class="text-sm text-gray-500 dark:text-gray-400">
                        <span class="text-gray-400 dark:text-gray-500">Cc:</span>
                        {{ implode(', ', array_map(fn($a) => $a['name'] ?: $a['email'], $message['cc'])) }}
                    </div>
                @endif

                @if(!empty($message['attachments']))
                    <div class="flex flex-wrap gap-2 mt-2">
                        @foreach($message['attachments'] as $att)
                            @php
                                $dlUrl = route('attachment.download', ['folder' => rawurlencode($message['folder']), 'uid' => $message['uid'], 'part' => $att['index']]);
                                $sz = $att['size'];
                                $sizeStr = $sz < 1024 ? $sz . ' B' : ($sz < 1048576 ? round($sz/1024,1).' KB' : round($sz/1048576,1).' MB');
                            @endphp
                            <a href="{{ $dlUrl }}" class="flex items-center gap-1.5 px-3 py-1.5 bg-gray-100 dark:bg-gray-800 hover:bg-gray-200 dark:hover:bg-gray-700 rounded-lg text-sm transition-colors max-w-xs">
                                <svg class="w-3.5 h-3.5 text-gray-500 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.172 7l-6.586 6.586a2 2 0 102.828 2.828l6.414-6.586a4 4 0 00-5.656-5.656l-6.415 6.585a6 6 0 108.486 8.486L20.5 13"/>
                                </svg>
                                <span class="truncate text-gray-700 dark:text-gray-300">{{ $att['name'] }}</span>
                                <span class="flex-shrink-0 text-[13px] text-gray-400">{{ $sizeStr }}</span>
                                <svg class="w-3.5 h-3.5 text-gray-400 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/>
                                </svg>
                            </a>
                        @endforeach
                    </div>
                @endif

                {{-- Status tags --}}
                <div class="flex flex-wrap gap-1.5 mt-2">
                    @if($isSnoozed)
                        <span style="display:inline-flex;align-items:center;padding:2px 8px;border-radius:4px;font-size:13px;background:#fef3c7;color:#92400e;border:1px solid #fde68a;">⏰ Snoozed</span>
                    @endif
                    @if($isSetAside)
                        <span style="display:inline-flex;align-items:center;padding:2px 8px;border-radius:4px;font-size:13px;background:#f5f3ff;color:#5b21b6;border:1px solid #ddd6fe;">📌 Set Aside</span>
                    @endif
                    @if($isReplyLater)
                        <span style="display:inline-flex;align-items:center;padding:2px 8px;border-radius:4px;font-size:13px;background:#eff6ff;color:#1e40af;border:1px solid #bfdbfe;">💬 Reply Later</span>
                    @endif
                    @if($note)
                        <span style="display:inline-flex;align-items:center;padding:2px 8px;border-radius:4px;font-size:13px;background:#fefce8;color:#713f12;border:1px solid #fef08a;">📝 Has note</span>
                    @endif
                </div>
            </div>
        </div>
    </div>

    {{-- ================================================================ --}}
    {{-- Message body                                                       --}}
    {{-- ================================================================ --}}
    <div class="flex-1 overflow-auto">
        @if($message['body_html'] !== '')
            @php
                $html = $message['body_html'];
                $defaultStyle = '<style>body{font-family:ui-sans-serif,system-ui,-apple-system,sans-serif;font-size:16px;line-height:1.7;color:#1f2937;margin:0;padding:20px 28px;}a{color:#f97316;}</style>';
                if (stripos($html, '<head>') !== false) {
                    $html = str_ireplace('<head>', '<head><base target="_blank">'.$defaultStyle, $html);
                } elseif (stripos($html, '<html') !== false) {
                    $html = preg_replace('/(<html[^>]*>)/i', '$1<head><base target="_blank">'.$defaultStyle.'</head>', $html);
                } else {
                    $html = '<base target="_blank">'.$defaultStyle.$html;
                }
            @endphp
            <iframe id="msg-body"
                    srcdoc="{!! htmlspecialchars($html, ENT_QUOTES, 'UTF-8') !!}"
                    sandbox="allow-same-origin allow-popups allow-popups-to-escape-sandbox"
                    class="w-full border-0"
                    style="min-height: 400px"
                    onload="this.style.height = Math.max(400, this.contentDocument.documentElement.scrollHeight) + 'px'; detectSpyPixels(this);">
            </iframe>
        @elseif($message['body_text'] !== '')
            <div class="px-7 py-5">
                <pre class="whitespace-pre-wrap text-base text-gray-800 dark:text-gray-200 font-sans leading-relaxed">{{ $message['body_text'] }}</pre>
            </div>
        @else
            <div class="flex items-center justify-center h-40 text-sm text-gray-400 dark:text-gray-500">No message content.</div>
        @endif
    </div>

    {{-- ================================================================ --}}
    {{-- Inline Reply                                                        --}}
    {{-- ================================================================ --}}
    <div x-show="replyOpen" x-cloak
         x-transition:enter="transition ease-out duration-150"
         x-transition:enter-start="opacity-0 translate-y-2"
         x-transition:enter-end="opacity-100 translate-y-0"
         class="border-t border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-900"
         x-data="{
             replySending: false,
             replyStatus: '',
             async send() {
                 this.replySending = true;
                 this.replyStatus = '';
                 const body = document.getElementById('inline-reply-editor');
                 const fd = new FormData();
                 fd.append('to', {{ Js::from($replyTo) }});
                 fd.append('subject', {{ Js::from($replySubj) }});
                 fd.append('body', body ? body.innerHTML : '');
                 try {
                     const r = await fetch({{ Js::from(route('compose.send')) }}, {
                         method: 'POST',
                         headers: { 'X-CSRF-TOKEN': {{ Js::from(csrf_token()) }}, 'Accept': 'application/json' },
                         body: fd,
                     });
                     if (r.ok) { this.replyStatus = 'sent'; setTimeout(() => { replyOpen = false; }, 2000); }
                     else { const j = await r.json().catch(() => ({})); this.replyStatus = j.message || 'Could not send reply.'; }
                 } catch { this.replyStatus = 'Could not send reply.'; }
                 this.replySending = false;
             }
         }">
        <div class="flex items-center justify-between px-6 py-3 border-b border-gray-100 dark:border-gray-800">
            <div class="text-sm text-gray-600 dark:text-gray-400">
                <span class="font-medium">Reply to:</span>
                <span class="ml-1 text-gray-800 dark:text-gray-200">{{ $replyTo }}</span>
                <span class="ml-2 text-gray-400">·</span>
                <span class="ml-2">{{ $replySubj }}</span>
            </div>
            <a href="{{ $replyHref }}" class="text-[13px] text-gray-400 hover:text-orange-500 transition-colors">Open full editor</a>
        </div>
        <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/trix@2/dist/trix.css">
        <script src="https://cdn.jsdelivr.net/npm/trix@2/dist/trix.umd.min.js"></script>
        <style>#inline-reply-editor trix-editor{min-height:140px;border:none!important;font-size:.9rem;padding:.75rem 1.5rem;}#inline-reply-editor trix-toolbar .trix-button-group--file-tools{display:none;}</style>
        <div id="inline-reply-editor" class="min-h-[160px]">
            <input type="hidden" id="inline-body" value="">
            <trix-editor input="inline-body" placeholder="Write your reply…"></trix-editor>
        </div>
        <div class="flex items-center gap-3 px-6 py-3 border-t border-gray-100 dark:border-gray-800">
            <button @click="send()" :disabled="replySending"
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
            <button @click="replyOpen = false" class="px-4 py-2 text-sm text-gray-500 hover:bg-gray-100 dark:hover:bg-gray-800 rounded-lg transition-colors">Cancel</button>
            <span x-show="replyStatus === 'sent'" x-cloak class="text-sm text-green-600 dark:text-green-400">Reply sent!</span>
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
    var flagUrl     = {{ Js::from(route('message.flag',    ['folder' => rawurlencode($message['folder']), 'uid' => $message['uid']])) }};
    var spamUrl     = {{ Js::from(route('message.spam',    ['folder' => rawurlencode($message['folder']), 'uid' => $message['uid']])) }};
    var notSpamUrl  = {{ Js::from(route('message.notspam', ['folder' => rawurlencode($message['folder']), 'uid' => $message['uid']])) }};
    var csrfToken   = {{ Js::from(csrf_token()) }};

    // Spy pixel detection
    window.detectSpyPixels = function(iframe) {
        try {
            var doc = iframe.contentDocument || iframe.contentWindow.document;
            var imgs = doc.querySelectorAll('img');
            var count = 0;
            imgs.forEach(function(img) {
                var w = img.naturalWidth || img.width || parseInt(img.getAttribute('width') || '99');
                var h = img.naturalHeight || img.height || parseInt(img.getAttribute('height') || '99');
                if (w <= 2 && h <= 2) {
                    img.style.display = 'none';
                    count++;
                }
            });
            if (count > 0) {
                var banner = document.getElementById('spy-banner');
                document.getElementById('spy-count').textContent = count;
                banner.classList.remove('hidden');
                banner.classList.add('flex');
            }
        } catch(e) {}
    };

    // Delete confirm
    document.getElementById('delete-form').addEventListener('submit', function(e) {
        if (!confirm('Move this message to Trash?')) e.preventDefault();
    });

    function markUnread() {
        fetch(flagUrl, {
            method: 'POST',
            headers: { 'X-CSRF-TOKEN': csrfToken, 'Content-Type': 'application/json', 'Accept': 'application/json' },
            body: JSON.stringify({ flag: '\\Seen', add: false }),
        }).then(function(r) { if (r.ok) window.location.href = backHref; });
    }

    document.getElementById('mark-unread-btn').addEventListener('click', markUnread);

    var spamBtn = document.getElementById('spam-btn');
    if (spamBtn) spamBtn.addEventListener('click', function() {
        fetch(spamUrl, { method: 'POST', headers: { 'X-CSRF-TOKEN': csrfToken, 'Accept': 'application/json' }})
            .then(function(r) { if (r.ok) window.location.href = backHref; });
    });

    var notSpamBtn = document.getElementById('not-spam-btn');
    if (notSpamBtn) notSpamBtn.addEventListener('click', function() {
        fetch(notSpamUrl, { method: 'POST', headers: { 'X-CSRF-TOKEN': csrfToken, 'Accept': 'application/json' }})
            .then(function(r) { if (r.ok) window.location.href = {{ Js::from(route('inbox')) }}; });
    });

    function isTyping(el) { var t = el.tagName; return t==='INPUT'||t==='TEXTAREA'||t==='SELECT'||el.isContentEditable; }

    document.addEventListener('keydown', function(e) {
        if (isTyping(e.target) || e.ctrlKey || e.altKey || e.metaKey) return;
        switch (e.key) {
            case 'r': var btn = document.querySelector('[\\@click*="replyOpen"]'); if(btn) btn.click(); break;
            case 'f': window.location.href = forwardHref; break;
            case 'u': markUnread(); break;
            case 'Escape': window.location.href = backHref; break;
            case 'Delete': case 'Backspace':
                e.preventDefault();
                if (confirm('Move this message to Trash?')) document.getElementById('delete-form').submit();
                break;
        }
    });
})();
</script>
@endpush
