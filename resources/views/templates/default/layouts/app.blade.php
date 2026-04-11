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

    {{-- ------------------------------------------------------------------ --}}
    {{-- Sidebar                                                              --}}
    {{-- ------------------------------------------------------------------ --}}
    <aside class="w-56 flex-shrink-0 bg-white border-r border-gray-200 flex flex-col">

        {{-- Logo --}}
        <div class="px-4 py-4 border-b border-gray-100 flex items-center gap-2">
            <div class="w-7 h-7 rounded-lg bg-orange-500 flex items-center justify-center flex-shrink-0">
                <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                          d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
                </svg>
            </div>
            <span class="font-semibold text-sm text-gray-800 truncate">Opterius Mail</span>
        </div>

        {{-- Compose button --}}
        <div class="px-3 py-3">
            <a href="{{ route('compose') }}"
               class="flex items-center justify-center gap-2 w-full py-2 px-3 bg-orange-500 hover:bg-orange-600 text-white text-sm font-medium rounded-lg transition-colors">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                </svg>
                Compose
            </a>
        </div>

        {{-- Folder list --}}
        <nav class="flex-1 px-2 py-1 overflow-y-auto">
            @php
                $sidebarFolders = $folders ?? [];
                $currentFolder  = $currentFolder ?? 'INBOX';

                // Map IMAP attributes → friendly display names and icons
                $folderMeta = static function (array $f): array {
                    $attrs = $f['attributes'] ?? [];
                    $name  = $f['name'];
                    $upper = strtoupper($name);

                    if ($upper === 'INBOX')                        return ['label' => 'Inbox',  'icon' => 'inbox'];
                    if (in_array('\\sent',   $attrs, true))        return ['label' => 'Sent',   'icon' => 'sent'];
                    if (in_array('\\drafts', $attrs, true))        return ['label' => 'Drafts', 'icon' => 'draft'];
                    if (in_array('\\trash',  $attrs, true))        return ['label' => 'Trash',  'icon' => 'trash'];
                    if (in_array('\\junk',   $attrs, true))        return ['label' => 'Spam',   'icon' => 'spam'];
                    if (str_contains($upper, 'SENT'))              return ['label' => 'Sent',   'icon' => 'sent'];
                    if (str_contains($upper, 'DRAFT'))             return ['label' => 'Drafts', 'icon' => 'draft'];
                    if (str_contains($upper, 'TRASH') || str_contains($upper, 'DELETED')) return ['label' => 'Trash', 'icon' => 'trash'];
                    if (str_contains($upper, 'JUNK') || str_contains($upper, 'SPAM'))     return ['label' => 'Spam',  'icon' => 'spam'];
                    return ['label' => $name, 'icon' => 'folder'];
                };
            @endphp

            @if(!empty($sidebarFolders))
                @foreach($sidebarFolders as $f)
                    @php
                        $meta    = $folderMeta($f);
                        $isActive = $currentFolder === $f['name'];
                        $unseen  = $f['unseen'] ?? 0;
                        $href    = $f['name'] === 'INBOX'
                            ? route('inbox')
                            : route('folder', ['folder' => rawurlencode($f['name'])]);
                    @endphp
                    <a href="{{ $href }}"
                       class="flex items-center justify-between gap-2 px-3 py-2 rounded-lg text-sm transition-colors
                              {{ $isActive ? 'bg-orange-50 text-orange-600 font-medium' : 'text-gray-700 hover:bg-gray-100' }}">
                        <span class="flex items-center gap-2 min-w-0">
                            @include(mailView('partials.folder-icon'), ['icon' => $meta['icon'], 'active' => $isActive])
                            <span class="truncate">{{ $meta['label'] }}</span>
                        </span>
                        @if($unseen > 0)
                            <span class="flex-shrink-0 text-xs font-semibold px-1.5 py-0.5 rounded-full
                                         {{ $isActive ? 'bg-orange-500 text-white' : 'bg-gray-200 text-gray-600' }}">
                                {{ $unseen > 99 ? '99+' : $unseen }}
                            </span>
                        @endif
                    </a>
                @endforeach
            @else
                {{-- Fallback when folder list is unavailable --}}
                <a href="{{ route('inbox') }}"
                   class="flex items-center gap-2 px-3 py-2 rounded-lg text-sm text-gray-700 hover:bg-gray-100
                          {{ request()->routeIs('inbox') ? 'bg-orange-50 text-orange-600 font-medium' : '' }}">
                    Inbox
                </a>
            @endif
        </nav>

        {{-- Bottom links --}}
        <div class="px-3 py-3 border-t border-gray-100 space-y-0.5">
            <a href="{{ route('contacts') }}"
               class="flex items-center gap-2 px-3 py-2 rounded-lg text-sm text-gray-500 hover:bg-gray-100 hover:text-gray-700">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                          d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"/>
                </svg>
                Contacts
            </a>
            <a href="{{ route('settings') }}"
               class="flex items-center gap-2 px-3 py-2 rounded-lg text-sm text-gray-500 hover:bg-gray-100 hover:text-gray-700">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                          d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/>
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                </svg>
                Settings
            </a>
            <form method="POST" action="{{ route('logout') }}">
                @csrf
                <button type="submit"
                        class="w-full text-left flex items-center gap-2 px-3 py-2 rounded-lg text-sm text-gray-500 hover:bg-gray-100 hover:text-gray-700">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                              d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/>
                    </svg>
                    Sign out
                </button>
            </form>
        </div>

        {{-- Current user --}}
        <div class="px-4 py-3 border-t border-gray-100 bg-gray-50">
            <p class="text-xs text-gray-500 truncate">{{ auth('web')->user()?->email }}</p>
        </div>
    </aside>

    {{-- ------------------------------------------------------------------ --}}
    {{-- Main content                                                         --}}
    {{-- ------------------------------------------------------------------ --}}
    <main class="flex-1 overflow-y-auto bg-white">
        @yield('content')
    </main>

</div>
</body>
</html>
