<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('bookings') && ! Schema::hasColumn('bookings', 'proof_photo_path')) {
            Schema::table('bookings', function (Blueprint $table): void {
                $table->string('proof_photo_path', 255)->nullable();
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('bookings') && Schema::hasColumn('bookings', 'proof_photo_path')) {
            Schema::table('bookings', function (Blueprint $table): void {
                $table->dropColumn('proof_photo_path');
            });
        }
    }
};
