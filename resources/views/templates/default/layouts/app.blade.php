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
<html lang="en" class="{{ userSettings()->theme === 'dark' ? 'dark' : '' }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('title', 'Inbox') — Opterius Mail</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
    <script>
        tailwind.config = {
            darkMode: 'class',
            theme: {
                extend: {
                    colors: { brand: '#f97316' },
                    fontSize: { '2xs': ['0.65rem', '1rem'] }
                }
            }
        }
    </script>
    <style>
        [x-cloak]{display:none!important}
        @media (max-width: 767px) { #mail-sidebar { transform: translateX(-100%); } }
        body { font-size: 15px; }
    </style>
</head>
<body class="min-h-screen bg-gray-50 dark:bg-gray-950 text-gray-900 dark:text-gray-100" style="font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;">
<div class="flex h-screen overflow-hidden" x-data="{ sidebarOpen: false }">

    {{-- Mobile backdrop --}}
    <div x-show="sidebarOpen" x-cloak
         @click="sidebarOpen = false"
         class="fixed inset-0 z-20 bg-black/50 md:hidden"></div>

    {{-- ------------------------------------------------------------------ --}}
    {{-- Sidebar                                                              --}}
    {{-- ------------------------------------------------------------------ --}}
    <aside id="mail-sidebar"
           class="fixed inset-y-0 left-0 z-30 w-64 flex flex-col flex-shrink-0
                  bg-white dark:bg-gray-900 border-r border-gray-200 dark:border-gray-800
                  transition-transform duration-200 ease-in-out
                  md:relative md:w-60 md:translate-x-0"
           :class="sidebarOpen ? 'translate-x-0' : ''">

        {{-- Logo --}}
        <div class="px-4 py-4 flex items-center gap-2.5">
            <div class="w-8 h-8 rounded-xl bg-orange-500 flex items-center justify-center flex-shrink-0 shadow-sm">
                <svg class="w-4.5 h-4.5 text-white" style="width:18px;height:18px;" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                          d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
                </svg>
            </div>
            <span class="font-bold text-base text-gray-900 dark:text-white truncate">Opterius Mail</span>
            <button @click="sidebarOpen = false"
                    class="ml-auto p-1.5 rounded-lg text-gray-400 hover:text-gray-700 dark:hover:text-gray-200 hover:bg-gray-100 dark:hover:bg-gray-800 md:hidden transition-colors">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                </svg>
            </button>
        </div>

        {{-- Compose button --}}
        <div class="px-3 pb-3">
            <a href="{{ route('compose') }}"
               class="flex items-center justify-center gap-2 w-full py-2.5 px-4 bg-orange-500 hover:bg-orange-600 active:bg-orange-700 text-white text-sm font-semibold rounded-xl transition-colors shadow-sm shadow-orange-200 dark:shadow-none">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 4v16m8-8H4"/>
                </svg>
                Compose
            </a>
        </div>

        {{-- Search --}}
        {{-- Folder list --}}
        <nav class="flex-1 px-2 overflow-y-auto"
             x-data="{ newFolderOpen: false, editingFolder: null, deletingFolder: null }">
            @php
                $sidebarFolders = $folders ?? [];
                $currentFolder  = $currentFolder ?? 'INBOX';

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
                    if (str_contains($upper, 'JUNK')  || str_contains($upper, 'SPAM'))    return ['label' => 'Spam',  'icon' => 'spam'];
                    return ['label' => $name, 'icon' => 'folder'];
                };
            @endphp

            @if(!empty($sidebarFolders))
                @foreach($sidebarFolders as $f)
                    @php
                        $meta     = $folderMeta($f);
                        $isActive = $currentFolder === $f['name'];
                        $isCustom = $meta['icon'] === 'folder';
                        $unseen   = $f['unseen'] ?? 0;
                        $href     = $f['name'] === 'INBOX'
                            ? route('inbox')
                            : route('folder', ['folder' => rawurlencode($f['name'])]);
                        $jsName   = json_encode($f['name']);
                    @endphp

                    @if($isCustom)
                        <div class="group relative"
                             x-show="editingFolder !== {{ $jsName }} && deletingFolder !== {{ $jsName }}">
                            <a href="{{ $href }}" @click="sidebarOpen = false"
                               class="flex items-center justify-between gap-2 px-3 py-2.5 rounded-xl text-sm font-medium transition-colors
                                      {{ $isActive
                                          ? 'bg-orange-50 dark:bg-orange-900/25 text-orange-600 dark:text-orange-400'
                                          : 'text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-800' }}">
                                <span class="flex items-center gap-3 min-w-0">
                                    @include(mailView('partials.folder-icon'), ['icon' => $meta['icon'], 'active' => $isActive])
                                    <span class="truncate">{{ $meta['label'] }}</span>
                                </span>
                                @if($unseen > 0)
                                    <span class="flex-shrink-0 text-xs font-bold px-1.5 py-0.5 rounded-full min-w-[20px] text-center
                                                 {{ $isActive ? 'bg-orange-500 text-white' : 'bg-gray-200 dark:bg-gray-700 text-gray-600 dark:text-gray-300' }}">
                                        {{ $unseen > 99 ? '99+' : $unseen }}
                                    </span>
                                @endif
                            </a>
                            <div class="absolute right-1 top-1/2 -translate-y-1/2 hidden group-hover:flex items-center gap-0.5 bg-white dark:bg-gray-900 rounded-lg shadow-sm border border-gray-100 dark:border-gray-700">
                                <button type="button" @click.prevent.stop="editingFolder = {{ $jsName }}" title="Rename"
                                        class="p-1.5 rounded-lg text-gray-400 hover:text-blue-500 transition-colors">
                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                              d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                                    </svg>
                                </button>
                                <button type="button" @click.prevent.stop="deletingFolder = {{ $jsName }}" title="Delete"
                                        class="p-1.5 rounded-lg text-gray-400 hover:text-red-500 transition-colors">
                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                              d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                    </svg>
                                </button>
                            </div>
                        </div>

                        <div x-show="editingFolder === {{ $jsName }}" x-cloak class="px-2 py-1">
                            <form method="POST" action="{{ route('folders.update') }}" class="flex items-center gap-1">
                                @csrf @method('PUT')
                                <input type="hidden" name="old_name" value="{{ $f['name'] }}">
                                <input type="text" name="new_name" value="{{ $f['name'] }}"
                                       class="flex-1 min-w-0 text-sm px-2 py-1.5 rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-800 text-gray-900 dark:text-gray-100 focus:outline-none focus:ring-2 focus:ring-orange-400"
                                       @keydown.escape="editingFolder = null">
                                <button type="submit" title="Save" class="p-1.5 rounded-lg text-green-600 hover:bg-green-50 transition-colors">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                                </button>
                                <button type="button" @click="editingFolder = null" title="Cancel" class="p-1.5 rounded-lg text-gray-400 hover:bg-gray-100 transition-colors">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                                </button>
                            </form>
                        </div>

                        <div x-show="deletingFolder === {{ $jsName }}" x-cloak
                             class="mx-2 mb-1 p-3 rounded-xl bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-800">
                            <p class="text-sm text-red-700 dark:text-red-300 mb-2 truncate font-medium">Delete "{{ $f['name'] }}"?</p>
                            <form method="POST" action="{{ route('folders.destroy') }}" class="flex gap-2">
                                @csrf @method('DELETE')
                                <input type="hidden" name="name" value="{{ $f['name'] }}">
                                <button type="submit" class="flex-1 text-sm py-1.5 rounded-lg bg-red-500 hover:bg-red-600 text-white font-semibold transition-colors">Delete</button>
                                <button type="button" @click="deletingFolder = null" class="flex-1 text-sm py-1.5 rounded-lg bg-white dark:bg-gray-700 border border-gray-200 dark:border-gray-600 text-gray-700 dark:text-gray-200 font-medium transition-colors">Cancel</button>
                            </form>
                        </div>

                    @else
                        <a href="{{ $href }}" @click="sidebarOpen = false"
                           class="flex items-center justify-between gap-2 px-3 py-2.5 rounded-xl text-sm font-medium transition-colors
                                  {{ $isActive
                                      ? 'bg-orange-50 dark:bg-orange-900/25 text-orange-600 dark:text-orange-400'
                                      : 'text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-800' }}">
                            <span class="flex items-center gap-3 min-w-0">
                                @include(mailView('partials.folder-icon'), ['icon' => $meta['icon'], 'active' => $isActive])
                                <span class="truncate">{{ $meta['label'] }}</span>
                            </span>
                            @if($f['name'] === 'INBOX')
                                <span id="inbox-unseen-badge"
                                      class="flex-shrink-0 text-xs font-bold px-1.5 py-0.5 rounded-full min-w-[20px] text-center
                                             {{ $isActive ? 'bg-orange-500 text-white' : 'bg-gray-200 dark:bg-gray-700 text-gray-600 dark:text-gray-300' }}
                                             {{ $unseen > 0 ? '' : 'hidden' }}">
                                    {{ $unseen > 99 ? '99+' : $unseen }}
                                </span>
                            @elseif($unseen > 0)
                                <span class="flex-shrink-0 text-xs font-bold px-1.5 py-0.5 rounded-full min-w-[20px] text-center
                                             {{ $isActive ? 'bg-orange-500 text-white' : 'bg-gray-200 dark:bg-gray-700 text-gray-600 dark:text-gray-300' }}">
                                    {{ $unseen > 99 ? '99+' : $unseen }}
                                </span>
                            @endif
                        </a>
                    @endif
                @endforeach

                {{-- New folder --}}
                <div class="mt-2 pt-2 border-t border-gray-100 dark:border-gray-800">
                    <button type="button"
                            x-show="!newFolderOpen"
                            @click="newFolderOpen = true; $nextTick(() => $refs.newFolderInput.focus())"
                            class="flex items-center gap-2 w-full px-3 py-2 rounded-xl text-sm text-gray-400 dark:text-gray-500 hover:text-gray-600 dark:hover:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-800 transition-colors">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                        </svg>
                        New folder
                    </button>
                    <div x-show="newFolderOpen" x-cloak class="px-2 py-1">
                        <form method="POST" action="{{ route('folders.store') }}" class="flex items-center gap-1">
                            @csrf
                            <input type="text" name="name" placeholder="Folder name"
                                   x-ref="newFolderInput"
                                   class="flex-1 min-w-0 text-sm px-2 py-1.5 rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-800 text-gray-900 dark:text-gray-100 focus:outline-none focus:ring-2 focus:ring-orange-400"
                                   @keydown.escape="newFolderOpen = false">
                            <button type="submit" title="Create" class="p-1.5 rounded-lg text-green-600 hover:bg-green-50 transition-colors">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                            </button>
                            <button type="button" @click="newFolderOpen = false" title="Cancel" class="p-1.5 rounded-lg text-gray-400 hover:bg-gray-100 transition-colors">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                            </button>
                        </form>
                    </div>
                </div>
            @else
                <a href="{{ route('inbox') }}" @click="sidebarOpen = false"
                   class="flex items-center gap-3 px-3 py-2.5 rounded-xl text-sm font-medium
                          {{ request()->routeIs('inbox') ? 'bg-orange-50 dark:bg-orange-900/25 text-orange-600 dark:text-orange-400' : 'text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-800' }}">
                    Inbox
                </a>
            @endif
        </nav>

        {{-- Bottom links --}}
        <div class="px-2 py-3 border-t border-gray-100 dark:border-gray-800 space-y-0.5">
            <a href="{{ route('contacts') }}"
               class="flex items-center gap-3 px-3 py-2.5 rounded-xl text-sm font-medium transition-colors
                      {{ request()->routeIs('contacts*') ? 'bg-orange-50 dark:bg-orange-900/25 text-orange-600 dark:text-orange-400' : 'text-gray-600 dark:text-gray-400 hover:bg-gray-100 dark:hover:bg-gray-800 hover:text-gray-800 dark:hover:text-gray-200' }}">
                <svg class="w-4.5 h-4.5 flex-shrink-0" style="width:18px;height:18px;" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                          d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"/>
                </svg>
                Contacts
            </a>
            {{-- ---- HEY-style sections ---- --}}
            <div class="pt-3 pb-1">
                <p class="px-3 text-[11px] font-semibold uppercase tracking-wider text-gray-400 dark:text-gray-600">Smart</p>
            </div>

            <a href="{{ route('screener.index') }}"
               class="flex items-center gap-3 px-3 py-2 rounded-xl text-sm font-medium transition-colors
                      {{ request()->routeIs('screener*') ? 'bg-orange-50 dark:bg-orange-900/25 text-orange-600 dark:text-orange-400' : 'text-gray-600 dark:text-gray-400 hover:bg-gray-100 dark:hover:bg-gray-800 hover:text-gray-800 dark:hover:text-gray-200' }}">
                <svg style="width:18px;height:18px;flex-shrink:0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/>
                </svg>
                Screener
            </a>

            <a href="{{ route('feed.index') }}"
               class="flex items-center gap-3 px-3 py-2 rounded-xl text-sm font-medium transition-colors
                      {{ request()->routeIs('feed*') ? 'bg-orange-50 dark:bg-orange-900/25 text-orange-600 dark:text-orange-400' : 'text-gray-600 dark:text-gray-400 hover:bg-gray-100 dark:hover:bg-gray-800 hover:text-gray-800 dark:hover:text-gray-200' }}">
                <svg style="width:18px;height:18px;flex-shrink:0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 5c7.18 0 13 5.82 13 13M6 11a7 7 0 017 7m-6 0a1 1 0 11-2 0 1 1 0 012 0z"/>
                </svg>
                Feed
            </a>

            <div class="pt-3 pb-1">
                <p class="px-3 text-[11px] font-semibold uppercase tracking-wider text-gray-400 dark:text-gray-600">Parked</p>
            </div>

            <a href="{{ route('snooze.index') }}"
               class="flex items-center gap-3 px-3 py-2 rounded-xl text-sm font-medium transition-colors
                      {{ request()->routeIs('snooze*') ? 'bg-orange-50 dark:bg-orange-900/25 text-orange-600 dark:text-orange-400' : 'text-gray-600 dark:text-gray-400 hover:bg-gray-100 dark:hover:bg-gray-800 hover:text-gray-800 dark:hover:text-gray-200' }}">
                <svg style="width:18px;height:18px;flex-shrink:0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
                Snoozed
            </a>

            <a href="{{ route('set-aside.index') }}"
               class="flex items-center gap-3 px-3 py-2 rounded-xl text-sm font-medium transition-colors
                      {{ request()->routeIs('set-aside*') ? 'bg-orange-50 dark:bg-orange-900/25 text-orange-600 dark:text-orange-400' : 'text-gray-600 dark:text-gray-400 hover:bg-gray-100 dark:hover:bg-gray-800 hover:text-gray-800 dark:hover:text-gray-200' }}">
                <svg style="width:18px;height:18px;flex-shrink:0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 5a2 2 0 012-2h10a2 2 0 012 2v16l-7-3.5L5 21V5z"/>
                </svg>
                Set Aside
            </a>

            <a href="{{ route('reply-later.index') }}"
               class="flex items-center gap-3 px-3 py-2 rounded-xl text-sm font-medium transition-colors
                      {{ request()->routeIs('reply-later*') ? 'bg-orange-50 dark:bg-orange-900/25 text-orange-600 dark:text-orange-400' : 'text-gray-600 dark:text-gray-400 hover:bg-gray-100 dark:hover:bg-gray-800 hover:text-gray-800 dark:hover:text-gray-200' }}">
                <svg style="width:18px;height:18px;flex-shrink:0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 10h.01M12 10h.01M16 10h.01M9 16H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-5l-5 5v-5z"/>
                </svg>
                Reply Later
            </a>

            <a href="{{ route('scheduled.index') }}"
               class="flex items-center gap-3 px-3 py-2 rounded-xl text-sm font-medium transition-colors
                      {{ request()->routeIs('scheduled*') ? 'bg-orange-50 dark:bg-orange-900/25 text-orange-600 dark:text-orange-400' : 'text-gray-600 dark:text-gray-400 hover:bg-gray-100 dark:hover:bg-gray-800 hover:text-gray-800 dark:hover:text-gray-200' }}">
                <svg style="width:18px;height:18px;flex-shrink:0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                </svg>
                Scheduled
            </a>

            <div class="pt-3 pb-1">
                <p class="px-3 text-[11px] font-semibold uppercase tracking-wider text-gray-400 dark:text-gray-600">Account</p>
            </div>

            <a href="{{ route('settings') }}"
               class="flex items-center gap-3 px-3 py-2.5 rounded-xl text-sm font-medium transition-colors
                      {{ request()->routeIs('settings*') ? 'bg-orange-50 dark:bg-orange-900/25 text-orange-600 dark:text-orange-400' : 'text-gray-600 dark:text-gray-400 hover:bg-gray-100 dark:hover:bg-gray-800 hover:text-gray-800 dark:hover:text-gray-200' }}">
                <svg class="w-4.5 h-4.5 flex-shrink-0" style="width:18px;height:18px;" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                          d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/>
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                </svg>
                Settings
            </a>
            <a href="{{ route('help') }}"
               class="flex items-center gap-3 px-3 py-2.5 rounded-xl text-sm font-medium transition-colors
                      {{ request()->routeIs('help') ? 'bg-orange-50 dark:bg-orange-900/25 text-orange-600 dark:text-orange-400' : 'text-gray-600 dark:text-gray-400 hover:bg-gray-100 dark:hover:bg-gray-800 hover:text-gray-800 dark:hover:text-gray-200' }}">
                <svg class="w-4.5 h-4.5 flex-shrink-0" style="width:18px;height:18px;" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                          d="M8.228 9c.549-1.165 2.03-2 3.772-2 2.21 0 4 1.343 4 3 0 1.4-1.278 2.575-3.006 2.907-.542.104-.994.54-.994 1.093m0 3h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
                Help
            </a>
            <form method="POST" action="{{ route('logout') }}">
                @csrf
                <button type="submit"
                        class="w-full text-left flex items-center gap-3 px-3 py-2.5 rounded-xl text-sm font-medium transition-colors
                               text-gray-600 dark:text-gray-400 hover:bg-gray-100 dark:hover:bg-gray-800 hover:text-gray-800 dark:hover:text-gray-200">
                    <svg class="w-4.5 h-4.5 flex-shrink-0" style="width:18px;height:18px;" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                              d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/>
                    </svg>
                    Sign out
                </button>
            </form>
        </div>

        {{-- Current user --}}
        <div class="px-4 py-3 border-t border-gray-100 dark:border-gray-800 bg-gray-50 dark:bg-gray-950/50">
            <p class="text-xs text-gray-500 dark:text-gray-500 truncate font-medium">{{ auth('web')->user()?->email }}</p>
        </div>
    </aside>

    {{-- ------------------------------------------------------------------ --}}
    {{-- Right panel                                                          --}}
    {{-- ------------------------------------------------------------------ --}}
    <div class="flex flex-col flex-1 min-w-0 overflow-hidden">

        {{-- Mobile topbar --}}
        <div class="md:hidden flex items-center gap-3 h-14 px-4 border-b border-gray-200 dark:border-gray-800
                    bg-white dark:bg-gray-900 flex-shrink-0">
            <button @click="sidebarOpen = true"
                    class="p-2 rounded-xl text-gray-500 hover:text-gray-800 dark:hover:text-gray-200 hover:bg-gray-100 dark:hover:bg-gray-800 transition-colors">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/>
                </svg>
            </button>
            <span class="font-semibold text-base text-gray-800 dark:text-gray-200 truncate flex-1">@yield('title', 'Inbox')</span>
            <a href="{{ route('compose') }}"
               class="flex-shrink-0 flex items-center gap-1.5 px-3 py-1.5 bg-orange-500 hover:bg-orange-600 text-white text-sm font-semibold rounded-xl transition-colors">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 4v16m8-8H4"/>
                </svg>
                Compose
            </a>
        </div>

        {{-- Main content --}}
        <main class="flex-1 overflow-y-auto bg-white dark:bg-gray-900 relative">
            @if(session('success'))
                <div x-data="{ show: true }" x-show="show" x-init="setTimeout(() => show = false, 4000)"
                     x-transition:leave="transition ease-in duration-300"
                     x-transition:leave-start="opacity-100 translate-y-0"
                     x-transition:leave-end="opacity-0 -translate-y-2"
                     class="absolute top-4 left-1/2 -translate-x-1/2 z-50 flex items-center gap-2 px-4 py-2.5
                            bg-green-600 text-white text-sm font-medium rounded-lg shadow-lg pointer-events-none">
                    <svg class="w-4 h-4 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                    </svg>
                    {{ session('success') }}
                </div>
            @endif
            @yield('content')
        </main>

    </div>

