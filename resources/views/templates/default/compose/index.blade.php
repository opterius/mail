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

@php
    $titles = ['compose' => 'New Message', 'reply' => 'Reply', 'forward' => 'Forward'];
    $pageTitle = $titles[$mode] ?? 'New Message';

    $origFolder = $originalFolder ?? null;
    $origUid    = $originalUid ?? null;
    $backHref   = ($origFolder !== null && $origUid !== null)
        ? route('message.show', ['folder' => rawurlencode($origFolder), 'uid' => $origUid])
        : route('inbox');
@endphp

@section('title', $pageTitle)

@section('content')
<div class="flex flex-col h-full"
     x-data="{
         showCc:  {{ $cc !== '' ? 'true' : 'false' }},
         showBcc: false,
         toSug:   [],
         ccSug:   [],
         bccSug:  [],
         async suggest(field, val) {
             const last = val.split(',').pop().trim();
             if (last.length < 2) { this[field + 'Sug'] = []; return; }
             try {
                 const r = await fetch('{{ route('contacts.autocomplete') }}?q=' + encodeURIComponent(last));
                 this[field + 'Sug'] = await r.json();
             } catch { this[field + 'Sug'] = []; }
         },
         pick(field, inputId, contact) {
             const el = document.getElementById(inputId);
             const parts = el.value.split(',');
             const label = contact.name
                 ? contact.name + ' <' + contact.email + '>'
                 : contact.email;
             parts[parts.length - 1] = ' ' + label;
             el.value = parts.join(',').replace(/^\s*,?\s*/, '');
             this[field + 'Sug'] = [];
             el.focus();
         }
     }">

    {{-- Toolbar --}}
    <div class="flex items-center gap-2 px-6 py-3 border-b border-gray-100 bg-white sticky top-0 z-10">
        <a href="{{ $backHref }}"
           class="flex items-center gap-1.5 px-3 py-1.5 text-sm text-gray-600 hover:bg-gray-100 rounded-lg transition-colors">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
            </svg>
            {{ $mode === 'compose' ? 'Inbox' : 'Back' }}
        </a>
        <span class="text-sm font-medium text-gray-700 ml-1">{{ $pageTitle }}</span>
    </div>

    {{-- Send error --}}
    @if($errors->has('send'))
        <div class="mx-6 mt-4 px-4 py-3 bg-red-50 border border-red-200 rounded-lg text-sm text-red-700">
            {{ $errors->first('send') }}
        </div>
    @endif

    {{-- Compose form --}}
    <form method="POST" action="{{ route('compose.send') }}" class="flex flex-col flex-1 min-h-0">
        @csrf

        {{-- Header fields --}}
        <div class="px-6 pt-5 pb-2 space-y-0 divide-y divide-gray-100 border-b border-gray-100">

            {{-- From --}}
            <div class="flex items-center gap-3 py-2.5">
                <span class="w-14 text-xs font-medium text-gray-400 flex-shrink-0 text-right">From</span>
                <span class="text-sm text-gray-700">
                    {{ $fromName ? $fromName . ' <' . $fromEmail . '>' : $fromEmail }}
                </span>
            </div>

            {{-- To --}}
            <div class="relative py-2.5">
                <div class="flex items-center gap-3">
                    <label for="to" class="w-14 text-xs font-medium text-gray-400 flex-shrink-0 text-right">To</label>
                    <input id="to"
                           name="to"
                           type="text"
                           value="{{ old('to', $to) }}"
                           placeholder="recipient@example.com"
                           autocomplete="off"
                           @input="suggest('to', $event.target.value)"
                           @keydown.escape="toSug = []"
                           class="flex-1 text-sm text-gray-800 outline-none placeholder-gray-300
                                  @error('to') border-b border-red-400 @enderror">
                    <div class="flex items-center gap-1 flex-shrink-0">
                        <button type="button"
                                @click="showCc = !showCc"
                                :class="showCc ? 'text-orange-500' : 'text-gray-400 hover:text-gray-600'"
                                class="px-2 py-1 text-xs font-medium transition-colors">Cc</button>
                        <button type="button"
                                @click="showBcc = !showBcc"
                                :class="showBcc ? 'text-orange-500' : 'text-gray-400 hover:text-gray-600'"
                                class="px-2 py-1 text-xs font-medium transition-colors">Bcc</button>
                    </div>
                </div>
                {{-- To suggestions --}}
                <div x-show="toSug.length > 0" x-cloak @click.outside="toSug = []"
                     class="absolute left-[4.25rem] right-0 mt-1 bg-white border border-gray-200 rounded-lg shadow-lg z-20 overflow-hidden">
                    <template x-for="c in toSug" :key="c.email">
                        <button type="button" @click="pick('to', 'to', c)"
                                class="flex items-center gap-3 w-full px-4 py-2.5 text-left hover:bg-gray-50 transition-colors">
                            <div class="w-7 h-7 rounded-full bg-orange-100 text-orange-600 flex items-center justify-center
                                        text-xs font-semibold uppercase flex-shrink-0"
                                 x-text="(c.name || c.email).charAt(0).toUpperCase()"></div>
                            <div class="min-w-0">
                                <p class="text-sm font-medium text-gray-800 truncate" x-text="c.name || c.email"></p>
                                <p x-show="c.name" class="text-xs text-gray-400 truncate" x-text="c.email"></p>
                            </div>
                        </button>
                    </template>
                </div>
            </div>

            @error('to')
                <p class="text-xs text-red-500 pb-1 pl-[4.25rem]">{{ $message }}</p>
            @enderror

            {{-- CC --}}
            <div class="relative py-2.5" x-show="showCc" x-cloak>
                <div class="flex items-center gap-3">
                    <label for="cc" class="w-14 text-xs font-medium text-gray-400 flex-shrink-0 text-right">Cc</label>
                    <input id="cc"
                           name="cc"
                           type="text"
                           value="{{ old('cc', $cc) }}"
                           placeholder="cc@example.com"
                           autocomplete="off"
                           @input="suggest('cc', $event.target.value)"
                           @keydown.escape="ccSug = []"
                           class="flex-1 text-sm text-gray-800 outline-none placeholder-gray-300">
                </div>
                <div x-show="ccSug.length > 0" x-cloak @click.outside="ccSug = []"
                     class="absolute left-[4.25rem] right-0 mt-1 bg-white border border-gray-200 rounded-lg shadow-lg z-20 overflow-hidden">
                    <template x-for="c in ccSug" :key="c.email">
                        <button type="button" @click="pick('cc', 'cc', c)"
                                class="flex items-center gap-3 w-full px-4 py-2.5 text-left hover:bg-gray-50 transition-colors">
                            <div class="w-7 h-7 rounded-full bg-orange-100 text-orange-600 flex items-center justify-center
                                        text-xs font-semibold uppercase flex-shrink-0"
                                 x-text="(c.name || c.email).charAt(0).toUpperCase()"></div>
                            <div class="min-w-0">
                                <p class="text-sm font-medium text-gray-800 truncate" x-text="c.name || c.email"></p>
                                <p x-show="c.name" class="text-xs text-gray-400 truncate" x-text="c.email"></p>
                            </div>
                        </button>
                    </template>
                </div>
            </div>

            {{-- BCC --}}
            <div class="relative py-2.5" x-show="showBcc" x-cloak>
                <div class="flex items-center gap-3">
                    <label for="bcc" class="w-14 text-xs font-medium text-gray-400 flex-shrink-0 text-right">Bcc</label>
                    <input id="bcc"
                           name="bcc"
                           type="text"
                           value="{{ old('bcc') }}"
                           placeholder="bcc@example.com"
                           autocomplete="off"
                           @input="suggest('bcc', $event.target.value)"
                           @keydown.escape="bccSug = []"
                           class="flex-1 text-sm text-gray-800 outline-none placeholder-gray-300">
                </div>
                <div x-show="bccSug.length > 0" x-cloak @click.outside="bccSug = []"
                     class="absolute left-[4.25rem] right-0 mt-1 bg-white border border-gray-200 rounded-lg shadow-lg z-20 overflow-hidden">
                    <template x-for="c in bccSug" :key="c.email">
                        <button type="button" @click="pick('bcc', 'bcc', c)"
                                class="flex items-center gap-3 w-full px-4 py-2.5 text-left hover:bg-gray-50 transition-colors">
                            <div class="w-7 h-7 rounded-full bg-orange-100 text-orange-600 flex items-center justify-center
                                        text-xs font-semibold uppercase flex-shrink-0"
                                 x-text="(c.name || c.email).charAt(0).toUpperCase()"></div>
                            <div class="min-w-0">
                                <p class="text-sm font-medium text-gray-800 truncate" x-text="c.name || c.email"></p>
                                <p x-show="c.name" class="text-xs text-gray-400 truncate" x-text="c.email"></p>
                            </div>
                        </button>
                    </template>
                </div>
            </div>

            {{-- Subject --}}
            <div class="flex items-center gap-3 py-2.5">
                <label for="subject" class="w-14 text-xs font-medium text-gray-400 flex-shrink-0 text-right">Subject</label>
                <input id="subject"
                       name="subject"
                       type="text"
                       value="{{ old('subject', $subject) }}"
                       placeholder="(no subject)"
                       class="flex-1 text-sm text-gray-800 outline-none placeholder-gray-300">
            </div>

        </div>

        {{-- Body --}}
        <div class="flex-1 flex flex-col px-6 pt-4 pb-4 min-h-0">
            <textarea id="body"
                      name="body"
                      class="flex-1 w-full text-sm text-gray-800 leading-relaxed outline-none resize-none
                             font-mono placeholder-gray-300 min-h-[320px]"
                      placeholder="Write your message here…">{{ old('body', $body) }}</textarea>
        </div>

        {{-- Footer / actions --}}
        <div class="flex items-center gap-3 px-6 py-3 border-t border-gray-100 bg-white sticky bottom-0">
            <button type="submit"
                    class="flex items-center gap-2 px-5 py-2 bg-orange-500 hover:bg-orange-600
                           text-white text-sm font-medium rounded-lg transition-colors">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                          d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8"/>
                </svg>
                Send
            </button>

            <a href="{{ $backHref }}"
               class="px-4 py-2 text-sm text-gray-500 hover:bg-gray-100 rounded-lg transition-colors">
                Discard
            </a>
        </div>
    </form>

</div>
@endsection
