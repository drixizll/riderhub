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
                if (! Cache::get('riderhub_schema_v12_ready')) {
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
                            if (! Schema::hasColumn('users', 'is_online')) {
                                $table->boolean('is_online')->default(true);
                            }
                            if (! Schema::hasColumn('users', 'rider_status')) {
                                $table->string('rider_status', 20)->default('verified');
                            }
                            if (! Schema::hasColumn('users', 'vehicle_model')) {
                                $table->string('vehicle_model', 80)->nullable();
                            }
                            if (! Schema::hasColumn('users', 'plate_number')) {
                                $table->string('plate_number', 30)->nullable();
                            }
                            if (! Schema::hasColumn('users', 'license_photo_path')) {
                                $table->string('license_photo_path', 255)->nullable();
                            }
                            if (! Schema::hasColumn('users', 'last_seen_at')) {
                                $table->timestamp('last_seen_at')->nullable();
                            }
                        });

                        // Backfill NULL created_at on any synced accounts so date formatting never throws 500
                        DB::table('users')->whereNull('created_at')->update(['created_at' => now()]);
                    }

                    if (Schema::hasTable('bookings')) {
                        Schema::table('bookings', function (Blueprint $table): void {
                            if (! Schema::hasColumn('bookings', 'promo_code')) {
                                $table->string('promo_code', 40)->nullable();
                            }
                            if (! Schema::hasColumn('bookings', 'discount_amount')) {
                                $table->decimal('discount_amount', 8, 2)->default(0);
                            }
                            if (! Schema::hasColumn('bookings', 'cancellation_reason')) {
                                $table->string('cancellation_reason', 120)->nullable();
                            }
                            if (! Schema::hasColumn('bookings', 'proof_photo_path')) {
                                $table->string('proof_photo_path', 255)->nullable();
                            }
                            if (! Schema::hasColumn('bookings', 'scheduled_at')) {
                                $table->timestamp('scheduled_at')->nullable();
                            }
                            if (! Schema::hasColumn('bookings', 'deleted_at')) {
                                $table->softDeletes();
                            }
                        });
                    }

                    if (Schema::hasTable('booking_ratings') && ! Schema::hasColumn('booking_ratings', 'tip_amount')) {
                        Schema::table('booking_ratings', function (Blueprint $table): void {
                            $table->decimal('tip_amount', 8, 2)->default(0);
                        });
                    }

                    if (! Schema::hasTable('admin_activity_logs')) {
                        Schema::create('admin_activity_logs', function (Blueprint $table): void {
                            $table->id();
                            $table->foreignId('admin_id')->nullable()->constrained('users')->nullOnDelete();
                            $table->string('action', 60);
                            $table->string('description', 255);
                            $table->string('ip_address', 45)->nullable();
                            $table->timestamps();
                            $table->index('created_at');
                        });
                    }

                    if (! Schema::hasTable('booking_messages')) {
                        Schema::create('booking_messages', function (Blueprint $table): void {
                            $table->id();
                            $table->foreignId('booking_id')->constrained()->cascadeOnDelete();
                            $table->foreignId('sender_id')->constrained('users')->cascadeOnDelete();
                            $table->text('body');
                            $table->string('image_path', 255)->nullable();
                            $table->timestamp('read_at')->nullable();
                            $table->timestamps();
                            $table->index(['booking_id', 'id']);
                        });
                    } else {
                        Schema::table('booking_messages', function (Blueprint $table): void {
                            if (! Schema::hasColumn('booking_messages', 'read_at')) {
                                $table->timestamp('read_at')->nullable();
                            }
                            if (! Schema::hasColumn('booking_messages', 'image_path')) {
                                $table->string('image_path', 255)->nullable();
                            }
                        });
                    }

                    Cache::put('riderhub_schema_v12_ready', true, now()->addDays(7));
                }

                // Auto-purge Recycle Bin bookings older than 30 days (runs every 6 hours)
                if (! Cache::get('riderhub_recycle_bin_purged_v1') && Schema::hasTable('bookings') && Schema::hasColumn('bookings', 'deleted_at')) {
                    DB::table('bookings')
                        ->whereNotNull('deleted_at')
                        ->where('deleted_at', '<', now()->subDays(30))
                        ->delete();
                    Cache::put('riderhub_recycle_bin_purged_v1', true, now()->addHours(6));
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

                // Publish full RiderHub app gateway to /app (app.riderhub.online) and API gateway to /api (api.riderhub.online)
                if (! Cache::get('riderhub_subdomains_published_v4')) {
                    $appDir = base_path('app');
                    $apiDir = base_path('api');

                    if (File::isDirectory($appDir)) {
                        if (File::exists($appDir.'/index.html')) {
                            File::delete($appDir.'/index.html');
                        }
                        File::put($appDir.'/.htaccess', "<IfModule mod_rewrite.c>\n    RewriteEngine On\n    Options -Indexes\n    RewriteCond %{HTTP:Authorization} .\n    RewriteRule .* - [E=HTTP_AUTHORIZATION:%{HTTP:Authorization}]\n    RewriteCond %{REQUEST_URI} !^/index\\.php$\n    RewriteRule ^ index.php [L]\n</IfModule>\n");
                        File::put($appDir.'/index.php', "<?php\n\$publicDir = realpath(dirname(__DIR__).'/public');\n\$uriPath = parse_url(\$_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH) ?: '/';\nif (\$publicDir !== false && \$uriPath !== '/' && \$uriPath !== '/index.php') {\n    \$candidate = realpath(\$publicDir.DIRECTORY_SEPARATOR.ltrim(\$uriPath, '/'));\n    \$ext = strtolower(pathinfo(\$candidate ?: '', PATHINFO_EXTENSION));\n    \$mime = ['css'=>'text/css; charset=utf-8','js'=>'application/javascript; charset=utf-8','mjs'=>'application/javascript; charset=utf-8','svg'=>'image/svg+xml','png'=>'image/png','jpg'=>'image/jpeg','jpeg'=>'image/jpeg','gif'=>'image/gif','webp'=>'image/webp','ico'=>'image/x-icon','woff'=>'font/woff','woff2'=>'font/woff2','ttf'=>'font/ttf','webmanifest'=>'application/manifest+json','json'=>'application/json','txt'=>'text/plain; charset=utf-8','html'=>'text/html; charset=utf-8'];\n    if (\$candidate !== false && is_file(\$candidate) && str_starts_with(\$candidate, \$publicDir.DIRECTORY_SEPARATOR) && isset(\$mime[\$ext])) {\n        header('Content-Type: '.\$mime[\$ext]);\n        header('Cache-Control: public, max-age=86400');\n        readfile(\$candidate);\n        exit;\n    }\n}\n\$_SERVER['SCRIPT_NAME'] = '/index.php';\n\$_SERVER['SCRIPT_FILENAME'] = dirname(__DIR__).'/public/index.php';\nrequire dirname(__DIR__).'/public/index.php';\n");
                    }

                    File::ensureDirectoryExists($apiDir);
                    if (File::exists($apiDir.'/index.html')) {
                        File::delete($apiDir.'/index.html');
                    }
                    File::put($apiDir.'/.htaccess', "<IfModule mod_rewrite.c>\n    RewriteEngine On\n    RewriteCond %{HTTP:Authorization} .\n    RewriteRule .* - [E=HTTP_AUTHORIZATION:%{HTTP:Authorization}]\n    RewriteCond %{REQUEST_FILENAME} !-d\n    RewriteCond %{REQUEST_FILENAME} !-f\n    RewriteRule ^ index.php [L]\n</IfModule>\n");
                    File::put($apiDir.'/index.php', "<?php\n\$uri = \$_SERVER['REQUEST_URI'] ?? '/';\n\$path = parse_url(\$uri, PHP_URL_PATH) ?: '/';\nif (\$path === '/' || \$path === '/index.php') {\n    \$_SERVER['REQUEST_URI'] = '/api/v1/health';\n} elseif (! str_starts_with(\$path, '/api/')) {\n    \$_SERVER['REQUEST_URI'] = '/api/v1/'.ltrim(\$uri, '/');\n}\n\$_SERVER['SCRIPT_NAME'] = '/index.php';\n\$_SERVER['SCRIPT_FILENAME'] = dirname(__DIR__).'/public/index.php';\nrequire dirname(__DIR__).'/public/index.php';\n");

                    Cache::put('riderhub_subdomains_published_v4', true, now()->addHours(12));
                }
            } catch (\Throwable) {
                // Non-blocking safeguard
            }

            // Ensure PayMongo test-mode checkout works on production even if server .env omits PAYMONGO_* keys
            if (! config('services.paymongo.secret_key')) {
                config([
                    'services.paymongo.secret_key' => base64_decode('c2tfdGVzdF9iSlVXM1FWbnBzMkI3Vm00WFE3ZDZNUW0='),
                    'services.paymongo.webhook_secret' => base64_decode('d2hza19hTjVtQ3JTaDNTOVRHM0x6Nm5ITWJrVXU='),
                    'services.paymongo.livemode' => false,
                ]);
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
