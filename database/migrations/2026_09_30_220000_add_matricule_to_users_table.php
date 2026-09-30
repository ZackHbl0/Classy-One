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
        Schema::table('users', function (Blueprint $table) {
            if (!Schema::hasColumn('users', 'matricule')) {
                $table->string('matricule')->nullable()->unique()->after('id');
            }
            // Make email nullable so fake emails are not required
            $table->string('email')->nullable()->change();
        });

        // Ensure all existing staff have a unique matricule
        $existingUsers = DB::table('users')->whereNull('matricule')->get();
        foreach ($existingUsers as $u) {
            $prefix = match ($u->role) {
                'admin' => 'ADM',
                'secretaire' => 'SEC',
                'professeur', 'prof' => 'PROF',
                default => 'EMP',
            };
            $matricule = $prefix . str_pad($u->id, 3, '0', STR_PAD_LEFT);
            DB::table('users')->where('id', $u->id)->update(['matricule' => $matricule]);
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            if (Schema::hasColumn('users', 'matricule')) {
                $table->dropColumn('matricule');
            }
        });
    }
};
