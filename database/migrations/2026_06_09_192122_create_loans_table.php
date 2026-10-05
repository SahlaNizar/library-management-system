<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('loans')) {
            Schema::create('loans', function (Blueprint $table) {
                $table->id();
                $table->foreignId('member_id')->nullable()->constrained('members')->onDelete('cascade');
                $table->foreignId('book_id')->nullable()->constrained('books')->onDelete('cascade');
                $table->date('borrowed_date');
                $table->date('due_date');
                $table->date('returned_date')->nullable();
                $table->string('status')->default('borrowed');
                $table->integer('renewal_count')->default(0);
                $table->text('notes')->nullable();
                $table->timestamps();
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('loans');
    }
};