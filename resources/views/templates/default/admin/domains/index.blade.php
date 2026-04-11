{{--
 | Opterius Mail - Open source webmail.
 | Admin — domain management.
 | https://opterius.com
 |
 | Copyright (c) Host Server SRL (Opterius)
 | @license  AGPL-3.0  https://opensource.org/license/agpl-v3
 | @author   Iosif Gabriel Chimilevschi <office@opterius.com>
--}}
@extends(mailView('admin.layouts.admin'))

@section('title', 'Domains')

@section('content')
<div class="p-6" x-data="{ showForm: false }">

    {{-- Header --}}
    <div class="flex items-center justify-between mb-6">
        <div>
            <h1 class="text-xl font-bold text-gray-900">Domains</h1>
            <p class="text-sm text-gray-400 mt-0.5">Manage hosted mail domains, accounts, and aliases.</p>
        </div>
        <button @click="showForm = !showForm"
                class="flex items-center gap-1.5 px-4 py-2 bg-orange-500 hover:bg-orange-600 text-white text-sm font-medium rounded-lg transition-colors">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
            </svg>
            Add domain
        </button>
    </div>

    {{-- Add form --}}
    <div x-show="showForm" x-cloak class="mb-6">
        <div class="bg-white rounded-xl border border-gray-200 p-5">
            <h2 class="font-semibold text-gray-800 text-sm mb-4">Add new domain</h2>
            <form method="POST" action="{{ route('admin.domains.store') }}" class="flex items-end gap-3 flex-wrap">
                @csrf
                <div class="flex-1 min-w-48">
                    <label class="block text-xs font-medium text-gray-600 mb-1.5">Domain name <span class="text-red-500">*</span></label>
                    <input type="text" name="domain" required placeholder="example.com"
                           value="{{ old('domain') }}"
                           class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-orange-400">
                    @error('domain') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
                </div>
                <button type="submit"
                        class="px-5 py-2 bg-orange-500 hover:bg-orange-600 text-white text-sm font-medium rounded-lg transition-colors whitespace-nowrap">
                    Add domain
                </button>
            </form>
        </div>
    </div>

    {{-- Table --}}
    @if($domains->isEmpty())
        <div class="bg-white rounded-xl border border-gray-200 px-6 py-16 text-center text-sm text-gray-400">
            No domains yet. Add one above.
        </div>
    @else
        <div class="bg-white rounded-xl border border-gray-200 overflow-hidden">
            <table class="w-full">
                <thead>
                    <tr class="border-b border-gray-100 text-left">
                        <th class="px-4 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wide">Domain</th>
                        <th class="px-4 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wide">Accounts</th>
                        <th class="px-4 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wide">Aliases</th>
                        <th class="px-4 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wide">Status</th>
                        <th class="px-4 py-3"></th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-50">
                    @foreach($domains as $domain)
                        <tr class="hover:bg-gray-50">
                            <td class="px-4 py-3 font-medium text-gray-900 text-sm">
                                {{ $domain->domain }}
                            </td>
                            <td class="px-4 py-3 text-sm text-gray-500">
                                <a href="{{ route('admin.accounts.index', ['domain' => $domain->domain]) }}"
                                   class="hover:text-orange-600 transition-colors">
                                    {{ $domain->accounts_count }}
                                </a>
                            </td>
                            <td class="px-4 py-3 text-sm text-gray-500">
                                <a href="{{ route('admin.aliases.index', ['domain' => $domain->domain]) }}"
                                   class="hover:text-orange-600 transition-colors">
                                    {{ $domain->aliases_count }}
                                </a>
                            </td>
                            <td class="px-4 py-3">
                                @if($domain->is_active)
                                    <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-green-50 text-green-700">Active</span>
                                @else
                                    <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-gray-100 text-gray-500">Disabled</span>
                                @endif
                            </td>
                            <td class="px-4 py-3 text-right whitespace-nowrap">
                                <a href="{{ route('admin.domains.dns', $domain) }}"
                                   class="text-xs text-gray-400 hover:text-gray-800 transition-colors mr-3">DNS check</a>
                                <form method="POST" action="{{ route('admin.domains.toggle', $domain) }}" class="inline mr-3">
                                    @csrf
                                    <button type="submit"
                                            class="text-xs {{ $domain->is_active ? 'text-yellow-500 hover:text-yellow-700' : 'text-green-500 hover:text-green-700' }} transition-colors">
                                        {{ $domain->is_active ? 'Disable' : 'Enable' }}
                                    </button>
                                </form>
                                <form method="POST" action="{{ route('admin.domains.destroy', $domain) }}" class="inline"
                                      onsubmit="return confirm('Delete {{ $domain->domain }} and all its accounts and aliases?')">
                                    @csrf @method('DELETE')
                                    <button type="submit" class="text-xs text-red-400 hover:text-red-600 transition-colors">Delete</button>
                                </form>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        @if($domains->hasPages())
            <div class="mt-4">{{ $domains->links() }}</div>
        @endif
    @endif

</div>
@endsection
