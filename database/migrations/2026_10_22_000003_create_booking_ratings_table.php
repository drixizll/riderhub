<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('booking_ratings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('booking_id')->unique()->constrained()->cascadeOnDelete();
            $table->foreignId('customer_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('rider_id')->constrained('users')->cascadeOnDelete();
            $table->unsignedTinyInteger('score');
            $table->string('comment', 500)->nullable();
            $table->timestamps();

            $table->index(['rider_id', 'score']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('booking_ratings');
    }
};
