{{--
 | Opterius Mail - Open source webmail.
 | Admin — global settings.
 | https://opterius.com
 |
 | Copyright (c) Host Server SRL (Opterius)
 | @license  AGPL-3.0  https://opensource.org/license/agpl-v3
 | @author   Iosif Gabriel Chimilevschi <office@opterius.com>
--}}
@extends(mailView('admin.layouts.admin'))

@section('title', 'Settings')

@section('content')
<div class="p-6 max-w-2xl">

    <div class="mb-6">
        <h1 class="text-xl font-bold text-gray-900">Settings</h1>
        <p class="text-sm text-gray-400 mt-0.5">Global defaults. Per-user limits are set via Groups.</p>
    </div>

    <form method="POST" action="{{ route('admin.settings.update') }}" class="space-y-6">
        @csrf

        {{-- General --}}
        <div class="bg-white rounded-xl border border-gray-200 overflow-hidden">
            <div class="px-5 py-3 border-b border-gray-100">
                <h2 class="font-semibold text-gray-800 text-sm">General</h2>
            </div>
            <div class="px-5 py-4 space-y-4">
                <div>
                    <label class="block text-xs font-medium text-gray-600 mb-1.5">Webmail display name</label>
                    <input type="text" name="webmail_name"
                           value="{{ old('webmail_name', $settings['webmail_name'] ?? 'Opterius Mail') }}"
                           maxlength="100"
                           class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-orange-400 focus:border-transparent"
                           placeholder="Opterius Mail">
                    <p class="text-xs text-gray-400 mt-1">Shown in browser title and email footer.</p>
                </div>
            </div>
        </div>

        {{-- Default sending limits --}}
        <div class="bg-white rounded-xl border border-gray-200 overflow-hidden">
            <div class="px-5 py-3 border-b border-gray-100">
                <h2 class="font-semibold text-gray-800 text-sm">Default sending limits</h2>
                <p class="text-xs text-gray-400 mt-0.5">Applied to users with no group assigned. Leave blank for unlimited.</p>
            </div>
            <div class="px-5 py-4">
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-xs font-medium text-gray-600 mb-1.5">Emails per hour</label>
                        <input type="number" name="default_hourly_limit" min="1"
                               value="{{ old('default_hourly_limit', $settings['default_hourly_limit'] ?? '') }}"
                               class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-orange-400 focus:border-transparent"
                               placeholder="Unlimited">
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-gray-600 mb-1.5">Emails per day</label>
                        <input type="number" name="default_daily_limit" min="1"
                               value="{{ old('default_daily_limit', $settings['default_daily_limit'] ?? '') }}"
                               class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-orange-400 focus:border-transparent"
                               placeholder="Unlimited">
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-gray-600 mb-1.5">Emails per week</label>
                        <input type="number" name="default_weekly_limit" min="1"
                               value="{{ old('default_weekly_limit', $settings['default_weekly_limit'] ?? '') }}"
                               class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-orange-400 focus:border-transparent"
                               placeholder="Unlimited">
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-gray-600 mb-1.5">Emails per month</label>
                        <input type="number" name="default_monthly_limit" min="1"
                               value="{{ old('default_monthly_limit', $settings['default_monthly_limit'] ?? '') }}"
                               class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-orange-400 focus:border-transparent"
                               placeholder="Unlimited">
                    </div>
                </div>

                <div class="mt-4">
                    <label class="block text-xs font-medium text-gray-600 mb-1.5">Max recipients per message</label>
                    <input type="number" name="default_max_recipients" min="1" max="9999"
                           value="{{ old('default_max_recipients', $settings['default_max_recipients'] ?? '') }}"
                           class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-orange-400 focus:border-transparent"
                           placeholder="Unlimited">
                </div>
            </div>
        </div>

        {{-- Deployment info (read-only) --}}
        <div class="bg-white rounded-xl border border-gray-200 overflow-hidden">
            <div class="px-5 py-3 border-b border-gray-100">
                <h2 class="font-semibold text-gray-800 text-sm">Deployment</h2>
            </div>
            <div class="px-5 py-4 space-y-2 text-sm text-gray-600">
                <div class="flex items-center justify-between">
                    <span class="text-gray-500">Mode</span>
                    <span class="font-medium {{ config('mail-ui.admin_mode') ? 'text-orange-600' : 'text-gray-800' }}">
                        {{ config('mail-ui.admin_mode') ? 'Panel-integrated (MAIL_ADMIN=true)' : 'Standalone (MAIL_ADMIN=false)' }}
                    </span>
                </div>
                <div class="flex items-center justify-between">
                    <span class="text-gray-500">Active UI template</span>
                    <span class="font-medium text-gray-800 font-mono">{{ config('mail-ui.template') }}</span>
                </div>
                <div class="flex items-center justify-between">
                    <span class="text-gray-500">Laravel version</span>
                    <span class="font-medium text-gray-800">{{ app()->version() }}</span>
                </div>
            </div>
        </div>

        {{-- Validation errors --}}
        @if ($errors->any())
            <div class="p-3 rounded-lg bg-red-50 border border-red-200 text-sm text-red-700">
                <ul class="list-disc list-inside space-y-0.5">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <div class="flex justify-end">
            <button type="submit"
                    class="px-6 py-2 bg-orange-500 hover:bg-orange-600 text-white text-sm font-medium rounded-lg transition-colors">
                Save settings
            </button>
        </div>
    </form>

</div>
@endsection
