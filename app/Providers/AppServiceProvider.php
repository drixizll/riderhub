<?php

namespace App\Providers;

use Illuminate\Auth\Notifications\ResetPassword;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
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
                if (! Cache::get('riderhub_schema_v3_ready')) {
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
                    }

                    if (! Schema::hasTable('booking_messages')) {
                        Schema::create('booking_messages', function (Blueprint $table): void {
                            $table->id();
                            $table->foreignId('booking_id')->constrained()->cascadeOnDelete();
                            $table->foreignId('sender_id')->constrained('users')->cascadeOnDelete();
                            $table->text('body');
                            $table->timestamps();
                            $table->index(['booking_id', 'id']);
                        });
                    }

                    Cache::put('riderhub_schema_v3_ready', true, now()->addDays(7));
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

                // Publish decoupled frontend to /app (app.riderhub.online) and API gateway to /api (api.riderhub.online)
                if (! Cache::get('riderhub_subdomains_published_v3')) {
                    $frontendDir = base_path('frontend');
                    $appDir = base_path('app');
                    $apiDir = base_path('api');

                    if (File::isDirectory($frontendDir) && File::isDirectory($appDir)) {
                        if (File::exists($appDir.'/index.php')) {
                            File::delete($appDir.'/index.php');
                        }
                        File::copy($frontendDir.'/index.html', $appDir.'/index.html');
                        File::ensureDirectoryExists($appDir.'/js');
                        File::copy($frontendDir.'/js/api.js', $appDir.'/js/api.js');
                    }

                    File::ensureDirectoryExists($apiDir);
                    if (File::exists($apiDir.'/index.html')) {
                        File::delete($apiDir.'/index.html');
                    }
                    File::put($apiDir.'/.htaccess', "<IfModule mod_rewrite.c>\n    RewriteEngine On\n    RewriteCond %{HTTP:Authorization} .\n    RewriteRule .* - [E=HTTP_AUTHORIZATION:%{HTTP:Authorization}]\n    RewriteCond %{REQUEST_FILENAME} !-d\n    RewriteCond %{REQUEST_FILENAME} !-f\n    RewriteRule ^ index.php [L]\n</IfModule>\n");
                    File::put($apiDir.'/index.php', "<?php\n\$uri = \$_SERVER['REQUEST_URI'] ?? '/';\n\$path = parse_url(\$uri, PHP_URL_PATH) ?: '/';\nif (\$path === '/' || \$path === '/index.php') {\n    \$_SERVER['REQUEST_URI'] = '/api/v1/health';\n} elseif (! str_starts_with(\$path, '/api/')) {\n    \$_SERVER['REQUEST_URI'] = '/api/v1/'.ltrim(\$uri, '/');\n}\n\$_SERVER['SCRIPT_NAME'] = '/index.php';\n\$_SERVER['SCRIPT_FILENAME'] = dirname(__DIR__).'/public/index.php';\nrequire dirname(__DIR__).'/public/index.php';\n");

                    Cache::put('riderhub_subdomains_published_v3', true, now()->addHours(12));
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
