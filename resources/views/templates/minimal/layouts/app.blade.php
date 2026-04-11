{{--
 | Opterius Mail - Open source webmail.
 | Minimal template — example of a custom template using the template system.
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
    <style>[x-cloak]{display:none!important}</style>
</head>
<body class="min-h-screen bg-gray-50 text-gray-900 text-sm">

    {{-- ------------------------------------------------------------------ --}}
    {{-- Top navigation                                                       --}}
    {{-- ------------------------------------------------------------------ --}}
    <header class="bg-gray-900 text-gray-100 border-b border-gray-800">
        <div class="flex items-center h-12 px-4 gap-6">

            {{-- Logo --}}
            <a href="{{ route('inbox') }}" class="flex items-center gap-2 flex-shrink-0">
                <div class="w-6 h-6 rounded bg-orange-500 flex items-center justify-center">
                    <svg class="w-3.5 h-3.5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                              d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
                    </svg>
                </div>
                <span class="text-sm font-semibold text-white">Opterius Mail</span>
            </a>

            {{-- Folder links --}}
            @php
                $sidebarFolders = $folders ?? [];
                $currentFolder  = $currentFolder ?? 'INBOX';

                $folderMeta = static function (array $f): array {
                    $attrs = $f['attributes'] ?? [];
                    $upper = strtoupper($f['name']);
                    if ($upper === 'INBOX')                        return 'Inbox';
                    if (in_array('\\sent',   $attrs, true))        return 'Sent';
                    if (in_array('\\drafts', $attrs, true))        return 'Drafts';
                    if (in_array('\\trash',  $attrs, true))        return 'Trash';
                    if (in_array('\\junk',   $attrs, true))        return 'Spam';
                    if (str_contains($upper, 'SENT'))              return 'Sent';
                    if (str_contains($upper, 'DRAFT'))             return 'Drafts';
                    if (str_contains($upper, 'TRASH') || str_contains($upper, 'DELETED')) return 'Trash';
                    if (str_contains($upper, 'JUNK')  || str_contains($upper, 'SPAM'))    return 'Spam';
                    return $f['name'];
                };
            @endphp

            <nav class="flex items-center gap-1 flex-1 overflow-x-auto">
                @if(!empty($sidebarFolders))
                    @foreach($sidebarFolders as $f)
                        @php
                            $label    = $folderMeta($f);
                            $isActive = $currentFolder === $f['name'];
                            $unseen   = $f['unseen'] ?? 0;
                            $href     = $f['name'] === 'INBOX'
                                ? route('inbox')
                                : route('folder', ['folder' => rawurlencode($f['name'])]);
                        @endphp
                        <a href="{{ $href }}"
                           class="flex items-center gap-1.5 px-3 py-1.5 rounded text-xs font-medium whitespace-nowrap transition-colors
                                  {{ $isActive ? 'bg-gray-700 text-white' : 'text-gray-400 hover:text-white hover:bg-gray-800' }}">
                            {{ $label }}
                            @if($unseen > 0)
                                <span class="bg-orange-500 text-white text-[10px] font-bold px-1.5 py-0.5 rounded-full leading-none">
                                    {{ $unseen > 99 ? '99+' : $unseen }}
                                </span>
                            @endif
                        </a>
                    @endforeach
                @else
                    <a href="{{ route('inbox') }}"
                       class="px-3 py-1.5 rounded text-xs font-medium text-gray-400 hover:text-white hover:bg-gray-800 transition-colors">
                        Inbox
                    </a>
                @endif
            </nav>

            {{-- Right side actions --}}
            <div class="flex items-center gap-1 flex-shrink-0">
                <a href="{{ route('compose') }}"
                   class="flex items-center gap-1.5 px-3 py-1.5 bg-orange-500 hover:bg-orange-600 text-white text-xs font-medium rounded transition-colors">
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                    </svg>
                    Compose
                </a>
                <a href="{{ route('search') }}"
                   class="p-1.5 text-gray-400 hover:text-white hover:bg-gray-800 rounded transition-colors"
                   title="Search (/)">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                              d="M21 21l-4.35-4.35M17 11A6 6 0 115 11a6 6 0 0112 0z"/>
                    </svg>
                </a>
                <a href="{{ route('contacts') }}"
                   class="p-1.5 text-gray-400 hover:text-white hover:bg-gray-800 rounded transition-colors"
                   title="Contacts">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                              d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"/>
                    </svg>
                </a>
                <a href="{{ route('settings') }}"
                   class="p-1.5 text-gray-400 hover:text-white hover:bg-gray-800 rounded transition-colors"
                   title="Settings">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                              d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/>
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                    </svg>
                </a>
                <form method="POST" action="{{ route('logout') }}" class="inline">
                    @csrf
                    <button type="submit"
                            class="p-1.5 text-gray-400 hover:text-white hover:bg-gray-800 rounded transition-colors"
                            title="Sign out">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                  d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/>
                        </svg>
                    </button>
                </form>
                <span class="text-xs text-gray-500 ml-2 hidden md:block">{{ auth('web')->user()?->email }}</span>
            </div>
        </div>
    </header>

    {{-- ------------------------------------------------------------------ --}}
    {{-- Main content                                                         --}}
    {{-- ------------------------------------------------------------------ --}}
    <main class="max-w-5xl mx-auto px-4 py-6">
        @yield('content')
    </main>

</div>

{{-- Global keyboard shortcuts --}}
<script>
(function () {
    function isTyping(el) {
        var tag = el.tagName;
        return tag === 'INPUT' || tag === 'TEXTAREA' || tag === 'SELECT' || el.isContentEditable;
    }
    document.addEventListener('keydown', function (e) {
        if (isTyping(e.target)) return;
        if (e.ctrlKey || e.altKey || e.metaKey) return;
        switch (e.key) {
            case 'c': window.location.href = '{{ route('compose') }}'; break;
            case '/': e.preventDefault(); window.location.href = '{{ route('search') }}'; break;
        }
    });
})();
</script>

@stack('scripts')

</body>
</html>
