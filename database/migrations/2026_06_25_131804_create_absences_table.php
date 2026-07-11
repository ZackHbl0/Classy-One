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
        Schema::create('absences', function (Blueprint $table) {
            $table->id();
            $table->integer('student_id');
            $table->integer('classe_id');
            $table->string('matiere');
            $table->unsignedBigInteger('prof_id');
            $table->date('date');
            $table->string('seance');
            $table->boolean('is_justified')->default(false);
            $table->text('justification_reason')->nullable();
            $table->timestamps();

            $table->foreign('student_id')->references('idStudent')->on('student')->onDelete('cascade');
            $table->foreign('classe_id')->references('id')->on('classe')->onDelete('cascade');
            $table->foreign('prof_id')->references('id')->on('users')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('absences');
    }
};
