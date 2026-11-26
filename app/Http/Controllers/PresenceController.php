<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Cache;

class PresenceController extends Controller
{
    public function heartbeat(Request $request): Response
    {
        $user = $request->user();
        if ($user) {
            User::whereKey($user->id)->update(['last_seen_at' => now()]);
            Cache::put('user_seen_'.$user->id, true, now()->addSeconds(15));
        }

        return response()->noContent();
    }

    public function offline(Request $request): Response
    {
        $user = $request->user();
        if ($user) {
            // Set just outside the 35-second online window so partner immediately sees "Offline"
            User::whereKey($user->id)->update(['last_seen_at' => now()->subSeconds(60)]);
            Cache::forget('user_seen_'.$user->id);
        }

        return response()->noContent();
    }
}
