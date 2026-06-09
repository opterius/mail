<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SnoozedEmail extends Model
{
    protected $fillable = ['user_email', 'imap_uid', 'mailbox', 'subject', 'from_email', 'from_name', 'snooze_until'];

    protected $casts = ['snooze_until' => 'datetime'];

    public static function isSnoozed(string $userEmail, int $uid, string $mailbox = 'INBOX'): bool
    {
        return static::where('user_email', $userEmail)
            ->where('imap_uid', $uid)
            ->where('mailbox', $mailbox)
            ->where('snooze_until', '>', now())
            ->exists();
    }

    public static function snoozedUids(string $userEmail, string $mailbox = 'INBOX'): array
    {
        return static::where('user_email', $userEmail)
            ->where('mailbox', $mailbox)
            ->where('snooze_until', '>', now())
            ->pluck('imap_uid')
            ->all();
    }
}
