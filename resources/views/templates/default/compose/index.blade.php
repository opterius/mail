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
         draftUid:    {{ (int)($draftUid ?? 0) }},
         draftFolder: {{ Js::from($draftFolder ?? '') }},
         draftStatus: '',
         draftTimer:  null,
         csrfToken:   {{ Js::from(csrf_token()) }},
         draftUrl:    {{ Js::from(route('compose.draft')) }},

         init() { this.scheduleDraft(); },

         scheduleDraft() {
             clearTimeout(this.draftTimer);
             this.draftTimer = setTimeout(() => this.saveDraft(), 60000);
         },

         resetDraftTimer() {
             this.draftStatus = '';
             this.scheduleDraft();
         },

         async saveDraft() {
             this.draftStatus = 'saving';
             const fd = new FormData();
             const g  = (id) => (document.getElementById(id)?.value ?? '');
             fd.append('to',      g('to'));
             fd.append('cc',      g('cc'));
             fd.append('bcc',     g('bcc'));
             fd.append('subject', g('subject'));
             fd.append('body',    g('body'));
             if (this.draftUid > 0)       fd.append('draft_uid',    this.draftUid);
             if (this.draftFolder !== '')  fd.append('draft_folder', this.draftFolder);
             try {
                 const r    = await fetch(this.draftUrl, {
                     method: 'POST',
                     headers: { 'X-CSRF-TOKEN': this.csrfToken, 'Accept': 'application/json' },
                     body: fd,
                 });
                 const json = await r.json();
                 if (json.ok) {
                     if (json.uid > 0)       this.draftUid    = json.uid;
                     if (json.folder !== '') this.draftFolder = json.folder;
                     this.draftStatus = 'saved';
                     setTimeout(() => { if (this.draftStatus === 'saved') this.draftStatus = ''; }, 3000);
                 } else {
                     this.draftStatus = 'error';
                 }
             } catch { this.draftStatus = 'error'; }
             this.scheduleDraft();
         },

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
     }"
     @keydown.window.debounce.2000ms="resetDraftTimer()">

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

        {{-- Draft save status indicator --}}
        <div class="ml-auto flex items-center gap-2">
            <span x-show="draftStatus === 'saving'" x-cloak
                  class="text-xs text-gray-400 flex items-center gap-1">
                <svg class="w-3 h-3 animate-spin" fill="none" viewBox="0 0 24 24">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8H4z"></path>
                </svg>
                Saving…
            </span>
            <span x-show="draftStatus === 'saved'" x-cloak
                  class="text-xs text-green-600 flex items-center gap-1">
                <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                </svg>
                Draft saved
            </span>
            <span x-show="draftStatus === 'error'" x-cloak
                  class="text-xs text-red-500">Could not save draft</span>

            <button type="button" @click="saveDraft()"
                    class="text-xs text-gray-400 hover:text-gray-600 px-2 py-1 rounded hover:bg-gray-100 transition-colors">
                Save draft
            </button>
        </div>
    </div>

    {{-- Send error --}}
    @if($errors->has('send'))
        <div class="mx-6 mt-4 px-4 py-3 bg-red-50 border border-red-200 rounded-lg text-sm text-red-700">
            {{ $errors->first('send') }}
        </div>
    @endif

    {{-- Compose form --}}
    <form id="compose-form" method="POST" action="{{ route('compose.send') }}" enctype="multipart/form-data"
          class="flex flex-col flex-1 min-h-0"
          x-data="{
              attachFiles: [],
              attachDt: new DataTransfer(),
              addFiles(e) {
                  for (const f of e.target.files) {
                      this.attachDt.items.add(f);
                      this.attachFiles.push({ name: f.name, size: f.size });
                  }
                  document.getElementById('attach-input').files = this.attachDt.files;
                  e.target.value = '';
              },
              removeFile(i) {
                  this.attachDt.items.remove(i);
                  this.attachFiles.splice(i, 1);
                  document.getElementById('attach-input').files = this.attachDt.files;
              },
              fmtSize(b) {
                  if (b < 1024) return b + ' B';
                  if (b < 1048576) return (b / 1024).toFixed(1) + ' KB';
                  return (b / 1048576).toFixed(1) + ' MB';
              }
          }">
        @csrf
        {{-- Draft tracking — values kept in sync by Alpine on the outer div --}}
        <input type="hidden" name="draft_uid"    :value="draftUid">
        <input type="hidden" name="draft_folder" :value="draftFolder">

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

        {{-- Attachments --}}
        <div class="px-6 pb-3 border-t border-gray-100">
            {{-- Hidden real file input that the form submits --}}
            <input type="file" id="attach-input" name="attachments[]" multiple class="hidden">

            {{-- Attach button --}}
            <div class="flex items-center gap-3 pt-3">
                <label for="attach-trigger"
                       class="flex items-center gap-1.5 cursor-pointer text-sm text-gray-500 hover:text-gray-700 transition-colors select-none">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                              d="M15.172 7l-6.586 6.586a2 2 0 102.828 2.828l6.414-6.586a4 4 0 00-5.656-5.656l-6.415 6.585a6 6 0 108.486 8.486L20.5 13"/>
                    </svg>
                    Attach files
                    <input type="file" id="attach-trigger" multiple class="hidden" @change="addFiles($event)">
                </label>
                <span class="text-xs text-gray-400">Max {{ config('smtp.max_attachment_mb', 25) }} MB per file</span>
            </div>

            {{-- Selected file chips --}}
            <div x-show="attachFiles.length > 0" x-cloak class="flex flex-wrap gap-2 mt-2">
                <template x-for="(f, i) in attachFiles" :key="i">
                    <div class="flex items-center gap-1.5 px-3 py-1.5 bg-gray-100 rounded-lg text-sm">
                        <svg class="w-3.5 h-3.5 text-gray-500 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                  d="M15.172 7l-6.586 6.586a2 2 0 102.828 2.828l6.414-6.586a4 4 0 00-5.656-5.656l-6.415 6.585a6 6 0 108.486 8.486L20.5 13"/>
                        </svg>
                        <span class="text-gray-700 truncate max-w-[140px]" x-text="f.name"></span>
                        <span class="text-xs text-gray-400 flex-shrink-0" x-text="fmtSize(f.size)"></span>
                        <button type="button" @click="removeFile(i)"
                                class="ml-0.5 text-gray-400 hover:text-red-500 transition-colors flex-shrink-0">
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                            </svg>
                        </button>
                    </div>
                </template>
            </div>
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
