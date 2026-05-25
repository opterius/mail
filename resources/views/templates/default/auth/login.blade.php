{{--
 | Opterius Mail - Open source webmail.
 | Modern, fast and responsive webmail that works with any IMAP/SMTP server.
 | https://opterius.com
 |
 | Copyright (c) Host Server SRL (Opterius)
 | @license  AGPL-3.0  https://opensource.org/license/agpl-v3
 | @author   Iosif Gabriel Chimilevschi <office@opterius.com>
--}}
@extends(mailView('layouts.auth'))

@php $webmailName = \App\Models\AdminSetting::get('webmail_name') ?: 'Webmail'; @endphp
@section('title', 'Sign in — ' . $webmailName)

@section('content')
<div class="text-center mb-8">
    <div class="inline-flex items-center justify-center w-12 h-12 rounded-xl bg-orange-500 mb-4">
        <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                  d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
        </svg>
    </div>
    <h1 class="text-2xl font-bold text-gray-900">{{ $webmailName }}</h1>
    <p class="text-sm text-gray-500 mt-1">Sign in with your email account</p>
</div>

<div class="bg-white rounded-2xl shadow-sm border border-gray-200 p-8">
    @if ($errors->any())
        <div class="mb-4 p-3 rounded-lg bg-red-50 border border-red-200 text-sm text-red-700">
            {{ $errors->first() }}
        </div>
    @endif

    <form method="POST" action="{{ route('login.submit') }}">
        @csrf

        <div class="mb-4">
            <label for="email" class="block text-sm font-medium text-gray-700 mb-1">Email address</label>
            <input id="email" name="email" type="email" autocomplete="email" required
                   value="{{ old('email') }}"
                   class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-orange-500 focus:border-transparent @error('email') border-red-400 @enderror">
        </div>

        <div class="mb-6">
            <label for="password" class="block text-sm font-medium text-gray-700 mb-1">Password</label>
            <input id="password" name="password" type="password" autocomplete="current-password" required
                   class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-orange-500 focus:border-transparent">
        </div>

        <button type="submit"
                class="w-full py-2.5 px-4 bg-orange-500 hover:bg-orange-600 text-white font-medium rounded-lg text-sm transition-colors">
            Sign in
        </button>
    </form>
</div>

<p class="text-center text-[13px] text-gray-400 mt-6">{{ $webmailName }}</p>
@endsection
