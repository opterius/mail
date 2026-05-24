{{--
 | Opterius Mail - Open source webmail.
 | Admin — job queue.
 | https://opterius.com
 |
 | Copyright (c) Host Server SRL (Opterius)
 | @license  AGPL-3.0  https://opensource.org/license/agpl-v3
 | @author   Iosif Gabriel Chimilevschi <office@opterius.com>
--}}
@extends(mailView('admin.layouts.admin'))

@section('title', 'Queue')

@section('content')
<div class="p-6">

    <div class="mb-6 flex items-center justify-between">
        <div>
            <h1 class="text-xl font-bold text-gray-900">Job Queue</h1>
            <p class="text-sm text-gray-400 mt-0.5">Pending and failed background jobs.</p>
        </div>
        @if($failed->isNotEmpty())
            <form method="POST" action="{{ route('admin.queue.flush') }}"
                  onsubmit="return confirm('Re-queue all {{ $failed->count() }} failed job(s)?')">
                @csrf
                <button type="submit"
                        class="flex items-center gap-1.5 px-4 py-2 bg-orange-500 hover:bg-orange-600 text-white text-sm font-medium rounded-lg transition-colors">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                              d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/>
                    </svg>
                    Retry all failed
                </button>
            </form>
        @endif
    </div>

    {{-- Pending --}}
    <div class="mb-6">
        <h2 class="text-sm font-semibold text-gray-700 mb-3 flex items-center gap-2">
            Pending
            <span class="px-2 py-0.5 rounded-full text-[13px] font-medium {{ $pending->isEmpty() ? 'bg-gray-100 text-gray-500' : 'bg-blue-100 text-blue-700' }}">
                {{ $pending->count() }}
            </span>
        </h2>

        @if($pending->isEmpty())
            <div class="bg-white rounded-xl border border-gray-200 px-6 py-8 text-center text-sm text-gray-400">
                No pending jobs.
            </div>
        @else
            <div class="bg-white rounded-xl border border-gray-200 overflow-hidden">
                <table class="w-full">
                    <thead>
                        <tr class="border-b border-gray-100 text-left">
                            <th class="px-4 py-3 text-[13px] font-semibold text-gray-500 uppercase tracking-wide">Job</th>
                            <th class="px-4 py-3 text-[13px] font-semibold text-gray-500 uppercase tracking-wide">Queue</th>
                            <th class="px-4 py-3 text-[13px] font-semibold text-gray-500 uppercase tracking-wide">Attempts</th>
                            <th class="px-4 py-3 text-[13px] font-semibold text-gray-500 uppercase tracking-wide">Queued</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-50">
                        @foreach($pending as $job)
                            <tr class="hover:bg-gray-50">
                                <td class="px-4 py-2.5 text-sm font-mono text-gray-800">{{ class_basename($job['display_name']) }}</td>
                                <td class="px-4 py-2.5 text-sm text-gray-500">{{ $job['queue'] }}</td>
                                <td class="px-4 py-2.5 text-sm text-gray-500">{{ $job['attempts'] }}</td>
                                <td class="px-4 py-2.5 text-[13px] text-gray-400">
                                    {{ \Carbon\Carbon::createFromTimestamp($job['created_at'])->diffForHumans() }}
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif
    </div>

    {{-- Failed --}}
    <div>
        <h2 class="text-sm font-semibold text-gray-700 mb-3 flex items-center gap-2">
            Failed
            <span class="px-2 py-0.5 rounded-full text-[13px] font-medium {{ $failed->isEmpty() ? 'bg-gray-100 text-gray-500' : 'bg-red-100 text-red-700' }}">
                {{ $failed->count() }}
            </span>
        </h2>

        @if($failed->isEmpty())
            <div class="bg-white rounded-xl border border-gray-200 px-6 py-8 text-center text-sm text-gray-400">
                No failed jobs.
            </div>
        @else
            <div class="bg-white rounded-xl border border-gray-200 overflow-hidden">
                <table class="w-full">
                    <thead>
                        <tr class="border-b border-gray-100 text-left">
                            <th class="px-4 py-3 text-[13px] font-semibold text-gray-500 uppercase tracking-wide">Job</th>
                            <th class="px-4 py-3 text-[13px] font-semibold text-gray-500 uppercase tracking-wide">Queue</th>
                            <th class="px-4 py-3 text-[13px] font-semibold text-gray-500 uppercase tracking-wide">Error</th>
                            <th class="px-4 py-3 text-[13px] font-semibold text-gray-500 uppercase tracking-wide">Failed at</th>
                            <th class="px-4 py-3"></th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-50">
                        @foreach($failed as $job)
                            <tr class="hover:bg-gray-50 bg-red-50/30">
                                <td class="px-4 py-2.5 text-sm font-mono text-gray-800">{{ class_basename($job['display_name']) }}</td>
                                <td class="px-4 py-2.5 text-sm text-gray-500">{{ $job['queue'] }}</td>
                                <td class="px-4 py-2.5 text-[13px] text-red-600 max-w-xs truncate">{{ $job['short_error'] ?: '—' }}</td>
                                <td class="px-4 py-2.5 text-[13px] text-gray-400 whitespace-nowrap">
                                    {{ \Carbon\Carbon::parse($job['failed_at'])->diffForHumans() }}
                                </td>
                                <td class="px-4 py-2.5 text-right">
                                    <form method="POST" action="{{ route('admin.queue.destroy', $job['id']) }}">
                                        @csrf @method('DELETE')
                                        <button type="submit" class="text-[13px] text-red-400 hover:text-red-600 transition-colors">Delete</button>
                                    </form>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif
    </div>

</div>
@endsection
