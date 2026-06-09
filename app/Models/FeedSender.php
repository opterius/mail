<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class FeedSender extends Model
{
    protected $fillable = ['user_email', 'sender_email'];

    public static function feedEmails(string $userEmail): array
    {
        return static::where('user_email', $userEmail)->pluck('sender_email')->all();
    }

    public static function isFeed(string $userEmail, string $senderEmail): bool
    {
        return static::where('user_email', $userEmail)
            ->where('sender_email', strtolower($senderEmail))
            ->exists();
    }
}
