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
        // 1. Ensure 'idStudent' is nullable (Fix for some previous DB versions)
        // Some DB drivers need raw SQL for this if there are existing FKs
        try {
            DB::statement('ALTER TABLE planning MODIFY idStudent bigint unsigned NULL');
        } catch (\Exception $e) {
            // Ignore if already nullable or other SQL issues
        }

        Schema::table('planning', function (Blueprint $table) {
            // 2. Add 'classe_id' if missing
            if (!Schema::hasColumn('planning', 'classe_id')) {
                $table->unsignedBigInteger('classe_id')->nullable()->after('idStudent');
            }

            // 3. Add 'professeur_name' if missing
            if (!Schema::hasColumn('planning', 'professeur_name')) {
                $table->string('professeur_name')->nullable()->after('salle');
            }

            // 4. Ensure 'matiere' and 'salle' exist (already added in 221325, but double checking)
            if (!Schema::hasColumn('planning', 'matiere')) {
                $table->string('matiere')->nullable();
            }
            if (!Schema::hasColumn('planning', 'salle')) {
                $table->string('salle')->nullable();
            }
        });

        // 5. Force 'status' to be a string (VARCHAR 255)
        // This is the direct fix for the "Data truncated" error
        DB::statement('ALTER TABLE planning MODIFY status VARCHAR(255) NULL');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('planning', function (Blueprint $table) {
            // We don't drop columns in down for this "fix" migration to avoid data loss
        });
    }
};
