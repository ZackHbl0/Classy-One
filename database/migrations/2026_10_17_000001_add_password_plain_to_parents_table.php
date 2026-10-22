<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        if (!Schema::hasColumn('parents', 'password_plain')) {
            Schema::table('parents', function (Blueprint $table) {
                $table->string('password_plain')->nullable()->after('password');
            });
        }

        // Populate password_plain for demo seeded accounts
        DB::table('parents')->update(['password_plain' => 'password123']);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasColumn('parents', 'password_plain')) {
            Schema::table('parents', function (Blueprint $table) {
                $table->dropColumn('password_plain');
            });
        }
    }
};
