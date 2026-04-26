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

@section('title', 'Contacts')

@section('content')
@php
    $colorOptions = \App\Models\ContactGroup::COLORS;
@endphp
<div class="flex flex-col h-full"
     x-data="{
         showForm: false,
         showImport: false,
         showGroups: false,
         filterGroup: null,
         contacts: {{ Js::from($contacts->map(fn($c) => ['id' => $c->id, 'group_ids' => $c->groups->pluck('id')])) }},
         visible(contactId, groupIds) {
             if (this.filterGroup === null) return true;
             const c = this.contacts.find(x => x.id === contactId);
             return c ? c.group_ids.includes(this.filterGroup) : false;
         }
     }">

    {{-- Toolbar --}}
    <div class="flex items-center justify-between px-6 py-3 border-b border-gray-100 dark:border-gray-700 bg-white dark:bg-gray-900 sticky top-0 z-10">
        <div class="flex items-center gap-3">
            <h1 class="text-sm font-semibold text-gray-800 dark:text-gray-100">Contacts</h1>
            @if($contacts->count() > 0)
                <span class="text-xs text-gray-400">{{ $contacts->count() }}</span>
            @endif
        </div>
        <div class="flex items-center gap-2">
            {{-- Groups manager --}}
            <button @click="showGroups = !showGroups"
                    :class="showGroups ? 'bg-gray-100 dark:bg-gray-800 text-gray-700 dark:text-gray-200' : 'text-gray-500 dark:text-gray-400 hover:bg-gray-100 dark:hover:bg-gray-800'"
                    class="flex items-center gap-1.5 px-3 py-1.5 text-sm rounded-lg transition-colors">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                          d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z"/>
                </svg>
                Groups
            </button>

            {{-- Export --}}
            <div x-data="{ open: false }" class="relative">
                <button @click="open = !open" @keydown.escape.window="open = false"
                        class="flex items-center gap-1.5 px-3 py-1.5 text-sm text-gray-500 dark:text-gray-400 hover:bg-gray-100 dark:hover:bg-gray-800 rounded-lg transition-colors">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/>
                    </svg>
                    Export
                </button>
                <div x-show="open" @click.outside="open = false" style="display:none"
                     class="absolute right-0 mt-1 w-36 bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-lg shadow-lg z-20 py-1">
                    <a href="{{ route('contacts.export', ['format' => 'vcf']) }}"
                       class="block px-4 py-2 text-sm text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-700">vCard (.vcf)</a>
                    <a href="{{ route('contacts.export', ['format' => 'csv']) }}"
                       class="block px-4 py-2 text-sm text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-700">CSV</a>
                </div>
            </div>

            {{-- Import --}}
            <button @click="showImport = !showImport"
                    class="flex items-center gap-1.5 px-3 py-1.5 text-sm text-gray-500 dark:text-gray-400 hover:bg-gray-100 dark:hover:bg-gray-800 rounded-lg transition-colors">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l4-4m0 0l4 4m-4-4v12"/>
                </svg>
                Import
            </button>

            <button @click="showForm = !showForm"
                    :class="showForm ? 'bg-gray-100 dark:bg-gray-800 text-gray-700 dark:text-gray-200' : 'text-orange-600 hover:bg-orange-50 dark:hover:bg-orange-900/20'"
                    class="flex items-center gap-1.5 px-3 py-1.5 text-sm font-medium rounded-lg transition-colors">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                </svg>
                New Contact
            </button>
        </div>
    </div>

    {{-- Groups panel --}}
    <div x-show="showGroups" x-cloak
         class="px-6 py-4 border-b border-gray-100 dark:border-gray-700 bg-gray-50 dark:bg-gray-800/50">
        <div class="flex flex-wrap gap-2 mb-3">
            <button @click="filterGroup = null"
                    :class="filterGroup === null ? 'ring-2 ring-orange-400' : ''"
                    class="px-3 py-1 text-xs font-medium bg-gray-100 dark:bg-gray-700 text-gray-600 dark:text-gray-300 rounded-full transition-all">
                All contacts
            </button>
            @foreach($groups as $group)
            <div class="flex items-center gap-1">
                <button @click="filterGroup = {{ $group->id }}"
                        :class="filterGroup === {{ $group->id }} ? 'ring-2 ring-offset-1 ring-orange-400' : ''"
                        class="{{ $group->badgeClass() }} flex items-center gap-1.5 px-3 py-1 text-xs font-medium rounded-full transition-all">
                    <span class="w-1.5 h-1.5 rounded-full {{ $group->dotClass() }}"></span>
                    {{ $group->name }}
                </button>
                {{-- Edit group --}}
                <div x-data="{ editOpen: false }" class="relative">
                    <button @click="editOpen = !editOpen"
                            class="p-0.5 text-gray-400 hover:text-gray-600 dark:hover:text-gray-300 rounded transition-colors">
                        <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                  d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                        </svg>
                    </button>
                    <div x-show="editOpen" @click.outside="editOpen = false" style="display:none"
                         class="absolute left-0 mt-1 w-52 bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-lg shadow-lg z-30 p-3">
                        <form method="POST" action="{{ route('contact-groups.update', $group) }}" class="space-y-2">
                            @csrf @method('PUT')
                            <input name="name" type="text" value="{{ $group->name }}"
                                   class="w-full px-2 py-1 text-xs border border-gray-200 dark:border-gray-600 rounded bg-white dark:bg-gray-700 dark:text-gray-100 outline-none focus:ring-1 focus:ring-orange-400">
                            <div class="flex gap-1 flex-wrap">
                                @foreach($colorOptions as $colorKey => $colorVal)
                                <label class="cursor-pointer">
                                    <input type="radio" name="color" value="{{ $colorKey }}" class="sr-only"
                                           {{ $group->color === $colorKey ? 'checked' : '' }}>
                                    <span class="block w-5 h-5 rounded-full {{ $colorVal['dot'] }} ring-2 ring-transparent hover:ring-gray-300 checked:ring-orange-400"></span>
                                </label>
                                @endforeach
                            </div>
                            <div class="flex gap-2">
                                <button type="submit" class="px-2 py-1 text-xs font-medium bg-orange-500 text-white rounded hover:bg-orange-600 transition-colors">Save</button>
                            </div>
                        </form>
                        <form method="POST" action="{{ route('contact-groups.destroy', $group) }}" class="mt-2"
                              onsubmit="return confirm('Delete group {{ addslashes($group->name) }}?')">
                            @csrf @method('DELETE')
                            <button type="submit" class="text-xs text-red-500 hover:text-red-700 transition-colors">Delete group</button>
                        </form>
                    </div>
                </div>
            </div>
            @endforeach
        </div>

        {{-- Create new group --}}
        <form method="POST" action="{{ route('contact-groups.store') }}" class="flex items-center gap-2">
            @csrf
            <input name="name" type="text" placeholder="New group name" required
                   class="px-3 py-1.5 text-xs border border-gray-200 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 dark:text-gray-100 outline-none focus:ring-1 focus:ring-orange-400 w-40">
            <div class="flex gap-1">
                @foreach($colorOptions as $colorKey => $colorVal)
                <label class="cursor-pointer">
                    <input type="radio" name="color" value="{{ $colorKey }}" class="sr-only peer" {{ $colorKey === 'blue' ? 'checked' : '' }}>
                    <span class="block w-5 h-5 rounded-full {{ $colorVal['dot'] }} ring-2 ring-transparent peer-checked:ring-orange-400 hover:ring-gray-300 transition-all"></span>
                </label>
                @endforeach
            </div>
            <button type="submit"
                    class="px-3 py-1.5 text-xs font-medium bg-orange-500 hover:bg-orange-600 text-white rounded-lg transition-colors">
                Add group
            </button>
        </form>
    </div>

    {{-- Import form --}}
    <div x-show="showImport" x-cloak class="px-6 py-4 border-b border-gray-100 dark:border-gray-700 bg-blue-50 dark:bg-blue-900/10">
        <form method="POST" action="{{ route('contacts.import') }}" enctype="multipart/form-data"
              class="flex items-center gap-3 flex-wrap">
            @csrf
            <label class="text-sm font-medium text-gray-700 dark:text-gray-300">Import vCard or CSV:</label>
            <input type="file" name="import_file" accept=".vcf,.csv,.txt" required
                   class="text-sm text-gray-600 dark:text-gray-400 file:mr-3 file:py-1.5 file:px-3 file:rounded-lg file:border-0 file:text-sm file:font-medium file:bg-orange-100 file:text-orange-700 hover:file:bg-orange-200">
            <button type="submit"
                    class="px-4 py-1.5 text-sm font-medium bg-orange-500 hover:bg-orange-600 text-white rounded-lg transition-colors">
                Import
            </button>
            <button type="button" @click="showImport = false"
                    class="px-4 py-1.5 text-sm text-gray-500 hover:bg-gray-200 dark:hover:bg-gray-700 rounded-lg transition-colors">
                Cancel
            </button>
        </form>
    </div>

    {{-- Flash --}}
    @if(session('success'))
        <div class="mx-6 mt-4 px-4 py-3 bg-green-50 dark:bg-green-900/20 border border-green-200 dark:border-green-800 rounded-lg text-sm text-green-700 dark:text-green-300">
            {{ session('success') }}
        </div>
    @endif

    {{-- New contact form --}}
    <div x-show="showForm" x-cloak class="px-6 py-5 border-b border-gray-100 dark:border-gray-700 bg-gray-50 dark:bg-gray-800/50">
        <form method="POST" action="{{ route('contacts.store') }}" enctype="multipart/form-data" class="space-y-3 max-w-2xl">
            @csrf
            <div class="grid grid-cols-2 gap-3">
                <div>
                    <label class="block text-xs font-medium text-gray-500 dark:text-gray-400 mb-1">Name</label>
                    <input name="name" type="text" value="{{ old('name') }}" placeholder="Full name"
                           class="w-full px-3 py-2 text-sm border border-gray-200 dark:border-gray-600 rounded-lg outline-none focus:ring-2 focus:ring-orange-400 bg-white dark:bg-gray-700 dark:text-gray-100">
                </div>
                <div>
                    <label class="block text-xs font-medium text-gray-500 dark:text-gray-400 mb-1">Email <span class="text-red-400">*</span></label>
                    <input name="email" type="email" value="{{ old('email') }}" placeholder="email@example.com" required
                           class="w-full px-3 py-2 text-sm border border-gray-200 dark:border-gray-600 rounded-lg outline-none focus:ring-2 focus:ring-orange-400 bg-white dark:bg-gray-700 dark:text-gray-100 @error('email') border-red-400 @enderror">
                    @error('email')<p class="text-xs text-red-500 mt-1">{{ $message }}</p>@enderror
                </div>
            </div>
            <div class="grid grid-cols-2 gap-3">
                <div>
                    <label class="block text-xs font-medium text-gray-500 dark:text-gray-400 mb-1">Phone</label>
                    <input name="phone" type="text" value="{{ old('phone') }}" placeholder="+1 555 000 0000"
                           class="w-full px-3 py-2 text-sm border border-gray-200 dark:border-gray-600 rounded-lg outline-none focus:ring-2 focus:ring-orange-400 bg-white dark:bg-gray-700 dark:text-gray-100">
                </div>
                <div>
                    <label class="block text-xs font-medium text-gray-500 dark:text-gray-400 mb-1">Birthday</label>
                    <input name="birthday" type="date" value="{{ old('birthday') }}"
                           class="w-full px-3 py-2 text-sm border border-gray-200 dark:border-gray-600 rounded-lg outline-none focus:ring-2 focus:ring-orange-400 bg-white dark:bg-gray-700 dark:text-gray-100">
                </div>
            </div>
            <div class="grid grid-cols-2 gap-3">
                <div>
                    <label class="block text-xs font-medium text-gray-500 dark:text-gray-400 mb-1">Website</label>
                    <input name="website" type="url" value="{{ old('website') }}" placeholder="https://example.com"
                           class="w-full px-3 py-2 text-sm border border-gray-200 dark:border-gray-600 rounded-lg outline-none focus:ring-2 focus:ring-orange-400 bg-white dark:bg-gray-700 dark:text-gray-100">
                </div>
                <div>
                    <label class="block text-xs font-medium text-gray-500 dark:text-gray-400 mb-1">Address</label>
                    <input name="address" type="text" value="{{ old('address') }}" placeholder="City, Country"
                           class="w-full px-3 py-2 text-sm border border-gray-200 dark:border-gray-600 rounded-lg outline-none focus:ring-2 focus:ring-orange-400 bg-white dark:bg-gray-700 dark:text-gray-100">
                </div>
            </div>
            @if($groups->count() > 0)
            <div>
                <label class="block text-xs font-medium text-gray-500 dark:text-gray-400 mb-1.5">Groups</label>
                <div class="flex flex-wrap gap-2">
                    @foreach($groups as $group)
                    <label class="flex items-center gap-1.5 cursor-pointer">
                        <input type="checkbox" name="groups[]" value="{{ $group->id }}"
                               class="w-3.5 h-3.5 rounded accent-orange-500">
                        <span class="{{ $group->badgeClass() }} flex items-center gap-1 px-2 py-0.5 text-xs rounded-full">
                            <span class="w-1.5 h-1.5 rounded-full {{ $group->dotClass() }}"></span>
                            {{ $group->name }}
                        </span>
                    </label>
                    @endforeach
                </div>
            </div>
            @endif
            <div class="flex items-center gap-2 pt-1">
                <button type="submit"
                        class="px-4 py-2 text-sm font-medium bg-orange-500 hover:bg-orange-600 text-white rounded-lg transition-colors">
                    Save Contact
                </button>
                <button type="button" @click="showForm = false"
                        class="px-4 py-2 text-sm text-gray-500 hover:bg-gray-100 dark:hover:bg-gray-700 rounded-lg transition-colors">
                    Cancel
                </button>
            </div>
        </form>
    </div>

    {{-- Empty state --}}
    @if($contacts->isEmpty())
        <div class="flex flex-col items-center justify-center flex-1 px-6 py-20 text-center">
            <div class="w-12 h-12 rounded-xl bg-gray-100 dark:bg-gray-800 flex items-center justify-center mb-4">
                <svg class="w-6 h-6 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                          d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"/>
                </svg>
            </div>
            <p class="text-sm font-medium text-gray-500 dark:text-gray-400">No contacts yet</p>
            <p class="text-xs text-gray-400 dark:text-gray-500 mt-1">Click "New Contact" to add one</p>
        </div>

    {{-- Contact list --}}
    @else
        <ul class="divide-y divide-gray-100 dark:divide-gray-800">
            @foreach($contacts as $contact)
            <li x-show="visible({{ $contact->id }}, {{ Js::from($contact->groups->pluck('id')) }})"
                class="flex items-center gap-4 px-6 py-3.5 hover:bg-gray-50 dark:hover:bg-gray-800/50 transition-colors group">

                {{-- Avatar --}}
                @if($contact->avatarUrl())
                    <img src="{{ $contact->avatarUrl() }}" alt=""
                         class="flex-shrink-0 w-9 h-9 rounded-full object-cover">
                @else
                    <div class="flex-shrink-0 w-9 h-9 rounded-full bg-orange-100 dark:bg-orange-900/30 text-orange-600 dark:text-orange-400
                                flex items-center justify-center text-sm font-semibold select-none uppercase">
                        {{ $contact->initial() }}
                    </div>
                @endif

                {{-- Info --}}
                <div class="flex-1 min-w-0">
                    <div class="flex items-center gap-2 flex-wrap">
                        @if($contact->name)
                            <p class="text-sm font-medium text-gray-900 dark:text-gray-100 truncate">{{ $contact->name }}</p>
                        @else
                            <p class="text-sm font-medium text-gray-900 dark:text-gray-100 truncate">{{ $contact->email }}</p>
                        @endif
                        @foreach($contact->groups as $group)
                            <span class="{{ $group->badgeClass() }} flex items-center gap-1 px-2 py-0.5 text-xs rounded-full">
                                <span class="w-1.5 h-1.5 rounded-full {{ $group->dotClass() }}"></span>
                                {{ $group->name }}
                            </span>
                        @endforeach
                    </div>
                    @if($contact->name)
                        <p class="text-xs text-gray-400 dark:text-gray-500 truncate">{{ $contact->email }}</p>
                    @endif
                </div>

                @if($contact->phone)
                    <span class="hidden sm:block flex-shrink-0 text-xs text-gray-400 dark:text-gray-500">{{ $contact->phone }}</span>
                @endif

                {{-- Actions --}}
                <div class="flex items-center gap-1 opacity-0 group-hover:opacity-100 transition-opacity">
                    <a href="{{ route('contacts.show', $contact) }}"
                       class="p-1.5 text-gray-400 hover:text-gray-700 dark:hover:text-gray-200 hover:bg-gray-100 dark:hover:bg-gray-700 rounded-lg transition-colors"
                       title="Edit">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                  d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                        </svg>
                    </a>
                    <form method="POST" action="{{ route('contacts.destroy', $contact) }}"
                          onsubmit="return confirm('Delete {{ addslashes($contact->name ?: $contact->email) }}?')">
                        @csrf @method('DELETE')
                        <button type="submit"
                                class="p-1.5 text-gray-400 hover:text-red-500 hover:bg-red-50 dark:hover:bg-red-900/20 rounded-lg transition-colors"
                                title="Delete">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                      d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                            </svg>
                        </button>
                    </form>
                    <a href="{{ route('compose') }}?to={{ rawurlencode($contact->name ? "\"{$contact->name}\" <{$contact->email}>" : $contact->email) }}"
                       class="p-1.5 text-gray-400 hover:text-orange-500 hover:bg-orange-50 dark:hover:bg-orange-900/20 rounded-lg transition-colors"
                       title="Send email">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                  d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8"/>
                        </svg>
                    </a>
                </div>
            </li>
            @endforeach
        </ul>
    @endif

</div>
@endsection
