{{--
 | Opterius Mail - Open source webmail.
 | Admin — activity logs.
 | https://opterius.com
 |
 | Copyright (c) Host Server SRL (Opterius)
 | @license  AGPL-3.0  https://opensource.org/license/agpl-v3
 | @author   Iosif Gabriel Chimilevschi <office@opterius.com>
--}}
@extends(mailView('admin.layouts.admin'))

@section('title', 'Logs')

@section('content')
<div class="p-6">

    {{-- Header --}}
    <div class="mb-6">
        <h1 class="text-xl font-bold text-gray-900">Logs</h1>
        <p class="text-sm text-gray-400 mt-0.5">Outbound mail and login history.</p>
    </div>

    {{-- Tabs --}}
    <div class="flex items-center gap-1 mb-4 border-b border-gray-200">
        <a href="{{ route('admin.logs.index', ['tab' => 'send']) }}"
           class="px-4 py-2 text-sm font-medium border-b-2 transition-colors -mb-px
                  {{ $tab === 'send' ? 'border-orange-500 text-orange-600' : 'border-transparent text-gray-500 hover:text-gray-800' }}">
            Outbound mail
        </a>
        <a href="{{ route('admin.logs.index', ['tab' => 'login']) }}"
           class="px-4 py-2 text-sm font-medium border-b-2 transition-colors -mb-px
                  {{ $tab === 'login' ? 'border-orange-500 text-orange-600' : 'border-transparent text-gray-500 hover:text-gray-800' }}">
            Login activity
        </a>
    </div>

    {{-- Table --}}
    <div class="bg-white rounded-xl border border-gray-200 overflow-hidden">
        @if($entries->isEmpty())
            <div class="px-6 py-16 text-center text-sm text-gray-400">No records yet.</div>
        @elseif($tab === 'send')
            <table class="w-full">
                <thead>
                    <tr class="border-b border-gray-100 text-left">
                        <th class="px-4 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wide">Sender</th>
                        <th class="px-4 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wide">Subject</th>
                        <th class="px-4 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wide">Rcpt</th>
                        <th class="px-4 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wide">Status</th>
                        <th class="px-4 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wide">IP</th>
                        <th class="px-4 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wide">Date</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-50">
                    @foreach($entries as $row)
                        <tr class="hover:bg-gray-50 {{ $row->status === 'failed' ? 'bg-red-50/40' : '' }}">
                            <td class="px-4 py-2.5 text-sm text-gray-800">{{ $row->email }}</td>
                            <td class="px-4 py-2.5 text-sm text-gray-600 max-w-xs truncate">{{ $row->subject ?: '—' }}</td>
                            <td class="px-4 py-2.5 text-sm text-gray-600">{{ $row->recipient_count }}</td>
                            <td class="px-4 py-2.5">
                                @if($row->status === 'failed')
                                    <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-700">failed</span>
                                @else
                                    <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-green-50 text-green-700">sent</span>
                                @endif
                            </td>
                            <td class="px-4 py-2.5 text-xs text-gray-400 font-mono">{{ $row->ip ?? '—' }}</td>
                            <td class="px-4 py-2.5 text-xs text-gray-400 whitespace-nowrap">{{ $row->created_at->format('Y-m-d H:i') }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        @else
            <table class="w-full">
                <thead>
                    <tr class="border-b border-gray-100 text-left">
                        <th class="px-4 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wide">Email</th>
                        <th class="px-4 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wide">Status</th>
                        <th class="px-4 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wide">IP</th>
                        <th class="px-4 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wide">User agent</th>
                        <th class="px-4 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wide">Date</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-50">
                    @foreach($entries as $row)
                        <tr class="hover:bg-gray-50 {{ !$row->success ? 'bg-red-50/40' : '' }}">
                            <td class="px-4 py-2.5 text-sm font-medium {{ $row->success ? 'text-gray-800' : 'text-red-700' }}">
                                {{ $row->email }}
                            </td>
                            <td class="px-4 py-2.5">
                                @if(!$row->success)
                                    <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-700">failed</span>
                                @else
                                    <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-green-50 text-green-700">success</span>
                                @endif
                            </td>
                            <td class="px-4 py-2.5 text-xs text-gray-400 font-mono">{{ $row->ip ?? '—' }}</td>
                            <td class="px-4 py-2.5 text-xs text-gray-400 max-w-xs truncate">{{ $row->user_agent ?? '—' }}</td>
                            <td class="px-4 py-2.5 text-xs text-gray-400 whitespace-nowrap">{{ $row->created_at->format('Y-m-d H:i') }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        @endif
    </div>

    {{-- Pagination --}}
    @if($entries->hasPages())
        <div class="mt-4">
            {{ $entries->links() }}
        </div>
    @endif

</div>
@endsection