</div>

<script>
(function () {
    function isTyping(el) {
        const tag = el.tagName;
        return tag === 'INPUT' || tag === 'TEXTAREA' || tag === 'SELECT' || el.isContentEditable;
    }

    document.addEventListener('keydown', function (e) {
        if (isTyping(e.target)) return;
        if (e.ctrlKey || e.altKey || e.metaKey) return;

        switch (e.key) {
            case 'c':
                window.location.href = '{{ route('compose') }}';
                break;
            case '/':
                e.preventDefault();
                var s = document.getElementById('sidebar-search');
                if (s) s.focus();
                break;
        }
    });
})();
</script>

{{-- ------------------------------------------------------------------ --}}
{{-- Real-time new mail polling (Phase 1: 45-second AJAX poll)           --}}
{{-- ------------------------------------------------------------------ --}}
<script>
(function () {
    var endpoint  = '{{ route('api.check-new') }}';
    var csrfToken = '{{ csrf_token() }}';
    var badge     = document.getElementById('inbox-unseen-badge');
    var notifShown = false;

    function updateBadge(unseen) {
        if (!badge) return;
        if (unseen > 0) {
            badge.textContent = unseen > 99 ? '99+' : unseen;
            badge.classList.remove('hidden');
        } else {
            badge.classList.add('hidden');
        }
        // Update page title prefix
        var title = document.title.replace(/^\(\d+\+?\) /, '');
        document.title = unseen > 0 ? '(' + (unseen > 99 ? '99+' : unseen) + ') ' + title : title;
    }

    function checkNew() {
        fetch(endpoint, {
            headers: { 'X-CSRF-TOKEN': csrfToken, 'Accept': 'application/json' }
        })
        .then(function (r) { return r.ok ? r.json() : null; })
        .then(function (data) {
            if (!data) return;

            updateBadge(data.unseen);

            // Browser notification on genuinely new mail (UIDNEXT increased)
            if (notificationsEnabled && data.has_new && !notifShown) {
                notifShown = true;
                setTimeout(function () { notifShown = false; }, 10000);

                if ('Notification' in window && Notification.permission === 'granted') {
                    new Notification('New mail arrived', {
                        body: data.unseen + ' unread message' + (data.unseen === 1 ? '' : 's') + ' in Inbox',
                        icon: '/favicon.ico',
                        tag:  'new-mail',
                    });
                }
            }
        })
        .catch(function () { /* ignore network errors */ });
    }

    var notificationsEnabled = {{ userSettings()->notifications_enabled ? 'true' : 'false' }};

    // Request notification permission once (non-blocking) when user has enabled notifications
    if (notificationsEnabled && 'Notification' in window && Notification.permission === 'default') {
        Notification.requestPermission();
    }

    // First check after 45 s, then every 45 s
    setInterval(checkNew, 45000);
})();
</script>

@stack('scripts')

@include(mailView('partials.undo-toast'))

</body>
</html>
