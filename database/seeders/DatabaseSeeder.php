<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Anneescolaire;
use App\Models\Classe;
use App\Models\Course;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // 1. Run standard RolesSeeder (Creates admin@osbt.com and secretaire@osbt.com)
        $this->call(RolesSeeder::class);

        // 2. Seed default school year (Anneescolaire)
        $schoolYear = Anneescolaire::firstOrCreate(
            ['libelle' => '2025/2026'],
            [
                'dateDebut' => '2025-09-01',
                'dateFin' => '2026-06-30'
            ]
        );

        // 3. Seed default Class (DEV201)
        $classe = Classe::firstOrCreate(
            ['nomClasse' => 'DEV201'],
            [
                'Ann_id' => $schoolYear->id,
                'Not_id' => null
            ]
        );

        // 4. Seed default Professor (unified users table with role professeur)
        $professor = User::firstOrCreate(
            ['email' => 'professor@osbt.com'],
            [
                'name' => 'Prof. Ahmed Alami',
                'password' => Hash::make('12345678'),
                'role' => 'professeur',
                'classe_id' => $classe->id,
            ]
        );

        // 5. Seed default Course
        Course::firstOrCreate(
            ['title' => 'Algorithmique & Structures de Données'],
            [
                'description' => 'Ce cours couvre les bases des algorithmes et des structures de données en PHP.',
                'file_path' => null,
                'professor_id' => $professor->id,
                'classe_id' => $classe->id,
            ]
        );
    }
}
