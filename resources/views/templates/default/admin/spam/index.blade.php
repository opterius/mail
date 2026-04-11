{{--
 | Opterius Mail - Open source webmail.
 | Admin — spam settings.
 | https://opterius.com
 |
 | Copyright (c) Host Server SRL (Opterius)
 | @license  AGPL-3.0  https://opensource.org/license/agpl-v3
 | @author   Iosif Gabriel Chimilevschi <office@opterius.com>
--}}
@extends(mailView('admin.layouts.admin'))

@section('title', 'Spam Settings')

@section('content')
<div class="p-6 max-w-2xl">

    <div class="mb-6">
        <h1 class="text-xl font-bold text-gray-900">Spam Settings</h1>
        <p class="text-sm text-gray-400 mt-0.5">Configure how SpamAssassin scores are handled. Requires SpamAssassin installed and integrated with your MTA.</p>
    </div>

    <form method="POST" action="{{ route('admin.spam.update') }}" class="space-y-6">
        @csrf

        <div class="bg-white rounded-xl border border-gray-200 overflow-hidden">
            <div class="px-5 py-3 border-b border-gray-100">
                <h2 class="font-semibold text-gray-800 text-sm">Spam detection threshold</h2>
            </div>
            <div class="px-5 py-4 space-y-4">

                <div>
                    <label class="block text-xs font-medium text-gray-600 mb-1.5">
                        Spam score threshold
                        <span class="text-gray-400 font-normal">(SpamAssassin default: 5.0)</span>
                    </label>
                    <div class="flex items-center gap-3">
                        <input type="number" name="spam_score_threshold" step="0.1" min="0" max="20"
                               value="{{ old('spam_score_threshold', $settings['spam_score_threshold'] ?? '5.0') }}"
                               class="w-28 border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-orange-400">
                        <p class="text-xs text-gray-400">Messages scoring above this value are considered spam.</p>
                    </div>
                </div>

                <div>
                    <label class="block text-xs font-medium text-gray-600 mb-2">Action for spam messages</label>
                    <div class="space-y-2">
                        @foreach([
                            'tag'         => ['Tag subject line', 'Deliver to inbox with subject prefix (e.g. [SPAM] original subject)'],
                            'quarantine'  => ['Quarantine', 'Move to Junk/Spam folder automatically'],
                            'reject'      => ['Reject', 'Reject at SMTP level — sender receives a bounce'],
                        ] as $value => [$label, $desc])
                            <label class="flex items-start gap-3 p-3 rounded-lg border cursor-pointer transition-colors
                                          {{ old('spam_action', $settings['spam_action'] ?? 'tag') === $value
                                              ? 'border-orange-400 bg-orange-50'
                                              : 'border-gray-200 hover:bg-gray-50' }}">
                                <input type="radio" name="spam_action" value="{{ $value }}"
                                       {{ old('spam_action', $settings['spam_action'] ?? 'tag') === $value ? 'checked' : '' }}
                                       class="mt-0.5 accent-orange-500">
                                <div>
                                    <p class="text-sm font-medium text-gray-800">{{ $label }}</p>
                                    <p class="text-xs text-gray-400 mt-0.5">{{ $desc }}</p>
                                </div>
                            </label>
                        @endforeach
                    </div>
                </div>

                <div>
                    <label class="block text-xs font-medium text-gray-600 mb-1.5">Subject prefix (tag mode only)</label>
                    <input type="text" name="spam_subject_prefix" maxlength="30"
                           value="{{ old('spam_subject_prefix', $settings['spam_subject_prefix'] ?? '[SPAM]') }}"
                           class="w-40 border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-orange-400"
                           placeholder="[SPAM]">
                </div>

            </div>
        </div>

        {{-- Info box --}}
        <div class="p-4 rounded-xl bg-blue-50 border border-blue-200 text-xs text-blue-700 space-y-1">
            <p class="font-semibold">Integration note</p>
            <p>These settings store your preferred thresholds and actions. For them to take effect, your MTA must pass the
            <code class="bg-blue-100 px-1 rounded">X-Spam-Score</code> header and be configured to call the appropriate action
            (Sieve script, Postfix header check, or Dovecot policy). See your mail server documentation.</p>
        </div>

        @if ($errors->any())
            <div class="p-3 rounded-lg bg-red-50 border border-red-200 text-sm text-red-700">
                <ul class="list-disc list-inside space-y-0.5">
                    @foreach ($errors->all() as $error)<li>{{ $error }}</li>@endforeach
                </ul>
            </div>
        @endif

        <div class="flex justify-end">
            <button type="submit"
                    class="px-6 py-2 bg-orange-500 hover:bg-orange-600 text-white text-sm font-medium rounded-lg transition-colors">
                Save settings
            </button>
        </div>
    </form>

</div>
@endsection
