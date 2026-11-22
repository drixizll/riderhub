<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('users') && ! Schema::hasColumn('users', 'last_seen_at')) {
            Schema::table('users', function (Blueprint $table): void {
                $table->timestamp('last_seen_at')->nullable();
            });
        }

        if (Schema::hasTable('booking_messages') && ! Schema::hasColumn('booking_messages', 'read_at')) {
            Schema::table('booking_messages', function (Blueprint $table): void {
                $table->timestamp('read_at')->nullable();
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('users') && Schema::hasColumn('users', 'last_seen_at')) {
            Schema::table('users', function (Blueprint $table): void {
                $table->dropColumn('last_seen_at');
            });
        }

        if (Schema::hasTable('booking_messages') && Schema::hasColumn('booking_messages', 'read_at')) {
            Schema::table('booking_messages', function (Blueprint $table): void {
                $table->dropColumn('read_at');
            });
        }
    }
};
