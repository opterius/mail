{{--
 | Opterius Mail - Open source webmail.
 | Admin — mail accounts list.
 | https://opterius.com
 |
 | Copyright (c) Host Server SRL (Opterius)
 | @license  AGPL-3.0  https://opensource.org/license/agpl-v3
 | @author   Iosif Gabriel Chimilevschi <office@opterius.com>
--}}
@extends(mailView('admin.layouts.admin'))

@section('title', 'Accounts')

@section('content')
<div class="p-6">

    {{-- Header --}}
    <div class="flex items-center justify-between mb-6">
        <div>
            <h1 class="text-xl font-bold text-gray-900">Mail Accounts</h1>
            <p class="text-sm text-gray-400 mt-0.5">
                @if($standaloneMode) Users who have logged into webmail. @else IMAP mailboxes managed by this server. @endif
            </p>
        </div>
        <div class="flex items-center gap-2">
            @if(!$standaloneMode)
                <a href="{{ route('admin.accounts.usage') }}"
                   class="flex items-center gap-1.5 px-3 py-2 text-sm text-gray-600 hover:bg-gray-100 border border-gray-200 rounded-lg transition-colors">
                    Usage report
                </a>
                <a href="{{ route('admin.accounts.create') }}"
                   class="flex items-center gap-1.5 px-4 py-2 bg-orange-500 hover:bg-orange-600 text-white text-sm font-medium rounded-lg transition-colors">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                    </svg>
                    New account
                </a>
            @else
                <form method="GET" action="{{ route('admin.accounts.index') }}">
                    <input type="text" name="search" value="{{ request('search') }}" placeholder="Search email…"
                           class="border border-gray-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-orange-400">
                </form>
            @endif
        </div>
    </div>

    {{-- Domain filter (panel mode only) --}}
    @if(!$standaloneMode && $domains->isNotEmpty())
        <div class="mb-4 flex items-center gap-2 flex-wrap">
            <a href="{{ route('admin.accounts.index') }}"
               class="px-3 py-1.5 rounded-lg text-xs font-medium transition-colors {{ !request('domain') ? 'bg-orange-500 text-white' : 'bg-white border border-gray-200 text-gray-600 hover:bg-gray-50' }}">
                All domains
            </a>
            @foreach($domains as $d)
                <a href="{{ route('admin.accounts.index', ['domain' => $d]) }}"
                   class="px-3 py-1.5 rounded-lg text-xs font-medium transition-colors {{ request('domain') === $d ? 'bg-orange-500 text-white' : 'bg-white border border-gray-200 text-gray-600 hover:bg-gray-50' }}">
                    {{ $d }}
                </a>
            @endforeach
        </div>
    @endif

    {{-- Table --}}
    @if($accounts->isEmpty())
        <div class="bg-white rounded-xl border border-gray-200 px-6 py-16 text-center text-sm text-gray-400">
            No accounts found.
            @if(!$standaloneMode && !request('domain'))
                <a href="{{ route('admin.accounts.create') }}" class="text-orange-500 hover:text-orange-700">Create the first one.</a>
            @endif
        </div>
    @else
        <div class="bg-white rounded-xl border border-gray-200 overflow-hidden">
            <table class="w-full">
                <thead>
                    <tr class="border-b border-gray-100 text-left">
                        <th class="px-4 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wide">Email</th>
                        <th class="px-4 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wide">Group</th>
                        @if($standaloneMode)
                            <th class="px-4 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wide">Display name</th>
                            <th class="px-4 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wide">Joined</th>
                        @else
                            <th class="px-4 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wide">Quota</th>
                            <th class="px-4 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wide">Status</th>
                        @endif
                        <th class="px-4 py-3"></th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-50">
                    @foreach($accounts as $account)
                        <tr class="hover:bg-gray-50">
                            <td class="px-4 py-3 text-sm font-medium text-gray-900">{{ $account->email }}</td>
                            <td class="px-4 py-3 text-sm text-gray-500">{{ $account->group?->name ?? '—' }}</td>
                            @if($standaloneMode)
                                <td class="px-4 py-3 text-sm text-gray-500">{{ $account->display_name ?: '—' }}</td>
                                <td class="px-4 py-3 text-sm text-gray-500">{{ $account->created_at?->format('Y-m-d') ?? '—' }}</td>
                            @else
                                <td class="px-4 py-3 text-sm text-gray-500">{{ $account->quotaLabel() }}</td>
                                <td class="px-4 py-3">
                                    @if($account->is_active)
                                        <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-green-50 text-green-700">Active</span>
                                    @else
                                        <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-gray-100 text-gray-500">Suspended</span>
                                    @endif
                                </td>
                            @endif
                            <td class="px-4 py-3 text-right whitespace-nowrap">
                                @if(!$standaloneMode)
                                    <a href="{{ route('admin.accounts.edit', $account) }}"
                                       class="text-xs text-gray-400 hover:text-gray-800 transition-colors mr-3">Edit</a>
                                    <form method="POST" action="{{ route('admin.accounts.toggle', $account) }}" class="inline mr-3">
                                        @csrf
                                        <button type="submit"
                                                class="text-xs {{ $account->is_active ? 'text-yellow-500 hover:text-yellow-700' : 'text-green-500 hover:text-green-700' }} transition-colors">
                                            {{ $account->is_active ? 'Suspend' : 'Enable' }}
                                        </button>
                                    </form>
                                    <form method="POST" action="{{ route('admin.accounts.destroy', $account) }}" class="inline"
                                          onsubmit="return confirm('Delete account {{ addslashes($account->email) }}? This cannot be undone.')">
                                        @csrf @method('DELETE')
                                        <button type="submit" class="text-xs text-red-400 hover:text-red-600 transition-colors">Delete</button>
                                    </form>
                                @endif
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        @if($accounts->hasPages())
            <div class="mt-4">{{ $accounts->links() }}</div>
        @endif
    @endif

</div>
@endsection
