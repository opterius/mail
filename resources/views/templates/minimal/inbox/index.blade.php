{{--
 | Opterius Mail - Open source webmail.
 | Minimal template — inbox list.
 | https://opterius.com
 |
 | Copyright (c) Host Server SRL (Opterius)
 | @license  AGPL-3.0  https://opensource.org/license/agpl-v3
 | @author   Iosif Gabriel Chimilevschi <office@opterius.com>
--}}
@extends(mailView('layouts.app'))

@php $folderLabel = strtoupper($currentFolder) === 'INBOX' ? 'Inbox' : $currentFolder; @endphp

@section('title', $folderLabel)

@section('content')

    {{-- Header --}}
    <div class="flex items-center justify-between mb-4">
        <div class="flex items-center gap-3">
            <h1 class="font-semibold text-gray-800">{{ $folderLabel }}</h1>
            @if(($total ?? 0) > 0)
                <span class="text-xs text-gray-400">{{ number_format($total) }} messages</span>
            @endif
        </div>
    </div>

    {{-- Error --}}
    @if(!empty($error))
        <div class="bg-red-50 border border-red-200 rounded px-4 py-3 text-red-700 text-sm">{{ $error }}</div>

    {{-- Empty --}}
    @elseif(empty($messages))
        <div class="text-center py-16 text-gray-400">No messages in {{ $folderLabel }}</div>

    {{-- Table --}}
    @else
        <div class="bg-white border border-gray-200 rounded-lg overflow-hidden">
            <table class="w-full">
                <tbody class="divide-y divide-gray-100">
                    @foreach($messages as $msg)
                        @php
                            $fromName = $msg['from']['name'] ?: $msg['from']['email'];
                            $href     = route('message.show', [
                                'folder' => rawurlencode($currentFolder),
                                'uid'    => $msg['uid'],
                            ]);
                        @endphp
                        <tr class="hover:bg-gray-50 transition-colors {{ $msg['seen'] ? '' : 'bg-blue-50/30' }}">
                            <td class="w-2 pl-3 pr-0 py-3">
                                <div class="w-1.5 h-1.5 rounded-full {{ $msg['seen'] ? 'bg-transparent' : 'bg-blue-500' }}"></div>
                            </td>
                            <td class="py-3 pl-3 pr-4 w-44">
                                <a href="{{ $href }}"
                                   class="block truncate text-sm {{ $msg['seen'] ? 'text-gray-600' : 'font-semibold text-gray-900' }}">
                                    {{ $fromName }}
                                </a>
                            </td>
                            <td class="py-3 px-2 min-w-0">
                                <a href="{{ $href }}" class="block truncate text-sm text-gray-700">
                                    {{ $msg['subject'] }}
                                </a>
                            </td>
                            <td class="py-3 pl-2 pr-4 w-24 text-right">
                                <span class="text-xs text-gray-400 whitespace-nowrap">{{ $msg['date_formatted'] ?? '' }}</span>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        @if(($total ?? 0) > count($messages))
            <p class="text-xs text-gray-400 text-center mt-4">
                Showing last {{ count($messages) }} of {{ number_format($total) }} messages
            </p>
        @endif
    @endif

@endsection
