<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
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
            });
        }

        if (Schema::hasTable('users')) {
            Schema::table('users', function (Blueprint $table): void {
                if (! Schema::hasColumn('users', 'license_photo_path')) {
                    $table->string('license_photo_path', 255)->nullable();
                }
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('bookings')) {
            Schema::table('bookings', function (Blueprint $table): void {
                foreach (['promo_code', 'discount_amount', 'cancellation_reason'] as $col) {
                    if (Schema::hasColumn('bookings', $col)) {
                        $table->dropColumn($col);
                    }
                }
            });
        }

        if (Schema::hasTable('users') && Schema::hasColumn('users', 'license_photo_path')) {
            Schema::table('users', function (Blueprint $table): void {
                $table->dropColumn('license_photo_path');
            });
        }
    }
};
