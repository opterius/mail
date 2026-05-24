{{--
 | Opterius Mail - Open source webmail.
 | Admin — mail groups.
 | https://opterius.com
 |
 | Copyright (c) Host Server SRL (Opterius)
 | @license  AGPL-3.0  https://opensource.org/license/agpl-v3
 | @author   Iosif Gabriel Chimilevschi <office@opterius.com>
--}}
@extends(mailView('admin.layouts.admin'))

@section('title', 'Groups')

@section('content')
<div class="p-6" x-data="groupsPage()">

    {{-- Header --}}
    <div class="flex items-center justify-between mb-6">
        <div>
            <h1 class="text-xl font-bold text-gray-900">Mail Groups</h1>
            <p class="text-sm text-gray-400 mt-0.5">Apply sending limits to sets of users.</p>
        </div>
        <button @click="openCreate()"
                class="flex items-center gap-1.5 px-4 py-2 bg-orange-500 hover:bg-orange-600 text-white text-sm font-medium rounded-lg transition-colors">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
            </svg>
            New Group
        </button>
    </div>

    {{-- Groups table --}}
    @if($groups->isEmpty())
        <div class="bg-white rounded-xl border border-gray-200 px-6 py-16 text-center">
            <p class="text-gray-400 text-sm">No groups yet. Create one to start applying sending limits.</p>
        </div>
    @else
        <div class="bg-white rounded-xl border border-gray-200 overflow-hidden">
            <table class="w-full">
                <thead>
                    <tr class="border-b border-gray-100 text-left">
                        <th class="px-4 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wide">Name</th>
                        <th class="px-4 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wide">Limits</th>
                        <th class="px-4 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wide">Max recipients/msg</th>
                        <th class="px-4 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wide">Members</th>
                        <th class="px-4 py-3"></th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-50">
                    @foreach($groups as $group)
                        <tr class="hover:bg-gray-50">
                            <td class="px-4 py-3">
                                <p class="font-medium text-gray-900">{{ $group->name }}</p>
                                @if($group->description)
                                    <p class="text-xs text-gray-400 mt-0.5">{{ $group->description }}</p>
                                @endif
                            </td>
                            <td class="px-4 py-3 text-sm text-gray-600">{{ $group->limitsLabel() }}</td>
                            <td class="px-4 py-3 text-sm text-gray-600">
                                {{ $group->max_recipients ?? 'Unlimited' }}
                            </td>
                            <td class="px-4 py-3 text-sm text-gray-600">{{ number_format($group->members_count) }}</td>
                            <td class="px-4 py-3 text-right">
                                <button @click="openEdit({{ Js::from($group) }})"
                                        class="text-xs text-gray-400 hover:text-gray-800 transition-colors mr-3">
                                    Edit
                                </button>
                                <form method="POST"
                                      action="{{ route('admin.groups.destroy', ['mail_group' => $group->id]) }}"
                                      class="inline"
                                      onsubmit="return confirm('Delete group {{ addslashes($group->name) }}? Members will be unassigned.')">
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

    {{-- ------------------------------------------------------------------ --}}
    {{-- Create / Edit modal                                                  --}}
    {{-- ------------------------------------------------------------------ --}}
    <div x-show="open" x-cloak
         class="fixed inset-0 z-50 flex items-center justify-center"
         @keydown.escape.window="open = false">

        {{-- Backdrop --}}
        <div class="absolute inset-0 bg-black/40" @click="open = false"></div>

        {{-- Panel --}}
        <div class="relative bg-white rounded-2xl shadow-xl w-full max-w-lg mx-4 p-6" @click.stop>

            <h2 class="text-base font-bold text-gray-900 mb-5" x-text="editId ? 'Edit Group' : 'New Group'"></h2>

            <form :action="editId ? '{{ url('admin/groups') }}/' + editId : '{{ route('admin.groups.store') }}'"
                  method="POST" class="space-y-4">
                @csrf
                <template x-if="editId"><input type="hidden" name="_method" value="PUT"></template>

                <div>
                    <label class="block text-xs font-medium text-gray-600 mb-1">Group name <span class="text-red-500">*</span></label>
                    <input type="text" name="name" :value="form.name" required maxlength="100"
                           class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-orange-400 focus:border-transparent"
                           placeholder="e.g. Standard, Premium, Restricted">
                </div>

                <div>
                    <label class="block text-xs font-medium text-gray-600 mb-1">Description</label>
                    <input type="text" name="description" :value="form.description" maxlength="500"
                           class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-orange-400 focus:border-transparent"
                           placeholder="Optional description">
                </div>

                <div>
                    <label class="block text-xs font-medium text-gray-600 mb-2">Sending limits <span class="text-gray-400 font-normal">(leave blank for unlimited)</span></label>
                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <label class="text-[11px] text-gray-400">Per hour</label>
                            <input type="number" name="hourly_limit" :value="form.hourly_limit" min="1"
                                   class="w-full border border-gray-200 rounded-lg px-3 py-1.5 text-sm focus:outline-none focus:ring-2 focus:ring-orange-400">
                        </div>
                        <div>
                            <label class="text-[11px] text-gray-400">Per day</label>
                            <input type="number" name="daily_limit" :value="form.daily_limit" min="1"
                                   class="w-full border border-gray-200 rounded-lg px-3 py-1.5 text-sm focus:outline-none focus:ring-2 focus:ring-orange-400">
                        </div>
                        <div>
                            <label class="text-[11px] text-gray-400">Per week</label>
                            <input type="number" name="weekly_limit" :value="form.weekly_limit" min="1"
                                   class="w-full border border-gray-200 rounded-lg px-3 py-1.5 text-sm focus:outline-none focus:ring-2 focus:ring-orange-400">
                        </div>
                        <div>
                            <label class="text-[11px] text-gray-400">Per month</label>
                            <input type="number" name="monthly_limit" :value="form.monthly_limit" min="1"
                                   class="w-full border border-gray-200 rounded-lg px-3 py-1.5 text-sm focus:outline-none focus:ring-2 focus:ring-orange-400">
                        </div>
                    </div>
                </div>

                <div>
                    <label class="block text-xs font-medium text-gray-600 mb-1">Max recipients per message</label>
                    <input type="number" name="max_recipients" :value="form.max_recipients" min="1" max="9999"
                           class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-orange-400 focus:border-transparent"
                           placeholder="Unlimited">
                </div>

                <div class="flex justify-end gap-3 pt-2">
                    <button type="button" @click="open = false"
                            class="px-4 py-2 text-sm text-gray-600 hover:text-gray-900 transition-colors">
                        Cancel
                    </button>
                    <button type="submit"
                            class="px-5 py-2 bg-orange-500 hover:bg-orange-600 text-white text-sm font-medium rounded-lg transition-colors">
                        <span x-text="editId ? 'Save changes' : 'Create group'"></span>
                    </button>
                </div>
            </form>
        </div>
    </div>

</div>
@endsection

@push('scripts')
<script>
function groupsPage() {
    return {
        open: false,
        editId: null,
        form: { name: '', description: '', hourly_limit: '', daily_limit: '', weekly_limit: '', monthly_limit: '', max_recipients: '' },

        openCreate() {
            this.editId = null;
            this.form = { name: '', description: '', hourly_limit: '', daily_limit: '', weekly_limit: '', monthly_limit: '', max_recipients: '' };
            this.open = true;
        },

        openEdit(group) {
            this.editId = group.id;
            this.form = {
                name:            group.name,
                description:     group.description ?? '',
                hourly_limit:    group.hourly_limit ?? '',
                daily_limit:     group.daily_limit ?? '',
                weekly_limit:    group.weekly_limit ?? '',
                monthly_limit:   group.monthly_limit ?? '',
                max_recipients:  group.max_recipients ?? '',
            };
            this.open = true;
        },
    };
}
</script>
@endpush
