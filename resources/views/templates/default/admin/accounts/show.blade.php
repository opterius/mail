{{--
 | Opterius Mail - Open source webmail.
 | Admin — account detail (standalone mode).
 | https://opterius.com
 |
 | Copyright (c) Host Server SRL (Opterius)
 | @license  AGPL-3.0  https://opensource.org/license/agpl-v3
 | @author   Iosif Gabriel Chimilevschi <office@opterius.com>
--}}
@extends(mailView('admin.layouts.admin'))

@section('title', $account->email)

@section('content')
<div class="p-6 space-y-6">

    {{-- Header --}}
    <div class="flex items-center gap-3">
        <a href="{{ route('admin.accounts.index') }}"
           class="text-gray-400 hover:text-gray-600 transition-colors">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
            </svg>
        </a>
        <div>
            <h1 class="text-xl font-bold text-gray-900">{{ $account->email }}</h1>
            <p class="text-[13px] text-gray-400 mt-0.5">
                Member since {{ $account->created_at?->format('Y-m-d') ?? '—' }}
                @if($account->group) &middot; Group: <span class="font-medium text-gray-600">{{ $account->group->name }}</span> @endif
            </p>
        </div>
    </div>

    {{-- Stats cards --}}
    <div class="grid grid-cols-2 sm:grid-cols-4 gap-4">
        @foreach([
            ['label' => 'Last 24 h',  'value' => number_format($stats->sent_24h)],
            ['label' => 'Last 7 days', 'value' => number_format($stats->sent_7d)],
            ['label' => 'Last 30 days','value' => number_format($stats->sent_30d)],
            ['label' => 'Last 90 days','value' => number_format($stats->sent_90d)],
        ] as $card)
            <div class="bg-white rounded-xl border border-gray-200 px-5 py-4">
                <p class="text-[11px] font-semibold uppercase tracking-wider text-gray-400">{{ $card['label'] }}</p>
                <p class="text-2xl font-bold text-gray-900 mt-1">{{ $card['value'] }}</p>
                <p class="text-[13px] text-gray-400 mt-0.5">emails sent</p>
            </div>
        @endforeach
    </div>

    {{-- Daily chart (last 30 days) --}}
    <div class="bg-white rounded-xl border border-gray-200 p-5">
        <h2 class="text-[13px] font-semibold text-gray-500 uppercase tracking-wider mb-4">Emails sent — last 30 days</h2>
        <div style="height: 200px;">
            <canvas id="sendChart"></canvas>
        </div>
    </div>

    {{-- Recent sends --}}
    <div class="bg-white rounded-xl border border-gray-200 overflow-hidden">
        <div class="px-5 py-3 border-b border-gray-100">
            <h2 class="text-[13px] font-semibold text-gray-500 uppercase tracking-wider">Recent sends</h2>
        </div>
        @if($recentLogs->isEmpty())
            <p class="px-5 py-8 text-center text-[13px] text-gray-400">No emails sent yet.</p>
        @else
            <table class="w-full">
                <thead>
                    <tr class="text-left border-b border-gray-100 bg-gray-50">
                        <th class="px-4 py-2.5 text-[13px] font-semibold text-gray-500 uppercase tracking-wide">Date</th>
                        <th class="px-4 py-2.5 text-[13px] font-semibold text-gray-500 uppercase tracking-wide">Subject</th>
                        <th class="px-4 py-2.5 text-[13px] font-semibold text-gray-500 uppercase tracking-wide text-right">Recipients</th>
                        <th class="px-4 py-2.5 text-[13px] font-semibold text-gray-500 uppercase tracking-wide">IP</th>
                        <th class="px-4 py-2.5 text-[13px] font-semibold text-gray-500 uppercase tracking-wide">Status</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-50">
                    @foreach($recentLogs as $log)
                        <tr class="hover:bg-gray-50">
                            <td class="px-4 py-2.5 text-[13px] text-gray-500 whitespace-nowrap">{{ $log->created_at->format('Y-m-d H:i') }}</td>
                            <td class="px-4 py-2.5 text-[13px] text-gray-700 max-w-xs truncate">{{ $log->subject ?: '(no subject)' }}</td>
                            <td class="px-4 py-2.5 text-[13px] text-gray-500 text-right">{{ $log->recipient_count }}</td>
                            <td class="px-4 py-2.5 text-[13px] text-gray-400 font-mono">{{ $log->ip ?: '—' }}</td>
                            <td class="px-4 py-2.5">
                                @if($log->status === 'sent')
                                    <span style="display:inline-flex;align-items:center;padding:1px 8px;border-radius:4px;font-size:13px;background:#f0fdf4;color:#15803d;border:1px solid #bbf7d0;">sent</span>
                                @else
                                    <span style="display:inline-flex;align-items:center;padding:1px 8px;border-radius:4px;font-size:13px;background:#fef2f2;color:#b91c1c;border:1px solid #fecaca;">{{ $log->status }}</span>
                                @endif
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        @endif
    </div>

</div>
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
<script>
(function () {
    const labels = @json($chartLabels);
    const data   = @json($chartData);
    const max    = Math.max(...data, 1);

    new Chart(document.getElementById('sendChart'), {
        type: 'bar',
        data: {
            labels,
            datasets: [{
                label: 'Emails sent',
                data,
                backgroundColor: 'rgba(249,115,22,0.15)',
                borderColor: '#f97316',
                borderWidth: 2,
                borderRadius: 4,
                borderSkipped: false,
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: { display: false },
                tooltip: {
                    callbacks: {
                        label: ctx => ` ${ctx.parsed.y} email${ctx.parsed.y !== 1 ? 's' : ''}`
                    }
                }
            },
            scales: {
                x: {
                    grid: { display: false },
                    ticks: { font: { size: 11 }, color: '#9ca3af', maxRotation: 45 }
                },
                y: {
                    beginAtZero: true,
                    suggestedMax: max + Math.ceil(max * 0.15),
                    ticks: {
                        font: { size: 11 },
                        color: '#9ca3af',
                        stepSize: max <= 10 ? 1 : undefined,
                        precision: 0,
                    },
                    grid: { color: '#f3f4f6' }
                }
            }
        }
    });
})();
</script>
@endpush
