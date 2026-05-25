<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasColumn('users', 'classe_id')) {
            Schema::table('users', function (Blueprint $table) {
                $table->integer('classe_id')->nullable()->after('role');
            });

            if (Schema::hasTable('classe')) {
                Schema::table('users', function (Blueprint $table) {
                    $table->foreign('classe_id')
                        ->references('id')
                        ->on('classe')
                        ->nullOnDelete();
                });
            }
        }

        if (Schema::hasColumn('users', 'role')) {
            DB::statement("ALTER TABLE users MODIFY COLUMN role ENUM('admin', 'secretaire', 'professeur') NOT NULL DEFAULT 'admin'");
        }
    }

    public function down(): void
    {
        if (Schema::hasColumn('users', 'classe_id')) {
            Schema::table('users', function (Blueprint $table) {
                $table->dropForeign(['classe_id']);
                $table->dropColumn('classe_id');
            });
        }

        if (Schema::hasColumn('users', 'role')) {
            DB::statement("ALTER TABLE users MODIFY COLUMN role ENUM('admin', 'secretaire') NOT NULL DEFAULT 'admin'");
        }
    }
};
