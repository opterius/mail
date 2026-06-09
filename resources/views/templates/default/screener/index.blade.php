{{-- Opterius Mail — Screener --}}
@extends(mailView('layouts.app'))
@section('title', 'Screener')
@section('content')
<div class="p-6 max-w-2xl mx-auto">

    <div class="mb-6">
        <h1 class="text-xl font-bold text-gray-900 dark:text-gray-100">Screener</h1>
        <p class="text-sm text-gray-400 mt-0.5">Manage who can reach your inbox. Approved senders land directly in Inbox; blocked senders are silently ignored.</p>
    </div>

    @if(session('success'))
        <div class="mb-4 p-3 rounded-lg bg-green-50 border border-green-200 text-sm text-green-700">{{ session('success') }}</div>
    @endif

    {{-- Blocked senders --}}
    <div class="bg-white dark:bg-gray-900 rounded-xl border border-gray-200 dark:border-gray-700 mb-5">
        <div class="px-4 py-3 border-b border-gray-100 dark:border-gray-800 flex items-center justify-between">
            <h2 class="text-sm font-semibold text-gray-700 dark:text-gray-300">Blocked <span class="text-gray-400 font-normal">({{ $blocked->count() }})</span></h2>
        </div>
        @if($blocked->isEmpty())
            <p class="px-4 py-8 text-center text-sm text-gray-400">No blocked senders.</p>
        @else
            <ul class="divide-y divide-gray-50 dark:divide-gray-800">
                @foreach($blocked as $s)
                <li class="flex items-center gap-3 px-4 py-2.5">
                    <span class="flex-1 text-sm text-gray-700 dark:text-gray-300">{{ $s->sender_email }}</span>
                    <form method="POST" action="{{ route('screener.approve') }}" class="inline">
                        @csrf <input type="hidden" name="sender_email" value="{{ $s->sender_email }}">
                        <button type="submit" class="text-[13px] text-green-600 hover:text-green-800 transition-colors">Approve</button>
                    </form>
                    <form method="POST" action="{{ route('screener.destroy') }}" class="inline">
                        @csrf @method('DELETE') <input type="hidden" name="sender_email" value="{{ $s->sender_email }}">
                        <button type="submit" class="text-[13px] text-gray-400 hover:text-red-500 transition-colors ml-3">Remove</button>
                    </form>
                </li>
                @endforeach
            </ul>
        @endif
    </div>

    {{-- Approved senders --}}
    <div class="bg-white dark:bg-gray-900 rounded-xl border border-gray-200 dark:border-gray-700">
        <div class="px-4 py-3 border-b border-gray-100 dark:border-gray-800">
            <h2 class="text-sm font-semibold text-gray-700 dark:text-gray-300">Approved <span class="text-gray-400 font-normal">({{ $approved->count() }})</span></h2>
        </div>
        @if($approved->isEmpty())
            <p class="px-4 py-8 text-center text-sm text-gray-400">No approved senders yet. Approve senders when reading emails.</p>
        @else
            <ul class="divide-y divide-gray-50 dark:divide-gray-800">
                @foreach($approved as $s)
                <li class="flex items-center gap-3 px-4 py-2.5">
                    <span class="flex-1 text-sm text-gray-700 dark:text-gray-300">{{ $s->sender_email }}</span>
                    <form method="POST" action="{{ route('screener.block') }}" class="inline">
                        @csrf <input type="hidden" name="sender_email" value="{{ $s->sender_email }}">
                        <button type="submit" class="text-[13px] text-red-400 hover:text-red-600 transition-colors">Block</button>
                    </form>
                    <form method="POST" action="{{ route('screener.destroy') }}" class="inline">
                        @csrf @method('DELETE') <input type="hidden" name="sender_email" value="{{ $s->sender_email }}">
                        <button type="submit" class="text-[13px] text-gray-400 hover:text-gray-600 transition-colors ml-3">Remove</button>
                    </form>
                </li>
                @endforeach
            </ul>
        @endif
    </div>
</div>
@endsection
