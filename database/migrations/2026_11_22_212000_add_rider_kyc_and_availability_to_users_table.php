<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('users')) {
            Schema::table('users', function (Blueprint $table): void {
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
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('users')) {
            Schema::table('users', function (Blueprint $table): void {
                foreach (['is_online', 'rider_status', 'vehicle_model', 'plate_number'] as $col) {
                    if (Schema::hasColumn('users', $col)) {
                        $table->dropColumn($col);
                    }
                }
            });
        }
    }
};
