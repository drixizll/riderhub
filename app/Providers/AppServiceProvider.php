<?php

namespace App\Providers;

use Illuminate\Auth\Notifications\ResetPassword;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Support\Facades\Cache;
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
            // Ensure live RiderHub company Gmail SMTP works automatically on both local serve and Repohive Cloud
            if (in_array(config('mail.default'), ['log', 'array', null, ''], true) || config('mail.mailers.smtp.username') !== 'riderhub.misor@gmail.com') {
                $smtpUser = base64_decode('cmlkZXJodWIubWlzb3JAZ21haWwuY29t');
                $smtpPass = base64_decode('enhidHVocWdtZ3N5bWJhZg==');
                config([
                    'mail.default' => 'smtp',
                    'mail.mailers.smtp.transport' => 'smtp',
                    'mail.mailers.smtp.scheme' => 'smtp',
                    'mail.mailers.smtp.host' => 'smtp.gmail.com',
                    'mail.mailers.smtp.port' => 587,
                    'mail.mailers.smtp.username' => $smtpUser,
                    'mail.mailers.smtp.password' => $smtpPass,
                    'mail.from.address' => $smtpUser,
                    'mail.from.name' => 'RiderHub',
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
                ->from((string) config('mail.from.address', 'noreply@riderhub.online'), 'RiderHub Security')
                ->subject('RiderHub — Your 6-Digit Password Reset Code')
                ->view('emails.password-reset-otp', [
                    'firstName' => $firstName,
                    'otp' => is_string($otp) ? $otp : null,
                    'resetUrl' => $resetUrl,
                ]);
        });
    }
}
