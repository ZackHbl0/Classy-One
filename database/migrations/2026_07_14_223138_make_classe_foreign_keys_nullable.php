<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('classe', function (Blueprint $table) {
            // Drop foreign keys first to allow column type change
            $table->dropForeign('classe_ibfk_1'); // Ann_id FK
            $table->dropForeign('classe_ibfk_2'); // Not_id FK (if it exists)
        });

        Schema::table('classe', function (Blueprint $table) {
            $table->unsignedBigInteger('Ann_id')->nullable()->change();
            $table->unsignedBigInteger('Not_id')->nullable()->change();
        });
    }

    public function down(): void
    {
        Schema::table('classe', function (Blueprint $table) {
            $table->unsignedBigInteger('Ann_id')->nullable(false)->change();
            $table->unsignedBigInteger('Not_id')->nullable(false)->change();
        });
    }
};
