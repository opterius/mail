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
    <style>[x-cloak]{display:none!important}</style>
</head>
<body class="min-h-screen bg-gray-100 text-gray-900 text-sm">
    <div class="flex h-screen overflow-hidden">

        {{-- ------------------------------------------------------------------ --}}
        {{-- Admin sidebar                                                        --}}
        {{-- ------------------------------------------------------------------ --}}
        <aside class="w-56 flex-shrink-0 bg-gray-900 text-gray-300 flex flex-col">

            {{-- Logo --}}
            <div class="px-4 py-4 border-b border-gray-700 flex items-center gap-2">
                <div class="w-7 h-7 rounded-lg bg-orange-500 flex items-center justify-center flex-shrink-0">
                    <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                              d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
                    </svg>
                </div>
                <div class="min-w-0">
                    <p class="font-semibold text-sm text-white leading-tight">Mail Admin</p>
                    <p class="text-[10px] text-gray-500 leading-tight truncate">
                        {{ config('mail-ui.admin_mode') ? 'Panel mode' : 'Standalone mode' }}
                    </p>
                </div>
            </div>

            {{-- Nav --}}
            <nav class="flex-1 px-2 py-3 space-y-0.5 overflow-y-auto">

                @php
                    $navItem = function(string $route, string $label, string $icon = '') {
                        $active = request()->routeIs($route) || request()->routeIs($route . '.*');
                        $cls = $active
                            ? 'bg-orange-500 text-white'
                            : 'text-gray-400 hover:bg-gray-800 hover:text-white';
                        $svg = $icon ? "<span class=\"mr-2 opacity-75\">{$icon}</span>" : '';
                        return "<a href=\"" . route($route) . "\" class=\"flex items-center px-3 py-2 rounded-lg {$cls} transition-colors\">{$svg}{$label}</a>";
                    };
                @endphp

                {{-- Always-visible items --}}
                {!! $navItem('admin.dashboard', 'Dashboard') !!}
                {!! $navItem('admin.groups.index', 'Groups') !!}
                {!! $navItem('admin.logs.index', 'Logs') !!}
                {!! $navItem('admin.settings.index', 'Settings') !!}

                {{-- Panel-mode only items --}}
                @if(config('mail-ui.admin_mode'))
                    <p class="px-3 pt-4 pb-1 text-[10px] font-semibold uppercase tracking-wider text-gray-600">
                        Mail Server
                    </p>
                    {!! $navItem('admin.domains.index', 'Domains') !!}
                    {!! $navItem('admin.accounts.index', 'Accounts') !!}
                    {!! $navItem('admin.aliases.index', 'Aliases') !!}
                    {!! $navItem('admin.autoresponders.index', 'Autoresponders') !!}
                    {!! $navItem('admin.spam.index', 'Spam') !!}
                    {!! $navItem('admin.dkim.index', 'DKIM') !!}
                    {!! $navItem('admin.queue.index', 'Queue') !!}
                    {!! $navItem('admin.settings.export', 'MTA Export') !!}
                @endif

            </nav>

            {{-- Admin user --}}
            <div class="px-3 py-3 border-t border-gray-700 text-xs text-gray-500">
                <p class="truncate font-medium text-gray-400">{{ Auth::guard('admin')->user()?->username }}</p>
                <form method="POST" action="{{ route('admin.logout') }}" class="mt-1.5">
                    @csrf
                    <button type="submit" class="text-gray-500 hover:text-white transition-colors">Sign out</button>
                </form>
            </div>
        </aside>

        {{-- ------------------------------------------------------------------ --}}
        {{-- Main content area                                                    --}}
        {{-- ------------------------------------------------------------------ --}}
        <main class="flex-1 overflow-y-auto bg-gray-50">
            @if (session('success'))
                <div class="mx-6 mt-4 p-3 rounded-lg bg-green-50 border border-green-200 text-sm text-green-700 flex items-center gap-2">
                    <svg class="w-4 h-4 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                    </svg>
                    {{ session('success') }}
                </div>
            @endif
            @if (session('error'))
                <div class="mx-6 mt-4 p-3 rounded-lg bg-red-50 border border-red-200 text-sm text-red-700 flex items-center gap-2">
                    <svg class="w-4 h-4 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                    {{ session('error') }}
                </div>
            @endif

            @yield('content')
        </main>
    </div>

    @stack('scripts')
</body>
</html>
