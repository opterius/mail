<?php

namespace App\Http\Controllers;

use App\Auth\ImapGuard;
use App\Services\ImapConnection;
use App\Services\MailboxListCache;

class HelpController extends Controller
{
    /**
     * Plain reference page that explains the HEY-style productivity
     * features (Screener, Snooze, Set Aside, Reply Later, Feed, Notes,
     * Send Later, etc.) plus the privacy ones (spy pixel blocking,
     * link cleaning). Users land here either via the sidebar link or
     * via the "?" buttons next to individual features.
     */
    public function index(): mixed
    {
        // Sidebar wants the folder list - reuse the cache so the help
        // page costs nothing extra on a warm cache.
        /** @var ImapGuard $guard */
        $guard   = auth('web');
        $folders = [];

        try {
            $imap = new ImapConnection();
            $imap->connect(
                host:         config('imap.host'),
                port:         config('imap.port'),
                encryption:   config('imap.encryption'),
                validateCert: config('imap.validate_cert', false),
                timeout:      config('imap.timeout', 5),
            );
            $imap->login($guard->getImapLogin(), $guard->getImapPassword());
            $folders = MailboxListCache::get($guard->getImapLogin(), fn () => array_map(
                fn (array $f) => array_merge($f, $imap->getFolderStatus($f['name'])),
                $imap->listFolders()
            ));
            $imap->logout();
        } catch (\Throwable) {
        }

        return view(mailView('help.index'), [
            'folders'       => $folders,
            'currentFolder' => '',
        ]);
    }
}
