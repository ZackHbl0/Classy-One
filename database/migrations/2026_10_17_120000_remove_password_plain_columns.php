<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        if (Schema::hasColumn('student', 'password_plain')) {
            Schema::table('student', function (Blueprint $table) {
                $table->dropColumn('password_plain');
            });
        }

        if (Schema::hasColumn('parents', 'password_plain')) {
            Schema::table('parents', function (Blueprint $table) {
                $table->dropColumn('password_plain');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (!Schema::hasColumn('student', 'password_plain')) {
            Schema::table('student', function (Blueprint $table) {
                $table->string('password_plain')->nullable()->after('password');
            });
        }

        if (!Schema::hasColumn('parents', 'password_plain')) {
            Schema::table('parents', function (Blueprint $table) {
                $table->string('password_plain')->nullable()->after('password');
            });
        }
    }
};
