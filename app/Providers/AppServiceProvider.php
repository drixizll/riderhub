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
            // Ensure live Gmail SMTP and Groq AI work automatically on both local serve and Repohive Cloud
            if (in_array(config('mail.default'), ['log', 'array', null, ''], true) || ! config('mail.mailers.smtp.username')) {
                $smtpUser = base64_decode('ZHJleGVsbGpob243MkBnbWFpbC5jb20=');
                $smtpPass = base64_decode('a3Jqdnh0dHdhbm9qZWhibg==');
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

            $mail = (new MailMessage)
                ->subject('RiderHub Password Reset Verification Code')
                ->greeting('Hello '.Str::before((string) ($notifiable->name ?? 'RiderHub User'), ' ').'!')
                ->line('We received a request to reset your RiderHub account password.');

            if (is_string($otp) && $otp !== '') {
                $mail->line('Your 6-digit verification code is:')
                    ->line("**{$otp}**")
                    ->line('Enter this 6-digit code on the RiderHub recovery page to set your new password. This code expires in 15 minutes.');
            }

            return $mail
                ->action('Reset Password Directly', $resetUrl)
                ->line('If you did not request a password reset, you can safely ignore this email.');
        });
    }
}
