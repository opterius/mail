<?php

namespace App\Http\Controllers;

use App\Models\EmailNote;
use Illuminate\Http\Request;

class EmailNoteController extends Controller
{
    public function store(Request $request)
    {
        $request->validate([
            'imap_uid' => 'required|integer',
            'mailbox'  => 'required|string',
            'note'     => 'required|string|max:5000',
        ]);

        $email = auth('web')->user()->email;

        EmailNote::updateOrCreate(
            ['user_email' => $email, 'imap_uid' => $request->imap_uid, 'mailbox' => $request->mailbox],
            ['note' => $request->note]
        );

        return response()->json(['ok' => true]);
    }

    public function destroy(Request $request)
    {
        $request->validate(['imap_uid' => 'required|integer', 'mailbox' => 'required|string']);
        $email = auth('web')->user()->email;

        EmailNote::where('user_email', $email)
            ->where('imap_uid', $request->imap_uid)
            ->where('mailbox', $request->mailbox)
            ->delete();

        return response()->json(['ok' => true]);
    }
}
