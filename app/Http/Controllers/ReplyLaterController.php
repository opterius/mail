<?php

namespace App\Http\Controllers;

use App\Models\ReplyLaterEmail;
use Illuminate\Http\Request;

class ReplyLaterController extends Controller
{
    public function index()
    {
        $email  = auth('web')->user()->email;
        $emails = ReplyLaterEmail::where('user_email', $email)->orderByDesc('created_at')->get();

        return view(mailView('reply-later.index'), compact('emails'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'imap_uid'  => 'required|integer',
            'mailbox'   => 'required|string',
            'subject'   => 'nullable|string|max:998',
            'from_email'=> 'nullable|email',
            'from_name' => 'nullable|string|max:255',
        ]);

        $email = auth('web')->user()->email;

        ReplyLaterEmail::firstOrCreate(
            ['user_email' => $email, 'imap_uid' => $request->imap_uid, 'mailbox' => $request->mailbox],
            ['subject' => $request->subject, 'from_email' => $request->from_email, 'from_name' => $request->from_name]
        );

        if ($request->expectsJson()) return response()->json(['ok' => true]);
        return back()->with('success', 'Added to Reply Later.');
    }

    public function destroy(Request $request)
    {
        $request->validate(['imap_uid' => 'required|integer', 'mailbox' => 'required|string']);
        $email = auth('web')->user()->email;

        ReplyLaterEmail::where('user_email', $email)
            ->where('imap_uid', $request->imap_uid)
            ->where('mailbox', $request->mailbox)
            ->delete();

        if ($request->expectsJson()) return response()->json(['ok' => true]);
        return redirect()->route('inbox')->with('success', 'Removed from Reply Later.');
    }
}
