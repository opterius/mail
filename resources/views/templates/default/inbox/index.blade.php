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
@endphp

@section('title', $folderLabel)

@section('content')
<div class="flex flex-col h-full">

    {{-- Toolbar --}}
    <div class="flex items-center justify-between px-6 py-3 border-b border-gray-100 bg-white sticky top-0 z-10">
        <div class="flex items-center gap-3">
            <h1 class="text-sm font-semibold text-gray-800">{{ $folderLabel }}</h1>
            @if(($total ?? 0) > 0)
                <span class="text-xs text-gray-400">{{ number_format($total) }}</span>
            @endif
        </div>
        <a href="{{ route('compose') }}"
           class="hidden sm:flex items-center gap-1.5 px-3 py-1.5 text-xs font-medium text-orange-600 hover:bg-orange-50 rounded-lg transition-colors">
            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
            </svg>
            Compose
        </a>
    </div>

    {{-- Error state --}}
    @if(!empty($error))
        <div class="flex flex-col items-center justify-center flex-1 px-6 py-20 text-center">
            <div class="w-12 h-12 rounded-xl bg-red-100 flex items-center justify-center mb-4">
                <svg class="w-6 h-6 text-red-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                          d="M12 9v2m0 4h.01M10.29 3.86L1.82 18a2 2 0 001.71 3h16.94a2 2 0 001.71-3L13.71 3.86a2 2 0 00-3.42 0z"/>
                </svg>
            </div>
            <p class="text-sm font-medium text-gray-700">Could not connect to mail server</p>
            <p class="text-xs text-gray-400 mt-1 max-w-xs">{{ $error }}</p>
        </div>

    {{-- Empty state --}}
    @elseif(empty($messages))
        <div class="flex flex-col items-center justify-center flex-1 px-6 py-20 text-center">
            <div class="w-12 h-12 rounded-xl bg-gray-100 flex items-center justify-center mb-4">
                <svg class="w-6 h-6 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                          d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
                </svg>
            </div>
            <p class="text-sm font-medium text-gray-500">No messages in {{ $folderLabel }}</p>
        </div>

    {{-- Message list --}}
    @else
        <ul class="divide-y divide-gray-100">
            @foreach($messages as $msg)
                @php
                    $fromName = $msg['from']['name'] ?: $msg['from']['email'];
                    $href     = route('message.show', [
                        'folder' => rawurlencode($currentFolder),
                        'uid'    => $msg['uid'],
                    ]);
                @endphp
                <li>
                    <a href="{{ $href }}"
                       class="flex items-start gap-3 px-6 py-3.5 hover:bg-gray-50 transition-colors group relative">

                        {{-- Unread dot --}}
                        <div class="flex-shrink-0 mt-1.5 w-2 h-2 rounded-full
                                    {{ $msg['seen'] ? 'bg-transparent' : 'bg-orange-500' }}">
                        </div>

                        {{-- Avatar initial --}}
                        <div class="flex-shrink-0 w-8 h-8 rounded-full bg-gray-200 flex items-center justify-center
                                    text-xs font-semibold text-gray-500 uppercase select-none">
                            {{ mb_substr($fromName, 0, 1, 'UTF-8') }}
                        </div>

                        {{-- Content --}}
                        <div class="flex-1 min-w-0">
                            <div class="flex items-baseline justify-between gap-2">
                                <span class="text-sm truncate
                                             {{ $msg['seen'] ? 'text-gray-600' : 'font-semibold text-gray-900' }}">
                                    {{ $fromName }}
                                </span>
                                <span class="flex-shrink-0 text-xs text-gray-400">
                                    {{ $msg['date_formatted'] ?? '' }}
                                </span>
                            </div>
                            <p class="text-sm truncate mt-0.5
                                      {{ $msg['seen'] ? 'text-gray-500' : 'font-medium text-gray-800' }}">
                                {{ $msg['subject'] }}
                            </p>
                        </div>

                        {{-- Flagged star --}}
                        @if($msg['flagged'])
                            <div class="flex-shrink-0 mt-1">
                                <svg class="w-4 h-4 text-orange-400" fill="currentColor" viewBox="0 0 20 20">
                                    <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/>
                                </svg>
                            </div>
                        @endif

                    </a>
                </li>
            @endforeach
        </ul>

        {{-- Load-more hint when folder has more messages --}}
        @if(($total ?? 0) > count($messages))
            <div class="px-6 py-4 text-center border-t border-gray-100">
                <p class="text-xs text-gray-400">
                    Showing last {{ count($messages) }} of {{ number_format($total) }} messages
                </p>
            </div>
        @endif
    @endif

</div>
@endsection
