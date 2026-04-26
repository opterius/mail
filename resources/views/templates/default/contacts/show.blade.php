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
@php $colorOptions = \App\Models\ContactGroup::COLORS; @endphp
<div class="flex flex-col h-full">

    {{-- Toolbar --}}
    <div class="flex items-center gap-2 px-6 py-3 border-b border-gray-100 dark:border-gray-700 bg-white dark:bg-gray-900 sticky top-0 z-10">
        <a href="{{ route('contacts') }}"
           class="flex items-center gap-1.5 px-3 py-1.5 text-sm text-gray-600 dark:text-gray-400 hover:bg-gray-100 dark:hover:bg-gray-800 rounded-lg transition-colors">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
            </svg>
            Contacts
        </a>
        <div class="ml-auto flex items-center gap-1">
            <a href="{{ route('compose') }}?to={{ rawurlencode($contact->name ? "\"{$contact->name}\" <{$contact->email}>" : $contact->email) }}"
               class="flex items-center gap-1.5 px-3 py-1.5 text-sm text-gray-600 dark:text-gray-400 hover:bg-gray-100 dark:hover:bg-gray-800 rounded-lg transition-colors">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                          d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8"/>
                </svg>
                Send Email
            </a>
            <form method="POST" action="{{ route('contacts.destroy', $contact) }}"
                  onsubmit="return confirm('Delete this contact?')">
                @csrf @method('DELETE')
                <button type="submit"
                        class="flex items-center gap-1.5 px-3 py-1.5 text-sm text-gray-600 dark:text-gray-400 hover:bg-red-50 dark:hover:bg-red-900/20 hover:text-red-600 dark:hover:text-red-400 rounded-lg transition-colors">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                              d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                    </svg>
                    Delete
                </button>
            </form>
        </div>
    </div>

    {{-- Flash --}}
    @if(session('success'))
        <div class="mx-6 mt-4 px-4 py-3 bg-green-50 dark:bg-green-900/20 border border-green-200 dark:border-green-800 rounded-lg text-sm text-green-700 dark:text-green-300">
            {{ session('success') }}
        </div>
    @endif

    {{-- Edit form --}}
    <div class="flex-1 px-6 py-6 overflow-auto">
        <div class="max-w-lg">

            <form method="POST" action="{{ route('contacts.update', $contact) }}"
                  enctype="multipart/form-data" class="space-y-5"
                  x-data="{
                      previewUrl: {{ Js::from($contact->avatarUrl()) }},
                      previewFile(e) {
                          const f = e.target.files[0];
                          if (f) this.previewUrl = URL.createObjectURL(f);
                      }
                  }">
                @csrf @method('PUT')

                {{-- Avatar upload --}}
                <div class="flex items-center gap-5">
                    <div class="relative flex-shrink-0 group cursor-pointer" @click="$refs.avatarInput.click()">
                        <template x-if="previewUrl">
                            <img :src="previewUrl" alt=""
                                 class="w-20 h-20 rounded-full object-cover ring-2 ring-gray-200 dark:ring-gray-600">
                        </template>
                        <template x-if="!previewUrl">
                            <div class="w-20 h-20 rounded-full bg-orange-100 dark:bg-orange-900/30 text-orange-600 dark:text-orange-400
                                        flex items-center justify-center text-2xl font-semibold select-none uppercase
                                        ring-2 ring-gray-200 dark:ring-gray-600">
                                {{ $contact->initial() }}
                            </div>
                        </template>
                        <div class="absolute inset-0 rounded-full bg-black/40 opacity-0 group-hover:opacity-100 transition-opacity
                                    flex items-center justify-center">
                            <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                      d="M3 9a2 2 0 012-2h.93a2 2 0 001.664-.89l.812-1.22A2 2 0 0110.07 4h3.86a2 2 0 011.664.89l.812 1.22A2 2 0 0018.07 7H19a2 2 0 012 2v9a2 2 0 01-2 2H5a2 2 0 01-2-2V9z"/>
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 13a3 3 0 11-6 0 3 3 0 016 0"/>
                            </svg>
                        </div>
                        <input x-ref="avatarInput" type="file" name="avatar" accept="image/*"
                               class="sr-only" @change="previewFile($event)">
                    </div>
                    <div>
                        <p class="font-medium text-gray-900 dark:text-gray-100">{{ $contact->name ?: $contact->email }}</p>
                        @if($contact->name)
                            <p class="text-sm text-gray-400 dark:text-gray-500">{{ $contact->email }}</p>
                        @endif
                        <p class="text-xs text-gray-400 dark:text-gray-500 mt-1">Click photo to change</p>
                    </div>
                </div>

                {{-- Name & Email --}}
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-xs font-medium text-gray-500 dark:text-gray-400 mb-1.5">Name</label>
                        <input id="name" name="name" type="text" value="{{ old('name', $contact->name) }}"
                               placeholder="Full name"
                               class="w-full px-3 py-2 text-sm border border-gray-200 dark:border-gray-600 rounded-lg outline-none
                                      focus:ring-2 focus:ring-orange-400 bg-white dark:bg-gray-800 dark:text-gray-100">
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-gray-500 dark:text-gray-400 mb-1.5">Email <span class="text-red-400">*</span></label>
                        <input id="email" name="email" type="email" value="{{ old('email', $contact->email) }}" required
                               class="w-full px-3 py-2 text-sm border border-gray-200 dark:border-gray-600 rounded-lg outline-none
                                      focus:ring-2 focus:ring-orange-400 bg-white dark:bg-gray-800 dark:text-gray-100
                                      @error('email') border-red-400 @enderror">
                        @error('email')<p class="text-xs text-red-500 mt-1">{{ $message }}</p>@enderror
                    </div>
                </div>

                {{-- Phone & Birthday --}}
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-xs font-medium text-gray-500 dark:text-gray-400 mb-1.5">Phone</label>
                        <input name="phone" type="text" value="{{ old('phone', $contact->phone) }}"
                               placeholder="+1 555 000 0000"
                               class="w-full px-3 py-2 text-sm border border-gray-200 dark:border-gray-600 rounded-lg outline-none
                                      focus:ring-2 focus:ring-orange-400 bg-white dark:bg-gray-800 dark:text-gray-100">
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-gray-500 dark:text-gray-400 mb-1.5">Birthday</label>
                        <input name="birthday" type="date"
                               value="{{ old('birthday', $contact->birthday?->format('Y-m-d')) }}"
                               class="w-full px-3 py-2 text-sm border border-gray-200 dark:border-gray-600 rounded-lg outline-none
                                      focus:ring-2 focus:ring-orange-400 bg-white dark:bg-gray-800 dark:text-gray-100">
                    </div>
                </div>

                {{-- Website --}}
                <div>
                    <label class="block text-xs font-medium text-gray-500 dark:text-gray-400 mb-1.5">Website</label>
                    <input name="website" type="url" value="{{ old('website', $contact->website) }}"
                           placeholder="https://example.com"
                           class="w-full px-3 py-2 text-sm border border-gray-200 dark:border-gray-600 rounded-lg outline-none
                                  focus:ring-2 focus:ring-orange-400 bg-white dark:bg-gray-800 dark:text-gray-100">
                </div>

                {{-- Address --}}
                <div>
                    <label class="block text-xs font-medium text-gray-500 dark:text-gray-400 mb-1.5">Address</label>
                    <input name="address" type="text" value="{{ old('address', $contact->address) }}"
                           placeholder="123 Main St, City, Country"
                           class="w-full px-3 py-2 text-sm border border-gray-200 dark:border-gray-600 rounded-lg outline-none
                                  focus:ring-2 focus:ring-orange-400 bg-white dark:bg-gray-800 dark:text-gray-100">
                </div>

                {{-- Notes --}}
                <div>
                    <label class="block text-xs font-medium text-gray-500 dark:text-gray-400 mb-1.5">Notes</label>
                    <textarea name="notes" rows="3" placeholder="Any notes about this contact…"
                              class="w-full px-3 py-2 text-sm border border-gray-200 dark:border-gray-600 rounded-lg outline-none
                                     focus:ring-2 focus:ring-orange-400 resize-none bg-white dark:bg-gray-800 dark:text-gray-100">{{ old('notes', $contact->notes) }}</textarea>
                </div>

                {{-- Groups --}}
                @if($groups->count() > 0)
                <div>
                    <label class="block text-xs font-medium text-gray-500 dark:text-gray-400 mb-2">Groups</label>
                    <div class="flex flex-wrap gap-2">
                        @foreach($groups as $group)
                        @php $checked = $contact->groups->contains($group->id); @endphp
                        <label class="flex items-center gap-1.5 cursor-pointer">
                            <input type="checkbox" name="groups[]" value="{{ $group->id }}"
                                   {{ $checked ? 'checked' : '' }}
                                   class="w-3.5 h-3.5 rounded accent-orange-500">
                            <span class="{{ $group->badgeClass() }} flex items-center gap-1 px-2.5 py-1 text-xs font-medium rounded-full">
                                <span class="w-1.5 h-1.5 rounded-full {{ $group->dotClass() }}"></span>
                                {{ $group->name }}
                            </span>
                        </label>
                        @endforeach
                    </div>
                </div>
                @endif

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
