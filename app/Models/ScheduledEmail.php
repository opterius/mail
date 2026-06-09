<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ScheduledEmail extends Model
{
    protected $fillable = ['user_email', 'to', 'cc', 'bcc', 'subject', 'body', 'attachments', 'send_at', 'status', 'error'];

    protected $casts = [
        'send_at'     => 'datetime',
        'attachments' => 'array',
    ];
}
