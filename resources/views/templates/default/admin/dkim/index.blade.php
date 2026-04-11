{{--
 | Opterius Mail - Open source webmail.
 | Admin — DKIM key management.
 | https://opterius.com
 |
 | Copyright (c) Host Server SRL (Opterius)
 | @license  AGPL-3.0  https://opensource.org/license/agpl-v3
 | @author   Iosif Gabriel Chimilevschi <office@opterius.com>
--}}
@extends(mailView('admin.layouts.admin'))

@section('title', 'DKIM Keys')

@section('content')
<div class="p-6" x-data="{ showForm: false, showKey: null }">

    {{-- Header --}}
    <div class="flex items-center justify-between mb-6">
        <div>
            <h1 class="text-xl font-bold text-gray-900">DKIM Keys</h1>
            <p class="text-sm text-gray-400 mt-0.5">Generate 2048-bit RSA keypairs and publish the DNS record to enable email signing.</p>
        </div>
        <button @click="showForm = !showForm"
                class="flex items-center gap-1.5 px-4 py-2 bg-orange-500 hover:bg-orange-600 text-white text-sm font-medium rounded-lg transition-colors">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
            </svg>
            Generate key
        </button>
    </div>

    {{-- Generate form --}}
    <div x-show="showForm" x-cloak class="mb-6">
        <div class="bg-white rounded-xl border border-gray-200 p-5">
            <h2 class="font-semibold text-gray-800 text-sm mb-4">Generate new DKIM keypair</h2>
            <form method="POST" action="{{ route('admin.dkim.generate') }}" class="flex items-end gap-3 flex-wrap">
                @csrf
                <div class="flex-1 min-w-40">
                    <label class="block text-xs font-medium text-gray-600 mb-1.5">Domain <span class="text-red-500">*</span></label>
                    <input type="text" name="domain" required placeholder="example.com"
                           value="{{ old('domain') }}"
                           class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-orange-400">
                </div>
                <div class="w-36">
                    <label class="block text-xs font-medium text-gray-600 mb-1.5">Selector</label>
                    <input type="text" name="selector" placeholder="mail"
                           value="{{ old('selector', 'mail') }}"
                           class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-orange-400">
                </div>
                <button type="submit"
                        class="px-5 py-2 bg-orange-500 hover:bg-orange-600 text-white text-sm font-medium rounded-lg transition-colors whitespace-nowrap">
                    Generate
                </button>
                <p class="w-full text-xs text-gray-400 -mt-1">
                    Generating a key for an existing domain+selector will revoke and replace the previous key.
                </p>
            </form>
        </div>
    </div>

    {{-- Keys table --}}
    @if($keys->isEmpty())
        <div class="bg-white rounded-xl border border-gray-200 px-6 py-16 text-center text-sm text-gray-400">
            No DKIM keys yet. Generate one above.
        </div>
    @else
        <div class="space-y-4">
            @foreach($keys as $key)
                <div class="bg-white rounded-xl border border-gray-200 overflow-hidden">
                    <div class="px-5 py-4 flex items-center justify-between gap-4">
                        <div class="min-w-0">
                            <p class="font-semibold text-gray-900">{{ $key->domain }}</p>
                            <p class="text-xs text-gray-400 mt-0.5">
                                Selector: <span class="font-mono">{{ $key->selector }}</span>
                                &middot; Generated {{ $key->created_at->diffForHumans() }}
                            </p>
                        </div>
                        <div class="flex items-center gap-3 flex-shrink-0">
                            <button @click="showKey = (showKey === {{ $key->id }} ? null : {{ $key->id }})"
                                    class="text-xs text-orange-500 hover:text-orange-700 transition-colors">
                                <span x-text="showKey === {{ $key->id }} ? 'Hide DNS record' : 'Show DNS record'">Show DNS record</span>
                            </button>
                            <form method="POST" action="{{ route('admin.dkim.destroy', ['dkim_key' => $key->id]) }}"
                                  onsubmit="return confirm('Delete DKIM key for {{ $key->selector }}._domainkey.{{ $key->domain }}?')">
                                @csrf @method('DELETE')
                                <button type="submit" class="text-xs text-red-400 hover:text-red-600 transition-colors">Delete</button>
                            </form>
                        </div>
                    </div>

                    {{-- DNS record panel --}}
                    <div x-show="showKey === {{ $key->id }}" x-cloak class="border-t border-gray-100 px-5 py-4 bg-gray-50">
                        <p class="text-xs font-semibold text-gray-600 mb-2 uppercase tracking-wide">DNS TXT record to publish</p>
                        <div class="mb-3">
                            <p class="text-xs text-gray-500 mb-1">Record name:</p>
                            <div class="flex items-center gap-2">
                                <code class="flex-1 bg-white border border-gray-200 rounded-lg px-3 py-2 text-xs font-mono text-gray-800 break-all">{{ $key->dnsName() }}</code>
                                <button onclick="navigator.clipboard.writeText('{{ $key->dnsName() }}')"
                                        class="flex-shrink-0 px-2.5 py-1.5 bg-gray-200 hover:bg-gray-300 text-gray-700 text-xs rounded-lg transition-colors">Copy</button>
                            </div>
                        </div>
                        <div>
                            <p class="text-xs text-gray-500 mb-1">Record value (TXT):</p>
                            <div class="flex items-start gap-2">
                                <code class="flex-1 bg-white border border-gray-200 rounded-lg px-3 py-2 text-xs font-mono text-gray-800 break-all leading-relaxed">{{ $key->dns_record }}</code>
                                <button onclick="navigator.clipboard.writeText({{ Js::from($key->dns_record) }})"
                                        class="flex-shrink-0 mt-0 px-2.5 py-1.5 bg-gray-200 hover:bg-gray-300 text-gray-700 text-xs rounded-lg transition-colors">Copy</button>
                            </div>
                        </div>
                        <p class="text-xs text-gray-400 mt-3">
                            After publishing the DNS record, DKIM signing must be configured in your MTA (Postfix + OpenDKIM, or Dovecot LMTP).
                            DNS propagation can take up to 48 hours.
                        </p>
                    </div>
                </div>
            @endforeach
        </div>
    @endif

</div>
@endsection
