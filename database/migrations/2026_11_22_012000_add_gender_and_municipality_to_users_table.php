<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table): void {
            if (! Schema::hasColumn('users', 'gender')) {
                $table->string('gender', 20)->nullable()->after('phone');
            }
            if (! Schema::hasColumn('users', 'municipality')) {
                $table->string('municipality', 80)->nullable()->after('gender');
            }
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table): void {
            $drop = array_values(array_filter(
                ['gender', 'municipality'],
                fn (string $col): bool => Schema::hasColumn('users', $col),
            ));
            if ($drop !== []) {
                $table->dropColumn($drop);
            }
        });
    }
};
