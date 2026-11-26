<?php

namespace App\Http\Controllers;

use App\Models\Booking;
use App\Models\BookingMessage;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Symfony\Component\HttpFoundation\StreamedResponse;

class BookingMessageController extends Controller
{
    public function index(Request $request, Booking $booking): JsonResponse
    {
        /** @var User $user */
        $user = $request->user();

        if (! $this->canAccess($user->id, $user->role, $booking)) {
            abort(403, 'You are not authorized to view messages for this booking.');
        }

        $isVisible = $request->query('visible', '1') === '1';

        // Only refresh live presence & mark messages seen when user's tab is actually visible
        if ($isVisible) {
            User::whereKey($user->id)->update(['last_seen_at' => now()]);

            if (in_array($user->id, [$booking->customer_id, $booking->rider_id], true)) {
                BookingMessage::query()
                    ->where('booking_id', $booking->id)
                    ->where('sender_id', '!=', $user->id)
                    ->whereNull('read_at')
                    ->update(['read_at' => now()]);
            }
        }

        $afterId = (int) $request->query('after_id', 0);

        $messages = BookingMessage::query()
            ->with('sender:id,name,role,profile_photo_path,updated_at')
            ->where('booking_id', $booking->id)
            ->when($afterId > 0, fn ($q) => $q->where('id', '>', $afterId))
            ->orderBy('id')
            ->limit(100)
            ->get()
            ->map(fn (BookingMessage $msg) => $this->serializeMessage($msg, $user->id));

        $seenUpToId = (int) (BookingMessage::query()
            ->where('booking_id', $booking->id)
            ->where('sender_id', $user->id)
            ->whereNotNull('read_at')
            ->max('id') ?? 0);

        $booking->loadMissing(['customer:id,name,role,gender,profile_photo_path,last_seen_at,updated_at', 'rider:id,name,role,gender,profile_photo_path,last_seen_at,updated_at']);
        $partner = $user->id === $booking->rider_id ? $booking->customer : $booking->rider;

        return response()->json([
            'messages' => $messages,
            'seen_up_to_id' => $seenUpToId,
            'partner' => $this->serializePartner($partner),
            'booking_status' => $booking->status,
            'can_send' => in_array($booking->status, ['accepted', 'picked_up'], true)
                && in_array($user->id, [$booking->customer_id, $booking->rider_id], true),
        ]);
    }

    public function store(Request $request, Booking $booking): JsonResponse
    {
        /** @var User $user */
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
            'body' => ['nullable', 'string', 'max:500'],
            'image' => ['nullable', 'image', 'mimes:jpeg,jpg,png,webp,gif', 'max:5120'],
        ]);

        $body = trim((string) ($validated['body'] ?? ''));
        $imagePath = null;

        if ($request->hasFile('image')) {
            $stored = $request->file('image')->store('chat-attachments', 'public');
            if ($stored !== false) {
                $imagePath = $stored;
            }
        }

        if ($body === '' && $imagePath === null) {
            return response()->json(['message' => 'Please enter a message or attach a photo.'], 422);
        }

        User::whereKey($user->id)->update(['last_seen_at' => now()]);

        $msg = BookingMessage::create([
            'booking_id' => $booking->id,
            'sender_id' => $user->id,
            'body' => $body,
            'image_path' => $imagePath,
        ]);

        $msg->load('sender:id,name,role,profile_photo_path,updated_at');

        return response()->json([
            'message' => $this->serializeMessage($msg, $user->id),
        ], 201);
    }

    public function showImage(Request $request, Booking $booking, BookingMessage $message): StreamedResponse
    {
        /** @var User $user */
        $user = $request->user();

        if (! $this->canAccess($user->id, $user->role, $booking) || $message->booking_id !== $booking->id) {
            abort(403);
        }

        if (! $message->image_path || ! Storage::disk('public')->exists($message->image_path)) {
            abort(404);
        }

        return Storage::disk('public')->response($message->image_path, null, [
            'Cache-Control' => 'private, max-age=86400',
        ]);
    }

    private function serializeMessage(BookingMessage $msg, int $viewerId): array
    {
        $sender = $msg->sender;
        $name = $sender?->name ?? 'User';

        return [
            'id' => $msg->id,
            'sender_id' => $msg->sender_id,
            'sender_name' => $name,
            'sender_initials' => $this->initials($name),
            'sender_avatar' => $sender?->profile_photo_path
                ? route('profile.photo.show', ['user' => $sender->id, 'v' => $sender->updated_at?->timestamp])
                : null,
            'sender_role' => $sender?->role ?? 'customer',
            'is_mine' => $msg->sender_id === $viewerId,
            'is_seen' => $msg->read_at !== null,
            'body' => $msg->body,
            'image_url' => $msg->image_path
                ? route('bookings.messages.image', ['booking' => $msg->booking_id, 'message' => $msg->id])
                : null,
            'time' => $msg->created_at?->timezone('Asia/Manila')->format('g:i A') ?? '',
        ];
    }

    private function serializePartner(?User $partner): array
    {
        if (! $partner) {
            return [
                'name' => 'Partner',
                'initials' => 'RH',
                'avatar' => null,
                'gender' => null,
                'is_online' => false,
                'status_text' => 'Offline',
            ];
        }

        $isOnline = $partner->isCurrentlyOnline();
        $statusText = $partner->presenceLabel();

        $genderLabel = in_array($partner->gender, ['male', 'female'], true)
            ? ucfirst($partner->gender)
            : null;

        return [
            'name' => $partner->name,
            'initials' => $this->initials($partner->name),
            'avatar' => $partner->profile_photo_path
                ? route('profile.photo.show', ['user' => $partner->id, 'v' => $partner->updated_at?->timestamp])
                : null,
            'gender' => $genderLabel,
            'is_online' => $isOnline,
            'status_text' => $statusText,
        ];
    }

    private function initials(string $name): string
    {
        return Str::of($name)->explode(' ')->filter()->take(2)->map(fn ($p) => mb_substr($p, 0, 1))->implode('') ?: 'U';
    }

    private function canAccess(int $userId, string $role, Booking $booking): bool
    {
        return $role === 'admin'
            || $booking->customer_id === $userId
            || $booking->rider_id === $userId;
    }
}
