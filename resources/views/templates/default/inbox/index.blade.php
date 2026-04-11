{{--
 | Opterius Mail - Open source webmail.
 | Modern, fast and responsive webmail that works with any IMAP/SMTP server.
 | https://opterius.com
 |
 | Copyright (c) Host Server SRL (Opterius)
 | @license  AGPL-3.0  https://opensource.org/license/agpl-v3
 | @author   Iosif Gabriel Chimilevschi <office@opterius.com>
--}}
@extends(mailView('layouts.app'))

@section('title', $folder)

@section('content')
<div class="p-8 text-center text-gray-400 mt-20">
    <p class="text-lg font-medium text-gray-500">{{ $folder }}</p>
    <p class="text-sm mt-2">Inbox loading will be implemented in Phase 2.</p>
    <p class="text-xs mt-4">Logged in as: <strong>{{ auth('web')->user()->email }}</strong></p>
</div>
@endsection
