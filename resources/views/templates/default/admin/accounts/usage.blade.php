{{--
 | Opterius Mail - Open source webmail.
 | Admin — per-account disk usage report.
 | https://opterius.com
 |
 | Copyright (c) Host Server SRL (Opterius)
 | @license  AGPL-3.0  https://opensource.org/license/agpl-v3
 | @author   Iosif Gabriel Chimilevschi <office@opterius.com>
--}}
@extends(mailView('admin.layouts.admin'))

@section('title', 'Usage Report')

@section('content')
<div class="p-6">

    {{-- Header --}}
    <div class="flex items-center justify-between mb-6">
        <div>
            <h1 class="text-xl font-bold text-gray-900">Per-Account Usage Report</h1>
            <p class="text-sm text-gray-400 mt-0.5">
                Disk usage per mailbox.
                @if(!config('mail.vhosts_path'))
                    <span class="text-yellow-600">Set <code class="text-[13px] bg-gray-100 px-1 rounded">MAIL_VHOSTS_PATH</code> in .env to enable live disk usage.</span>
                @endif
            </p>
        </div>
        <a href="{{ route('admin.accounts.index') }}"
           class="flex items-center gap-1.5 px-3 py-1.5 text-sm text-gray-600 hover:bg-gray-100 rounded-lg transition-colors">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
            </svg>
            Back to Accounts
        </a>
    </div>

    @php
        function usageSortUrl(string $col, string $current, string $dir): string {
            $newDir = ($current === $col && $dir === 'asc') ? 'desc' : 'asc';
            return request()->fullUrlWithQuery(['sort' => $col, 'dir' => $newDir]);
        }
        function usageSortIcon(string $col, string $current, string $dir): string {
            if ($current !== $col) return '↕';
            return $dir === 'asc' ? '↑' : '↓';
        }
    @endphp

    @if($rows->isEmpty())
        <div class="bg-white rounded-xl border border-gray-200 px-6 py-16 text-center text-sm text-gray-400">
            No accounts found.
        </div>
    @else
        {{-- Summary bar --}}
        @php
            $over90  = $rows->filter(fn($r) => $r['pct'] !== null && $r['pct'] >= 90)->count();
            $over70  = $rows->filter(fn($r) => $r['pct'] !== null && $r['pct'] >= 70 && $r['pct'] < 90)->count();
        @endphp
        @if($over90 > 0 || $over70 > 0)
        <div class="flex gap-3 mb-4">
            @if($over90 > 0)
            <span class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg text-[13px] font-semibold bg-red-50 text-red-700 border border-red-200">
                <svg class="w-3.5 h-3.5" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/>
                </svg>
                {{ $over90 }} account{{ $over90 !== 1 ? 's' : '' }} over 90%
            </span>
            @endif
            @if($over70 > 0)
            <span class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg text-[13px] font-semibold bg-yellow-50 text-yellow-700 border border-yellow-200">
                ⚠ {{ $over70 }} account{{ $over70 !== 1 ? 's' : '' }} over 70%
            </span>
            @endif
        </div>
        @endif

        <div class="bg-white rounded-xl border border-gray-200 overflow-hidden">
            <table class="w-full">
                <thead>
                    <tr class="border-b border-gray-100 text-left">
                        <th class="px-4 py-3 text-[13px] font-semibold text-gray-500 uppercase tracking-wide">
                            <a href="{{ usageSortUrl('email', $sort, $dir) }}" class="hover:text-gray-800 flex items-center gap-1">
                                Email <span class="text-gray-400">{{ usageSortIcon('email', $sort, $dir) }}</span>
                            </a>
                        </th>
                        <th class="px-4 py-3 text-[13px] font-semibold text-gray-500 uppercase tracking-wide">
                            <a href="{{ usageSortUrl('used_mb', $sort, $dir) }}" class="hover:text-gray-800 flex items-center gap-1">
                                Used <span class="text-gray-400">{{ usageSortIcon('used_mb', $sort, $dir) }}</span>
                            </a>
                        </th>
                        <th class="px-4 py-3 text-[13px] font-semibold text-gray-500 uppercase tracking-wide">
                            <a href="{{ usageSortUrl('quota_mb', $sort, $dir) }}" class="hover:text-gray-800 flex items-center gap-1">
                                Quota <span class="text-gray-400">{{ usageSortIcon('quota_mb', $sort, $dir) }}</span>
                            </a>
                        </th>
                        <th class="px-4 py-3 text-[13px] font-semibold text-gray-500 uppercase tracking-wide w-48">
                            <a href="{{ usageSortUrl('pct', $sort, $dir) }}" class="hover:text-gray-800 flex items-center gap-1">
                                Usage <span class="text-gray-400">{{ usageSortIcon('pct', $sort, $dir) }}</span>
                            </a>
                        </th>
                        <th class="px-4 py-3 text-[13px] font-semibold text-gray-500 uppercase tracking-wide">Status</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-50">
                    @foreach($rows as $row)
                        @php
                            $account = $row['account'];
                            $usedMb  = $row['used_mb'];
                            $pct     = $row['pct'];
                            $barColor = $pct === null ? 'bg-gray-300'
                                      : ($pct >= 90 ? 'bg-red-500'
                                      : ($pct >= 70 ? 'bg-yellow-500' : 'bg-orange-500'));
                            $alertClass = $pct !== null && $pct >= 90
                                        ? 'bg-red-50'
                                        : ($pct !== null && $pct >= 70 ? 'bg-yellow-50' : '');
                        @endphp
                        <tr class="hover:bg-gray-50 {{ !$account->is_active ? 'opacity-60' : '' }} {{ $alertClass }}">
                            <td class="px-4 py-3 text-sm font-medium text-gray-900">
                                {{ $account->email }}
                                @if($pct !== null && $pct >= 90)
                                    <span class="ml-1.5 inline-block w-2 h-2 rounded-full bg-red-500" title="Over 90% full"></span>
                                @elseif($pct !== null && $pct >= 70)
                                    <span class="ml-1.5 inline-block w-2 h-2 rounded-full bg-yellow-500" title="Over 70% full"></span>
                                @endif
                            </td>
                            <td class="px-4 py-3 text-sm text-gray-600">
                                @if($usedMb !== null)
                                    {{ number_format($usedMb, 1) }} MB
                                @else
                                    <span class="text-gray-300">—</span>
                                @endif
                            </td>
                            <td class="px-4 py-3 text-sm text-gray-500">
                                {{ $account->quotaLabel() }}
                            </td>
                            <td class="px-4 py-3">
                                @if($pct !== null && $account->quota_mb !== null)
                                    <div class="flex items-center gap-2">
                                        <div class="flex-1 h-1.5 bg-gray-200 rounded-full overflow-hidden">
                                            <div class="{{ $barColor }} h-1.5 rounded-full" style="width: {{ $pct }}%"></div>
                                        </div>
                                        <span class="text-[13px] text-gray-500 w-9 text-right">{{ $pct }}%</span>
                                    </div>
                                @elseif($usedMb !== null)
                                    <span class="text-[13px] text-gray-400">No quota set</span>
                                @else
                                    <span class="text-[13px] text-gray-300">—</span>
                                @endif
                            </td>
                            <td class="px-4 py-3">
                                @if($account->is_active)
                                    <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[13px] font-medium bg-green-50 text-green-700">Active</span>
                                @else
                                    <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[13px] font-medium bg-gray-100 text-gray-500">Suspended</span>
                                @endif
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        <p class="text-[13px] text-gray-400 mt-3">
            {{ $rows->count() }} account{{ $rows->count() !== 1 ? 's' : '' }}.
            @if(config('mail.vhosts_path'))
                Disk usage read from <code class="bg-gray-100 px-1 rounded">{{ config('mail.vhosts_path') }}</code> via <code class="bg-gray-100 px-1 rounded">du -sm</code>.
            @endif
        </p>
    @endif

</div>
@endsection
