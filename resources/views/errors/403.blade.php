{{--
 | Opterius Mail - Open source webmail.
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
    <title>403 Forbidden — Opterius Mail</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="min-h-screen bg-gray-50 flex flex-col items-center justify-center p-6 text-center">

    <div class="w-14 h-14 rounded-2xl bg-red-500 flex items-center justify-center mb-6">
        <svg class="w-7 h-7 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                  d="M12 15v2m0 0v2m0-2h2m-2 0H10m2-5V9m0 0V7m0 2h2M12 9H10m9 3a9 9 0 11-18 0 9 9 0 0118 0z"/>
        </svg>
    </div>

    <p class="text-7xl font-bold text-gray-200 leading-none mb-3">403</p>
    <h1 class="text-lg font-semibold text-gray-800 mb-2">Access denied</h1>
    <p class="text-sm text-gray-500 mb-8 max-w-xs">
        {{ $exception->getMessage() ?: "You don't have permission to access this page." }}
    </p>

    <a href="/"
       class="inline-flex items-center gap-2 px-5 py-2.5 bg-orange-500 hover:bg-orange-600 text-white text-sm font-medium rounded-lg transition-colors">
        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/>
        </svg>
        Go to inbox
    </a>

</body>
</html>
