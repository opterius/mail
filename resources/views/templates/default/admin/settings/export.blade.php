{{--
 | Opterius Mail - Open source webmail.
 | Admin — MTA integration export.
 | https://opterius.com
 |
 | Copyright (c) Host Server SRL (Opterius)
 | @license  AGPL-3.0  https://opensource.org/license/agpl-v3
 | @author   Iosif Gabriel Chimilevschi <office@opterius.com>
--}}
@extends(mailView('admin.layouts.admin'))

@section('title', 'MTA Integration')

@section('content')
<div class="p-6 max-w-4xl" x-data="{ tab: 'dovecot' }">

    <div class="mb-6">
        <h1 class="text-xl font-bold text-gray-900">MTA Integration</h1>
        <p class="text-sm text-gray-400 mt-0.5">
            Ready-to-paste config files for Dovecot and Postfix to use the accounts, aliases, and
            domains stored in this admin panel.
        </p>
    </div>

    @if(!$isMysql)
        <div class="p-4 rounded-xl bg-amber-50 border border-amber-200 text-sm text-amber-700 mb-6">
            <p class="font-semibold mb-1">MySQL / MariaDB required</p>
            <p>
                Your application is configured with the <code class="bg-amber-100 px-1 rounded font-mono">{{ $db['driver'] }}</code>
                database driver. Dovecot and Postfix SQL integration requires <strong>MySQL or MariaDB</strong>.
                Update your <code class="bg-amber-100 px-1 rounded font-mono">.env</code> to use
                <code class="bg-amber-100 px-1 rounded font-mono">DB_CONNECTION=mysql</code> and re-run migrations.
            </p>
        </div>
    @else

        {{-- Info box --}}
        <div class="p-4 rounded-xl bg-blue-50 border border-blue-200 text-[13px] text-blue-700 space-y-1.5 mb-6">
            <p class="font-semibold text-sm">Before you paste</p>
            <ul class="list-disc list-inside space-y-1">
                <li>These snippets use your current database credentials. Keep the generated files readable only by root and the mail daemons.</li>
                <li>Replace <code class="bg-blue-100 px-1 rounded font-mono">1000</code> (uid/gid) with your actual <code class="bg-blue-100 px-1 rounded font-mono">vmail</code> user/group IDs (<code class="bg-blue-100 px-1 rounded font-mono">id vmail</code>).</li>
                <li>Replace <code class="bg-blue-100 px-1 rounded font-mono">/var/mail/vhosts</code> with the actual mail spool root on your server.</li>
                <li>Reload Dovecot and Postfix after applying changes: <code class="bg-blue-100 px-1 rounded font-mono">doveadm reload &amp;&amp; postfix reload</code></li>
            </ul>
        </div>

        {{-- Tabs --}}
        <div class="flex gap-1 mb-5">
            <button @click="tab = 'dovecot'"
                    :class="tab === 'dovecot' ? 'bg-white border-gray-200 text-gray-900 font-semibold shadow-sm' : 'text-gray-500 hover:text-gray-700'"
                    class="px-4 py-2 rounded-lg border text-sm transition-colors">
                Dovecot
            </button>
            <button @click="tab = 'postfix'"
                    :class="tab === 'postfix' ? 'bg-white border-gray-200 text-gray-900 font-semibold shadow-sm' : 'text-gray-500 hover:text-gray-700'"
                    class="px-4 py-2 rounded-lg border text-sm transition-colors">
                Postfix
            </button>
        </div>

        {{-- ========================= DOVECOT TAB ========================= --}}
        <div x-show="tab === 'dovecot'" class="space-y-5">

            @php
                $blocks = [
                    [
                        'file'  => '/etc/dovecot/dovecot-sql.conf.ext',
                        'label' => 'SQL connection + queries',
                        'desc'  => 'Main SQL config file. Contains the DB connection string and the passdb/userdb queries.',
                        'key'   => 'sqlConf',
                    ],
                    [
                        'file'  => '/etc/dovecot/conf.d/auth-sql.conf.ext',
                        'label' => 'passdb / userdb blocks',
                        'desc'  => 'Tells Dovecot to use the SQL driver for authentication. Add to or replace your existing auth conf.',
                        'key'   => 'dovecotConf',
                    ],
                    [
                        'file'  => '/etc/dovecot/conf.d/10-mail.conf',
                        'label' => 'Mail location',
                        'desc'  => 'Sets the mailbox path format. Must match the home path in the user_query above.',
                        'key'   => 'mailConf',
                    ],
                    [
                        'file'  => '/etc/dovecot/conf.d/90-quota.conf',
                        'label' => 'Quota plugin (optional)',
                        'desc'  => 'Enables per-mailbox storage quota from the quota_mb column. Skip if you do not use quotas.',
                        'key'   => 'quotaConf',
                    ],
                ];
            @endphp

            @foreach($blocks as $block)
                <div class="bg-white rounded-xl border border-gray-200 overflow-hidden">
                    <div class="px-5 py-3 border-b border-gray-100 flex items-start justify-between gap-4">
                        <div>
                            <h2 class="font-semibold text-gray-800 text-sm">{{ $block['label'] }}</h2>
                            <p class="text-[13px] text-gray-400 mt-0.5 font-mono">{{ $block['file'] }}</p>
                            <p class="text-[13px] text-gray-500 mt-1">{{ $block['desc'] }}</p>
                        </div>
                        <button onclick="copyBlock(this)"
                                data-target="{{ $loop->index }}-dovecot"
                                class="flex-shrink-0 px-3 py-1.5 bg-gray-100 hover:bg-gray-200 text-gray-700 text-[13px] font-medium rounded-lg transition-colors whitespace-nowrap">
                            Copy
                        </button>
                    </div>
                    <div class="relative">
                        <pre id="{{ $loop->index }}-dovecot" class="px-5 py-4 text-[13px] font-mono text-gray-700 bg-gray-50 overflow-x-auto leading-relaxed">{{ $dovecot[$block['key']] }}</pre>
                    </div>
                </div>
            @endforeach

        </div>

        {{-- ========================= POSTFIX TAB ========================= --}}
        <div x-show="tab === 'postfix'" class="space-y-5">

            @php
                $pfBlocks = [
                    [
                        'file'  => '/etc/postfix/mysql_virtual_domains.cf',
                        'label' => 'Virtual domains',
                        'desc'  => 'Postfix queries this to confirm a domain is hosted here before accepting mail.',
                        'key'   => 'domains',
                    ],
                    [
                        'file'  => '/etc/postfix/mysql_virtual_mailboxes.cf',
                        'label' => 'Virtual mailboxes',
                        'desc'  => 'Maps a recipient address to a mailbox path. Postfix uses this to deliver to the correct directory.',
                        'key'   => 'mailboxes',
                    ],
                    [
                        'file'  => '/etc/postfix/mysql_virtual_aliases.cf',
                        'label' => 'Virtual aliases',
                        'desc'  => 'Rewrites alias addresses to their destination before delivery.',
                        'key'   => 'aliases',
                    ],
                    [
                        'file'  => '/etc/postfix/main.cf',
                        'label' => 'main.cf additions',
                        'desc'  => 'Add or merge these lines into your existing main.cf. They wire up the three map files above.',
                        'key'   => 'mainCf',
                    ],
                ];
            @endphp

            @foreach($pfBlocks as $block)
                <div class="bg-white rounded-xl border border-gray-200 overflow-hidden">
                    <div class="px-5 py-3 border-b border-gray-100 flex items-start justify-between gap-4">
                        <div>
                            <h2 class="font-semibold text-gray-800 text-sm">{{ $block['label'] }}</h2>
                            <p class="text-[13px] text-gray-400 mt-0.5 font-mono">{{ $block['file'] }}</p>
                            <p class="text-[13px] text-gray-500 mt-1">{{ $block['desc'] }}</p>
                        </div>
                        <button onclick="copyBlock(this)"
                                data-target="{{ $loop->index }}-postfix"
                                class="flex-shrink-0 px-3 py-1.5 bg-gray-100 hover:bg-gray-200 text-gray-700 text-[13px] font-medium rounded-lg transition-colors whitespace-nowrap">
                            Copy
                        </button>
                    </div>
                    <div class="relative">
                        <pre id="{{ $loop->index }}-postfix" class="px-5 py-4 text-[13px] font-mono text-gray-700 bg-gray-50 overflow-x-auto leading-relaxed">{{ $postfix[$block['key']] }}</pre>
                    </div>
                </div>
            @endforeach

            {{-- Postfix reload reminder --}}
            <div class="p-4 rounded-xl bg-gray-100 border border-gray-200 text-[13px] text-gray-600">
                <p class="font-semibold mb-1">After applying all files</p>
                <pre class="font-mono mt-1">postmap /etc/postfix/mysql_virtual_domains.cf
postmap /etc/postfix/mysql_virtual_mailboxes.cf
postmap /etc/postfix/mysql_virtual_aliases.cf
postfix reload</pre>
                <p class="mt-2">Test with: <code class="bg-white px-1 rounded border border-gray-200">postmap -q user@example.com mysql:/etc/postfix/mysql_virtual_mailboxes.cf</code></p>
            </div>

        </div>

    @endif

</div>
@endsection

@push('scripts')
<script>
function copyBlock(btn) {
    const targetId = btn.getAttribute('data-target');
    const pre = document.getElementById(targetId);
    if (!pre) return;
    navigator.clipboard.writeText(pre.textContent.trim()).then(() => {
        const orig = btn.textContent;
        btn.textContent = 'Copied!';
        btn.classList.add('bg-green-100', 'text-green-700');
        setTimeout(() => {
            btn.textContent = orig;
            btn.classList.remove('bg-green-100', 'text-green-700');
        }, 1500);
    });
}
</script>
@endpush
