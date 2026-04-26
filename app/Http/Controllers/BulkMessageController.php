<?php

/**
 * Opterius Mail - Open source webmail.
 * https://opterius.com
 *
 * @copyright  Copyright (c) Host Server SRL (Opterius)
 * @license    https://opensource.org/license/agpl-v3  AGPL-3.0 License.
 */

namespace App\Http\Controllers;

use App\Auth\ImapGuard;
use App\Services\ImapConnection;
use Illuminate\Http\Request;

class BulkMessageController extends Controller
{
    /**
     * POST /bulk
     *
     * Body:
     *   folder  — IMAP folder name
     *   uids[]  — array of integer UIDs
     *   action  — delete | read | unread | flag | unflag | move
     *   target  — target folder name (required for action=move)
     *   page    — current page (echoed back for redirect)
     */
    public function __invoke(Request $request): mixed
    {
        $folder = $request->input('folder', 'INBOX');
        $uids   = array_filter(array_map('intval', (array) $request->input('uids', [])));
        $action = $request->input('action');
        $target = $request->input('target', '');
        $page   = (int) $request->input('page', 1);

        if (empty($uids) || !$action) {
            return $this->back($folder, $page);
        }

        // Build a comma-separated UID set safe for IMAP
        $uidSet = implode(',', $uids);

        /** @var ImapGuard $guard */
        $guard = auth('web');
        $imap  = new ImapConnection();

        try {
            $imap->connect(
                host:         config('imap.host'),
                port:         config('imap.port'),
                encryption:   config('imap.encryption'),
                validateCert: config('imap.validate_cert', false),
                timeout:      config('imap.timeout', 10),
            );
            $imap->login($guard->getImapLogin(), $guard->getImapPassword());
            $imap->selectFolder($folder);

            match ($action) {
                'delete' => $imap->deleteMessages($uidSet),
                'read'   => $imap->storeFlags($uidSet, '\\Seen', true),
                'unread' => $imap->storeFlags($uidSet, '\\Seen', false),
                'flag'   => $imap->storeFlags($uidSet, '\\Flagged', true),
                'unflag' => $imap->storeFlags($uidSet, '\\Flagged', false),
                'move'   => $target ? $imap->moveMessages($uidSet, $target) : null,
                default  => null,
            };

            $imap->logout();
        } catch (\Throwable) {
            $imap->close();
        }

        return $this->back($folder, $page);
    }

    private function back(string $folder, int $page): mixed
    {
        $url = strtoupper($folder) === 'INBOX'
            ? route('inbox')
            : route('folder', ['folder' => rawurlencode($folder)]);

        if ($page > 1) {
            $url .= '?page=' . $page;
        }

        return redirect($url);
    }
}
