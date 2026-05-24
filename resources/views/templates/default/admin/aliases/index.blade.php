{{--
 | Opterius Mail - Open source webmail.
 | Admin — mail aliases.
 | https://opterius.com
 |
 | Copyright (c) Host Server SRL (Opterius)
 | @license  AGPL-3.0  https://opensource.org/license/agpl-v3
 | @author   Iosif Gabriel Chimilevschi <office@opterius.com>
--}}
@extends(mailView('admin.layouts.admin'))

@section('title', 'Aliases')

@section('content')
<div class="p-6" x-data="aliasPage()">

    {{-- Header --}}
    <div class="flex items-center justify-between mb-6">
        <div>
            <h1 class="text-xl font-bold text-gray-900">Mail Aliases</h1>
            <p class="text-sm text-gray-400 mt-0.5">Forward addresses to one or more mailboxes.</p>
        </div>
        <button @click="openCreate()"
                class="flex items-center gap-1.5 px-4 py-2 bg-orange-500 hover:bg-orange-600 text-white text-sm font-medium rounded-lg transition-colors">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
            </svg>
            New alias
        </button>
    </div>

    {{-- Domain filter --}}
    @if($domains->isNotEmpty())
        <div class="mb-4 flex items-center gap-2 flex-wrap">
            <a href="{{ route('admin.aliases.index') }}"
               class="px-3 py-1.5 rounded-lg text-[13px] font-medium transition-colors {{ !request('domain') ? 'bg-orange-500 text-white' : 'bg-white border border-gray-200 text-gray-600 hover:bg-gray-50' }}">
                All domains
            </a>
            @foreach($domains as $d)
                <a href="{{ route('admin.aliases.index', ['domain' => $d]) }}"
                   class="px-3 py-1.5 rounded-lg text-[13px] font-medium transition-colors {{ request('domain') === $d ? 'bg-orange-500 text-white' : 'bg-white border border-gray-200 text-gray-600 hover:bg-gray-50' }}">
                    {{ $d }}
                </a>
            @endforeach
        </div>
    @endif

    {{-- Table --}}
    @if($aliases->isEmpty())
        <div class="bg-white rounded-xl border border-gray-200 px-6 py-16 text-center text-sm text-gray-400">
            No aliases found.
        </div>
    @else
        <div class="bg-white rounded-xl border border-gray-200 overflow-hidden">
            <table class="w-full">
                <thead>
                    <tr class="border-b border-gray-100 text-left">
                        <th class="px-4 py-3 text-[13px] font-semibold text-gray-500 uppercase tracking-wide">Alias</th>
                        <th class="px-4 py-3 text-[13px] font-semibold text-gray-500 uppercase tracking-wide">Destination</th>
                        <th class="px-4 py-3 text-[13px] font-semibold text-gray-500 uppercase tracking-wide">Status</th>
                        <th class="px-4 py-3"></th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-50">
                    @foreach($aliases as $alias)
                        <tr class="hover:bg-gray-50 {{ !$alias->is_active ? 'opacity-60' : '' }}">
                            <td class="px-4 py-3 text-sm font-medium text-gray-900">{{ $alias->alias_email }}</td>
                            <td class="px-4 py-3 text-sm text-gray-500">
                                <span class="text-gray-400 mr-1">→</span>{{ $alias->destination_email }}
                            </td>
                            <td class="px-4 py-3">
                                @if($alias->is_active)
                                    <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[13px] font-medium bg-green-50 text-green-700">Active</span>
                                @else
                                    <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[13px] font-medium bg-gray-100 text-gray-500">Disabled</span>
                                @endif
                            </td>
                            <td class="px-4 py-3 text-right whitespace-nowrap">
                                <button @click="openEdit({{ Js::from($alias) }})"
                                        class="text-[13px] text-gray-400 hover:text-gray-800 transition-colors mr-3">Edit</button>
                                <form method="POST" action="{{ route('admin.aliases.toggle', $alias) }}" class="inline mr-3">
                                    @csrf
                                    <button type="submit"
                                            class="text-[13px] {{ $alias->is_active ? 'text-yellow-500 hover:text-yellow-700' : 'text-green-500 hover:text-green-700' }} transition-colors">
                                        {{ $alias->is_active ? 'Disable' : 'Enable' }}
                                    </button>
                                </form>
                                <form method="POST" action="{{ route('admin.aliases.destroy', $alias) }}" class="inline"
                                      onsubmit="return confirm('Delete alias {{ addslashes($alias->alias_email) }}?')">
                                    @csrf @method('DELETE')
                                    <button type="submit" class="text-[13px] text-red-400 hover:text-red-600 transition-colors">Delete</button>
                                </form>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        @if($aliases->hasPages())
            <div class="mt-4">{{ $aliases->links() }}</div>
        @endif
    @endif

    {{-- Modal --}}
    <div x-show="open" x-cloak class="fixed inset-0 z-50 flex items-center justify-center" @keydown.escape.window="open=false">
        <div class="absolute inset-0 bg-black/40" @click="open=false"></div>
        <div class="relative bg-white rounded-2xl shadow-xl w-full max-w-lg mx-4 p-6" @click.stop>

            <h2 class="text-base font-bold text-gray-900 mb-5" x-text="editId ? 'Edit Alias' : 'New Alias'"></h2>

            <form :action="editId ? '{{ url('admin/aliases') }}/' + editId : '{{ route('admin.aliases.store') }}'"
                  method="POST" class="space-y-4">
                @csrf
                <input x-show="editId" type="hidden" name="_method" value="PUT">

                {{-- Create: alias address + domain --}}
                <div x-show="!editId">
                    <label class="block text-[13px] font-medium text-gray-600 mb-1.5">Alias address <span class="text-red-500">*</span></label>
                    <div class="flex items-stretch gap-0">
                        <input type="text" name="local" :required="!editId" :value="form.local"
                               placeholder="info"
                               class="flex-1 min-w-0 border border-gray-300 rounded-l-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-orange-400">
                        <span class="flex items-center bg-gray-100 border border-l-0 border-gray-300 px-3 text-sm text-gray-500">@</span>
                        <select name="domain_id" :required="!editId"
                                class="border border-l-0 border-gray-300 rounded-r-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-orange-400 bg-white">
                            <option value="">Domain...</option>
                            @foreach(\App\Models\MailDomain::orderBy('domain')->get() as $d)
                                <option value="{{ $d->id }}">{{ $d->domain }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>

                {{-- Edit: show current alias (read-only) --}}
                <div x-show="editId">
                    <label class="block text-[13px] font-medium text-gray-600 mb-1.5">Alias</label>
                    <input type="text" disabled :value="form.alias_email"
                           class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm bg-gray-50 text-gray-500 cursor-not-allowed">
                </div>

                <div>
                    <label class="block text-[13px] font-medium text-gray-600 mb-1.5">Destination email <span class="text-red-500">*</span></label>
                    <input type="email" name="destination_email" required :value="form.destination_email"
                           placeholder="user@example.com"
                           class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-orange-400">
                </div>

                <div class="flex justify-end gap-3 pt-2">
                    <button type="button" @click="open=false"
                            class="px-4 py-2 text-sm text-gray-600 hover:text-gray-900">Cancel</button>
                    <button type="submit"
                            class="px-5 py-2 bg-orange-500 hover:bg-orange-600 text-white text-sm font-medium rounded-lg transition-colors">
                        <span x-text="editId ? 'Save changes' : 'Create'"></span>
                    </button>
                </div>
            </form>
        </div>
    </div>

</div>
@endsection

@push('scripts')
<script>
function aliasPage() {
    return {
        open: false,
        editId: null,
        form: { local: '', alias_email: '', destination_email: '' },

        openCreate() {
            this.editId = null;
            this.form = { local: '', alias_email: '', destination_email: '' };
            this.open = true;
        },

        openEdit(alias) {
            this.editId = alias.id;
            this.form = {
                alias_email:       alias.alias_email,
                destination_email: alias.destination_email,
                local:             '',
            };
            this.open = true;
        },
    };
}
</script>
@endpush
