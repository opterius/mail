<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class EmailNote extends Model
{
    protected $fillable = ['user_email', 'imap_uid', 'mailbox', 'note'];
}
