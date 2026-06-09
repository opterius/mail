<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class KnownSender extends Model
{
    protected $fillable = ['user_email', 'sender_email', 'status'];

    public static function isApproved(string $userEmail, string $senderEmail): bool
    {
        return static::where('user_email', $userEmail)
            ->where('sender_email', strtolower($senderEmail))
            ->where('status', 'approved')
            ->exists();
    }

    public static function isBlocked(string $userEmail, string $senderEmail): bool
    {
        return static::where('user_email', $userEmail)
            ->where('sender_email', strtolower($senderEmail))
            ->where('status', 'blocked')
            ->exists();
    }

    public static function isKnown(string $userEmail, string $senderEmail): bool
    {
        return static::where('user_email', $userEmail)
            ->where('sender_email', strtolower($senderEmail))
            ->exists();
    }
}
