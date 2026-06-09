{{-- Opterius Mail — Feed --}}
@extends(mailView('layouts.app'))
@section('title', 'Feed')
@section('content')
<div class="p-6 max-w-2xl mx-auto">
    <div class="mb-6">
        <h1 class="text-xl font-bold text-gray-900 dark:text-gray-100">Feed</h1>
        <p class="text-sm text-gray-400 mt-0.5">Newsletters and subscriptions. Read when you want — they never crowd your inbox.</p>
    </div>

    @if(session('success'))
        <div class="mb-4 p-3 rounded-lg bg-green-50 border border-green-200 text-sm text-green-700">{{ session('success') }}</div>
    @endif

    @if($feedSenders->isEmpty())
        <div class="bg-white dark:bg-gray-900 rounded-xl border border-gray-200 dark:border-gray-700 px-6 py-16 text-center">
            <svg class="w-10 h-10 text-gray-300 dark:text-gray-600 mx-auto mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M6 5c7.18 0 13 5.82 13 13M6 11a7 7 0 017 7m-6 0a1 1 0 11-2 0 1 1 0 012 0z"/>
            </svg>
            <p class="text-sm text-gray-400">No feed senders yet. Move newsletters here from the message view.</p>
        </div>
    @else
        <div class="bg-white dark:bg-gray-900 rounded-xl border border-gray-200 dark:border-gray-700 divide-y divide-gray-50 dark:divide-gray-800">
            @foreach($feedSenders as $s)
            <div class="flex items-center gap-3 px-4 py-3">
                <div class="w-7 h-7 rounded-full bg-blue-100 dark:bg-blue-900/30 text-blue-600 dark:text-blue-400 flex items-center justify-center text-[13px] font-semibold flex-shrink-0">
                    {{ strtoupper(substr($s->sender_email, 0, 1)) }}
                </div>
                <span class="flex-1 text-sm text-gray-700 dark:text-gray-300">{{ $s->sender_email }}</span>
                <form method="POST" action="{{ route('feed.destroy') }}" class="inline">
                    @csrf @method('DELETE')
                    <input type="hidden" name="sender_email" value="{{ $s->sender_email }}">
                    <button type="submit" class="text-[13px] text-gray-400 hover:text-red-500 transition-colors">Remove</button>
                </form>
            </div>
            @endforeach
        </div>
    @endif
</div>
@endsection
