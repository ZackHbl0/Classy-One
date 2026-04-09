<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class CourseScheduleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $classe_id = 1; // DI2005 belongs to Class 1
        
        $courses = [
            // Today (2026-04-02)
            [
                'classe_id' => $classe_id,
                'matiere' => 'Programmation Java',
                'professeur_name' => 'Prof. Alami',
                'salle' => 'Salle 102',
                'date' => '2026-04-02',
                'check_in' => '08:00:00',
                'check_out' => '10:00:00',
                'status' => 'Actif',
            ],
            [
                'classe_id' => $classe_id,
                'matiere' => 'Développement PHP',
                'professeur_name' => 'Prof. Bennani',
                'salle' => 'Labo 2',
                'date' => '2026-04-02',
                'check_in' => '10:15:00',
                'check_out' => '12:15:00',
                'status' => 'Actif',
            ],
            [
                'classe_id' => $classe_id,
                'matiere' => 'Algorithmique 2',
                'professeur_name' => 'Prof. Touzani',
                'salle' => 'Salle 4',
                'date' => '2026-04-02',
                'check_in' => '14:00:00',
                'check_out' => '16:00:00',
                'status' => 'Actif',
            ],
            
            // Tomorrow (2026-04-03)
            [
                'classe_id' => $classe_id,
                'matiere' => 'JavaScript Moderne',
                'professeur_name' => 'Prof. Idrissi',
                'salle' => 'Salle Informatisée 1',
                'date' => '2026-04-03',
                'check_in' => '08:30:00',
                'check_out' => '11:00:00',
                'status' => 'Actif',
            ],
            [
                'classe_id' => $classe_id,
                'matiere' => 'Structure de Données',
                'professeur_name' => 'Prof. Mansouri',
                'salle' => 'Salle 305',
                'date' => '2026-04-03',
                'check_in' => '11:15:00',
                'check_out' => '13:00:00',
                'status' => 'Actif',
            ],
            
            // Next Monday (2026-04-06)
            [
                'classe_id' => $classe_id,
                'matiere' => 'C++ Fondamentaux',
                'professeur_name' => 'Prof. Meskini',
                'salle' => 'Amphi B',
                'date' => '2026-04-06',
                'check_in' => '09:00:00',
                'check_out' => '12:00:00',
                'status' => 'Actif',
            ],
            [
                'classe_id' => $classe_id,
                'matiere' => 'Design UI/UX',
                'professeur_name' => 'Prof. Zahi',
                'salle' => 'Studio Design',
                'date' => '2026-04-06',
                'check_in' => '14:00:00',
                'check_out' => '17:00:00',
                'status' => 'Actif',
            ],
        ];

        foreach ($courses as $course) {
            DB::table('planning')->insert($course);
        }
    }
}
