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

@section('title', $contact->name ?: $contact->email)

@section('content')
<div class="flex flex-col h-full">

    {{-- Toolbar --}}
    <div class="flex items-center gap-2 px-6 py-3 border-b border-gray-100 bg-white sticky top-0 z-10">
        <a href="{{ route('contacts') }}"
           class="flex items-center gap-1.5 px-3 py-1.5 text-sm text-gray-600 hover:bg-gray-100 rounded-lg transition-colors">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
            </svg>
            Contacts
        </a>

        <div class="ml-auto flex items-center gap-1">
            {{-- Compose to this contact --}}
            <a href="{{ route('compose') }}?to={{ rawurlencode($contact->name ? "\"{$contact->name}\" <{$contact->email}>" : $contact->email) }}"
               class="flex items-center gap-1.5 px-3 py-1.5 text-sm text-gray-600 hover:bg-gray-100 rounded-lg transition-colors">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                          d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8"/>
                </svg>
                Send Email
            </a>

            {{-- Delete --}}
            <form method="POST"
                  action="{{ route('contacts.destroy', $contact) }}"
                  onsubmit="return confirm('Delete this contact?')">
                @csrf
                @method('DELETE')
                <button type="submit"
                        class="flex items-center gap-1.5 px-3 py-1.5 text-sm text-gray-600 hover:bg-red-50 hover:text-red-600 rounded-lg transition-colors">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                              d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                    </svg>
                    Delete
                </button>
            </form>
        </div>
    </div>

    {{-- Edit form --}}
    <div class="flex-1 px-6 py-6 overflow-auto">
        <div class="max-w-lg">

            {{-- Avatar --}}
            <div class="flex items-center gap-4 mb-6">
                <div class="w-14 h-14 rounded-full bg-orange-100 text-orange-600
                            flex items-center justify-center text-xl font-semibold select-none uppercase">
                    {{ mb_substr($contact->name ?: $contact->email, 0, 1, 'UTF-8') }}
                </div>
                <div>
                    <p class="font-medium text-gray-900">{{ $contact->name ?: $contact->email }}</p>
                    @if($contact->name)
                        <p class="text-sm text-gray-400">{{ $contact->email }}</p>
                    @endif
                </div>
            </div>

            <form method="POST" action="{{ route('contacts.update', $contact) }}" class="space-y-4">
                @csrf
                @method('PUT')

                <div>
                    <label for="name" class="block text-xs font-medium text-gray-500 mb-1.5">Name</label>
                    <input id="name" name="name" type="text"
                           value="{{ old('name', $contact->name) }}"
                           placeholder="Full name"
                           class="w-full px-3 py-2 text-sm border border-gray-200 rounded-lg outline-none
                                  focus:ring-2 focus:ring-orange-400 focus:border-transparent">
                </div>

                <div>
                    <label for="email" class="block text-xs font-medium text-gray-500 mb-1.5">
                        Email <span class="text-red-400">*</span>
                    </label>
                    <input id="email" name="email" type="email"
                           value="{{ old('email', $contact->email) }}"
                           required
                           class="w-full px-3 py-2 text-sm border border-gray-200 rounded-lg outline-none
                                  focus:ring-2 focus:ring-orange-400 focus:border-transparent
                                  @error('email') border-red-400 @enderror">
                    @error('email')
                        <p class="text-xs text-red-500 mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="phone" class="block text-xs font-medium text-gray-500 mb-1.5">Phone</label>
                    <input id="phone" name="phone" type="text"
                           value="{{ old('phone', $contact->phone) }}"
                           placeholder="+1 555 000 0000"
                           class="w-full px-3 py-2 text-sm border border-gray-200 rounded-lg outline-none
                                  focus:ring-2 focus:ring-orange-400 focus:border-transparent">
                </div>

                <div>
                    <label for="notes" class="block text-xs font-medium text-gray-500 mb-1.5">Notes</label>
                    <textarea id="notes" name="notes" rows="4"
                              placeholder="Any notes about this contact…"
                              class="w-full px-3 py-2 text-sm border border-gray-200 rounded-lg outline-none
                                     focus:ring-2 focus:ring-orange-400 focus:border-transparent resize-none">{{ old('notes', $contact->notes) }}</textarea>
                </div>

                <div class="pt-1">
                    <button type="submit"
                            class="px-5 py-2 text-sm font-medium bg-orange-500 hover:bg-orange-600 text-white rounded-lg transition-colors">
                        Save Changes
                    </button>
                </div>
            </form>
        </div>
    </div>

</div>
@endsection
