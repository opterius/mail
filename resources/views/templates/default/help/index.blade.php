@extends(mailView('layouts.app'))

@section('title', 'Help')

@section('content')
<div class="max-w-3xl mx-auto px-6 py-8">

    <header class="mb-8">
        <h1 class="text-2xl font-bold text-gray-900 dark:text-white">Feature guide</h1>
        <p class="text-sm text-gray-500 dark:text-gray-400 mt-2">
            Most webmails treat your inbox as a to-do list with no sorting. This one ships a handful of
            features borrowed from the HEY school of email to put you back in charge of what reaches you,
            when, and how you respond. Quick reference below.
        </p>
    </header>

    {{-- ───────── Sender control ───────── --}}
    <section class="mt-10">
        <h2 class="text-base font-semibold text-gray-900 dark:text-white mb-4 border-b border-gray-100 dark:border-gray-700 pb-2">Sender control</h2>

        <article class="mt-5">
            <h3 class="text-base font-semibold text-gray-900 dark:text-white">Screener</h3>
            <p class="text-sm text-gray-600 dark:text-gray-400 mt-1">
                The first time someone you do not know writes you, their message stops at the Screener
                instead of landing in the inbox. You decide: <strong>Approve</strong> lets every future
                message from that address through, <strong>Block</strong> sends them all straight to the
                Screener forever. You can change your mind in <strong>Settings &rarr; Blocked Senders</strong>
                at any time. Right after a block you also get a 5-second Undo at the bottom of the page in
                case you clicked by accident.
            </p>
        </article>

        <article class="mt-5">
            <h3 class="text-base font-semibold text-gray-900 dark:text-white">Trust badge</h3>
            <p class="text-sm text-gray-600 dark:text-gray-400 mt-1">
                Next to each From line you see a small badge - <em>New sender</em>, <em>Known</em>,
                <em>Trusted</em>. It is built from your own history with that address: how often you replied,
                how often you wrote them first, how recently they appeared. No external reputation service is
                involved.
            </p>
        </article>
    </section>

    {{-- ───────── Triage ───────── --}}
    <section class="mt-12">
        <h2 class="text-base font-semibold text-gray-900 dark:text-white mb-4 border-b border-gray-100 dark:border-gray-700 pb-2">Triage</h2>

        <article class="mt-5">
            <h3 class="text-base font-semibold text-gray-900 dark:text-white">Snooze</h3>
            <p class="text-sm text-gray-600 dark:text-gray-400 mt-1">
                Hide a message from the inbox until a future moment. Pick "tomorrow morning",
                "next week", or a custom date. At that moment it pops back to the top, marked unread.
                Useful for things you do not want to forget but cannot deal with right now.
                Find sleeping messages under <strong>Snoozed</strong> in the sidebar.
            </p>
        </article>

        <article class="mt-5">
            <h3 class="text-base font-semibold text-gray-900 dark:text-white">Set Aside</h3>
            <p class="text-sm text-gray-600 dark:text-gray-400 mt-1">
                Pin a message to a small shelf without moving it. The inbox stays clean, the message
                stays one click away in the <strong>Set Aside</strong> sidebar entry. Think of it as a
                bookmark - <em>"I want to come back to this soon, but it is not actionable yet"</em>.
            </p>
        </article>

        <article class="mt-5">
            <h3 class="text-base font-semibold text-gray-900 dark:text-white">Reply Later</h3>
            <p class="text-sm text-gray-600 dark:text-gray-400 mt-1">
                A focused list of messages you owe a reply on. Mark a thread "Reply Later" and it joins
                a dedicated queue you can plow through in one session. Designed for the
                <em>"I need to write back, but not right now"</em> reaction.
            </p>
        </article>

        <article class="mt-5">
            <h3 class="text-base font-semibold text-gray-900 dark:text-white">Notes</h3>
            <p class="text-sm text-gray-600 dark:text-gray-400 mt-1">
                A private text box attached to a message - only you see it. Use for context the message
                does not carry: <em>"client confirmed on phone"</em>, <em>"saying yes if they push back on
                price"</em>, etc.
            </p>
        </article>
    </section>

    {{-- ───────── Newsletters & noise ───────── --}}
    <section class="mt-12">
        <h2 class="text-base font-semibold text-gray-900 dark:text-white mb-4 border-b border-gray-100 dark:border-gray-700 pb-2">Newsletters &amp; noise</h2>

        <article class="mt-5">
            <h3 class="text-base font-semibold text-gray-900 dark:text-white">Feed</h3>
            <p class="text-sm text-gray-600 dark:text-gray-400 mt-1">
                Move a sender to your <strong>Feed</strong> when you want to keep reading their messages
                but not be interrupted by them. Newsletters, marketing recaps, and digests live here.
                The Feed reads like a magazine, not like an inbox - no unread counters, no notification
                pressure. Glance through it on your own schedule.
            </p>
        </article>

        <article class="mt-5">
            <h3 class="text-base font-semibold text-gray-900 dark:text-white">Unsubscribe</h3>
            <p class="text-sm text-gray-600 dark:text-gray-400 mt-1">
                When a newsletter exposes a List-Unsubscribe header, the message view shows a one-click
                Unsubscribe button. It sends the unsubscribe request on your behalf - no hunting through
                the email footer for a tiny link.
            </p>
        </article>
    </section>

    {{-- ───────── Privacy ───────── --}}
    <section class="mt-12">
        <h2 class="text-base font-semibold text-gray-900 dark:text-white mb-4 border-b border-gray-100 dark:border-gray-700 pb-2">Privacy</h2>

        <article class="mt-5">
            <h3 class="text-base font-semibold text-gray-900 dark:text-white">Spy pixel blocker</h3>
            <p class="text-sm text-gray-600 dark:text-gray-400 mt-1">
                Marketing emails embed invisible 1x1 images so the sender knows when, where, and how
                often you opened them. The webmail strips those pixels before rendering and shows a
                small count of how many were neutralised on each message. The sender sees nothing.
            </p>
        </article>

        <article class="mt-5">
            <h3 class="text-base font-semibold text-gray-900 dark:text-white">Link cleaning</h3>
            <p class="text-sm text-gray-600 dark:text-gray-400 mt-1">
                Tracking parameters added to links (utm_*, fbclid, gclid, mc_eid, and similar) are
                removed before the page renders. The link still works, but you do not carry a unique
                identifier when you click.
            </p>
        </article>
    </section>

    {{-- ───────── Sending ───────── --}}
    <section class="mt-12">
        <h2 class="text-base font-semibold text-gray-900 dark:text-white mb-4 border-b border-gray-100 dark:border-gray-700 pb-2">Sending</h2>

        <article class="mt-5">
            <h3 class="text-base font-semibold text-gray-900 dark:text-white">Send Later</h3>
            <p class="text-sm text-gray-600 dark:text-gray-400 mt-1">
                Write a message now, schedule it to leave at a specific moment. Useful for delivering
                across time zones, or simply not signalling that you work at 11pm. Scheduled drafts
                live under <strong>Scheduled</strong> and can be edited or cancelled before they go.
            </p>
        </article>

        <article class="mt-5">
            <h3 class="text-base font-semibold text-gray-900 dark:text-white">Read receipts</h3>
            <p class="text-sm text-gray-600 dark:text-gray-400 mt-1">
                When a sender asks for a read receipt, the default is to <em>ask you</em> first - the
                receipt only goes back if you allow it. You can switch this to always-send or
                never-send in <strong>Settings &rarr; Profile</strong>.
            </p>
        </article>
    </section>

    {{-- ───────── Inbox basics ───────── --}}
    <section class="mt-12">
        <h2 class="text-base font-semibold text-gray-900 dark:text-white mb-4 border-b border-gray-100 dark:border-gray-700 pb-2">Inbox basics</h2>

        <article class="mt-5">
            <h3 class="text-base font-semibold text-gray-900 dark:text-white">Folders &amp; labels</h3>
            <p class="text-sm text-gray-600 dark:text-gray-400 mt-1">
                Standard IMAP folders - Sent, Drafts, Trash, Spam, Archive - plus any custom folders
                you create. Move a message between folders, or use bulk actions from the inbox toolbar
                when you select multiple messages.
            </p>
        </article>

        <article class="mt-5">
            <h3 class="text-base font-semibold text-gray-900 dark:text-white">Search</h3>
            <p class="text-sm text-gray-600 dark:text-gray-400 mt-1">
                Search across all folders or limit to a specific one. You can match on sender,
                subject, body, attachments, or any combination. The search hits the mail server
                directly - results reflect the live mailbox, not a stale index.
            </p>
        </article>
    </section>

    <footer class="mt-16 pt-6 border-t border-gray-100 dark:border-gray-700 text-sm text-gray-400 dark:text-gray-500">
        Something missing? <a href="mailto:office@opterius.com" class="underline">Drop us a note</a> and we will add it.
    </footer>

</div>
@endsection
