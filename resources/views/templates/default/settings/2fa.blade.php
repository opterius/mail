{{--
 | Opterius Mail - Open source webmail.
 | Modern, fast and responsive webmail that works with any IMAP/SMTP server.
 | https://opterius.com
 |
 | Copyright (c) Host Server SRL (Opterius)
 | @license  AGPL-3.0  https://opensource.org/license/agpl-v3
 | @author   Iosif Gabriel Chimilevschi <office@opterius.com>
--}}
@extends(mailView('layouts.app'))

@section('title', 'Two-Factor Authentication')

@section('content')
<div class="flex flex-col h-full">

    {{-- Toolbar --}}
    <div class="flex items-center px-6 py-3 border-b border-gray-100 dark:border-gray-700 bg-white dark:bg-gray-900 sticky top-0 z-10">
        <a href="{{ route('settings') }}"
           class="flex items-center gap-1.5 px-3 py-1.5 text-sm text-gray-600 dark:text-gray-400 hover:bg-gray-100 dark:hover:bg-gray-800 rounded-lg transition-colors mr-2">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
            </svg>
            Settings
        </a>
        <h1 class="text-sm font-semibold text-gray-800 dark:text-gray-200">Two-Factor Authentication</h1>
    </div>

    <div class="flex-1 overflow-auto px-6 py-6 max-w-lg">

        @if($errors->any())
            <div class="mb-4 px-4 py-3 bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-700 rounded-lg text-sm text-red-700 dark:text-red-400">
                {{ $errors->first() }}
            </div>
        @endif

        @if(session('success'))
            <div class="mb-4 px-4 py-3 bg-green-50 dark:bg-green-900/20 border border-green-200 dark:border-green-700 rounded-lg text-sm text-green-700 dark:text-green-400">
                {{ session('success') }}
            </div>
        @endif

        @if($enabled)
            {{-- 2FA is enabled --}}
            <div class="flex items-center gap-3 p-4 bg-green-50 dark:bg-green-900/20 border border-green-200 dark:border-green-700 rounded-lg mb-6">
                <svg class="w-5 h-5 text-green-600 dark:text-green-400 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/>
                </svg>
                <p class="text-sm font-medium text-green-800 dark:text-green-300">Two-factor authentication is active</p>
            </div>

            {{-- Fresh recovery codes flash --}}
            @if(session('recovery_codes_fresh'))
                <div class="mb-6 p-4 bg-yellow-50 dark:bg-yellow-900/20 border border-yellow-200 dark:border-yellow-700 rounded-lg">
                    <p class="text-sm font-semibold text-yellow-800 dark:text-yellow-300 mb-2">Save your recovery codes</p>
                    <p class="text-xs text-yellow-700 dark:text-yellow-400 mb-3">Store these somewhere safe. Each code can only be used once to bypass 2FA if you lose access to your authenticator app.</p>
                    <div class="grid grid-cols-2 gap-1.5">
                        @foreach(session('recovery_codes_fresh') as $code)
                            <code class="font-mono text-sm bg-white dark:bg-gray-900 border border-yellow-300 dark:border-yellow-700 px-3 py-1.5 rounded text-center text-gray-800 dark:text-gray-200">{{ $code }}</code>
                        @endforeach
                    </div>
                </div>
            @endif

            {{-- Recovery codes (already saved) --}}
            @if(!session('recovery_codes_fresh') && !empty($recoveryCodes))
                <section class="mb-6">
                    <h2 class="text-xs font-semibold text-gray-400 uppercase tracking-wider mb-3">Recovery Codes</h2>
                    <div class="grid grid-cols-2 gap-1.5">
                        @foreach($recoveryCodes as $code)
                            <code class="font-mono text-sm bg-gray-50 dark:bg-gray-800 border border-gray-200 dark:border-gray-700 px-3 py-1.5 rounded text-center text-gray-800 dark:text-gray-200">{{ $code }}</code>
                        @endforeach
                    </div>
                    <p class="text-xs text-gray-400 dark:text-gray-500 mt-2">{{ count($recoveryCodes) }} code(s) remaining.</p>
                </section>
            @endif

            {{-- Disable 2FA --}}
            <section x-data="{ open: false }">
                <h2 class="text-xs font-semibold text-gray-400 uppercase tracking-wider mb-3">Disable Two-Factor Authentication</h2>
                <button @click="open = !open"
                        class="px-4 py-2 text-sm text-red-600 border border-red-200 hover:bg-red-50 dark:hover:bg-red-900/20 rounded-lg transition-colors">
                    Disable 2FA
                </button>
                <div x-show="open" x-cloak class="mt-4 p-4 bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-700 rounded-lg">
                    <p class="text-sm text-red-700 dark:text-red-400 mb-3">Enter your current TOTP code or a recovery code to confirm.</p>
                    <form method="POST" action="{{ route('2fa.disable') }}" class="flex items-center gap-3">
                        @csrf
                        <input name="code" type="text" placeholder="123456 or recovery code" required
                               class="flex-1 px-3 py-2 text-sm border border-red-300 dark:border-red-700 rounded-lg outline-none focus:ring-2 focus:ring-red-400 bg-white dark:bg-gray-900 text-gray-800 dark:text-gray-200">
                        <button type="submit"
                                class="px-4 py-2 text-sm font-medium bg-red-600 hover:bg-red-700 text-white rounded-lg transition-colors">
                            Confirm Disable
                        </button>
                    </form>
                </div>
            </section>

        @else
            {{-- Setup 2FA --}}
            <p class="text-sm text-gray-600 dark:text-gray-400 mb-6">
                Scan the QR code below with your authenticator app (Google Authenticator, Authy, 1Password, etc.),
                then enter the 6-digit code to activate two-factor authentication.
            </p>

            {{-- QR Code (rendered by JS) --}}
            <div class="mb-6 flex flex-col items-center">
                <div id="qr-code" class="bg-white p-3 rounded-xl border border-gray-200 dark:border-gray-700 inline-block mb-3"></div>
                <p class="text-xs text-gray-400 dark:text-gray-500">Or enter this key manually:</p>
                <code class="mt-1 font-mono text-sm bg-gray-100 dark:bg-gray-800 px-4 py-2 rounded-lg text-gray-800 dark:text-gray-200 tracking-widest break-all">
                    {{ $secret }}
                </code>
            </div>

            {{-- Confirm code --}}
            <form method="POST" action="{{ route('2fa.enable') }}">
                @csrf
                <div class="mb-4">
                    <label for="code" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1.5">
                        Verification code
                    </label>
                    <input id="code" name="code" type="text" inputmode="numeric" autocomplete="one-time-code"
                           maxlength="6" required autofocus placeholder="000000"
                           class="w-full px-3 py-2 text-sm border border-gray-200 dark:border-gray-600 rounded-lg outline-none focus:ring-2 focus:ring-orange-400 bg-white dark:bg-gray-800 text-gray-800 dark:text-gray-200 text-center tracking-widest text-lg">
                </div>
                <button type="submit"
                        class="px-5 py-2 text-sm font-medium bg-orange-500 hover:bg-orange-600 text-white rounded-lg transition-colors">
                    Enable Two-Factor Auth
                </button>
            </form>

            <script src="https://cdn.jsdelivr.net/npm/qrcode@1/build/qrcode.min.js"></script>
            <script>
            QRCode.toCanvas(document.createElement('canvas'), {{ Js::from($qrUri) }}, { width: 200 }, function (err, canvas) {
                if (!err) {
                    document.getElementById('qr-code').appendChild(canvas);
                }
            });
            </script>
        @endif

    </div>

</div>
@endsection
