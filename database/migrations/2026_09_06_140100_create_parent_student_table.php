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
        Schema::create('parent_student', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('parent_id');
            $table->integer('student_id');
            $table->timestamps();

            $table->foreign('parent_id')->references('id')->on('parents')->onDelete('cascade');
            $table->foreign('student_id')->references('idStudent')->on('student')->onDelete('cascade');

            $table->unique(['parent_id', 'student_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('parent_student');
    }
};
