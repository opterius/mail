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
    <title>@yield('title', 'Admin') — Opterius Mail</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
    <script>
        tailwind.config = {
            darkMode: 'class',
            theme: { extend: { colors: { brand: '#f97316' } } }
        }
    </script>
</head>
<body class="min-h-screen bg-gray-100 text-gray-900">
    <div class="flex h-screen overflow-hidden">
        {{-- Admin sidebar --}}
        <aside class="w-56 flex-shrink-0 bg-gray-900 text-gray-300 flex flex-col">
            <div class="px-4 py-4 border-b border-gray-700 flex items-center gap-2">
                <div class="w-7 h-7 rounded-lg bg-orange-500 flex items-center justify-center">
                    <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                              d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
                    </svg>
                </div>
                <span class="font-semibold text-sm text-white">Mail Admin</span>
            </div>

            <nav class="flex-1 px-2 py-3 space-y-0.5 overflow-y-auto text-sm">
                @php
                    $navItem = function(string $route, string $label) {
                        $active = request()->routeIs($route) || request()->routeIs($route . '.*');
                        $cls = $active
                            ? 'bg-orange-500 text-white'
                            : 'text-gray-400 hover:bg-gray-800 hover:text-white';
                        return "<a href=\"" . route($route) . "\" class=\"flex items-center px-3 py-2 rounded-lg {$cls}\">{$label}</a>";
                    };
                @endphp

                {!! $navItem('admin.dashboard', 'Dashboard') !!}
                {!! $navItem('admin.domains.index', 'Domains') !!}
                {!! $navItem('admin.accounts.index', 'Accounts') !!}
                {!! $navItem('admin.aliases.index', 'Aliases') !!}
                {!! $navItem('admin.autoresponders.index', 'Autoresponders') !!}
                {!! $navItem('admin.spam.index', 'Spam') !!}
                {!! $navItem('admin.dkim.index', 'DKIM') !!}
                {!! $navItem('admin.queue.index', 'Queue') !!}
                {!! $navItem('admin.logs.index', 'Logs') !!}
                {!! $navItem('admin.settings.index', 'Settings') !!}
            </nav>

            <div class="px-3 py-3 border-t border-gray-700 text-xs text-gray-500">
                <p class="truncate">{{ Auth::guard('admin')->user()?->username }}</p>
                <form method="POST" action="{{ route('admin.logout') }}" class="mt-2">
                    @csrf
                    <button type="submit" class="text-gray-400 hover:text-white transition-colors">Sign out</button>
                </form>
            </div>
        </aside>

        {{-- Main content --}}
        <main class="flex-1 overflow-y-auto">
            @if (session('success'))
                <div class="m-4 p-3 rounded-lg bg-green-50 border border-green-200 text-sm text-green-700">
                    {{ session('success') }}
                </div>
            @endif
            @yield('content')
        </main>
    </div>
</body>
</html>
