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
        Schema::create('classe_user', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id');
            $table->integer('classe_id'); // classe_id is int(11) in users and classe table id is int(11)
            $table->timestamps();

            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('classe_id')->references('id')->on('classe')->onDelete('cascade');
        });

        // Migrate existing data safely
        DB::statement("
            INSERT INTO classe_user (user_id, classe_id, created_at, updated_at)
            SELECT id, classe_id, NOW(), NOW()
            FROM users 
            WHERE role = 'professeur' AND classe_id IS NOT NULL
        ");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('classe_user');
    }
};
