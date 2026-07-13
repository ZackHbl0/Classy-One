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
        Schema::table('grades', function (Blueprint $table) {
            $table->index('student_id');
        });

        Schema::table('absences', function (Blueprint $table) {
            $table->index('student_id');
        });

        Schema::table('planning', function (Blueprint $table) {
            $table->index('classe_id');
            $table->index('idStudent');
        });

        Schema::table('document_requests', function (Blueprint $table) {
            $table->index('idStudent');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('tables', function (Blueprint $table) {
            //
        });
    }
};
