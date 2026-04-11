{{--
 | Opterius Mail - Open source webmail.
 | Admin — create mail account.
 | https://opterius.com
 |
 | Copyright (c) Host Server SRL (Opterius)
 | @license  AGPL-3.0  https://opensource.org/license/agpl-v3
 | @author   Iosif Gabriel Chimilevschi <office@opterius.com>
--}}
@extends(mailView('admin.layouts.admin'))

@section('title', 'New Account')

@section('content')
<div class="p-6 max-w-xl">

    {{-- Back + header --}}
    <div class="mb-6">
        <a href="{{ route('admin.accounts.index') }}"
           class="text-xs text-gray-400 hover:text-gray-700 transition-colors flex items-center gap-1 mb-3 w-fit">
            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
            </svg>
            Back to accounts
        </a>
        <h1 class="text-xl font-bold text-gray-900">New Mail Account</h1>
        <p class="text-sm text-gray-400 mt-0.5">Creates an IMAP mailbox. Configure your mail server to use these credentials.</p>
    </div>

    <form method="POST" action="{{ route('admin.accounts.store') }}" class="space-y-5">
        @csrf

        <div class="bg-white rounded-xl border border-gray-200 p-5 space-y-4">

            {{-- Email --}}
            <div>
                <label class="block text-xs font-medium text-gray-600 mb-1.5">Email address <span class="text-red-500">*</span></label>
                <div class="flex items-stretch gap-0">
                    <input type="text" name="local" required
                           value="{{ old('local') }}"
                           placeholder="user"
                           class="flex-1 min-w-0 border border-gray-300 rounded-l-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-orange-400">
                    <span class="flex items-center bg-gray-100 border border-l-0 border-gray-300 px-3 text-sm text-gray-500">@</span>
                    <select name="domain_id" required
                            class="border border-l-0 border-gray-300 rounded-r-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-orange-400 bg-white">
                        <option value="">Domain...</option>
                        @foreach($domains as $domain)
                            <option value="{{ $domain->id }}" {{ old('domain_id') == $domain->id ? 'selected' : '' }}>
                                {{ $domain->domain }}
                            </option>
                        @endforeach
                    </select>
                </div>
                @error('local') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
                @error('domain_id') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
            </div>

            {{-- Password --}}
            <div>
                <label class="block text-xs font-medium text-gray-600 mb-1.5">Password <span class="text-red-500">*</span></label>
                <input type="password" name="password" required autocomplete="new-password"
                       class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-orange-400">
                @error('password') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
            </div>

            <div>
                <label class="block text-xs font-medium text-gray-600 mb-1.5">Confirm password <span class="text-red-500">*</span></label>
                <input type="password" name="password_confirmation" required autocomplete="new-password"
                       class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-orange-400">
            </div>

            {{-- Quota --}}
            <div>
                <label class="block text-xs font-medium text-gray-600 mb-1.5">Quota (MB)</label>
                <div class="flex items-center gap-3">
                    <input type="number" name="quota_mb" min="1" max="1048576"
                           value="{{ old('quota_mb') }}"
                           placeholder="Leave empty for unlimited"
                           class="w-48 border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-orange-400">
                    <p class="text-xs text-gray-400">Empty = unlimited. E.g. 1024 = 1 GB.</p>
                </div>
                @error('quota_mb') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
            </div>

            {{-- Group --}}
            @if($groups->isNotEmpty())
                <div>
                    <label class="block text-xs font-medium text-gray-600 mb-1.5">Mail group</label>
                    <select name="group_id"
                            class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-orange-400 bg-white">
                        <option value="">No group</option>
                        @foreach($groups as $group)
                            <option value="{{ $group->id }}" {{ old('group_id') == $group->id ? 'selected' : '' }}>
                                {{ $group->name }}
                            </option>
                        @endforeach
                    </select>
                    <p class="text-xs text-gray-400 mt-1">Groups control sending limits for this account.</p>
                </div>
            @endif

        </div>

        @if($errors->any())
            <div class="p-3 rounded-lg bg-red-50 border border-red-200 text-sm text-red-700">
                <ul class="list-disc list-inside space-y-0.5">
                    @foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach
                </ul>
            </div>
        @endif

        <div class="flex justify-end gap-3">
            <a href="{{ route('admin.accounts.index') }}"
               class="px-4 py-2 text-sm text-gray-600 hover:text-gray-900">Cancel</a>
            <button type="submit"
                    class="px-6 py-2 bg-orange-500 hover:bg-orange-600 text-white text-sm font-medium rounded-lg transition-colors">
                Create account
            </button>
        </div>
    </form>

</div>
@endsection
