{{-- Opterius Mail — Reply Later --}}
@extends(mailView('layouts.app'))
@section('title', 'Reply Later')
@section('content')
<div class="p-6 max-w-2xl mx-auto">
    <div class="mb-6">
        <h1 class="text-xl font-bold text-gray-900 dark:text-gray-100">Reply Later</h1>
        <p class="text-sm text-gray-400 mt-0.5">Emails you need to reply to when you have time.</p>
    </div>

    @if($emails->isEmpty())
        <div class="bg-white dark:bg-gray-900 rounded-xl border border-gray-200 dark:border-gray-700 px-6 py-16 text-center">
            <svg class="w-10 h-10 text-gray-300 dark:text-gray-600 mx-auto mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M8 10h.01M12 10h.01M16 10h.01M9 16H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-5l-5 5v-5z"/>
            </svg>
            <p class="text-sm text-gray-400">No emails marked for later reply.</p>
        </div>
    @else
        <div class="bg-white dark:bg-gray-900 rounded-xl border border-gray-200 dark:border-gray-700 divide-y divide-gray-50 dark:divide-gray-800">
            @foreach($emails as $e)
            <div class="flex items-center gap-4 px-4 py-3">
                <div class="flex-1 min-w-0">
                    <p class="text-sm font-medium text-gray-800 dark:text-gray-200 truncate">{{ $e->from_name ?: $e->from_email }}</p>
                    <p class="text-[13px] text-gray-400 truncate mt-0.5">{{ $e->subject ?: '(no subject)' }}</p>
                </div>
                <div class="flex items-center gap-3 flex-shrink-0 text-[13px]">
                    <a href="{{ route('compose.reply', ['folder' => rawurlencode($e->mailbox), 'uid' => $e->imap_uid]) }}"
                       class="text-orange-500 hover:text-orange-700 transition-colors font-medium">Reply now</a>
                    <a href="{{ route('message.show', ['folder' => rawurlencode($e->mailbox), 'uid' => $e->imap_uid]) }}"
                       class="text-gray-400 hover:text-gray-700 dark:hover:text-gray-200 transition-colors">View</a>
                    <form method="POST" action="{{ route('reply-later.destroy') }}" class="inline">
                        @csrf @method('DELETE')
                        <input type="hidden" name="imap_uid" value="{{ $e->imap_uid }}">
                        <input type="hidden" name="mailbox" value="{{ $e->mailbox }}">
                        <button type="submit" class="text-gray-300 hover:text-red-400 transition-colors">✕</button>
                    </form>
                </div>
            </div>
            @endforeach
        </div>
    @endif
</div>
@endsection
