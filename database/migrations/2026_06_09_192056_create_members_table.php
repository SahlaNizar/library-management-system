<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('members')) {
            Schema::create('members', function (Blueprint $table) {
                $table->id();
                $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
                $table->string('student_id')->nullable();
                $table->string('grade_level')->nullable();
                $table->string('phone')->nullable();
                $table->string('address')->nullable();
                $table->string('status')->default('active');
                $table->date('membership_date')->nullable();
                $table->date('membership_expiry')->nullable();
                $table->timestamps();
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('members');
    }
};