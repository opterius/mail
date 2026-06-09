{{-- Opterius Mail — Snoozed --}}
@extends(mailView('layouts.app'))
@section('title', 'Snoozed')
@section('content')
<div class="p-6 max-w-2xl mx-auto">
    <div class="mb-6">
        <h1 class="text-xl font-bold text-gray-900 dark:text-gray-100">Snoozed</h1>
        <p class="text-sm text-gray-400 mt-0.5">These emails will return to your inbox at the scheduled time.</p>
    </div>

    @if($snoozed->isEmpty())
        <div class="bg-white dark:bg-gray-900 rounded-xl border border-gray-200 dark:border-gray-700 px-6 py-16 text-center">
            <svg class="w-10 h-10 text-gray-300 dark:text-gray-600 mx-auto mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
            </svg>
            <p class="text-sm text-gray-400">Nothing snoozed. Snooze emails from the message view.</p>
        </div>
    @else
        <div class="bg-white dark:bg-gray-900 rounded-xl border border-gray-200 dark:border-gray-700 divide-y divide-gray-50 dark:divide-gray-800">
            @foreach($snoozed as $s)
            <div class="flex items-center gap-4 px-4 py-3">
                <div class="flex-1 min-w-0">
                    <p class="text-sm font-medium text-gray-800 dark:text-gray-200 truncate">{{ $s->from_name ?: $s->from_email }}</p>
                    <p class="text-[13px] text-gray-400 truncate mt-0.5">{{ $s->subject ?: '(no subject)' }}</p>
                </div>
                <div class="flex items-center gap-3 flex-shrink-0">
                    <span style="display:inline-flex;align-items:center;padding:2px 8px;border-radius:4px;font-size:13px;background:#fef3c7;color:#92400e;border:1px solid #fde68a;">
                        ⏰ {{ $s->snooze_until->format('M j, g:i A') }}
                    </span>
                    <form method="POST" action="{{ route('snooze.destroy') }}" class="inline">
                        @csrf @method('DELETE')
                        <input type="hidden" name="imap_uid" value="{{ $s->imap_uid }}">
                        <input type="hidden" name="mailbox" value="{{ $s->mailbox }}">
                        <button type="submit" class="text-[13px] text-gray-400 hover:text-gray-700 dark:hover:text-gray-200 transition-colors">Unsnooze</button>
                    </form>
                </div>
            </div>
            @endforeach
        </div>
    @endif
</div>
@endsection
