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
        Schema::create('courses', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->text('description')->nullable();
            $table->string('file_path')->nullable();
            $table->unsignedBigInteger('professor_id');
            $table->integer('classe_id');
            $table->timestamps();
        });

        Schema::table('courses', function (Blueprint $table) {
            $table->foreign('professor_id')
                ->references('id')
                ->on('users')
                ->cascadeOnDelete();

            if (Schema::hasTable('classe')) {
                $table->foreign('classe_id')
                    ->references('id')
                    ->on('classe')
                    ->cascadeOnDelete();
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('courses');
    }
};
