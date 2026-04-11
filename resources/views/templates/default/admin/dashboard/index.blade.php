{{--
 | Opterius Mail - Open source webmail.
 | Admin — dashboard.
 | https://opterius.com
 |
 | Copyright (c) Host Server SRL (Opterius)
 | @license  AGPL-3.0  https://opensource.org/license/agpl-v3
 | @author   Iosif Gabriel Chimilevschi <office@opterius.com>
--}}
@extends(mailView('admin.layouts.admin'))

@section('title', 'Dashboard')

@section('content')
<div class="p-6">

    {{-- Page header --}}
    <div class="mb-6">
        <h1 class="text-xl font-bold text-gray-900">Dashboard</h1>
        <p class="text-sm text-gray-400 mt-0.5">Activity overview · today is {{ now()->format('l, F j, Y') }}</p>
    </div>

    {{-- ------------------------------------------------------------------ --}}
    {{-- Stat cards                                                           --}}
    {{-- ------------------------------------------------------------------ --}}
    <div class="grid grid-cols-2 lg:grid-cols-4 gap-4 mb-6">

        <div class="bg-white rounded-xl border border-gray-200 p-4">
            <p class="text-xs font-medium text-gray-400 uppercase tracking-wide">Sent today</p>
            <p class="text-3xl font-bold text-gray-900 mt-1">{{ number_format($sentToday) }}</p>
            @if($failedToday > 0)
                <p class="text-xs text-red-500 mt-1">{{ $failedToday }} failed</p>
            @else
                <p class="text-xs text-gray-300 mt-1">&nbsp;</p>
            @endif
        </div>

        <div class="bg-white rounded-xl border border-gray-200 p-4">
            <p class="text-xs font-medium text-gray-400 uppercase tracking-wide">This week</p>
            <p class="text-3xl font-bold text-gray-900 mt-1">{{ number_format($sentWeek) }}</p>
            <p class="text-xs text-gray-300 mt-1">&nbsp;</p>
        </div>

        <div class="bg-white rounded-xl border border-gray-200 p-4">
            <p class="text-xs font-medium text-gray-400 uppercase tracking-wide">This month</p>
            <p class="text-3xl font-bold text-gray-900 mt-1">{{ number_format($sentMonth) }}</p>
            <p class="text-xs text-gray-300 mt-1">&nbsp;</p>
        </div>

        <div class="bg-white rounded-xl border border-gray-200 p-4">
            <p class="text-xs font-medium text-gray-400 uppercase tracking-wide">Active users (30d)</p>
            <p class="text-3xl font-bold text-gray-900 mt-1">{{ number_format($activeUsers) }}</p>
            <p class="text-xs text-gray-300 mt-1">&nbsp;</p>
        </div>

    </div>

    {{-- Login stats --}}
    <div class="grid grid-cols-2 lg:grid-cols-4 gap-4 mb-6">

        <div class="bg-white rounded-xl border border-gray-200 p-4">
            <p class="text-xs font-medium text-gray-400 uppercase tracking-wide">Logins today</p>
            <p class="text-2xl font-bold text-gray-900 mt-1">{{ number_format($loginsToday) }}</p>
        </div>

        <div class="bg-white rounded-xl border border-gray-200 p-4">
            <p class="text-xs font-medium text-gray-400 uppercase tracking-wide">Failed logins today</p>
            <p class="text-2xl font-bold {{ $failedLogins > 0 ? 'text-red-600' : 'text-gray-900' }} mt-1">
                {{ number_format($failedLogins) }}
            </p>
        </div>

        <div class="bg-white rounded-xl border border-gray-200 p-4">
            <p class="text-xs font-medium text-gray-400 uppercase tracking-wide">Groups</p>
            <p class="text-2xl font-bold text-gray-900 mt-1">{{ number_format($groupCount) }}</p>
        </div>

        <div class="bg-white rounded-xl border border-gray-200 p-4">
            <p class="text-xs font-medium text-gray-400 uppercase tracking-wide">Mode</p>
            <p class="text-sm font-semibold mt-2 {{ config('mail-ui.admin_mode') ? 'text-orange-600' : 'text-gray-600' }}">
                {{ config('mail-ui.admin_mode') ? 'Panel' : 'Standalone' }}
            </p>
        </div>

    </div>

    {{-- ------------------------------------------------------------------ --}}
    {{-- Recent activity (two columns)                                        --}}
    {{-- ------------------------------------------------------------------ --}}
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">

        {{-- Recent sends --}}
        <div class="bg-white rounded-xl border border-gray-200 overflow-hidden">
            <div class="px-4 py-3 border-b border-gray-100 flex items-center justify-between">
                <h2 class="font-semibold text-gray-800 text-sm">Recent outbound mail</h2>
                <a href="{{ route('admin.logs.index') }}" class="text-xs text-orange-500 hover:text-orange-700">View all</a>
            </div>
            @if($recentSends->isEmpty())
                <div class="px-4 py-8 text-center text-sm text-gray-400">No messages sent yet.</div>
            @else
                <table class="w-full">
                    <tbody class="divide-y divide-gray-50">
                        @foreach($recentSends as $row)
                            <tr class="hover:bg-gray-50">
                                <td class="px-4 py-2.5">
                                    <p class="text-xs font-medium text-gray-800 truncate max-w-[180px]">{{ $row->email }}</p>
                                    <p class="text-[11px] text-gray-400 truncate max-w-[180px]">{{ $row->subject ?: '(no subject)' }}</p>
                                </td>
                                <td class="px-3 py-2.5 text-right whitespace-nowrap">
                                    @if($row->status === 'failed')
                                        <span class="inline-flex items-center px-1.5 py-0.5 rounded text-[10px] font-medium bg-red-50 text-red-600">failed</span>
                                    @endif
                                    <p class="text-[11px] text-gray-400">{{ $row->created_at->diffForHumans() }}</p>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            @endif
        </div>

        {{-- Recent logins --}}
        <div class="bg-white rounded-xl border border-gray-200 overflow-hidden">
            <div class="px-4 py-3 border-b border-gray-100 flex items-center justify-between">
                <h2 class="font-semibold text-gray-800 text-sm">Recent logins</h2>
                <a href="{{ route('admin.logs.index', ['tab' => 'login']) }}" class="text-xs text-orange-500 hover:text-orange-700">View all</a>
            </div>
            @if($recentLogins->isEmpty())
                <div class="px-4 py-8 text-center text-sm text-gray-400">No logins recorded yet.</div>
            @else
                <table class="w-full">
                    <tbody class="divide-y divide-gray-50">
                        @foreach($recentLogins as $row)
                            <tr class="hover:bg-gray-50 {{ $row->success ? '' : 'bg-red-50/50' }}">
                                <td class="px-4 py-2.5">
                                    <p class="text-xs font-medium {{ $row->success ? 'text-gray-800' : 'text-red-700' }} truncate max-w-[180px]">
                                        {{ $row->email }}
                                    </p>
                                    <p class="text-[11px] text-gray-400">{{ $row->ip }}</p>
                                </td>
                                <td class="px-3 py-2.5 text-right whitespace-nowrap">
                                    @if(!$row->success)
                                        <span class="inline-flex items-center px-1.5 py-0.5 rounded text-[10px] font-medium bg-red-100 text-red-600">failed</span>
                                    @else
                                        <span class="inline-flex items-center px-1.5 py-0.5 rounded text-[10px] font-medium bg-green-50 text-green-600">ok</span>
                                    @endif
                                    <p class="text-[11px] text-gray-400 mt-0.5">{{ $row->created_at->diffForHumans() }}</p>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            @endif
        </div>

    </div>

</div>
@endsection
