<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasColumn('student', 'password_plain')) {
            Schema::table('student', function (Blueprint $table) {
                $table->string('password_plain')->nullable()->after('password');
            });
        }

        // Migrate any unhashed passwords into password_plain
        DB::statement("UPDATE student SET password_plain = password WHERE password IS NOT NULL AND password NOT LIKE '$2y$%' AND password NOT LIKE '$2a$%'");
    }

    public function down(): void
    {
        if (Schema::hasColumn('student', 'password_plain')) {
            Schema::table('student', function (Blueprint $table) {
                $table->dropColumn('password_plain');
            });
        }
    }
};
