<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * Creates the grades table for storing student academic grades.
     */
    public function up(): void
    {
        Schema::create('grades', function (Blueprint $table) {
            $table->id();

            // Foreign Keys
            $table->integer('student_id')->unsigned()->comment('References student.idStudent');
            $table->unsignedBigInteger('teacher_id')->comment('References users.id (professor)');
            $table->unsignedBigInteger('course_id')->comment('References courses.id');
            $table->integer('classe_id')->unsigned()->nullable()->comment('Denormalized for faster queries');

            // Grade Information
            $table->decimal('note', 5, 2)->comment('Grade score (e.g., 14.50)');
            $table->enum('type', [
                'Contrôle 1',
                'Contrôle 2',
                'Examen Final',
                'Examen Blanc',
                'Devoir',
                'TP',
                'Projet'
            ])->default('Contrôle 1')->comment('Type of evaluation');

            // Additional Context
            $table->string('subject_name')->nullable()->comment('Cached subject/course name');
            $table->date('exam_date')->nullable()->comment('Date of the exam/evaluation');
            $table->text('comment')->nullable()->comment('Teacher comments or observations');
            $table->string('semester', 50)->nullable()->comment('Semester/Period (e.g., S1, S2)');

            $table->timestamps();

            // Indexes for performance
            $table->index('student_id', 'idx_grades_student');
            $table->index('teacher_id', 'idx_grades_teacher');
            $table->index('course_id', 'idx_grades_course');
            $table->index('classe_id', 'idx_grades_classe');
            $table->index(['student_id', 'course_id', 'type'], 'idx_grades_student_course_type');

            // Note: Foreign key constraint for student_id is commented out
            // because student.idStudent may not be properly indexed as PK.
            // The relationship is maintained at the application level (Eloquent).

            // $table->foreign('student_id')
            //     ->references('idStudent')
            //     ->on('student')
            //     ->cascadeOnDelete();

            $table->foreign('teacher_id')
                ->references('id')
                ->on('users')
                ->cascadeOnDelete();

            $table->foreign('course_id')
                ->references('id')
                ->on('courses')
                ->cascadeOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('grades');
    }
};
