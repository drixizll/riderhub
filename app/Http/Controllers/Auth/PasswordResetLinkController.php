<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Password;
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
     * Handle an incoming password reset link request.
     *
     * @throws ValidationException
     */
    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'email' => ['required', 'email'],
        ]);

        $status = Password::sendResetLink(
            $request->only('email')
        );

        if ($status !== Password::RESET_LINK_SENT) {
            return back()
                ->withInput($request->only('email'))
                ->withErrors(['email' => __($status)]);
        }

        $response = back()->with('status', __($status));

        if ($localUrl = $this->localPreviewResetUrl($request->string('email')->toString())) {
            $response->with('local_reset_url', $localUrl);
        }

        return $response;
    }

    /**
     * Recover a forgotten account by registered phone number and send a password reset link.
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

        $status = Password::sendResetLink(['email' => $user->email]);

        if ($status !== Password::RESET_LINK_SENT) {
            return back()
                ->withInput($request->only('phone'))
                ->with('recovery_tab', 'phone')
                ->withErrors(['phone' => __($status)]);
        }

        $maskedEmail = $this->maskEmail($user->email);
        $response = back()
            ->with('recovery_tab', 'phone')
            ->with('status', "Account found ({$maskedEmail})! We sent a password reset link to your email.");

        if ($localUrl = $this->localPreviewResetUrl($user->email)) {
            $response->with('local_reset_url', $localUrl);
        }

        return $response;
    }

    private function findUserByPhone(string $input): ?User
    {
        $normalized = $this->normalizePhone($input);

        if (strlen($normalized) < 7) {
            return null;
        }

        return User::query()
            ->whereNotNull('phone')
            ->get(['id', 'email', 'phone'])
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

    /**
     * In local development with MAIL_MAILER=log, expose a direct reset link so the flow
     * can be tested immediately without configuring an external SMTP server.
     */
    private function localPreviewResetUrl(string $email): ?string
    {
        if (! app()->isLocal() || config('mail.default') !== 'log') {
            return null;
        }

        $user = User::where('email', $email)->first();

        if (! $user) {
            return null;
        }

        $token = Password::broker()->createToken($user);

        return route('password.reset', ['token' => $token, 'email' => $user->email]);
    }
}
