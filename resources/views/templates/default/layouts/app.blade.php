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
    <title>@yield('title', 'Inbox') — Opterius Mail</title>
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
        {{-- Sidebar --}}
        <aside class="w-56 flex-shrink-0 bg-white border-r border-gray-200 flex flex-col">
            <div class="px-4 py-4 border-b border-gray-100 flex items-center gap-2">
                <div class="w-7 h-7 rounded-lg bg-orange-500 flex items-center justify-center">
                    <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                              d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
                    </svg>
                </div>
                <span class="font-semibold text-sm text-gray-800">Opterius Mail</span>
            </div>

            <div class="px-3 py-3">
                <a href="{{ route('compose') }}"
                   class="flex items-center justify-center gap-2 w-full py-2 px-3 bg-orange-500 hover:bg-orange-600 text-white text-sm font-medium rounded-lg transition-colors">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                    </svg>
                    Compose
                </a>
            </div>

            <nav class="flex-1 px-2 py-1 overflow-y-auto">
                <a href="{{ route('inbox') }}"
                   class="flex items-center gap-2 px-3 py-2 rounded-lg text-sm text-gray-700 hover:bg-gray-100 {{ request()->routeIs('inbox') ? 'bg-orange-50 text-orange-600 font-medium' : '' }}">
                    Inbox
                </a>
                <a href="{{ route('folder', ['folder' => 'Sent']) }}"
                   class="flex items-center gap-2 px-3 py-2 rounded-lg text-sm text-gray-700 hover:bg-gray-100">
                    Sent
                </a>
                <a href="{{ route('folder', ['folder' => 'Drafts']) }}"
                   class="flex items-center gap-2 px-3 py-2 rounded-lg text-sm text-gray-700 hover:bg-gray-100">
                    Drafts
                </a>
                <a href="{{ route('folder', ['folder' => 'Trash']) }}"
                   class="flex items-center gap-2 px-3 py-2 rounded-lg text-sm text-gray-700 hover:bg-gray-100">
                    Trash
                </a>
                <a href="{{ route('folder', ['folder' => 'Junk']) }}"
                   class="flex items-center gap-2 px-3 py-2 rounded-lg text-sm text-gray-700 hover:bg-gray-100">
                    Spam
                </a>
            </nav>

            <div class="px-3 py-3 border-t border-gray-100 space-y-1">
                <a href="{{ route('contacts') }}"
                   class="flex items-center gap-2 px-3 py-2 rounded-lg text-sm text-gray-600 hover:bg-gray-100">
                    Contacts
                </a>
                <a href="{{ route('settings') }}"
                   class="flex items-center gap-2 px-3 py-2 rounded-lg text-sm text-gray-600 hover:bg-gray-100">
                    Settings
                </a>
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button type="submit"
                            class="w-full text-left flex items-center gap-2 px-3 py-2 rounded-lg text-sm text-gray-600 hover:bg-gray-100">
                        Sign out
                    </button>
                </form>
            </div>
        </aside>

        {{-- Main content --}}
        <main class="flex-1 overflow-y-auto">
            @yield('content')
        </main>
    </div>
</body>
</html>
