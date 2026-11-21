<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class AuthApiController extends Controller
{
    public function register(Request $request): JsonResponse
    {
        $request->merge([
            'email' => Str::lower(trim((string) $request->input('email', ''))),
        ]);

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users,email'],
            'phone' => ['nullable', 'string', 'max:30'],
            'role' => ['nullable', 'in:customer,rider'],
            'password' => ['required', 'string', 'min:4'],
        ]);

        $plainToken = Str::random(60);

        $user = User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'phone' => $validated['phone'] ?? null,
            'role' => $validated['role'] ?? 'customer',
            'password' => $validated['password'],
        ]);
        $user->forceFill(['api_token' => hash('sha256', $plainToken)])->save();

        return response()->json([
            'token' => $plainToken,
            'user' => $this->formatUser($user),
        ], 201);
    }

    public function login(Request $request): JsonResponse
    {
        $request->merge([
            'email' => Str::lower(trim((string) $request->input('email', ''))),
        ]);

        $validated = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required', 'string'],
        ]);

        $user = User::where('email', $validated['email'])->first();

        if (! $user || ! Hash::check($validated['password'], $user->password)) {
            return response()->json(['message' => 'Invalid email or password.'], 422);
        }

        $plainToken = Str::random(60);
        $user->forceFill(['api_token' => hash('sha256', $plainToken)])->save();

        return response()->json([
            'token' => $plainToken,
            'user' => $this->formatUser($user),
        ]);
    }

    public function me(Request $request): JsonResponse
    {
        /** @var User $user */
        $user = $request->user();

        return response()->json([
            'user' => $this->formatUser($user),
            'stats' => [
                'bookings_count' => $user->role === 'rider'
                    ? $user->riderBookings()->count()
                    : $user->bookings()->count(),
            ],
        ]);
    }

    public function logout(Request $request): JsonResponse
    {
        /** @var User $user */
        $user = $request->user();
        $user->forceFill(['api_token' => null])->save();

        return response()->json(['message' => 'Logged out successfully.']);
    }

    /**
     * @return array<string, mixed>
     */
    private function formatUser(User $user): array
    {
        return [
            'id' => $user->id,
            'name' => $user->name,
            'email' => $user->email,
            'phone' => $user->phone,
            'role' => $user->role,
            'photo_url' => $user->profile_photo_path
                ? route('profile.photo.show', ['user' => $user->id, 'v' => $user->updated_at?->timestamp])
                : null,
        ];
    }
}
