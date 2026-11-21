<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Notifications\RiderHubNotification;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class PasswordResetLinkController extends Controller
{
    /**
     * Display the password reset link request view.
     */
    public function create(): View
    {
        return view('auth.forgot-password');
    }

    /**
     * Handle an incoming password reset link request (applicable to all roles).
     *
     * @throws ValidationException
     */
    public function store(Request $request): RedirectResponse
    {
        $request->merge([
            'email' => Str::lower(trim((string) $request->input('email', ''))),
        ]);

        $validated = $request->validate([
            'email' => ['required', 'email'],
        ]);

        $user = User::query()
            ->whereRaw('LOWER(email) = ?', [$validated['email']])
            ->first();

        if (! $user) {
            return back()
                ->withInput($request->only('email'))
                ->withErrors(['email' => __('passwords.user')]);
        }

        $result = $this->dispatchResetForUser($user, 'email');

        if (! $result['ok']) {
            return back()
                ->withInput($request->only('email'))
                ->withErrors(['email' => $result['message']]);
        }

        $response = back()
            ->with('status', $result['message'])
            ->with('otp_email', $user->email);

        if ($result['reset_url']) {
            $response->with('local_reset_url', $result['reset_url']);
        }

        return $response;
    }

    /**
     * Recover a forgotten account by registered phone number and issue a password reset link.
     */
    public function recover(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'phone' => ['required', 'string', 'max:25'],
        ]);

        $user = $this->findUserByPhone($validated['phone']);

        if (! $user) {
            return back()
                ->withInput($request->only('phone'))
                ->with('recovery_tab', 'phone')
                ->withErrors(['phone' => 'We could not find a RiderHub account linked to that phone number.']);
        }

        $result = $this->dispatchResetForUser($user, 'phone');

        if (! $result['ok']) {
            return back()
                ->withInput($request->only('phone'))
                ->with('recovery_tab', 'phone')
                ->withErrors(['phone' => $result['message']]);
        }

        $maskedEmail = $this->maskEmail($user->email);
        $response = back()
            ->with('recovery_tab', 'phone')
            ->with('otp_email', $user->email)
            ->with('status', "Account found ({$maskedEmail})! We sent a password reset link to your email.");

        if ($result['reset_url']) {
            $response->with('local_reset_url', $result['reset_url']);
        }

        return $response;
    }

    /**
     * Verify a 6-digit OTP code sent via email/phone recovery and reset the user's password.
     */
    public function resetWithCode(Request $request): RedirectResponse
    {
        $request->merge([
            'otp_email' => Str::lower(trim((string) $request->input('otp_email', ''))),
            'code' => preg_replace('/\D+/', '', (string) $request->input('code', '')) ?? '',
        ]);

        $validated = $request->validate([
            'otp_email' => ['required', 'email'],
            'code' => ['required', 'digits:6'],
            'password' => ['required', 'string', 'min:4', 'confirmed'],
        ]);

        $user = User::query()
            ->whereRaw('LOWER(email) = ?', [$validated['otp_email']])
            ->first();

        $cacheKey = 'password_reset_otp:'.sha1($validated['otp_email']);
        $hashedOtp = Cache::get($cacheKey);

        if (! $user || ! is_string($hashedOtp) || ! Hash::check($validated['code'], $hashedOtp)) {
            return back()
                ->with('otp_email', $validated['otp_email'])
                ->withInput($request->only('otp_email', 'code'))
                ->withErrors(['code' => 'Invalid or expired 6-digit verification code. Please request a new code.']);
        }

        $user->forceFill([
            'password' => Hash::make($validated['password']),
            'remember_token' => Str::random(60),
        ])->save();

        Cache::forget($cacheKey);
        Password::broker()->deleteToken($user);

        try {
            $user->notify(new RiderHubNotification(
                title: 'Password updated successfully',
                message: 'Your RiderHub password was changed using a 6-digit verification code.',
                url: route('profile.edit'),
                category: 'security',
            ));
        } catch (\Throwable) {
            // Non-blocking
        }

        return redirect()->route('login')
            ->with('status', 'Your password has been reset successfully! You can now log in with your new password.');
    }

    /**
     * Generates a single valid password reset token and 6-digit OTP code, sends the
     * email notification (when SMTP is configured), records an in-app security
     * notification, and exposes the reset URL directly when no live SMTP mailer is active.
     *
     * @return array{ok: bool, emailed: bool, message: string, reset_url: ?string}
     */
    private function dispatchResetForUser(User $user, string $channel): array
    {
        $mailer = (string) config('mail.default', 'log');
        $hasLiveMailer = ! in_array($mailer, ['log', 'array'], true);
        $broker = Password::broker();
        $token = $broker->createToken($user);
        $otp = str_pad((string) random_int(0, 999999), 6, '0', STR_PAD_LEFT);
        $emailKey = sha1(Str::lower($user->email));

        Cache::put("password_reset_otp:{$emailKey}", Hash::make($otp), now()->addMinutes(15));
        Cache::put("password_reset_otp_plain:{$emailKey}", $otp, now()->addSeconds(30));

        $emailed = false;

        try {
            $user->sendPasswordResetNotification($token);
            $emailed = $hasLiveMailer;
        } catch (\Throwable $exception) {
            Log::warning('Password reset email transport failed; falling back to direct reset link.', [
                'user_id' => $user->id,
                'error' => $exception->getMessage(),
            ]);
            $emailed = false;
        } finally {
            Cache::forget("password_reset_otp_plain:{$emailKey}");
        }

        // Record an in-app security notification for the user account.
        try {
            $viaLabel = $channel === 'phone' ? 'phone number recovery' : 'email recovery';
            $user->notify(new RiderHubNotification(
                title: 'Password reset requested',
                message: "A password reset link was requested for your account via {$viaLabel}. If this wasn't you, update your password immediately.",
                url: route('profile.edit'),
                category: 'security',
            ));
        } catch (\Throwable) {
            // Never block password reset if notification storage fails.
        }

        return [
            'ok' => true,
            'emailed' => $emailed,
            'message' => __('passwords.sent'),
            'reset_url' => $emailed ? null : route('password.reset', ['token' => $token, 'email' => $user->email]),
        ];
    }

    private function findUserByPhone(string $input): ?User
    {
        $normalized = $this->normalizePhone($input);

        if (strlen($normalized) < 7) {
            return null;
        }

        return User::query()
            ->whereNotNull('phone')
            ->get(['id', 'name', 'email', 'phone', 'role'])
            ->first(fn (User $candidate): bool => $this->normalizePhone((string) $candidate->phone) === $normalized);
    }

    private function normalizePhone(string $phone): string
    {
        $digits = preg_replace('/\D+/', '', $phone) ?? '';

        // Normalize Philippine local format (09XXXXXXXXX) and international (+639XXXXXXXXX).
        if (str_starts_with($digits, '63') && strlen($digits) === 12) {
            return '0'.substr($digits, 2);
        }

        return $digits;
    }

    private function maskEmail(string $email): string
    {
        if (! str_contains($email, '@')) {
            return '***';
        }

        [$local, $domain] = explode('@', $email, 2);
        $visible = strlen($local) <= 2 ? substr($local, 0, 1) : substr($local, 0, 2);

        return $visible.'***@'.$domain;
    }
}
