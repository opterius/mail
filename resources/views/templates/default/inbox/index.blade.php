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

@php
    $folderLabel = strtoupper($currentFolder) === 'INBOX' ? 'Inbox' : $currentFolder;
    $avatarMap   = $avatarMap ?? [];

    $avatarColors = [
        'A' => '#ef4444','B' => '#f97316','C' => '#eab308','D' => '#22c55e',
        'E' => '#14b8a6','F' => '#3b82f6','G' => '#8b5cf6','H' => '#ec4899',
        'I' => '#f43f5e','J' => '#06b6d4','K' => '#84cc16','L' => '#f97316',
        'M' => '#6366f1','N' => '#10b981','O' => '#f59e0b','P' => '#3b82f6',
        'Q' => '#8b5cf6','R' => '#ef4444','S' => '#14b8a6','T' => '#22c55e',
        'U' => '#ec4899','V' => '#6366f1','W' => '#f43f5e','X' => '#06b6d4',
        'Y' => '#84cc16','Z' => '#f97316',
    ];

    // Folder list for the "Move to" dropdown (exclude current)
    $moveFolders = collect($folders ?? [])
        ->filter(fn($f) => $f['name'] !== $currentFolder)
        ->values();
@endphp

@section('title', $folderLabel)

@section('content')
<div class="flex flex-col h-full"
     x-data="{
        selected: new Set(),
        allUids: {{ json_encode(array_column($messages ?? [], 'uid')) }},
        toggle(uid) {
            if (this.selected.has(uid)) { this.selected.delete(uid); }
            else { this.selected.add(uid); }
            this.selected = new Set(this.selected);
        },
        toggleAll() {
            if (this.selected.size === this.allUids.length) {
                this.selected = new Set();
            } else {
                this.selected = new Set(this.allUids);
            }
        },
        isAllSelected() { return this.allUids.length > 0 && this.selected.size === this.allUids.length; },
        isSomeSelected() { return this.selected.size > 0 && this.selected.size < this.allUids.length; },
        submitBulk(action, target) {
            if (this.selected.size === 0) return;
            var form = document.getElementById('bulk-form');
            form.querySelector('[name=action]').value = action;
            form.querySelector('[name=target]').value = target || '';
            // Rebuild uid inputs
            form.querySelectorAll('[name=\'uids[]\']').forEach(el => el.remove());
            this.selected.forEach(uid => {
                var inp = document.createElement('input');
                inp.type = 'hidden'; inp.name = 'uids[]'; inp.value = uid;
                form.appendChild(inp);
            });
            form.submit();
        }
     }">

    {{-- Hidden bulk form --}}
    <form id="bulk-form" method="POST" action="{{ route('messages.bulk') }}" class="hidden">
        @csrf
        <input type="hidden" name="folder" value="{{ $currentFolder }}">
        <input type="hidden" name="action" value="">
        <input type="hidden" name="target" value="">
        <input type="hidden" name="page"   value="{{ $page ?? 1 }}">
    </form>

    {{-- ---------------------------------------------------------------- --}}
    {{-- Toolbar                                                           --}}
    {{-- ---------------------------------------------------------------- --}}
    <div class="flex items-center justify-between px-5 py-3.5 border-b border-gray-100 dark:border-gray-800 bg-white dark:bg-gray-900 sticky top-0 z-10">
        <div class="flex items-center gap-3">
            {{-- Select-all checkbox --}}
            @if(!empty($messages))
            <label class="flex items-center cursor-pointer">
                <input type="checkbox"
                       :checked="isAllSelected()"
                       :indeterminate="isSomeSelected()"
                       @change="toggleAll()"
                       class="w-4 h-4 rounded border-gray-300 dark:border-gray-600 text-orange-500 focus:ring-orange-400 cursor-pointer">
            </label>
            @endif
            <h1 class="text-base font-bold text-gray-900 dark:text-white">{{ $folderLabel }}</h1>
            @if(($total ?? 0) > 0)
                <span class="text-sm text-gray-400 dark:text-gray-500">
                    {{ number_format($total) }}
                    @if(($totalPages ?? 1) > 1)
                        &middot; page {{ $page }} of {{ $totalPages }}
                    @endif
                </span>
            @endif
        </div>
        <div class="flex items-center gap-2">
            {{-- Inline search --}}
            <form method="GET" action="{{ route('search') }}" class="hidden sm:block">
                <div class="relative">
                    <svg class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-gray-400 pointer-events-none"
                         fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                              d="M21 21l-4.35-4.35M17 11A6 6 0 115 11a6 6 0 0112 0z"/>
                    </svg>
                    <input name="q" type="search" placeholder="Search…" value="{{ request('q') }}"
                           class="pl-9 pr-4 py-2 text-sm bg-gray-100 dark:bg-gray-800 border border-transparent focus:border-orange-400 focus:bg-white dark:focus:bg-gray-700 rounded-xl outline-none transition-colors w-52 text-gray-800 dark:text-gray-200 placeholder-gray-400">
                </div>
            </form>
            <a href="{{ route('compose') }}"
               class="hidden sm:flex items-center gap-1.5 px-3 py-2 text-sm font-semibold text-orange-600 dark:text-orange-400 hover:bg-orange-50 dark:hover:bg-orange-900/20 rounded-xl transition-colors">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 4v16m8-8H4"/>
                </svg>
                Compose
            </a>
        </div>
    </div>

    {{-- ---------------------------------------------------------------- --}}
    {{-- Bulk action bar (visible when any selected)                       --}}
    {{-- ---------------------------------------------------------------- --}}
    <div x-show="selected.size > 0" x-cloak
         class="flex items-center gap-2 px-5 py-2.5 bg-orange-50 dark:bg-orange-900/20 border-b border-orange-100 dark:border-orange-900/40 flex-wrap">

        <span class="text-sm font-semibold text-orange-700 dark:text-orange-300 mr-1"
              x-text="selected.size + ' selected'"></span>

        <button @click="submitBulk('read')"
                class="flex items-center gap-1.5 px-3 py-1.5 text-sm font-medium bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-lg hover:bg-gray-50 dark:hover:bg-gray-700 text-gray-700 dark:text-gray-300 transition-colors">
            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 19V5a2 2 0 012-2h14a2 2 0 012 2v14"/>
            </svg>
            Mark read
        </button>

        <button @click="submitBulk('unread')"
                class="flex items-center gap-1.5 px-3 py-1.5 text-sm font-medium bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-lg hover:bg-gray-50 dark:hover:bg-gray-700 text-gray-700 dark:text-gray-300 transition-colors">
            <svg class="w-3.5 h-3.5" fill="currentColor" viewBox="0 0 20 20">
                <circle cx="10" cy="10" r="4"/>
            </svg>
            Mark unread
        </button>

        <button @click="submitBulk('flag')"
                class="flex items-center gap-1.5 px-3 py-1.5 text-sm font-medium bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-lg hover:bg-gray-50 dark:hover:bg-gray-700 text-gray-700 dark:text-gray-300 transition-colors">
            <svg class="w-3.5 h-3.5 text-orange-400" fill="currentColor" viewBox="0 0 20 20">
                <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/>
            </svg>
            Flag
        </button>

        @if($moveFolders->isNotEmpty())
        <div x-data="{ open: false }" class="relative">
            <button @click="open = !open"
                    class="flex items-center gap-1.5 px-3 py-1.5 text-sm font-medium bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-lg hover:bg-gray-50 dark:hover:bg-gray-700 text-gray-700 dark:text-gray-300 transition-colors">
                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                          d="M3 7a2 2 0 012-2h4l2 2h8a2 2 0 012 2v9a2 2 0 01-2 2H5a2 2 0 01-2-2V7z"/>
                </svg>
                Move to
                <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                </svg>
            </button>
            <div x-show="open" x-cloak @click.outside="open = false"
                 class="absolute left-0 top-full mt-1 w-48 bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-xl shadow-lg z-20 py-1 max-h-60 overflow-y-auto">
                @foreach($moveFolders as $mf)
                    <button @click="submitBulk('move', {{ json_encode($mf['name']) }}); open = false"
                            class="w-full text-left px-4 py-2 text-sm text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-700 transition-colors">
                        {{ $mf['name'] === 'INBOX' ? 'Inbox' : $mf['name'] }}
                    </button>
                @endforeach
            </div>
        </div>
        @endif

        <button @click="submitBulk('delete')"
                class="flex items-center gap-1.5 px-3 py-1.5 text-sm font-medium bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-800 rounded-lg hover:bg-red-100 dark:hover:bg-red-900/40 text-red-600 dark:text-red-400 transition-colors ml-auto">
            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                      d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
            </svg>
            Delete
        </button>

        <button @click="selected = new Set()"
                class="p-1.5 rounded-lg text-gray-400 hover:text-gray-600 dark:hover:text-gray-300 hover:bg-white dark:hover:bg-gray-800 transition-colors">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
            </svg>
        </button>
    </div>

    {{-- ---------------------------------------------------------------- --}}
    {{-- Error state                                                        --}}
    {{-- ---------------------------------------------------------------- --}}
    @if(!empty($error))
        <div class="flex flex-col items-center justify-center flex-1 px-6 py-20 text-center">
            <div class="w-14 h-14 rounded-2xl bg-red-100 dark:bg-red-900/20 flex items-center justify-center mb-4">
                <svg class="w-7 h-7 text-red-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                          d="M12 9v2m0 4h.01M10.29 3.86L1.82 18a2 2 0 001.71 3h16.94a2 2 0 001.71-3L13.71 3.86a2 2 0 00-3.42 0z"/>
                </svg>
            </div>
            <p class="text-base font-semibold text-gray-800 dark:text-gray-200">Could not connect to mail server</p>
            <p class="text-sm text-gray-500 dark:text-gray-400 mt-1.5 max-w-sm">{{ $error }}</p>
        </div>

    {{-- Empty state --}}
    @elseif(empty($messages))
        <div class="flex flex-col items-center justify-center flex-1 px-6 py-20 text-center">
            <div class="w-14 h-14 rounded-2xl bg-gray-100 dark:bg-gray-800 flex items-center justify-center mb-4">
                <svg class="w-7 h-7 text-gray-400 dark:text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                          d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
                </svg>
            </div>
            <p class="text-base font-semibold text-gray-600 dark:text-gray-400">No messages in {{ $folderLabel }}</p>
        </div>

    {{-- ---------------------------------------------------------------- --}}
    {{-- Message list (thread-grouped)                                     --}}
    {{-- ---------------------------------------------------------------- --}}
    @else
        <ul class="divide-y divide-gray-100 dark:divide-gray-800">
            @foreach($threads as $thread)
                @php
                    $msg        = $thread['latest'];
                    $threadCount = $thread['count'];
                    $fromName   = $msg['from']['name'] ?: $msg['from']['email'];
                    $initial    = mb_strtoupper(mb_substr($fromName, 0, 1, 'UTF-8'), 'UTF-8');
                    $avatarBg   = $avatarColors[$initial] ?? '#6366f1';
                    $href       = route('message.show', ['folder' => rawurlencode($currentFolder), 'uid' => $msg['uid']]);
                    $flagUrl    = route('message.flag',    ['folder' => rawurlencode($currentFolder), 'uid' => $msg['uid']]);
                    $destroyUrl = route('message.destroy', ['folder' => rawurlencode($currentFolder), 'uid' => $msg['uid']]);
                    $isUnread   = !$msg['seen'];
                    $uid        = $msg['uid'];
                    $isSeen     = $msg['seen'];
                @endphp

                {{-- For multi-message threads, show a collapsible thread row --}}
                @if($threadCount > 1)
                <li x-data="{ expanded: false }" class="border-b border-gray-100 dark:border-gray-800">
                    {{-- Thread summary row --}}
                    <div @click="expanded = !expanded"
                         class="relative flex items-center gap-4 px-5 py-3.5 cursor-pointer transition-colors group
                                {{ $isUnread ? 'bg-orange-50/40 dark:bg-orange-900/10 hover:bg-orange-50 dark:hover:bg-orange-900/20' : 'hover:bg-gray-50 dark:hover:bg-gray-800/60' }}">

                        {{-- Stacked avatars for thread participants --}}
                        <div class="flex-shrink-0 flex -space-x-2 w-12">
                            @foreach(array_slice($thread['messages'], 0, 3) as $i => $tm)
                                @php
                                    $tn = $tm['from']['name'] ?: $tm['from']['email'];
                                    $ti = mb_strtoupper(mb_substr($tn, 0, 1, 'UTF-8'), 'UTF-8');
                                    $tb = $avatarColors[$ti] ?? '#6366f1';
                                    $tmEmail = $tm['from']['email'] ?? '';
                                @endphp
                                @if(!empty($avatarMap[$tmEmail]))
                                    <img src="{{ $avatarMap[$tmEmail] }}" alt=""
                                         class="w-8 h-8 rounded-full object-cover border-2 border-white dark:border-gray-900"
                                         style="z-index: {{ 10 - $i }}">
                                @else
                                    <div class="w-8 h-8 rounded-full flex items-center justify-center text-xs font-bold text-white uppercase border-2 border-white dark:border-gray-900"
                                         style="background-color: {{ $tb }}; z-index: {{ 10 - $i }}">
                                        {{ $ti }}
                                    </div>
                                @endif
                            @endforeach
                        </div>

                        {{-- Expand/collapse arrow --}}
                        <div class="flex-shrink-0 text-gray-400 transition-transform duration-150" :class="expanded ? 'rotate-90' : ''">
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                            </svg>
                        </div>

                        {{-- Thread content --}}
                        <div class="flex-1 min-w-0">
                            <div class="flex items-center justify-between gap-3 mb-0.5">
                                <div class="flex items-center gap-2 min-w-0">
                                    @if($isUnread)
                                        <div class="flex-shrink-0 w-2 h-2 rounded-full bg-orange-500"></div>
                                    @endif
                                    <span class="text-base truncate {{ $isUnread ? 'font-bold text-gray-900 dark:text-white' : 'font-medium text-gray-700 dark:text-gray-300' }}">
                                        {{ $msg['subject'] ?: '(no subject)' }}
                                    </span>
                                    <span class="flex-shrink-0 px-1.5 py-0.5 text-xs font-medium bg-gray-200 dark:bg-gray-700 text-gray-600 dark:text-gray-400 rounded-full">
                                        {{ $threadCount }}
                                    </span>
                                </div>
                                <span class="flex-shrink-0 text-sm {{ $isUnread ? 'text-gray-600 dark:text-gray-400 font-medium' : 'text-gray-400 dark:text-gray-500' }}">
                                    {{ $msg['date_formatted'] ?? '' }}
                                </span>
                            </div>
                            <p class="text-sm text-gray-400 dark:text-gray-500 truncate">
                                {{ implode(', ', array_unique(array_map(fn($m) => $m['from']['name'] ?: $m['from']['email'], $thread['messages']))) }}
                            </p>
                        </div>
                    </div>

                    {{-- Expanded thread messages --}}
                    <div x-show="expanded" x-cloak
                         x-transition:enter="transition ease-out duration-100"
                         x-transition:enter-start="opacity-0"
                         x-transition:enter-end="opacity-100"
                         class="bg-gray-50/50 dark:bg-gray-800/30 border-t border-gray-100 dark:border-gray-800">
                        @foreach($thread['messages'] as $tm)
                            @php
                                $tn  = $tm['from']['name'] ?: $tm['from']['email'];
                                $th  = route('message.show', ['folder' => rawurlencode($currentFolder), 'uid' => $tm['uid']]);
                                $tfl = route('message.flag',    ['folder' => rawurlencode($currentFolder), 'uid' => $tm['uid']]);
                                $tdl = route('message.destroy', ['folder' => rawurlencode($currentFolder), 'uid' => $tm['uid']]);
                                $ti  = mb_strtoupper(mb_substr($tn, 0, 1, 'UTF-8'), 'UTF-8');
                                $tb  = $avatarColors[$ti] ?? '#6366f1';
                            @endphp
                            <div x-data="{
                                flagged: {{ $tm['flagged'] ? 'true' : 'false' }},
                                seen: {{ $tm['seen'] ? 'true' : 'false' }},
                                deleted: false,
                                toggleFlag() {
                                    fetch({{ Js::from($tfl) }}, { method:'POST', headers:{'X-CSRF-TOKEN':{{ Js::from(csrf_token()) }},'Content-Type':'application/json','Accept':'application/json'}, body: JSON.stringify({flag:'\\Flagged',add:!this.flagged}) }).then(r=>{ if(r.ok) this.flagged=!this.flagged; });
                                },
                                deleteMsg() {
                                    fetch({{ Js::from($tdl) }}, { method:'DELETE', headers:{'X-CSRF-TOKEN':{{ Js::from(csrf_token()) }},'Accept':'application/json'} }).then(r=>{ if(r.ok) this.deleted=true; });
                                }
                            }"
                                 x-show="!deleted"
                                 class="relative flex items-center gap-3 pl-8 pr-5 py-3 border-t border-gray-100 dark:border-gray-800 hover:bg-white dark:hover:bg-gray-800 group transition-colors">

                                @php $tmEmail2 = $tm['from']['email'] ?? ''; @endphp
                                @if(!empty($avatarMap[$tmEmail2]))
                                    <img src="{{ $avatarMap[$tmEmail2] }}" alt=""
                                         class="flex-shrink-0 w-7 h-7 rounded-full object-cover">
                                @else
                                    <div class="flex-shrink-0 w-7 h-7 rounded-full flex items-center justify-center text-xs font-bold text-white uppercase"
                                         style="background-color: {{ $tb }}">
                                        {{ $ti }}
                                    </div>
                                @endif

                                <a href="{{ $th }}" class="flex-1 min-w-0">
                                    <div class="flex items-center justify-between gap-2">
                                        <span class="text-base truncate {{ !$tm['seen'] ? 'font-semibold text-gray-900 dark:text-white' : 'text-gray-700 dark:text-gray-300' }}">
                                            {{ $tn }}
                                        </span>
                                        <span class="text-sm text-gray-400">{{ $tm['date_formatted'] ?? '' }}</span>
                                    </div>
                                </a>

                                <div class="opacity-0 group-hover:opacity-100 flex items-center gap-0.5 transition-opacity" @click.stop>
                                    <button @click="toggleFlag()" :class="flagged ? 'text-orange-400' : 'text-gray-400 hover:text-orange-400'"
                                            class="p-1.5 rounded hover:bg-gray-100 dark:hover:bg-gray-700 transition-colors">
                                        <svg class="w-3.5 h-3.5" fill="currentColor" viewBox="0 0 20 20">
                                            <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/>
                                        </svg>
                                    </button>
                                    <button @click="deleteMsg()" class="p-1.5 rounded text-gray-400 hover:text-red-500 hover:bg-red-50 dark:hover:bg-red-900/20 transition-colors">
                                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                        </svg>
                                    </button>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </li>

                @else
                {{-- Single message row (original style) --}}
                <li x-data="{
                        flagged: {{ $msg['flagged'] ? 'true' : 'false' }},
                        seen: {{ $isSeen ? 'true' : 'false' }},
                        deleted: false,
                        toggleFlag() {
                            fetch({{ Js::from($flagUrl) }}, { method:'POST', headers:{'X-CSRF-TOKEN':{{ Js::from(csrf_token()) }},'Content-Type':'application/json','Accept':'application/json'}, body: JSON.stringify({flag:'\\Flagged',add:!this.flagged}) }).then(r=>{ if(r.ok) this.flagged=!this.flagged; });
                        },
                        toggleSeen() {
                            fetch({{ Js::from($flagUrl) }}, { method:'POST', headers:{'X-CSRF-TOKEN':{{ Js::from(csrf_token()) }},'Content-Type':'application/json','Accept':'application/json'}, body: JSON.stringify({flag:'\\Seen',add:!this.seen}) }).then(r=>{ if(r.ok) this.seen=!this.seen; });
                        },
                        deleteMsg() {
                            fetch({{ Js::from($destroyUrl) }}, { method:'DELETE', headers:{'X-CSRF-TOKEN':{{ Js::from(csrf_token()) }},'Accept':'application/json'} }).then(r=>{ if(r.ok) this.deleted=true; });
                        }
                    }"
                    x-show="!deleted"
                    x-transition:leave="transition ease-in duration-150"
                    x-transition:leave-start="opacity-100"
                    x-transition:leave-end="opacity-0 -translate-x-2">
                    <div class="relative flex items-center gap-4 px-5 py-4 transition-colors group
                                {{ $isUnread ? 'bg-orange-50/40 dark:bg-orange-900/10 hover:bg-orange-50 dark:hover:bg-orange-900/20' : 'hover:bg-gray-50 dark:hover:bg-gray-800/60' }}"
                         :class="selected.has({{ $uid }}) ? 'bg-orange-50 dark:bg-orange-900/20' : ''">

                        <label class="flex-shrink-0 flex items-center cursor-pointer" @click.stop>
                            <input type="checkbox"
                                   :checked="selected.has({{ $uid }})"
                                   @change="toggle({{ $uid }})"
                                   class="w-4 h-4 rounded border-gray-300 dark:border-gray-600 text-orange-500 focus:ring-orange-400 cursor-pointer">
                        </label>

                        <div class="flex-shrink-0 w-2">
                            <div class="w-2 h-2 rounded-full bg-orange-500" x-show="!seen"></div>
                        </div>

                        @if(!empty($avatarMap[$msg['from']['email']]))
                            <img src="{{ $avatarMap[$msg['from']['email']] }}" alt=""
                                 class="flex-shrink-0 w-10 h-10 rounded-full object-cover shadow-sm">
                        @else
                            <div class="flex-shrink-0 w-10 h-10 rounded-full flex items-center justify-center text-sm font-bold text-white uppercase select-none shadow-sm"
                                 style="background-color: {{ $avatarBg }}">
                                {{ $initial }}
                            </div>
                        @endif

                        <a href="{{ $href }}" class="flex-1 min-w-0">
                            <div class="flex items-center justify-between gap-3 mb-0.5">
                                <span class="text-base truncate {{ $isUnread ? 'font-bold text-gray-900 dark:text-white' : 'font-medium text-gray-700 dark:text-gray-300' }}">
                                    {{ $fromName }}
                                </span>
                                <span class="flex-shrink-0 text-sm {{ $isUnread ? 'text-gray-600 dark:text-gray-400 font-medium' : 'text-gray-400 dark:text-gray-500' }}">
                                    {{ $msg['date_formatted'] ?? '' }}
                                </span>
                            </div>
                            <div class="flex items-center gap-1.5 min-w-0">
                                @if(!empty($msg['has_attachment']))
                                    <svg class="flex-shrink-0 w-3.5 h-3.5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.172 7l-6.586 6.586a2 2 0 102.828 2.828l6.414-6.586a4 4 0 00-5.656-5.656l-6.415 6.585a6 6 0 108.486 8.486L20.5 13"/>
                                    </svg>
                                @endif
                                <p class="text-base truncate {{ $isUnread ? 'font-semibold text-gray-800 dark:text-gray-200' : 'text-gray-600 dark:text-gray-400' }}">
                                    {{ $msg['subject'] ?: '(no subject)' }}
                                </p>
                            </div>
                        </a>

                        <div class="flex-shrink-0 flex items-center" style="width:88px" @click.stop>
                            <span x-show="flagged" class="transition-opacity duration-150 group-hover:opacity-0 pointer-events-none">
                                <svg class="w-4 h-4 text-orange-400" fill="currentColor" viewBox="0 0 20 20">
                                    <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/>
                                </svg>
                            </span>
                            <div class="opacity-0 group-hover:opacity-100 transition-opacity duration-150 flex items-center gap-0.5 absolute" style="right: 20px">
                                <button @click="toggleSeen()" :title="seen ? 'Mark as unread' : 'Mark as read'"
                                        class="p-1.5 rounded-lg text-gray-400 hover:text-blue-500 hover:bg-blue-50 dark:hover:bg-blue-900/20 transition-colors">
                                    <template x-if="seen">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
                                        </svg>
                                    </template>
                                    <template x-if="!seen">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0l-8 5-8-5"/>
                                        </svg>
                                    </template>
                                </button>
                                <button @click="toggleFlag()" :title="flagged ? 'Unflag' : 'Flag'"
                                        :class="flagged ? 'text-orange-400 hover:text-gray-400' : 'text-gray-400 hover:text-orange-400'"
                                        class="p-1.5 rounded-lg hover:bg-gray-100 dark:hover:bg-gray-700 transition-colors">
                                    <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                                        <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/>
                                    </svg>
                                </button>
                                <button @click="deleteMsg()" title="Delete"
                                        class="p-1.5 rounded-lg text-gray-400 hover:text-red-500 hover:bg-red-50 dark:hover:bg-red-900/20 transition-colors">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                    </svg>
                                </button>
                            </div>
                        </div>
                    </div>
                </li>
                @endif
            @endforeach
        </ul>

        {{-- ---------------------------------------------------------------- --}}
        {{-- Pagination                                                         --}}
        {{-- ---------------------------------------------------------------- --}}
        @if(($totalPages ?? 1) > 1)
            @php
                $page       = $page ?? 1;
                $totalPages = $totalPages ?? 1;
                $baseUrl    = $currentFolder === 'INBOX'
                    ? route('inbox')
                    : route('folder', ['folder' => rawurlencode($currentFolder)]);
                $pageUrl    = fn(int $p) => $baseUrl . '?page=' . $p;
                $window = 2;
                $pages  = [];
                for ($i = 1; $i <= $totalPages; $i++) {
                    if ($i === 1 || $i === $totalPages || abs($i - $page) <= $window) {
                        $pages[] = $i;
                    }
                }
            @endphp
            <div class="flex items-center justify-between px-5 py-3.5 border-t border-gray-100 dark:border-gray-800 bg-gray-50 dark:bg-gray-900/50">
                @if($page > 1)
                    <a href="{{ $pageUrl($page - 1) }}"
                       class="flex items-center gap-1.5 px-3 py-1.5 text-sm font-medium text-gray-600 dark:text-gray-400 hover:bg-white dark:hover:bg-gray-800 rounded-lg border border-gray-200 dark:border-gray-700 transition-colors">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
                        </svg>
                        Newer
                    </a>
                @else
                    <div></div>
                @endif

                <div class="flex items-center gap-1">
                    @php $prev = null; @endphp
                    @foreach($pages as $p)
                        @if($prev !== null && $p - $prev > 1)
                            <span class="px-1.5 text-gray-400 text-sm select-none">…</span>
                        @endif
                        <a href="{{ $pageUrl($p) }}"
                           class="min-w-[32px] h-8 flex items-center justify-center text-sm rounded-lg transition-colors
                                  {{ $p === $page
                                      ? 'bg-orange-500 text-white font-semibold'
                                      : 'text-gray-600 dark:text-gray-400 hover:bg-white dark:hover:bg-gray-800 border border-transparent hover:border-gray-200 dark:hover:border-gray-700' }}">
                            {{ $p }}
                        </a>
                        @php $prev = $p; @endphp
                    @endforeach
                </div>

                @if($page < $totalPages)
                    <a href="{{ $pageUrl($page + 1) }}"
                       class="flex items-center gap-1.5 px-3 py-1.5 text-sm font-medium text-gray-600 dark:text-gray-400 hover:bg-white dark:hover:bg-gray-800 rounded-lg border border-gray-200 dark:border-gray-700 transition-colors">
                        Older
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                        </svg>
                    </a>
                @else
                    <div></div>
                @endif
            </div>
        @endif
    @endif

</div>
@endsection
