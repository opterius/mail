<?php

namespace App\Services;

use Illuminate\Support\Facades\Cache;

/**
 * Per-user folder list cache with a short TTL.
 *
 * Every page load currently does:
 *   $imap->listFolders()  -> 1 round trip
 *   getFolderStatus($f)   -> N round trips (one per folder)
 *
 * On a busy inbox with 8-15 folders that's 10-16 IMAP commands just to
 * draw the sidebar - dwarfing the actual page work. At scale the IMAP
 * server CPU and connection pool become the bottleneck before anything
 * Laravel does.
 *
 * Caching the combined (name + status) shape for 30s reduces this to a
 * handful of round trips per user per minute. The trade-off: unread
 * counts in the sidebar can lag by up to 30s. That's the same latency
 * Gmail/Outlook clients show by default - acceptable.
 *
 * Folder CRUD invalidates the cache immediately (forget()) so the
 * sidebar reflects "new folder created" within the same request.
 */
class MailboxListCache
{
    public const TTL_SECONDS = 30;

    /**
     * Returns the cached folder list + status for the given user, or
     * runs $builder and caches the result if missing. $builder is the
     * IMAP-touching closure - kept caller-supplied because some paths
     * (e.g. InboxController) need to run ensureSystemFolders inside
     * the cache miss branch, not after it.
     */
    public static function get(string $userKey, callable $builder): array
    {
        return Cache::remember(self::key($userKey), self::TTL_SECONDS, $builder);
    }

    /**
     * Bust the cache - call after folder create / rename / delete so the
     * sidebar reflects the change without waiting out the TTL.
     */
    public static function forget(string $userKey): void
    {
        Cache::forget(self::key($userKey));
    }

    private static function key(string $userKey): string
    {
        return 'mail:folders:' . sha1(strtolower(trim($userKey)));
    }
}
