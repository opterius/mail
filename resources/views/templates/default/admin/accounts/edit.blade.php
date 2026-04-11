{{--
 | Opterius Mail - Open source webmail.
 | Admin — edit mail account.
 | https://opterius.com
 |
 | Copyright (c) Host Server SRL (Opterius)
 | @license  AGPL-3.0  https://opensource.org/license/agpl-v3
 | @author   Iosif Gabriel Chimilevschi <office@opterius.com>
--}}
@extends(mailView('admin.layouts.admin'))

@section('title', 'Edit Account')

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
        <h1 class="text-xl font-bold text-gray-900">Edit Account</h1>
        <p class="text-sm text-gray-400 mt-0.5 font-mono">{{ $mail_account->email }}</p>
    </div>

    <form method="POST" action="{{ route('admin.accounts.update', $mail_account) }}" class="space-y-5">
        @csrf @method('PUT')

        <div class="bg-white rounded-xl border border-gray-200 p-5 space-y-4">

            {{-- Email (read-only) --}}
            <div>
                <label class="block text-xs font-medium text-gray-600 mb-1.5">Email address</label>
                <input type="text" disabled value="{{ $mail_account->email }}"
                       class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm bg-gray-50 text-gray-500 cursor-not-allowed">
                <p class="text-xs text-gray-400 mt-1">Email cannot be changed. Delete and recreate the account to change it.</p>
            </div>

            {{-- Password (optional) --}}
            <div>
                <label class="block text-xs font-medium text-gray-600 mb-1.5">New password</label>
                <input type="password" name="password" autocomplete="new-password"
                       class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-orange-400"
                       placeholder="Leave blank to keep current password">
                @error('password') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
            </div>

            <div>
                <label class="block text-xs font-medium text-gray-600 mb-1.5">Confirm new password</label>
                <input type="password" name="password_confirmation" autocomplete="new-password"
                       class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-orange-400"
                       placeholder="Leave blank to keep current password">
            </div>

            {{-- Quota --}}
            <div>
                <label class="block text-xs font-medium text-gray-600 mb-1.5">Quota (MB)</label>
                <div class="flex items-center gap-3">
                    <input type="number" name="quota_mb" min="1" max="1048576"
                           value="{{ old('quota_mb', $mail_account->quota_mb) }}"
                           placeholder="Unlimited"
                           class="w-48 border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-orange-400">
                    <p class="text-xs text-gray-400">Empty = unlimited.</p>
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
                            <option value="{{ $group->id }}"
                                {{ old('group_id', $mail_account->group_id) == $group->id ? 'selected' : '' }}>
                                {{ $group->name }}
                            </option>
                        @endforeach
                    </select>
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
                Save changes
            </button>
        </div>
    </form>

</div>
@endsection
