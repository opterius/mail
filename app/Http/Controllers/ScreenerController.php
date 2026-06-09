<?php

namespace App\Http\Controllers;

use App\Models\KnownSender;
use Illuminate\Http\Request;

class ScreenerController extends Controller
{
    public function index()
    {
        $email   = auth('web')->user()->email;
        $blocked = KnownSender::where('user_email', $email)->where('status', 'blocked')->orderBy('sender_email')->get();
        $approved = KnownSender::where('user_email', $email)->where('status', 'approved')->orderBy('sender_email')->get();

        return view(mailView('screener.index'), compact('blocked', 'approved'));
    }

    public function approve(Request $request)
    {
        $request->validate(['sender_email' => 'required|email']);
        $email = auth('web')->user()->email;

        KnownSender::updateOrCreate(
            ['user_email' => $email, 'sender_email' => strtolower($request->sender_email)],
            ['status' => 'approved']
        );

        return back()->with('success', "{$request->sender_email} approved.");
    }

    public function block(Request $request)
    {
        $request->validate(['sender_email' => 'required|email']);
        $email = auth('web')->user()->email;

        KnownSender::updateOrCreate(
            ['user_email' => $email, 'sender_email' => strtolower($request->sender_email)],
            ['status' => 'blocked']
        );

        return back()->with('success', "{$request->sender_email} blocked.");
    }

    public function destroy(Request $request)
    {
        $request->validate(['sender_email' => 'required|email']);
        $email = auth('web')->user()->email;

        KnownSender::where('user_email', $email)
            ->where('sender_email', strtolower($request->sender_email))
            ->delete();

        return back()->with('success', "Removed from known senders.");
    }
}
