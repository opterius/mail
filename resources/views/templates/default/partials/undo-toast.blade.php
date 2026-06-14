{{-- Undo toast - rendered when a session('undo_block') payload is present.
     Stays visible ~5s, then auto-dismisses. Clicking "Undo" hits
     /screener (DELETE) which removes the just-created blocked row,
     effectively reverting the Block action. --}}
@if($undo = session('undo_block'))
<div x-data="{ open: true }"
     x-init="setTimeout(() => open = false, 5000)"
     x-show="open"
     x-transition.opacity.duration.300ms
     class="fixed bottom-6 left-1/2 -translate-x-1/2 z-50 flex items-center gap-3
            bg-gray-900 dark:bg-gray-800 text-white rounded-xl shadow-2xl
            px-4 py-3 text-sm min-w-[320px] max-w-md">
    <svg class="w-5 h-5 text-gray-300 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
              d="M18.364 18.364A9 9 0 005.636 5.636m12.728 12.728L5.636 5.636m12.728 12.728L18.364 5.636M5.636 18.364l12.728-12.728"/>
    </svg>
    <span class="flex-1">{{ $undo['message'] }}</span>
    <form method="POST" action="{{ route('screener.destroy') }}" class="inline">
        @csrf @method('DELETE')
        <input type="hidden" name="sender_email" value="{{ $undo['sender'] }}">
        <button type="submit"
                class="px-3 py-1 bg-white/10 hover:bg-white/20 rounded-lg
                       text-white font-medium text-[13px] transition-colors">
            Undo
        </button>
    </form>
    <button type="button" @click="open = false"
            class="text-gray-400 hover:text-white transition-colors"
            aria-label="Dismiss">
        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
        </svg>
    </button>
</div>
@endif
