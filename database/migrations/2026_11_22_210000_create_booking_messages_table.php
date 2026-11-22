<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
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
    }

    public function down(): void
    {
        Schema::dropIfExists('booking_messages');
    }
};
