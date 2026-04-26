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

@section('title', $query !== '' ? 'Search: ' . $query : 'Search')

@section('content')
@php
    $activeFilters = $fromFilter !== '' || $since !== '' || $before !== '' || $seen !== '' || $hasAttach;
@endphp
<div class="flex flex-col h-full">

    {{-- Search bar --}}
    <div class="px-6 py-4 border-b border-gray-100 bg-white sticky top-0 z-10"
         x-data="{ filtersOpen: {{ ($activeFilters ? 'true' : 'false') }} }">
        <form method="GET" action="{{ route('search') }}">
            <div class="flex items-center gap-3">
                <div class="relative flex-1 max-w-xl">
                    <svg class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-gray-400 pointer-events-none"
                         fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                              d="M21 21l-4.35-4.35M17 11A6 6 0 115 11a6 6 0 0112 0z"/>
                    </svg>
                    <input name="q"
                           type="search"
                           value="{{ $query }}"
                           placeholder="Search by subject or sender…"
                           autofocus
                           class="w-full pl-9 pr-4 py-2 text-sm bg-gray-100 rounded-lg outline-none
                                  focus:bg-white focus:ring-2 focus:ring-orange-400 transition">
                </div>
                <button type="button" @click="filtersOpen = !filtersOpen"
                        :class="filtersOpen || {{ $activeFilters ? 'true' : 'false' }} ? 'text-orange-500 bg-orange-50' : 'text-gray-500 hover:bg-gray-100'"
                        class="flex items-center gap-1.5 px-3 py-2 text-sm rounded-lg transition-colors">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                              d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2a1 1 0 01-.293.707L13 13.414V19a1 1 0 01-.553.894l-4 2A1 1 0 017 21v-7.586L3.293 6.707A1 1 0 013 6V4z"/>
                    </svg>
                    Filters
                </button>
                <button type="submit"
                        class="px-4 py-2 text-sm font-medium bg-orange-500 hover:bg-orange-600 text-white rounded-lg transition-colors">
                    Search
                </button>
            </div>

            {{-- Advanced filter panel --}}
            <div x-show="filtersOpen" x-cloak
                 x-transition:enter="transition ease-out duration-100"
                 x-transition:enter-start="opacity-0 -translate-y-1"
                 x-transition:enter-end="opacity-100 translate-y-0"
                 class="mt-3 pt-3 border-t border-gray-100 grid grid-cols-2 gap-3 md:grid-cols-3">

                {{-- Folder --}}
                <div>
                    <label class="block text-xs font-medium text-gray-500 mb-1">Folder</label>
                    <select name="folder"
                            class="w-full px-3 py-2 text-sm border border-gray-200 rounded-lg outline-none focus:ring-2 focus:ring-orange-400">
                        @foreach($folders as $f)
                            <option value="{{ $f['name'] }}" {{ $searchFolder === $f['name'] ? 'selected' : '' }}>
                                {{ $f['name'] === 'INBOX' ? 'Inbox' : $f['name'] }}
                            </option>
                        @endforeach
                    </select>
                </div>

                {{-- From --}}
                <div>
                    <label class="block text-xs font-medium text-gray-500 mb-1">From</label>
                    <input name="from" type="text" value="{{ $fromFilter }}" placeholder="sender@example.com"
                           class="w-full px-3 py-2 text-sm border border-gray-200 rounded-lg outline-none focus:ring-2 focus:ring-orange-400">
                </div>

                {{-- Since --}}
                <div>
                    <label class="block text-xs font-medium text-gray-500 mb-1">After date</label>
                    <input name="since" type="date" value="{{ $since }}"
                           class="w-full px-3 py-2 text-sm border border-gray-200 rounded-lg outline-none focus:ring-2 focus:ring-orange-400">
                </div>

                {{-- Before --}}
                <div>
                    <label class="block text-xs font-medium text-gray-500 mb-1">Before date</label>
                    <input name="before" type="date" value="{{ $before }}"
                           class="w-full px-3 py-2 text-sm border border-gray-200 rounded-lg outline-none focus:ring-2 focus:ring-orange-400">
                </div>

                {{-- Read/Unread --}}
                <div>
                    <label class="block text-xs font-medium text-gray-500 mb-1">Read status</label>
                    <select name="seen"
                            class="w-full px-3 py-2 text-sm border border-gray-200 rounded-lg outline-none focus:ring-2 focus:ring-orange-400">
                        <option value="" {{ $seen === '' ? 'selected' : '' }}>Any</option>
                        <option value="0" {{ $seen === '0' ? 'selected' : '' }}>Unread</option>
                        <option value="1" {{ $seen === '1' ? 'selected' : '' }}>Read</option>
                    </select>
                </div>

                {{-- Has attachment --}}
                <div class="flex items-end pb-2">
                    <label class="flex items-center gap-2 cursor-pointer">
                        <input type="checkbox" name="attachment" value="1" {{ $hasAttach ? 'checked' : '' }}
                               class="w-4 h-4 accent-orange-500">
                        <span class="text-sm text-gray-700">Has attachment</span>
                    </label>
                </div>
            </div>
        </form>
    </div>

    {{-- Error --}}
    @if(!empty($error))
        <div class="flex flex-col items-center justify-center flex-1 px-6 py-20 text-center">
            <div class="w-12 h-12 rounded-xl bg-red-100 flex items-center justify-center mb-4">
                <svg class="w-6 h-6 text-red-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                          d="M12 9v2m0 4h.01M10.29 3.86L1.82 18a2 2 0 001.71 3h16.94a2 2 0 001.71-3L13.71 3.86a2 2 0 00-3.42 0z"/>
                </svg>
            </div>
            <p class="text-sm font-medium text-gray-700">Could not connect to mail server</p>
            <p class="text-xs text-gray-400 mt-1">{{ $error }}</p>
        </div>

    {{-- No query yet --}}
    @elseif($query === '' && !$activeFilters)
        <div class="flex flex-col items-center justify-center flex-1 px-6 py-20 text-center">
            <div class="w-12 h-12 rounded-xl bg-gray-100 flex items-center justify-center mb-4">
                <svg class="w-6 h-6 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                          d="M21 21l-4.35-4.35M17 11A6 6 0 115 11a6 6 0 0112 0z"/>
                </svg>
            </div>
            <p class="text-sm text-gray-400">Type a search term above</p>
        </div>

    {{-- No results --}}
    @elseif(empty($messages))
        <div class="flex flex-col items-center justify-center flex-1 px-6 py-20 text-center">
            <div class="w-12 h-12 rounded-xl bg-gray-100 flex items-center justify-center mb-4">
                <svg class="w-6 h-6 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                          d="M9.172 16.172a4 4 0 015.656 0M9 10h.01M15 10h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
            </div>
            <p class="text-sm font-medium text-gray-700">No results for "{{ $query }}"</p>
            <p class="text-xs text-gray-400 mt-1">Try a different subject or sender name</p>
        </div>

    {{-- Results --}}
    @else
        <div class="flex items-center justify-between px-6 py-2.5 border-b border-gray-100">
            <p class="text-xs text-gray-400">
                {{ $total }} {{ Str::plural('result', $total) }}
                @if($query !== '')
                    for <span class="font-medium text-gray-600">"{{ $query }}"</span>
                @endif
                in <span class="font-medium text-gray-600">{{ $searchFolder === 'INBOX' ? 'Inbox' : $searchFolder }}</span>
            </p>
        </div>

        <ul class="divide-y divide-gray-100">
            @foreach($messages as $msg)
                @php
                    $fromName = $msg['from']['name'] ?: $msg['from']['email'];
                    $href     = route('message.show', [
                        'folder' => rawurlencode('INBOX'),
                        'uid'    => $msg['uid'],
                    ]);
                @endphp
                <li>
                    <a href="{{ $href }}"
                       class="flex items-start gap-3 px-6 py-3.5 hover:bg-gray-50 transition-colors">

                        {{-- Unread dot --}}
                        <div class="flex-shrink-0 mt-1.5 w-2 h-2 rounded-full
                                    {{ $msg['seen'] ? 'bg-transparent' : 'bg-orange-500' }}"></div>

                        {{-- Avatar --}}
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
    @endif

</div>
@endsection
