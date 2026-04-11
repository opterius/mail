{{--
 | Opterius Mail - Open source webmail.
 | Admin — autoresponders.
 | https://opterius.com
 |
 | Copyright (c) Host Server SRL (Opterius)
 | @license  AGPL-3.0  https://opensource.org/license/agpl-v3
 | @author   Iosif Gabriel Chimilevschi <office@opterius.com>
--}}
@extends(mailView('admin.layouts.admin'))

@section('title', 'Autoresponders')

@section('content')
<div class="p-6" x-data="arPage()">

    {{-- Header --}}
    <div class="flex items-center justify-between mb-6">
        <div>
            <h1 class="text-xl font-bold text-gray-900">Autoresponders</h1>
            <p class="text-sm text-gray-400 mt-0.5">Out-of-office and vacation replies, sent automatically via cron.</p>
        </div>
        <button @click="openCreate()"
                class="flex items-center gap-1.5 px-4 py-2 bg-orange-500 hover:bg-orange-600 text-white text-sm font-medium rounded-lg transition-colors">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
            </svg>
            New autoresponder
        </button>
    </div>

    {{-- Cron notice --}}
    <div class="mb-4 p-3 rounded-lg bg-amber-50 border border-amber-200 text-xs text-amber-700 flex items-start gap-2">
        <svg class="w-4 h-4 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
        </svg>
        <span>
            Autoresponders are sent by <code class="bg-amber-100 px-1 rounded">php artisan mail:autorespond</code>.
            Add it to cron (every 5–15 min) and set <code class="bg-amber-100 px-1 rounded">IMAP_MASTER_USER</code> /
            <code class="bg-amber-100 px-1 rounded">IMAP_MASTER_PASS</code> in <code class="bg-amber-100 px-1 rounded">.env</code>.
        </span>
    </div>

    {{-- Table --}}
    @if($autoresponders->isEmpty())
        <div class="bg-white rounded-xl border border-gray-200 px-6 py-16 text-center text-sm text-gray-400">
            No autoresponders yet.
        </div>
    @else
        <div class="bg-white rounded-xl border border-gray-200 overflow-hidden">
            <table class="w-full">
                <thead>
                    <tr class="border-b border-gray-100 text-left">
                        <th class="px-4 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wide">Email</th>
                        <th class="px-4 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wide">Subject</th>
                        <th class="px-4 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wide">Active from</th>
                        <th class="px-4 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wide">Status</th>
                        <th class="px-4 py-3"></th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-50">
                    @foreach($autoresponders as $ar)
                        <tr class="hover:bg-gray-50">
                            <td class="px-4 py-3 font-medium text-gray-900 text-sm">{{ $ar->email }}</td>
                            <td class="px-4 py-3 text-sm text-gray-600 max-w-xs truncate">{{ $ar->subject }}</td>
                            <td class="px-4 py-3 text-sm text-gray-500">
                                @if($ar->starts_at || $ar->ends_at)
                                    {{ $ar->starts_at?->format('M j') ?? '—' }}
                                    –
                                    {{ $ar->ends_at?->format('M j, Y') ?? 'indefinite' }}
                                @else
                                    Always
                                @endif
                            </td>
                            <td class="px-4 py-3">
                                @if($ar->isCurrentlyActive())
                                    <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-green-50 text-green-700">Active</span>
                                @elseif($ar->is_active)
                                    <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-yellow-50 text-yellow-700">Scheduled</span>
                                @else
                                    <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-gray-100 text-gray-500">Disabled</span>
                                @endif
                            </td>
                            <td class="px-4 py-3 text-right whitespace-nowrap">
                                <button @click="openEdit({{ Js::from($ar) }})"
                                        class="text-xs text-gray-400 hover:text-gray-800 transition-colors mr-2">Edit</button>
                                <form method="POST" action="{{ route('admin.autoresponders.toggle', $ar) }}" class="inline mr-2">
                                    @csrf
                                    <button type="submit" class="text-xs {{ $ar->is_active ? 'text-yellow-500 hover:text-yellow-700' : 'text-green-500 hover:text-green-700' }} transition-colors">
                                        {{ $ar->is_active ? 'Disable' : 'Enable' }}
                                    </button>
                                </form>
                                <form method="POST" action="{{ route('admin.autoresponders.destroy', $ar) }}" class="inline"
                                      onsubmit="return confirm('Delete autoresponder for {{ addslashes($ar->email) }}?')">
                                    @csrf @method('DELETE')
                                    <button type="submit" class="text-xs text-red-400 hover:text-red-600 transition-colors">Delete</button>
                                </form>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    @endif

    {{-- Modal --}}
    <div x-show="open" x-cloak class="fixed inset-0 z-50 flex items-center justify-center" @keydown.escape.window="open=false">
        <div class="absolute inset-0 bg-black/40" @click="open=false"></div>
        <div class="relative bg-white rounded-2xl shadow-xl w-full max-w-lg mx-4 p-6" @click.stop>

            <h2 class="text-base font-bold text-gray-900 mb-5" x-text="editId ? 'Edit Autoresponder' : 'New Autoresponder'"></h2>

            <form :action="editId ? '{{ url('admin/autoresponders') }}/' + editId : '{{ route('admin.autoresponders.store') }}'"
                  method="POST" class="space-y-4">
                @csrf
                <input x-show="editId" type="hidden" name="_method" value="PUT">

                <div>
                    <label class="block text-xs font-medium text-gray-600 mb-1">Email address <span class="text-red-500">*</span></label>
                    <input type="email" name="email" :value="form.email" required
                           class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-orange-400"
                           placeholder="user@example.com">
                </div>

                <div>
                    <label class="block text-xs font-medium text-gray-600 mb-1">Reply subject <span class="text-red-500">*</span></label>
                    <input type="text" name="subject" :value="form.subject" required maxlength="255"
                           class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-orange-400"
                           placeholder="Out of office: I'll be back on...">
                </div>

                <div>
                    <label class="block text-xs font-medium text-gray-600 mb-1">Reply body <span class="text-red-500">*</span></label>
                    <textarea name="body" :value="form.body" required rows="5"
                              class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-orange-400 resize-none"
                              placeholder="Thank you for your message. I am currently out of office..."></textarea>
                </div>

                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block text-xs font-medium text-gray-600 mb-1">Active from</label>
                        <input type="date" name="starts_at" :value="form.starts_at"
                               class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-orange-400">
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-gray-600 mb-1">Active until</label>
                        <input type="date" name="ends_at" :value="form.ends_at"
                               class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-orange-400">
                    </div>
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
function arPage() {
    return {
        open: false,
        editId: null,
        form: { email: '', subject: '', body: '', starts_at: '', ends_at: '' },

        openCreate() {
            this.editId = null;
            this.form = { email: '', subject: '', body: '', starts_at: '', ends_at: '' };
            this.open = true;
        },

        openEdit(ar) {
            this.editId = ar.id;
            this.form = {
                email:     ar.email,
                subject:   ar.subject,
                body:      ar.body,
                starts_at: ar.starts_at ?? '',
                ends_at:   ar.ends_at ?? '',
            };
            this.open = true;
        },
    };
}
</script>
@endpush
