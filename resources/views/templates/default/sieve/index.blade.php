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

@section('title', 'Filtering Rules')

@section('content')
<div class="flex flex-col h-full">

    {{-- Toolbar --}}
    <div class="flex items-center px-6 py-3 border-b border-gray-100 dark:border-gray-700 bg-white dark:bg-gray-900 sticky top-0 z-10">
        <a href="{{ route('settings') }}"
           class="flex items-center gap-1.5 px-3 py-1.5 text-sm text-gray-600 dark:text-gray-400 hover:bg-gray-100 dark:hover:bg-gray-800 rounded-lg transition-colors mr-2">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
            </svg>
            Settings
        </a>
        <h1 class="text-sm font-semibold text-gray-800 dark:text-gray-200">Message Filtering Rules</h1>
    </div>

    <div class="flex-1 overflow-auto px-6 py-6 max-w-2xl">

        @if(session('success'))
            <div class="mb-4 px-4 py-3 bg-green-50 dark:bg-green-900/20 border border-green-200 dark:border-green-700 rounded-lg text-sm text-green-700 dark:text-green-400">
                {{ session('success') }}
            </div>
        @endif
        @if(session('sieve_error'))
            <div class="mb-4 px-4 py-3 bg-yellow-50 dark:bg-yellow-900/20 border border-yellow-200 dark:border-yellow-700 rounded-lg text-sm text-yellow-700 dark:text-yellow-400">
                {{ session('sieve_error') }}
            </div>
        @endif

        <p class="text-sm text-gray-500 dark:text-gray-400 mb-6">
            Rules are applied in order. Messages matching a rule are processed and the next rule is checked.
            Rules are uploaded to your mail server via ManageSieve (port {{ config('mail.sieve_port', 4190) }}).
        </p>

        {{-- Existing rules --}}
        @if(!empty($rules))
        <section class="mb-8">
            <h2 class="text-xs font-semibold text-gray-400 dark:text-gray-500 uppercase tracking-wider mb-3">Active Rules</h2>
            <ul class="space-y-2">
                @foreach($rules as $rule)
                <li class="flex items-center justify-between gap-4 px-4 py-3 bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-lg">
                    <div class="flex-1 min-w-0">
                        <p class="text-sm text-gray-800 dark:text-gray-200">
                            @if($rule['condition'] === 'all')
                                <span class="font-medium">All messages</span>
                            @else
                                <span class="font-medium">{{ ucfirst($rule['condition']) }}</span>
                                contains
                                <span class="font-mono text-orange-600 dark:text-orange-400">"{{ $rule['value'] }}"</span>
                            @endif
                            →
                            @switch($rule['action'])
                                @case('move')
                                    Move to <span class="font-medium">{{ $rule['action_target'] ?: '?' }}</span>
                                    @break
                                @case('delete')
                                    <span class="text-red-600 dark:text-red-400">Discard (delete)</span>
                                    @break
                                @case('mark_read')
                                    Mark as read
                                    @break
                                @case('flag')
                                    Flag message
                                    @break
                            @endswitch
                        </p>
                    </div>
                    <form method="POST" action="{{ route('sieve.destroy', $rule['id']) }}">
                        @csrf
                        @method('DELETE')
                        <button type="submit"
                                class="p-1.5 text-gray-400 hover:text-red-500 hover:bg-red-50 dark:hover:bg-red-900/20 rounded-lg transition-colors flex-shrink-0"
                                title="Delete rule">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                            </svg>
                        </button>
                    </form>
                </li>
                @endforeach
            </ul>
        </section>
        @endif

        {{-- Add new rule --}}
        <section x-data="{ action: 'move', condition: 'from' }">
            <h2 class="text-xs font-semibold text-gray-400 dark:text-gray-500 uppercase tracking-wider mb-3">Add Rule</h2>
            <form method="POST" action="{{ route('sieve.store') }}"
                  class="bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-lg p-5 space-y-4">
                @csrf

                <div class="grid grid-cols-2 gap-4">
                    {{-- Condition field --}}
                    <div>
                        <label class="block text-xs font-medium text-gray-500 dark:text-gray-400 mb-1.5">If</label>
                        <select name="condition" x-model="condition"
                                class="w-full px-3 py-2 text-sm border border-gray-200 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-900 text-gray-800 dark:text-gray-200 outline-none focus:ring-2 focus:ring-orange-400">
                            <option value="from">From (sender)</option>
                            <option value="subject">Subject</option>
                            <option value="to">To</option>
                            <option value="all">All messages</option>
                        </select>
                    </div>

                    {{-- Condition value --}}
                    <div x-show="condition !== 'all'">
                        <label class="block text-xs font-medium text-gray-500 dark:text-gray-400 mb-1.5">Contains</label>
                        <input name="value" type="text" placeholder="e.g. newsletter@example.com"
                               class="w-full px-3 py-2 text-sm border border-gray-200 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-900 text-gray-800 dark:text-gray-200 outline-none focus:ring-2 focus:ring-orange-400">
                    </div>
                </div>

                <div class="grid grid-cols-2 gap-4">
                    {{-- Action --}}
                    <div>
                        <label class="block text-xs font-medium text-gray-500 dark:text-gray-400 mb-1.5">Then</label>
                        <select name="action" x-model="action"
                                class="w-full px-3 py-2 text-sm border border-gray-200 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-900 text-gray-800 dark:text-gray-200 outline-none focus:ring-2 focus:ring-orange-400">
                            <option value="move">Move to folder</option>
                            <option value="delete">Discard (delete)</option>
                            <option value="mark_read">Mark as read</option>
                            <option value="flag">Flag</option>
                        </select>
                    </div>

                    {{-- Action target (folder name) --}}
                    <div x-show="action === 'move'">
                        <label class="block text-xs font-medium text-gray-500 dark:text-gray-400 mb-1.5">Destination folder</label>
                        <input name="action_target" type="text" placeholder="e.g. Newsletters"
                               class="w-full px-3 py-2 text-sm border border-gray-200 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-900 text-gray-800 dark:text-gray-200 outline-none focus:ring-2 focus:ring-orange-400">
                    </div>
                </div>

                @if($errors->any())
                    <div class="px-3 py-2 bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-700 rounded-lg text-sm text-red-600 dark:text-red-400">
                        {{ $errors->first() }}
                    </div>
                @endif

                <div>
                    <button type="submit"
                            class="px-5 py-2 text-sm font-medium bg-orange-500 hover:bg-orange-600 text-white rounded-lg transition-colors">
                        Add Rule
                    </button>
                </div>
            </form>
        </section>

    </div>

</div>
@endsection
