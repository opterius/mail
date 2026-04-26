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

    $avatarColors = [
        'A' => '#ef4444','B' => '#f97316','C' => '#eab308','D' => '#22c55e',
        'E' => '#14b8a6','F' => '#3b82f6','G' => '#8b5cf6','H' => '#ec4899',
        'I' => '#f43f5e','J' => '#06b6d4','K' => '#84cc16','L' => '#f97316',
        'M' => '#6366f1','N' => '#10b981','O' => '#f59e0b','P' => '#3b82f6',
        'Q' => '#8b5cf6','R' => '#ef4444','S' => '#14b8a6','T' => '#22c55e',
        'U' => '#ec4899','V' => '#6366f1','W' => '#f43f5e','X' => '#06b6d4',
        'Y' => '#84cc16','Z' => '#f97316',
    ];
@endphp

@section('title', $folderLabel)

@section('content')
<div class="flex flex-col h-full">

    {{-- Toolbar --}}
    <div class="flex items-center justify-between px-5 py-3.5 border-b border-gray-100 dark:border-gray-800 bg-white dark:bg-gray-900 sticky top-0 z-10">
        <div class="flex items-center gap-3">
            <h1 class="text-base font-bold text-gray-900 dark:text-white">{{ $folderLabel }}</h1>
            @if(($total ?? 0) > 0)
                <span class="text-sm text-gray-400 dark:text-gray-500">{{ number_format($total) }}</span>
            @endif
        </div>
        <div class="flex items-center gap-2">
            {{-- Inline search (desktop) --}}
            <form method="GET" action="{{ route('search') }}" class="hidden sm:block">
                <div class="relative">
                    <svg class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-gray-400 pointer-events-none"
                         fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                              d="M21 21l-4.35-4.35M17 11A6 6 0 115 11a6 6 0 0112 0z"/>
                    </svg>
                    <input name="q" type="search"
                           placeholder="Search…"
                           value="{{ request('q') }}"
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

    {{-- Error state --}}
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

    {{-- Message list --}}
    @else
        <ul class="divide-y divide-gray-100 dark:divide-gray-800">
            @foreach($messages as $msg)
                @php
                    $fromName  = $msg['from']['name'] ?: $msg['from']['email'];
                    $initial   = mb_strtoupper(mb_substr($fromName, 0, 1, 'UTF-8'), 'UTF-8');
                    $avatarBg  = $avatarColors[$initial] ?? '#6366f1';
                    $href      = route('message.show', [
                        'folder' => rawurlencode($currentFolder),
                        'uid'    => $msg['uid'],
                    ]);
                    $isUnread  = !$msg['seen'];
                @endphp
                <li>
                    <a href="{{ $href }}"
                       class="flex items-center gap-4 px-5 py-4 transition-colors group
                              {{ $isUnread
                                  ? 'bg-orange-50/40 dark:bg-orange-900/10 hover:bg-orange-50 dark:hover:bg-orange-900/20'
                                  : 'hover:bg-gray-50 dark:hover:bg-gray-800/60' }}">

                        {{-- Unread indicator --}}
                        <div class="flex-shrink-0 w-2 flex items-center justify-center">
                            @if($isUnread)
                                <div class="w-2 h-2 rounded-full bg-orange-500"></div>
                            @endif
                        </div>

                        {{-- Avatar --}}
                        <div class="flex-shrink-0 w-10 h-10 rounded-full flex items-center justify-center
                                    text-sm font-bold text-white uppercase select-none shadow-sm"
                             style="background-color: {{ $avatarBg }}">
                            {{ $initial }}
                        </div>

                        {{-- Content --}}
                        <div class="flex-1 min-w-0">
                            <div class="flex items-center justify-between gap-3 mb-0.5">
                                <span class="text-sm truncate
                                             {{ $isUnread
                                                 ? 'font-bold text-gray-900 dark:text-white'
                                                 : 'font-medium text-gray-700 dark:text-gray-300' }}">
                                    {{ $fromName }}
                                </span>
                                <span class="flex-shrink-0 text-xs {{ $isUnread ? 'text-gray-600 dark:text-gray-400 font-medium' : 'text-gray-400 dark:text-gray-500' }}">
                                    {{ $msg['date_formatted'] ?? '' }}
                                </span>
                            </div>
                            <p class="text-sm truncate
                                      {{ $isUnread
                                          ? 'font-semibold text-gray-800 dark:text-gray-200'
                                          : 'text-gray-600 dark:text-gray-400' }}">
                                {{ $msg['subject'] ?: '(no subject)' }}
                            </p>
                        </div>

                        {{-- Flagged star --}}
                        @if($msg['flagged'])
                            <div class="flex-shrink-0">
                                <svg class="w-4 h-4 text-orange-400" fill="currentColor" viewBox="0 0 20 20">
                                    <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/>
                                </svg>
                            </div>
                        @endif

                    </a>
                </li>
            @endforeach
        </ul>

        @if(($total ?? 0) > count($messages))
            <div class="px-5 py-4 text-center border-t border-gray-100 dark:border-gray-800 bg-gray-50 dark:bg-gray-900/50">
                <p class="text-sm text-gray-500 dark:text-gray-400">
                    Showing {{ count($messages) }} of {{ number_format($total) }} messages
                </p>
            </div>
        @endif
    @endif

</div>
@endsection
