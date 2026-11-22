<?php

namespace App\Http\Controllers;

use App\Models\Booking;
use App\Models\BookingMessage;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class BookingMessageController extends Controller
{
    public function index(Request $request, Booking $booking): JsonResponse
    {
        $user = $request->user();

        if (! $this->canAccess($user->id, $user->role, $booking)) {
            abort(403, 'You are not authorized to view messages for this booking.');
        }

        $afterId = (int) $request->query('after_id', 0);

        $messages = BookingMessage::query()
            ->with('sender:id,name,role')
            ->where('booking_id', $booking->id)
            ->when($afterId > 0, fn ($q) => $q->where('id', '>', $afterId))
            ->orderBy('id')
            ->limit(100)
            ->get()
            ->map(fn (BookingMessage $msg) => [
                'id' => $msg->id,
                'sender_id' => $msg->sender_id,
                'sender_name' => $msg->sender?->name ?? 'User',
                'sender_role' => $msg->sender?->role ?? 'customer',
                'is_mine' => $msg->sender_id === $user->id,
                'body' => $msg->body,
                'time' => $msg->created_at?->timezone('Asia/Manila')->format('g:i A') ?? '',
            ]);

        return response()->json([
            'messages' => $messages,
            'booking_status' => $booking->status,
            'can_send' => in_array($booking->status, ['accepted', 'picked_up'], true)
                && in_array($user->id, [$booking->customer_id, $booking->rider_id], true),
        ]);
    }

    public function store(Request $request, Booking $booking): JsonResponse
    {
        $user = $request->user();

        if (! in_array($user->id, [$booking->customer_id, $booking->rider_id], true)) {
            abort(403, 'Only the assigned rider and customer can send messages.');
        }

        if (! in_array($booking->status, ['accepted', 'picked_up'], true)) {
            return response()->json([
                'message' => 'Chat is only available while the trip is active.',
            ], 422);
        }

        $validated = $request->validate([
            'body' => ['required', 'string', 'max:500'],
        ]);

        $body = trim($validated['body']);
        if ($body === '') {
            return response()->json(['message' => 'Message cannot be empty.'], 422);
        }

        $msg = BookingMessage::create([
            'booking_id' => $booking->id,
            'sender_id' => $user->id,
            'body' => $body,
        ]);

        $msg->load('sender:id,name,role');

        return response()->json([
            'message' => [
                'id' => $msg->id,
                'sender_id' => $msg->sender_id,
                'sender_name' => $msg->sender?->name ?? $user->name,
                'sender_role' => $msg->sender?->role ?? $user->role,
                'is_mine' => true,
                'body' => $msg->body,
                'time' => $msg->created_at?->timezone('Asia/Manila')->format('g:i A') ?? '',
            ],
        ], 201);
    }

    private function canAccess(int $userId, string $role, Booking $booking): bool
    {
        return $role === 'admin'
            || $booking->customer_id === $userId
            || $booking->rider_id === $userId;
    }
}
