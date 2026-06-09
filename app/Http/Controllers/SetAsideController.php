<?php

namespace App\Http\Controllers;

use App\Models\SetAsideEmail;
use Illuminate\Http\Request;

class SetAsideController extends Controller
{
    public function index()
    {
        $email  = auth('web')->user()->email;
        $emails = SetAsideEmail::where('user_email', $email)->orderByDesc('created_at')->get();

        return view(mailView('set-aside.index'), compact('emails'));
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

        SetAsideEmail::firstOrCreate(
            ['user_email' => $email, 'imap_uid' => $request->imap_uid, 'mailbox' => $request->mailbox],
            ['subject' => $request->subject, 'from_email' => $request->from_email, 'from_name' => $request->from_name]
        );

        if ($request->expectsJson()) return response()->json(['ok' => true]);
        return back()->with('success', 'Email set aside.');
    }

    public function destroy(Request $request)
    {
        $request->validate(['imap_uid' => 'required|integer', 'mailbox' => 'required|string']);
        $email = auth('web')->user()->email;

        SetAsideEmail::where('user_email', $email)
            ->where('imap_uid', $request->imap_uid)
            ->where('mailbox', $request->mailbox)
            ->delete();

        if ($request->expectsJson()) return response()->json(['ok' => true]);
        return redirect()->route('inbox')->with('success', 'Back in inbox.');
    }
}
