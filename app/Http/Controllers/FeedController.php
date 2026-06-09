<?php

namespace App\Http\Controllers;

use App\Models\FeedSender;
use Illuminate\Http\Request;

class FeedController extends Controller
{
    public function index()
    {
        $email       = auth('web')->user()->email;
        $feedSenders = FeedSender::where('user_email', $email)->orderBy('sender_email')->get();

        return view(mailView('feed.index'), compact('feedSenders'));
    }

    public function store(Request $request)
    {
        $request->validate(['sender_email' => 'required|email']);
        $email = auth('web')->user()->email;

        FeedSender::firstOrCreate([
            'user_email'   => $email,
            'sender_email' => strtolower($request->sender_email),
        ]);

        if ($request->expectsJson()) return response()->json(['ok' => true]);
        return back()->with('success', "{$request->sender_email} moved to Feed.");
    }

    public function destroy(Request $request)
    {
        $request->validate(['sender_email' => 'required|email']);
        $email = auth('web')->user()->email;

        FeedSender::where('user_email', $email)
            ->where('sender_email', strtolower($request->sender_email))
            ->delete();

        if ($request->expectsJson()) return response()->json(['ok' => true]);
        return back()->with('success', "Removed from Feed.");
    }
}
