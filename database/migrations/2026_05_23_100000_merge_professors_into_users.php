<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('professors')) {
            return;
        }

        $idMap = [];

        foreach (DB::table('professors')->orderBy('id')->get() as $professor) {
            $existingUser = DB::table('users')->where('email', $professor->email)->first();

            if ($existingUser) {
                DB::table('users')->where('id', $existingUser->id)->update([
                    'role' => 'professeur',
                    'classe_id' => $professor->classe_id,
                    'name' => $professor->name,
                    'password' => $professor->password,
                    'updated_at' => now(),
                ]);
                $idMap[$professor->id] = $existingUser->id;
            } else {
                $userId = DB::table('users')->insertGetId([
                    'name' => $professor->name,
                    'email' => $professor->email,
                    'password' => $professor->password,
                    'role' => 'professeur',
                    'classe_id' => $professor->classe_id,
                    'remember_token' => $professor->remember_token,
                    'created_at' => $professor->created_at ?? now(),
                    'updated_at' => $professor->updated_at ?? now(),
                ]);
                $idMap[$professor->id] = $userId;
            }
        }

        if (Schema::hasTable('courses') && Schema::hasColumn('courses', 'professor_id')) {
            Schema::table('courses', function (Blueprint $table) {
                $table->dropForeign(['professor_id']);
            });

            foreach ($idMap as $oldId => $newId) {
                DB::table('courses')->where('professor_id', $oldId)->update(['professor_id' => $newId]);
            }

            Schema::table('courses', function (Blueprint $table) {
                $table->foreign('professor_id')
                    ->references('id')
                    ->on('users')
                    ->cascadeOnDelete();
            });
        }

        Schema::dropIfExists('professors');
    }

    public function down(): void
    {
        // Irreversible: legacy professor rows were merged into users.
    }
};
