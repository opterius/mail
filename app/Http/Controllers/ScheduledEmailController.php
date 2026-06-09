<?php

namespace App\Http\Controllers;

use App\Models\ScheduledEmail;
use Illuminate\Http\Request;

class ScheduledEmailController extends Controller
{
    public function index()
    {
        $email     = auth('web')->user()->email;
        $scheduled = ScheduledEmail::where('user_email', $email)
            ->where('status', 'pending')
            ->orderBy('send_at')
            ->get();

        return view(mailView('scheduled.index'), compact('scheduled'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'to'      => 'required|string',
            'subject' => 'nullable|string|max:998',
            'body'    => 'nullable|string',
            'send_at' => 'required|date|after:now',
            'cc'      => 'nullable|string',
            'bcc'     => 'nullable|string',
        ]);

        $email = auth('web')->user()->email;

        $scheduled = ScheduledEmail::create([
            'user_email' => $email,
            'to'         => $request->to,
            'cc'         => $request->cc,
            'bcc'        => $request->bcc,
            'subject'    => $request->subject,
            'body'       => $request->body,
            'send_at'    => $request->send_at,
            'status'     => 'pending',
        ]);

        return response()->json(['ok' => true, 'id' => $scheduled->id]);
    }

    public function destroy(ScheduledEmail $scheduledEmail)
    {
        $email = auth('web')->user()->email;

        if ($scheduledEmail->user_email !== $email) {
            abort(403);
        }

        $scheduledEmail->delete();

        return redirect()->route('scheduled.index')->with('success', 'Scheduled email cancelled.');
    }
}
