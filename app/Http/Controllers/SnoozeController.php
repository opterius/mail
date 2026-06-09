<?php

namespace App\Http\Controllers;

use App\Models\SnoozedEmail;
use Illuminate\Http\Request;

class SnoozeController extends Controller
{
    public function store(Request $request)
    {
        $request->validate([
            'imap_uid'    => 'required|integer',
            'mailbox'     => 'required|string',
            'snooze_until'=> 'required|date|after:now',
            'subject'     => 'nullable|string|max:998',
            'from_email'  => 'nullable|email',
            'from_name'   => 'nullable|string|max:255',
        ]);

        $email = auth('web')->user()->email;

        SnoozedEmail::updateOrCreate(
            ['user_email' => $email, 'imap_uid' => $request->imap_uid, 'mailbox' => $request->mailbox],
            [
                'subject'      => $request->subject,
                'from_email'   => $request->from_email,
                'from_name'    => $request->from_name,
                'snooze_until' => $request->snooze_until,
            ]
        );

        if ($request->expectsJson()) {
            return response()->json(['ok' => true]);
        }

        return back()->with('success', 'Email snoozed.');
    }

    public function destroy(Request $request)
    {
        $request->validate(['imap_uid' => 'required|integer', 'mailbox' => 'required|string']);
        $email = auth('web')->user()->email;

        SnoozedEmail::where('user_email', $email)
            ->where('imap_uid', $request->imap_uid)
            ->where('mailbox', $request->mailbox)
            ->delete();

        if ($request->expectsJson()) {
            return response()->json(['ok' => true]);
        }

        return redirect()->route('inbox')->with('success', 'Snooze cancelled.');
    }

    public function index()
    {
        $email   = auth('web')->user()->email;
        $snoozed = SnoozedEmail::where('user_email', $email)
            ->where('snooze_until', '>', now())
            ->orderBy('snooze_until')
            ->get();

        return view(mailView('snooze.index'), compact('snoozed'));
    }
}
