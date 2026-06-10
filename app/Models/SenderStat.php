<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class SenderStat extends Model
{
    protected $fillable = [
        'user_email','sender_email',
        'received_count','replied_count','sent_to_count',
        'deleted_unread_count','marked_spam_count','snoozed_count',
        'first_seen_at','last_received_at','last_replied_at','last_sent_to_at',
    ];

    protected function casts(): array
    {
        return [
            'first_seen_at'     => 'datetime',
            'last_received_at'  => 'datetime',
            'last_replied_at'   => 'datetime',
            'last_sent_to_at'   => 'datetime',
            'received_count'    => 'integer',
            'replied_count'     => 'integer',
            'sent_to_count'     => 'integer',
            'deleted_unread_count'=> 'integer',
            'marked_spam_count' => 'integer',
            'snoozed_count'     => 'integer',
        ];
    }

    /**
     * Atomic counter bump for one (user, sender) pair. Inserts the
     * row if it does not exist - safe under concurrent inbox sync.
     */
    public static function bump(string $userEmail, string $senderEmail, string $field, int $by = 1, ?string $timestampField = null): void
    {
        $userEmail   = strtolower(trim($userEmail));
        $senderEmail = strtolower(trim($senderEmail));
        if ($userEmail === '' || $senderEmail === '') return;

        $now = now();
        $row = static::firstOrCreate(
            ['user_email' => $userEmail, 'sender_email' => $senderEmail],
            ['first_seen_at' => $now],
        );

        $updates = [$field => DB::raw("`{$field}` + " . (int) $by)];
        if ($timestampField) {
            $updates[$timestampField] = $now;
        }
        // updated_at refresh as well.
        $row->update($updates);
    }

    public static function for(string $userEmail, string $senderEmail): ?self
    {
        return static::where('user_email', strtolower(trim($userEmail)))
            ->where('sender_email', strtolower(trim($senderEmail)))
            ->first();
    }
}
