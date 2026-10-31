{{--
 | Opterius Mail - Open source webmail.
 | Modern, fast and responsive webmail that works with any IMAP/SMTP server.
 | https://opterius.com
 |
 | Copyright (c) Host Server SRL (Opterius)
 | @license  AGPL-3.0  https://opensource.org/license/agpl-v3
 | @author   Iosif Gabriel Chimilevschi <office@opterius.com>
--}}
@extends(mailView('admin.layouts.admin'))

@section('title', 'Security')

@section('content')
<div class="p-6 max-w-2xl">

    <div class="mb-6">
        <h1 class="text-xl font-bold text-gray-900">Security</h1>
        <p class="text-sm text-gray-400 mt-0.5">Two-factor authentication for the admin account <strong>{{ Auth::guard('admin')->user()->username }}</strong>.</p>
    </div>

    @if ($errors->any())
        <div class="mb-4 px-4 py-3 bg-red-50 border border-red-200 rounded-lg text-red-700 text-sm">
            @foreach ($errors->all() as $error)<p>{{ $error }}</p>@endforeach
        </div>
    @endif

    {{-- Recovery codes: shown once, right after the second factor was turned on --}}
    @if (!empty($freshCodes))
        <div class="mb-6 bg-amber-50 border border-amber-200 rounded-xl p-5">
            <h2 class="font-semibold text-amber-900 text-sm">Save your recovery codes now</h2>
            <p class="text-[13px] text-amber-800 mt-1">Each code works once, if you lose your phone. They are not shown again.</p>
            <div class="grid grid-cols-2 gap-2 mt-3 font-mono text-sm text-gray-900">
                @foreach ($freshCodes as $c)<span class="bg-white border border-amber-200 rounded px-2 py-1">{{ $c }}</span>@endforeach
            </div>
        </div>
    @endif

    @if ($enabled)
        <div class="bg-white rounded-xl border border-gray-200 overflow-hidden mb-6">
            <div class="px-5 py-3 border-b border-gray-100 flex items-center justify-between">
                <h2 class="font-semibold text-gray-800 text-sm">Two-factor authentication</h2>
                <span class="text-[12px] font-medium text-green-700 bg-green-50 border border-green-200 rounded-full px-2 py-0.5">On</span>
            </div>
            <div class="px-5 py-4 text-[13px] text-gray-600">
                Signing in asks for the password and a code from your authenticator app.
                {{ $recoveryCount }} recovery {{ $recoveryCount === 1 ? 'code is' : 'codes are' }} left.
            </div>
        </div>

        <form method="POST" action="{{ route('admin.security.2fa.disable') }}" class="bg-white rounded-xl border border-gray-200 overflow-hidden">
            @csrf
            <div class="px-5 py-3 border-b border-gray-100">
                <h2 class="font-semibold text-gray-800 text-sm">Turn it off</h2>
                <p class="text-[13px] text-gray-400 mt-0.5">Needs your password and a current code (or a recovery code).</p>
            </div>
            <div class="px-5 py-4 space-y-3">
                <input type="password" name="password" placeholder="Password" autocomplete="current-password" required
                       class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-orange-400">
                <input type="text" name="code" placeholder="Code or recovery code" autocomplete="one-time-code" required
                       class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-orange-400">
                <button type="submit" class="px-4 py-2 bg-red-600 hover:bg-red-700 text-white font-medium rounded-lg text-sm">Turn off</button>
            </div>
        </form>
    @else
        <div class="bg-white rounded-xl border border-gray-200 overflow-hidden">
            <div class="px-5 py-3 border-b border-gray-100 flex items-center justify-between">
                <h2 class="font-semibold text-gray-800 text-sm">Two-factor authentication</h2>
                <span class="text-[12px] font-medium text-gray-600 bg-gray-50 border border-gray-200 rounded-full px-2 py-0.5">Off</span>
            </div>
            <div class="px-5 py-4 space-y-4">
                <p class="text-[13px] text-gray-600">
                    Scan the code with an authenticator app (Google Authenticator, Authy, 1Password and the like), then type the
                    6 digits it shows to turn it on.
                </p>
                <div id="qr-code" class="bg-white p-3 rounded-xl border border-gray-200 inline-block"></div>
                <p class="text-[13px] text-gray-500">Or type this key into the app: <span class="font-mono text-gray-900 break-all">{{ $secret }}</span></p>

                <form method="POST" action="{{ route('admin.security.2fa.enable') }}" class="flex items-center gap-3">
                    @csrf
                    <input type="text" name="code" inputmode="numeric" maxlength="6" placeholder="123456" autocomplete="one-time-code" required
                           class="w-32 border border-gray-300 rounded-lg px-3 py-2 text-sm text-center tracking-widest focus:outline-none focus:ring-2 focus:ring-orange-400">
                    <button type="submit" class="px-4 py-2 bg-orange-500 hover:bg-orange-600 text-white font-medium rounded-lg text-sm">Turn on</button>
                </form>
            </div>
        </div>

        <script src="https://cdn.jsdelivr.net/npm/qrcode@1/build/qrcode.min.js"></script>
        <script>
            QRCode.toCanvas(document.createElement('canvas'), {{ Js::from($qrUri) }}, { width: 200 }, function (err, canvas) {
                if (!err) { document.getElementById('qr-code').appendChild(canvas); }
            });
        </script>
    @endif
</div>
@endsection
