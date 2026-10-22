<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use App\Models\Student;
use App\Models\Classe;
use App\Models\Registre;

class DummyStudentSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $faker = \Faker\Factory::create('fr_FR');
        $classes = Classe::all();
        
        // Ensure there's at least one class
        if ($classes->isEmpty()) {
            $class = Classe::create([
                'nom_classe' => 'DevOps 1ere Année Master',
                'niveau' => 'Master 1',
                'filiere' => 'Informatique',
                'description' => 'Classe auto-générée pour les tests',
            ]);
        } else {
            $class = $classes->first(); // Just assign them to the first available class
        }

        $guardianFirstNames = ['Mohamed', 'Hassan', 'Brahim', 'Abdelkader', 'Omar', 'Rachid', 'Mustapha', 'Khalid', 'Youssef', 'Aziz'];

        // Generate 20 dummy students
        for ($i = 1; $i <= 20; $i++) {
            $lastName = $faker->lastName;
            $tuteurName = $guardianFirstNames[array_rand($guardianFirstNames)] . ' ' . $lastName;

            $student = Student::updateOrCreate(
                ['matricule' => 'STUD' . str_pad($i, 3, '0', STR_PAD_LEFT)],
                [
                    'nom' => $lastName,
                    'prenom' => $faker->firstName,
                    'tuteur_name' => $tuteurName,
                    'telephone' => $faker->phoneNumber,
                    'numero_tuteur' => '07' . str_pad(mt_rand(10000000, 99999999), 8, '0', STR_PAD_LEFT),
                    'password' => Hash::make('password'),
                    // Simulate a realistic FCM token length
                    'fcmToken' => Str::random(152), 
                ]
            );

            // Link the student to the class via the Registre model
            Registre::updateOrCreate(
                ['idStudent' => $student->idStudent],
                ['Cla_id' => $class->id]
            );
        }
    }
}
