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
