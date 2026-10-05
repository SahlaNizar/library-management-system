<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('users', function (Blueprint $table) {
            if (!Schema::hasColumn('users', 'role')) {
                $table->string('role')->default('student')->after('password');
            }
            if (!Schema::hasColumn('users', 'grade_level')) {
                $table->string('grade_level')->nullable()->after('role');
            }
            if (!Schema::hasColumn('users', 'fine_balance')) {
                $table->decimal('fine_balance', 8, 2)->default(0.00);
            }
            if (!Schema::hasColumn('users', 'avatar')) {
                $table->string('avatar')->nullable();
            }
        });
    }

    public function down()
    {
        Schema::table('users', function (Blueprint $table) {
            $cols = [];
            if (Schema::hasColumn('users', 'role')) $cols[] = 'role';
            if (Schema::hasColumn('users', 'grade_level')) $cols[] = 'grade_level';
            if (Schema::hasColumn('users', 'fine_balance')) $cols[] = 'fine_balance';
            if (Schema::hasColumn('users', 'avatar')) $cols[] = 'avatar';
            if (!empty($cols)) {
                $table->dropColumn($cols);
            }
        });
    }
};