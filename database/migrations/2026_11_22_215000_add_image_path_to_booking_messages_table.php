<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('booking_messages') && ! Schema::hasColumn('booking_messages', 'image_path')) {
            Schema::table('booking_messages', function (Blueprint $table): void {
                $table->string('image_path', 255)->nullable();
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('booking_messages') && Schema::hasColumn('booking_messages', 'image_path')) {
            Schema::table('booking_messages', function (Blueprint $table): void {
                $table->dropColumn('image_path');
            });
        }
    }
};
