{{--
 | Opterius Mail - Open source webmail.
 | Admin — domain DNS check.
 | https://opterius.com
 |
 | Copyright (c) Host Server SRL (Opterius)
 | @license  AGPL-3.0  https://opensource.org/license/agpl-v3
 | @author   Iosif Gabriel Chimilevschi <office@opterius.com>
--}}
@extends(mailView('admin.layouts.admin'))

@section('title', 'DNS Check')

@section('content')
<div class="p-6 max-w-3xl">

    {{-- Back + header --}}
    <div class="mb-6">
        <a href="{{ route('admin.domains.index') }}"
           class="text-[13px] text-gray-400 hover:text-gray-700 transition-colors flex items-center gap-1 mb-3 w-fit">
            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
            </svg>
            Back to domains
        </a>
        <h1 class="text-xl font-bold text-gray-900">DNS Check</h1>
        <p class="text-sm text-gray-400 mt-0.5 font-mono">{{ $mail_domain->domain }}</p>
    </div>

    <div class="space-y-4">

        {{-- MX --}}
        <div class="bg-white rounded-xl border border-gray-200 overflow-hidden">
            <div class="px-5 py-3 border-b border-gray-100 flex items-center justify-between">
                <div>
                    <h2 class="font-semibold text-gray-800 text-sm">MX Records</h2>
                    <p class="text-[13px] text-gray-400 mt-0.5">Mail exchange servers for this domain.</p>
                </div>
                @if($mx['found'])
                    <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[13px] font-medium bg-green-50 text-green-700">
                        <svg class="w-3 h-3" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414L8.414 15l-4.707-4.707a1 1 0 011.414-1.414L8.414 12.172l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/></svg>
                        Found
                    </span>
                @else
                    <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[13px] font-medium bg-red-50 text-red-700">
                        <svg class="w-3 h-3" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd"/></svg>
                        Missing
                    </span>
                @endif
            </div>
            <div class="px-5 py-4">
                @if($mx['found'])
                    <ul class="space-y-1">
                        @foreach($mx['records'] as $record)
                            <li class="font-mono text-[13px] text-gray-700">{{ $record }}</li>
                        @endforeach
                    </ul>
                @else
                    <p class="text-sm text-gray-400">No MX records found for {{ $mail_domain->domain }}.</p>
                @endif
            </div>
        </div>

        {{-- SPF --}}
        <div class="bg-white rounded-xl border border-gray-200 overflow-hidden">
            <div class="px-5 py-3 border-b border-gray-100 flex items-center justify-between">
                <div>
                    <h2 class="font-semibold text-gray-800 text-sm">SPF Record</h2>
                    <p class="text-[13px] text-gray-400 mt-0.5">Specifies which servers are allowed to send email.</p>
                </div>
                @if($spf['found'])
                    <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[13px] font-medium bg-green-50 text-green-700">
                        <svg class="w-3 h-3" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414L8.414 15l-4.707-4.707a1 1 0 011.414-1.414L8.414 12.172l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/></svg>
                        Found
                    </span>
                @else
                    <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[13px] font-medium bg-amber-50 text-amber-700">
                        Missing
                    </span>
                @endif
            </div>
            <div class="px-5 py-4">
                @if($spf['found'])
                    <code class="block bg-gray-50 border border-gray-200 rounded-lg px-3 py-2 text-[13px] font-mono text-gray-800 break-all">{{ $spf['record'] }}</code>
                @else
                    <p class="text-sm text-gray-400">No SPF TXT record found. Add one to prevent spoofing.</p>
                    <p class="text-[13px] text-gray-400 mt-2">Example: <code class="bg-gray-100 px-1 rounded">v=spf1 mx ~all</code></p>
                @endif
            </div>
        </div>

        {{-- DMARC --}}
        <div class="bg-white rounded-xl border border-gray-200 overflow-hidden">
            <div class="px-5 py-3 border-b border-gray-100 flex items-center justify-between">
                <div>
                    <h2 class="font-semibold text-gray-800 text-sm">DMARC Record</h2>
                    <p class="text-[13px] text-gray-400 mt-0.5">Policy for handling authentication failures.</p>
                </div>
                @if($dmarc['found'])
                    <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[13px] font-medium bg-green-50 text-green-700">
                        <svg class="w-3 h-3" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414L8.414 15l-4.707-4.707a1 1 0 011.414-1.414L8.414 12.172l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/></svg>
                        Found
                    </span>
                @else
                    <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[13px] font-medium bg-amber-50 text-amber-700">
                        Missing
                    </span>
                @endif
            </div>
            <div class="px-5 py-4">
                @if($dmarc['found'])
                    <code class="block bg-gray-50 border border-gray-200 rounded-lg px-3 py-2 text-[13px] font-mono text-gray-800 break-all">{{ $dmarc['record'] }}</code>
                @else
                    <p class="text-sm text-gray-400">No DMARC TXT record found at <code class="bg-gray-100 px-1 rounded">_dmarc.{{ $mail_domain->domain }}</code>.</p>
                    <p class="text-[13px] text-gray-400 mt-2">Example: <code class="bg-gray-100 px-1 rounded">v=DMARC1; p=none; rua=mailto:dmarc@{{ $mail_domain->domain }}</code></p>
                @endif
            </div>
        </div>

        {{-- DKIM --}}
        <div class="bg-white rounded-xl border border-gray-200 overflow-hidden">
            <div class="px-5 py-3 border-b border-gray-100 flex items-center justify-between">
                <h2 class="font-semibold text-gray-800 text-sm">DKIM Keys</h2>
                <a href="{{ route('admin.dkim.index') }}"
                   class="text-[13px] text-orange-500 hover:text-orange-700 transition-colors">Manage keys →</a>
            </div>
            <div class="px-5 py-4">
                @if(empty($dkim))
                    <p class="text-sm text-gray-400">No DKIM keys configured for this domain. Generate one in DKIM settings.</p>
                @else
                    <ul class="space-y-3">
                        @foreach($dkim as $entry)
                            <li class="flex items-center justify-between gap-4">
                                <p class="text-[13px] font-mono text-gray-700">{{ $entry['host'] }}</p>
                                @if($entry['published'])
                                    <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[13px] font-medium bg-green-50 text-green-700 flex-shrink-0">
                                        <svg class="w-3 h-3" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414L8.414 15l-4.707-4.707a1 1 0 011.414-1.414L8.414 12.172l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/></svg>
                                        Published
                                    </span>
                                @else
                                    <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[13px] font-medium bg-amber-50 text-amber-700 flex-shrink-0">
                                        Not published
                                    </span>
                                @endif
                            </li>
                        @endforeach
                    </ul>
                @endif
            </div>
        </div>

    </div>

    <p class="mt-4 text-[13px] text-gray-400">DNS lookup performed server-side at {{ now()->format('H:i:s') }} UTC. Results may be cached by your server's resolver.</p>

</div>
@endsection
