<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SetAsideEmail extends Model
{
    protected $fillable = ['user_email', 'imap_uid', 'mailbox', 'subject', 'from_email', 'from_name'];

    public static function setAsideUids(string $userEmail, string $mailbox = 'INBOX'): array
    {
        return static::where('user_email', $userEmail)
            ->where('mailbox', $mailbox)
            ->pluck('imap_uid')
            ->all();
    }
}
