<?php

/**
 * Opterius Mail - Open source webmail.
 * https://opterius.com
 *
 * @copyright  Copyright (c) Host Server SRL (Opterius)
 * @license    https://opensource.org/license/agpl-v3  AGPL-3.0 License.
 */

namespace App\Http\Controllers\Api;

use App\Auth\ImapGuard;
use App\Http\Controllers\Controller;
use App\Services\ImapConnection;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class CheckNewMailController extends Controller
{
    /**
     * GET /api/check-new
     *
     * Runs IMAP STATUS on INBOX and returns unseen count + whether genuinely
     * new messages arrived since the last check (UIDNEXT increased).
     * Called by the browser every 45 seconds.
     */
    public function __invoke(Request $request): JsonResponse
    {
        /** @var ImapGuard $guard */
        $guard = auth('web');

        $imap = new ImapConnection();

        try {
            $imap->connect(
                host:         config('imap.host'),
                port:         config('imap.port'),
                encryption:   config('imap.encryption'),
                validateCert: config('imap.validate_cert', false),
                timeout:      8,
            );

            $imap->login($guard->getImapLogin(), $guard->getImapPassword());

            $status = $imap->getFolderStatus('INBOX');

        } catch (\Throwable) {
            return response()->json(['error' => 'imap_unavailable'], 503);
        }

        // Detect genuinely new messages: UIDNEXT increased since last check.
        $lastUidnext = session('check_new_uidnext');
        $hasNew      = $lastUidnext !== null && $status['uidnext'] > $lastUidnext;

        session(['check_new_uidnext' => $status['uidnext']]);

        return response()->json([
            'unseen'  => $status['unseen'],
            'has_new' => $hasNew,
        ]);
    }
}
