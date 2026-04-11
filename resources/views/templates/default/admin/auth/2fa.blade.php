{{--
 | Opterius Mail - Open source webmail.
 | Modern, fast and responsive webmail that works with any IMAP/SMTP server.
 | https://opterius.com
 |
 | Copyright (c) Host Server SRL (Opterius)
 | @license  AGPL-3.0  https://opensource.org/license/agpl-v3
 | @author   Iosif Gabriel Chimilevschi <office@opterius.com>
--}}
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Admin 2FA — Opterius Mail</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="min-h-screen bg-gray-900 flex items-center justify-center">
    <div class="w-full max-w-sm px-4">
        <div class="text-center mb-8">
            <h1 class="text-2xl font-bold text-white">Two-factor authentication</h1>
        </div>
        <div class="bg-gray-800 rounded-2xl border border-gray-700 p-8">
            <form method="POST" action="{{ route('admin.2fa.verify') }}">
                @csrf
                <div class="mb-6">
                    <label for="code" class="block text-sm font-medium text-gray-300 mb-1">Authentication code</label>
                    <input id="code" name="code" type="text" inputmode="numeric" maxlength="6" required autofocus
                           class="w-full px-3 py-2 bg-gray-700 border border-gray-600 rounded-lg text-sm text-white text-center tracking-widest text-lg focus:outline-none focus:ring-2 focus:ring-orange-500">
                </div>
                <button type="submit"
                        class="w-full py-2.5 px-4 bg-orange-500 hover:bg-orange-600 text-white font-medium rounded-lg text-sm transition-colors">
                    Verify
                </button>
            </form>
        </div>
    </div>
</body>
</html>
