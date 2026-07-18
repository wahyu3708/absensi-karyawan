<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('attendances', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->date('date');
            $table->dateTime('clock_in')->nullable();
            $table->dateTime('clock_out')->nullable();
            $table->enum('clock_in_status', ['on_time', 'late', 'very_late'])->nullable();
            $table->enum('clock_out_status', ['on_time', 'early_leave'])->nullable();
            $table->foreignId('shift_id')->constrained()->cascadeOnDelete();
            $table->unsignedBigInteger('qr_token_id')->nullable();
            $table->text('notes')->nullable();
            $table->string('ip_address', 45)->nullable();
            $table->text('user_agent')->nullable();
            $table->decimal('latitude', 10, 8)->nullable();
            $table->decimal('longitude', 11, 8)->nullable();
            $table->boolean('location_valid')->default(false);
            $table->timestamps();

            $table->unique(['user_id', 'date', 'shift_id']);
            $table->index(['date', 'shift_id']);
            $table->index(['user_id', 'date']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('attendances');
    }
};
