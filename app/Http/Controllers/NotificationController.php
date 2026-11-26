<?php

namespace App\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\View\View;

class NotificationController extends Controller
{
    public function unreadCount(Request $request): JsonResponse
    {
        $user = $request->user();
        $latest = $user->unreadNotifications()->latest()->first();

        return response()->json([
            'count' => $user->unreadNotifications()->count(),
            'latest_id' => $latest?->id,
            'latest_title' => (string) ($latest?->data['title'] ?? ''),
            'latest_message' => (string) ($latest?->data['message'] ?? ''),
        ]);
    }

    public function index(Request $request): View
    {
        return view('notifications.index', [
            'notifications' => $request->user()->notifications()->latest()->paginate(20),
            'unreadCount' => $request->user()->unreadNotifications()->count(),
        ]);
    }

    public function markRead(Request $request, string $notification): RedirectResponse
    {
        $record = $request->user()->notifications()->whereKey($notification)->firstOrFail();
        $record->markAsRead();

        $destination = $record->data['url'] ?? route('notifications.index');
        $destinationPath = parse_url($destination, PHP_URL_PATH);

        if (! is_string($destinationPath) || ! Str::startsWith($destinationPath, '/') || Str::startsWith($destinationPath, '//')) {
            $destination = route('notifications.index');
        }

        return redirect()->to($destination);
    }

    public function markAllRead(Request $request): RedirectResponse
    {
        $request->user()->unreadNotifications()->update(['read_at' => now()]);

        return redirect()->route('notifications.index')->with('status', 'notifications-read');
    }
}
