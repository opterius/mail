<?php

/**
 * Opterius Mail - Open source webmail.
 * https://opterius.com
 *
 * @copyright  Copyright (c) Host Server SRL (Opterius)
 * @license    https://opensource.org/license/agpl-v3  AGPL-3.0 License.
 */

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\MailAccount;
use App\Models\MailDomain;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * Internal account sync endpoint — called by the Opterius agent when it
 * creates, deletes, or updates email accounts.
 *
 * Authentication: X-Sync-Secret header must match MAIL_SYNC_SECRET env var.
 * If MAIL_SYNC_SECRET is not set the endpoint is disabled (returns 404).
 *
 * The mail app works fully standalone without this endpoint configured.
 */
class SyncController extends Controller
{
    public function account(Request $request): JsonResponse
    {
        $secret = config('mail-ui.sync_secret');

        // Disabled when no secret is configured (standalone mode)
        if (!$secret) {
            return response()->json(['error' => 'Not found.'], 404);
        }

        if (!hash_equals($secret, $request->header('X-Sync-Secret', ''))) {
            return response()->json(['error' => 'Unauthorized.'], 403);
        }

        $request->validate([
            'action' => 'required|in:create,delete,password',
            'email'  => 'required|email',
        ]);

        $action = $request->input('action');
        $email  = strtolower($request->input('email'));

        match ($action) {
            'create'   => $this->syncCreate($email, $request),
            'delete'   => $this->syncDelete($email),
            'password' => $this->syncPassword($email, $request),
        };

        return response()->json(['status' => 'ok']);
    }

    private function syncCreate(string $email, Request $request): void
    {
        [, $domainName] = explode('@', $email, 2);

        $domain = MailDomain::firstOrCreate(
            ['domain' => $domainName],
            ['is_active' => true]
        );

        $account = MailAccount::firstOrNew(['email' => $email]);
        $account->domain_id = $domain->id;
        $account->password  = $request->input('password', \Illuminate\Support\Str::random(32));
        $account->quota_mb  = ($request->input('quota_mb') > 0) ? (int) $request->input('quota_mb') : null;
        $account->is_active = true;
        $account->save();
    }

    private function syncDelete(string $email): void
    {
        MailAccount::where('email', $email)->delete();
    }

    private function syncPassword(string $email, Request $request): void
    {
        $request->validate(['password' => 'required|string']);

        $account = MailAccount::where('email', $email)->first();
        if ($account) {
            $account->password = $request->input('password'); // triggers Hash::make via mutator
            $account->save();
        }
    }
}
