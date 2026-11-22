<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
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

        if (Schema::hasTable('booking_ratings') && ! Schema::hasColumn('booking_ratings', 'tip_amount')) {
            Schema::table('booking_ratings', function (Blueprint $table): void {
                $table->decimal('tip_amount', 8, 2)->default(0);
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('admin_activity_logs');

        if (Schema::hasTable('booking_ratings') && Schema::hasColumn('booking_ratings', 'tip_amount')) {
            Schema::table('booking_ratings', function (Blueprint $table): void {
                $table->dropColumn('tip_amount');
            });
        }
    }
};
