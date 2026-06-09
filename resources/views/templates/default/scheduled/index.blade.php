{{-- Opterius Mail — Scheduled Emails --}}
@extends(mailView('layouts.app'))
@section('title', 'Scheduled')
@section('content')
<div class="p-6 max-w-2xl mx-auto">
    <div class="mb-6">
        <h1 class="text-xl font-bold text-gray-900 dark:text-gray-100">Scheduled Emails</h1>
        <p class="text-sm text-gray-400 mt-0.5">Emails waiting to be sent at the scheduled time.</p>
    </div>

    @if(session('success'))
        <div class="mb-4 p-3 rounded-lg bg-green-50 border border-green-200 text-sm text-green-700">{{ session('success') }}</div>
    @endif

    @if($scheduled->isEmpty())
        <div class="bg-white dark:bg-gray-900 rounded-xl border border-gray-200 dark:border-gray-700 px-6 py-16 text-center">
            <svg class="w-10 h-10 text-gray-300 dark:text-gray-600 mx-auto mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
            </svg>
            <p class="text-sm text-gray-400">No scheduled emails. Use "Send Later" when composing.</p>
        </div>
    @else
        <div class="bg-white dark:bg-gray-900 rounded-xl border border-gray-200 dark:border-gray-700 divide-y divide-gray-50 dark:divide-gray-800">
            @foreach($scheduled as $s)
            <div class="flex items-center gap-4 px-4 py-3">
                <div class="flex-1 min-w-0">
                    <p class="text-sm font-medium text-gray-800 dark:text-gray-200 truncate">{{ $s->subject ?: '(no subject)' }}</p>
                    <p class="text-[13px] text-gray-400 mt-0.5">To: {{ $s->to }}</p>
                </div>
                <div class="flex items-center gap-3 flex-shrink-0">
                    <span style="display:inline-flex;align-items:center;padding:2px 8px;border-radius:4px;font-size:13px;background:#eff6ff;color:#1e40af;border:1px solid #bfdbfe;">
                        📅 {{ $s->send_at->format('M j, g:i A') }}
                    </span>
                    <form method="POST" action="{{ route('scheduled.destroy', $s) }}" class="inline"
                          onsubmit="return confirm('Cancel this scheduled email?')">
                        @csrf @method('DELETE')
                        <button type="submit" class="text-[13px] text-red-400 hover:text-red-600 transition-colors">Cancel</button>
                    </form>
                </div>
            </div>
            @endforeach
        </div>
    @endif
</div>
@endsection
