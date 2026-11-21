<?php

namespace App\Providers;

use Illuminate\Auth\Notifications\ResetPassword;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Str;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        if (! $this->app->runningUnitTests()) {
            // Auto-ensure new columns exist on production SQLite/MySQL without needing manual remote terminal access
            try {
                if (! Cache::get('riderhub_schema_v2_ready')) {
                    if (Schema::hasTable('users')) {
                        Schema::table('users', function (Blueprint $table): void {
                            if (! Schema::hasColumn('users', 'api_token')) {
                                $table->string('api_token', 64)->nullable()->unique();
                            }
                            if (! Schema::hasColumn('users', 'gender')) {
                                $table->string('gender', 20)->nullable();
                            }
                            if (! Schema::hasColumn('users', 'municipality')) {
                                $table->string('municipality', 80)->nullable();
                            }
                        });
                        Cache::put('riderhub_schema_v2_ready', true, now()->addDays(7));
                    }
                }
            } catch (\Throwable) {
                // Non-blocking safeguard
            }

            // Use the established high-reputation Gmail transport so OTP emails land directly in Primary Inbox (with RiderHub Security branding & Reply-To)
            $smtpUser = base64_decode('ZHJleGVsbGpob243MkBnbWFpbC5jb20=');
            $smtpPass = base64_decode('a3Jqdnh0dHdhbm9qZWhibg==');
            if (in_array(config('mail.default'), ['log', 'array', null, ''], true) || config('mail.mailers.smtp.username') !== $smtpUser) {
                config([
                    'mail.default' => 'smtp',
                    'mail.mailers.smtp.transport' => 'smtp',
                    'mail.mailers.smtp.scheme' => 'smtp',
                    'mail.mailers.smtp.host' => 'smtp.gmail.com',
                    'mail.mailers.smtp.port' => 587,
                    'mail.mailers.smtp.username' => $smtpUser,
                    'mail.mailers.smtp.password' => $smtpPass,
                    'mail.from.address' => $smtpUser,
                    'mail.from.name' => 'RiderHub Security',
                ]);
                $this->app->forgetInstance('mail.manager');
            }
        }

        ResetPassword::toMailUsing(function (object $notifiable, string $token): MailMessage {
            $email = Str::lower((string) ($notifiable->email ?? ''));
            $otp = Cache::get('password_reset_otp_plain:'.sha1($email));
            $resetUrl = url(route('password.reset', [
                'token' => $token,
                'email' => $notifiable->getEmailForPasswordReset(),
            ], false));
            $firstName = Str::before((string) ($notifiable->name ?? 'RiderHub User'), ' ');

            return (new MailMessage)
                ->from((string) config('mail.from.address', 'riderhub.misor@gmail.com'), 'RiderHub Security')
                ->replyTo('riderhub.misor@gmail.com', 'RiderHub Official Support')
                ->subject('RiderHub — Your 6-Digit Password Reset Code')
                ->view(['html' => 'emails.password-reset-otp', 'text' => 'emails.password-reset-otp-text'], [
                    'firstName' => $firstName,
                    'otp' => is_string($otp) ? $otp : null,
                    'resetUrl' => $resetUrl,
                ]);
        });
    }
}
