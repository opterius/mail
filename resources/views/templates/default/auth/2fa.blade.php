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

@section('title', 'Two-Factor Auth — Opterius Mail')

@section('content')
<div class="text-center mb-8">
    <h1 class="text-2xl font-bold text-gray-900">Two-factor authentication</h1>
    <p class="text-sm text-gray-500 mt-1">Enter the code from your authenticator app</p>
</div>

<div class="bg-white rounded-2xl shadow-sm border border-gray-200 p-8">
    @if ($errors->any())
        <div class="mb-4 p-3 rounded-lg bg-red-50 border border-red-200 text-sm text-red-700">
            {{ $errors->first() }}
        </div>
    @endif

    <form method="POST" action="{{ route('2fa.verify') }}">
        @csrf
        <div class="mb-6">
            <label for="code" class="block text-sm font-medium text-gray-700 mb-1">Authentication code</label>
            <input id="code" name="code" type="text" inputmode="numeric" autocomplete="one-time-code"
                   maxlength="6" required autofocus
                   class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm text-center tracking-widest text-lg focus:outline-none focus:ring-2 focus:ring-orange-500 focus:border-transparent">
        </div>
        <button type="submit"
                class="w-full py-2.5 px-4 bg-orange-500 hover:bg-orange-600 text-white font-medium rounded-lg text-sm transition-colors">
            Verify
        </button>
    </form>
</div>
@endsection
