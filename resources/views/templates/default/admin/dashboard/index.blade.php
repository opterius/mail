{{--
 | Opterius Mail - Open source webmail.
 | Modern, fast and responsive webmail that works with any IMAP/SMTP server.
 | https://opterius.com
 |
 | Copyright (c) Chimilevschi Iosif-Gabriel / Opterius
 | @license  AGPL-3.0  https://opensource.org/license/agpl-v3
 | @author   Chimilevschi Iosif-Gabriel <office@opterius.com>
--}}
@extends(mailView('admin.layouts.admin'))

@section('title', 'Dashboard')

@section('content')
<div class="p-6">
    <h1 class="text-xl font-bold text-gray-900 mb-6">Dashboard</h1>

    <div class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-8">
        @foreach (['Domains' => '—', 'Accounts' => '—', 'Aliases' => '—', 'Queue' => '—'] as $label => $value)
        <div class="bg-white rounded-xl border border-gray-200 p-4">
            <p class="text-sm text-gray-500">{{ $label }}</p>
            <p class="text-2xl font-bold text-gray-900 mt-1">{{ $value }}</p>
        </div>
        @endforeach
    </div>

    <div class="bg-white rounded-xl border border-gray-200 p-6">
        <p class="text-sm text-gray-500">
            Admin mode is active. Mail server management will be implemented in Phase 6.
        </p>
    </div>
</div>
@endsection
