<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('activity_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->foreignId('book_id')->nullable()->constrained('books')->onDelete('cascade');
            $table->enum('action', ['borrowed', 'returned', 'reserved', 'renewed']);
            $table->string('genre')->nullable();
            $table->string('grade_level')->nullable();
            $table->timestamp('action_date');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('activity_logs');
    }
};