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
        Schema::table('absences', function (Blueprint $table) {
            $table->text('student_explanation')->nullable()->after('justification_reason');
            $table->string('status')->default('pending_justification')->after('student_explanation');
        });

        // Update existing absences that were already justified
        \Illuminate\Support\Facades\DB::table('absences')
            ->where('is_justified', true)
            ->update(['status' => 'approved']);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('absences', function (Blueprint $table) {
            $table->dropColumn(['student_explanation', 'status']);
        });
    }
};
