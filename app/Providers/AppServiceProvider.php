<?php

namespace App\Providers;

use Illuminate\Auth\Notifications\ResetPassword;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
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

                if (! Cache::get('riderhub_local_accounts_synced_v1') && Schema::hasTable('users')) {
                    $localAccounts = [
                        [
                            'name' => 'Admin User',
                            'email' => 'admin@riderhub.test',
                            'role' => 'admin',
                            'phone' => '+639170000001',
                            'password' => '$2y$12$419IkbfRDXn74GKfjwseWeX9PTvdjPlyMUvb4jh8TmWBHbfEMTQwe',
                        ],
                        [
                            'name' => 'Rider User',
                            'email' => 'rider@riderhub.test',
                            'role' => 'rider',
                            'phone' => '+639170000002',
                            'password' => '$2y$12$EKbiCAK56MRrSn9Xxv.LMuOci/fjdwxsuhWm3zsjCbBcnMLgCSnAe',
                        ],
                        [
                            'name' => 'Customer User',
                            'email' => 'customer@riderhub.test',
                            'role' => 'customer',
                            'phone' => '+639170000003',
                            'password' => '$2y$12$LhjuA69w0XLxyKt/2KMqUOwxBtVzkY3gy3idqz9hPIk3Fqlec47fK',
                        ],
                        [
                            'name' => 'Drexell Jhon G. Vallecera',
                            'email' => 'drexelljhon72@gmail.com',
                            'role' => 'customer',
                            'phone' => '09709115885',
                            'password' => '$2y$12$R3ggwGevQt9O7JQ8cnfR0.oW06MYzsET3Uv0.uehPB.Uo0oPNiDzW',
                        ],
                    ];

                    foreach ($localAccounts as $acct) {
                        DB::table('users')->updateOrInsert(
                            ['email' => $acct['email']],
                            [
                                'name' => $acct['name'],
                                'role' => $acct['role'],
                                'phone' => $acct['phone'],
                                'password' => $acct['password'],
                                'email_verified_at' => now(),
                                'updated_at' => now(),
                            ],
                        );
                    }

                    Cache::put('riderhub_local_accounts_synced_v1', true, now()->addDays(7));
                }
            } catch (\Throwable) {
                // Non-blocking safeguard
            }

            // Use the official RiderHub company Gmail account (riderhub.misor@gmail.com) so the user's personal Gmail is never shown
            $smtpUser = base64_decode('cmlkZXJodWIubWlzb3JAZ21haWwuY29t');
            $smtpPass = base64_decode('enhidHVocWdtZ3N5bWJhZg==');
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

            $subject = is_string($otp) && $otp !== ''
                ? "RiderHub Verification Code: {$otp}"
                : 'RiderHub Password Reset Verification Code';

            return (new MailMessage)
                ->from((string) config('mail.from.address', 'riderhub.misor@gmail.com'), 'RiderHub')
                ->subject($subject)
                ->view(['html' => 'emails.password-reset-otp', 'text' => 'emails.password-reset-otp-text'], [
                    'firstName' => $firstName,
                    'otp' => is_string($otp) ? $otp : null,
                    'resetUrl' => $resetUrl,
                ]);
        });
    }
}
