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

@section('title', 'Settings')

@section('content')
<div class="flex flex-col h-full">

    {{-- Toolbar --}}
    <div class="flex items-center px-6 py-3 border-b border-gray-100 dark:border-gray-700 bg-white dark:bg-gray-900 sticky top-0 z-10">
        <h1 class="text-sm font-semibold text-gray-800 dark:text-gray-200">Settings</h1>
    </div>

    {{-- Flash --}}
    @if(session('success'))
        <div class="mx-6 mt-4 px-4 py-3 bg-green-50 dark:bg-green-900/20 border border-green-200 dark:border-green-700 rounded-lg text-sm text-green-700 dark:text-green-400">
            {{ session('success') }}
        </div>
    @endif

    <div class="flex-1 overflow-auto px-6 py-6">
        <form method="POST" action="{{ route('settings.update') }}" class="max-w-xl space-y-8">
            @csrf

            {{-- Profile --}}
            <section>
                <h2 class="text-xs font-semibold text-gray-400 dark:text-gray-500 uppercase tracking-wider mb-3">Profile</h2>
                <div class="space-y-4">
                    <div>
                        <label for="display_name" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1.5">
                            Display Name
                        </label>
                        <input id="display_name" name="display_name" type="text"
                               value="{{ old('display_name', $settings->display_name) }}"
                               placeholder="{{ auth('web')->user()?->name }}"
                               class="w-full px-3 py-2 text-sm border border-gray-200 dark:border-gray-600 rounded-lg
                                      bg-white dark:bg-gray-800 text-gray-800 dark:text-gray-200 outline-none
                                      focus:ring-2 focus:ring-orange-400 focus:border-transparent">
                        <p class="text-xs text-gray-400 dark:text-gray-500 mt-1">Shown as sender name in outgoing messages</p>
                    </div>
                </div>
            </section>

            {{-- Signature --}}
            <section>
                <h2 class="text-xs font-semibold text-gray-400 dark:text-gray-500 uppercase tracking-wider mb-3">Signature</h2>
                <textarea id="signature" name="signature" rows="5"
                          placeholder="Your email signature…"
                          class="w-full px-3 py-2 text-sm border border-gray-200 dark:border-gray-600 rounded-lg
                                 bg-white dark:bg-gray-800 text-gray-800 dark:text-gray-200 outline-none font-mono
                                 focus:ring-2 focus:ring-orange-400 focus:border-transparent resize-none">{{ old('signature', $settings->signature) }}</textarea>
                <p class="text-xs text-gray-400 dark:text-gray-500 mt-1">Auto-appended to all outgoing messages. Plain text only.</p>
            </section>

            {{-- Appearance --}}
            <section>
                <h2 class="text-xs font-semibold text-gray-400 dark:text-gray-500 uppercase tracking-wider mb-3">Appearance</h2>
                <div class="space-y-4">

                    {{-- Theme --}}
                    <div>
                        <p class="text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Theme</p>
                        <div class="flex gap-3">
                            <label class="relative cursor-pointer">
                                <input type="radio" name="theme" value="light" class="sr-only peer"
                                       {{ old('theme', $settings->theme) === 'light' ? 'checked' : '' }}>
                                <div class="flex items-center gap-2 px-4 py-2.5 rounded-lg border-2 transition-colors
                                            border-gray-200 dark:border-gray-600
                                            peer-checked:border-orange-500 peer-checked:bg-orange-50 dark:peer-checked:bg-orange-900/20">
                                    <svg class="w-4 h-4 text-yellow-500" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M10 2a1 1 0 011 1v1a1 1 0 11-2 0V3a1 1 0 011-1zm4 8a4 4 0 11-8 0 4 4 0 018 0zm-.464 4.95l.707.707a1 1 0 001.414-1.414l-.707-.707a1 1 0 00-1.414 1.414zm2.12-10.607a1 1 0 010 1.414l-.706.707a1 1 0 11-1.414-1.414l.707-.707a1 1 0 011.414 0zM17 11a1 1 0 100-2h-1a1 1 0 100 2h1zm-7 4a1 1 0 011 1v1a1 1 0 11-2 0v-1a1 1 0 011-1zM5.05 6.464A1 1 0 106.465 5.05l-.708-.707a1 1 0 00-1.414 1.414l.707.707zm1.414 8.486l-.707.707a1 1 0 01-1.414-1.414l.707-.707a1 1 0 011.414 1.414zM4 11a1 1 0 100-2H3a1 1 0 000 2h1z" clip-rule="evenodd"/>
                                    </svg>
                                    <span class="text-sm font-medium text-gray-700 dark:text-gray-300">Light</span>
                                </div>
                            </label>
                            <label class="relative cursor-pointer">
                                <input type="radio" name="theme" value="dark" class="sr-only peer"
                                       {{ old('theme', $settings->theme) === 'dark' ? 'checked' : '' }}>
                                <div class="flex items-center gap-2 px-4 py-2.5 rounded-lg border-2 transition-colors
                                            border-gray-200 dark:border-gray-600
                                            peer-checked:border-orange-500 peer-checked:bg-orange-50 dark:peer-checked:bg-orange-900/20">
                                    <svg class="w-4 h-4 text-indigo-400" fill="currentColor" viewBox="0 0 20 20">
                                        <path d="M17.293 13.293A8 8 0 016.707 2.707a8.001 8.001 0 1010.586 10.586z"/>
                                    </svg>
                                    <span class="text-sm font-medium text-gray-700 dark:text-gray-300">Dark</span>
                                </div>
                            </label>
                        </div>
                    </div>

                    {{-- Per page --}}
                    <div>
                        <label for="per_page" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1.5">
                            Messages per page
                        </label>
                        <select id="per_page" name="per_page"
                                class="px-3 py-2 text-sm border border-gray-200 dark:border-gray-600 rounded-lg
                                       bg-white dark:bg-gray-800 text-gray-800 dark:text-gray-200 outline-none
                                       focus:ring-2 focus:ring-orange-400 focus:border-transparent">
                            @foreach([10, 25, 50, 100] as $n)
                                <option value="{{ $n }}" {{ (int) old('per_page', $settings->per_page) === $n ? 'selected' : '' }}>
                                    {{ $n }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                </div>
            </section>

            {{-- Compose --}}
            <section>
                <h2 class="text-xs font-semibold text-gray-400 dark:text-gray-500 uppercase tracking-wider mb-3">Compose</h2>
                <div>
                    <label for="reply_behavior" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1.5">
                        Default reply action
                    </label>
                    <select id="reply_behavior" name="reply_behavior"
                            class="px-3 py-2 text-sm border border-gray-200 dark:border-gray-600 rounded-lg
                                   bg-white dark:bg-gray-800 text-gray-800 dark:text-gray-200 outline-none
                                   focus:ring-2 focus:ring-orange-400 focus:border-transparent">
                        <option value="reply"     {{ old('reply_behavior', $settings->reply_behavior) === 'reply'     ? 'selected' : '' }}>Reply</option>
                        <option value="reply_all" {{ old('reply_behavior', $settings->reply_behavior) === 'reply_all' ? 'selected' : '' }}>Reply All</option>
                    </select>
                </div>
            </section>

            {{-- Images --}}
            <section>
                <h2 class="text-xs font-semibold text-gray-400 dark:text-gray-500 uppercase tracking-wider mb-3">Remote Images</h2>
                <div>
                    <label for="image_loading" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1.5">
                        Load remote images in messages
                    </label>
                    <select id="image_loading" name="image_loading"
                            class="px-3 py-2 text-sm border border-gray-200 dark:border-gray-600 rounded-lg
                                   bg-white dark:bg-gray-800 text-gray-800 dark:text-gray-200 outline-none
                                   focus:ring-2 focus:ring-orange-400 focus:border-transparent">
                        <option value="ask"    {{ old('image_loading', $settings->image_loading) === 'ask'    ? 'selected' : '' }}>Ask each time</option>
                        <option value="always" {{ old('image_loading', $settings->image_loading) === 'always' ? 'selected' : '' }}>Always load</option>
                        <option value="never"  {{ old('image_loading', $settings->image_loading) === 'never'  ? 'selected' : '' }}>Never load</option>
                    </select>
                </div>
            </section>

            {{-- Save --}}
            <div class="pt-2 border-t border-gray-100 dark:border-gray-700">
                <button type="submit"
                        class="px-5 py-2 text-sm font-medium bg-orange-500 hover:bg-orange-600 text-white rounded-lg transition-colors">
                    Save Settings
                </button>
            </div>

        </form>
    </div>

</div>
@endsection
