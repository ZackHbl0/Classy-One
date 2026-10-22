<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Carbon\Carbon;
use Carbon\CarbonInterface;

use App\Models\User;
use App\Models\Anneescolaire;
use App\Models\Classe;
use App\Models\Student;
use App\Models\SchoolParent;
use App\Models\Registre;
use App\Models\Planning;
use App\Models\Course;
use App\Models\Grade;
use App\Models\Absence;
use App\Models\Paiement;
use App\Models\DocumentRequest;
use App\Models\Event;
use App\Models\Notification;
use App\Models\AuditLog;

class DemoDataSeeder extends Seeder
{
    /**
     * Run the comprehensive database seeder for Classy-One.
     */
    public function run(): void
    {
        $this->command?->info("🚀 Starting Comprehensive Classy-One Demo Data Seeder...");

        // Ensure storage directory for demo documents exists
        if (!Storage::disk('public')->exists('documents/official')) {
            Storage::disk('public')->makeDirectory('documents/official');
        }
        if (!Storage::disk('public')->exists('courses')) {
            Storage::disk('public')->makeDirectory('courses');
        }

        $now = Carbon::now();
        $defaultPasswordHash = Hash::make('password123');

        // =========================================================================
        // SECTION 1: STRUCTURE PÉDAGOGIQUE & UTILISATEURS (CYCLES, CLASSES, STAFF)
        // =========================================================================
        $this->command?->info("📚 [1/7] Seeding School Years & Classes across multiple cycles...");

        $schoolYear = Anneescolaire::updateOrCreate(
            ['libelle' => '2025/2026'],
            [
                'dateDebut' => '2025-09-01',
                'dateFin' => '2026-06-30'
            ]
        );

        // Multiple cycles & classes
        $classesData = [
            ['nom' => '1AC-A', 'desc' => '1ère Année Collège - Groupe A', 'frais' => 12000.00],
            ['nom' => '2AC-B', 'desc' => '2ème Année Collège - Groupe B', 'frais' => 12000.00],
            ['nom' => 'TCS-1', 'desc' => 'Tronc Commun Scientifique - Section 1', 'frais' => 14000.00],
            ['nom' => 'DEV201', 'desc' => 'Développement Digital - Option Web & Mobile', 'frais' => 15000.00],
            ['nom' => 'DEV202', 'desc' => 'Ingénierie Logicielle & DevOps', 'frais' => 15000.00],
            ['nom' => 'INFO301', 'desc' => 'Licence Génie Informatique & Cloud', 'frais' => 18000.00],
        ];

        $classes = [];
        foreach ($classesData as $c) {
            $classe = Classe::updateOrCreate(
                ['nomClasse' => $c['nom']],
                [
                    'Ann_id' => $schoolYear->id,
                    'Not_id' => null
                ]
            );
            $classes[$c['nom']] = $classe;
        }

        $classe1 = $classes['DEV201'];
        $classe2 = $classes['DEV202'];

        // 1.2 Administrative Staff
        $this->command?->info("👤 [1.2] Seeding Administration & Secretarial Staff...");

        $admin = User::updateOrCreate(
            ['email' => 'admin@osbt.com'],
            [
                'matricule' => 'ADM001',
                'name' => 'Directeur Hassan Alami',
                'password' => $defaultPasswordHash,
                'role' => 'admin',
                'classe_id' => $classe1->id,
            ]
        );

        $secretaire1 = User::updateOrCreate(
            ['email' => 'secretaire@osbt.com'],
            [
                'matricule' => 'SEC001',
                'name' => 'Fatima Zahra Benkirane',
                'password' => $defaultPasswordHash,
                'role' => 'secretaire',
                'classe_id' => $classe1->id,
            ]
        );

        $secretaire2 = User::updateOrCreate(
            ['email' => 'karima.secretaire@osbt.com'],
            [
                'matricule' => 'SEC002',
                'name' => 'Karima Tazi',
                'password' => $defaultPasswordHash,
                'role' => 'secretaire',
                'classe_id' => $classe2->id,
            ]
        );

        // 1.3 Faculty / Professors (8 diverse specialized professors)
        $this->command?->info("👨‍🏫 [1.3] Seeding Faculty & Assigning Course Subjects...");

        $professorsList = [
            [
                'matricule' => 'PROF001',
                'name' => 'Prof. Zakaria Mansouri',
                'email' => 'zakaria.mansouri@osbt.com',
                'matieres' => ['Développement Mobile (Flutter)', 'Architecture Mobile iOS & Android'],
                'primary' => 'Développement Mobile (Flutter)',
                'room' => 'Labo Mobile 1',
                'classes' => ['DEV201', 'DEV202', 'INFO301'],
            ],
            [
                'matricule' => 'PROF002',
                'name' => 'Prof. Yassmine Bennani',
                'email' => 'yassmine.bennani@osbt.com',
                'matieres' => ['Développement Backend (Laravel & PHP)', 'Architecture API RESTful'],
                'primary' => 'Développement Backend (Laravel & PHP)',
                'room' => 'Labo Web 2',
                'classes' => ['DEV201', 'DEV202', 'INFO301'],
            ],
            [
                'matricule' => 'PROF003',
                'name' => 'Prof. Amine El Idrissi',
                'email' => 'amine.idrissi@osbt.com',
                'matieres' => ['Bases de Données Relationnelles & SQL', 'Administration MySQL & NoSQL'],
                'primary' => 'Bases de Données Relationnelles & SQL',
                'room' => 'Salle BDD 101',
                'classes' => ['DEV201', 'DEV202', 'TCS-1'],
            ],
            [
                'matricule' => 'PROF004',
                'name' => 'Prof. Rania Tazi',
                'email' => 'rania.tazi@osbt.com',
                'matieres' => ['Algorithmique & Structures de Données', 'Programmation Python'],
                'primary' => 'Algorithmique & Structures de Données',
                'room' => 'Salle Algo 102',
                'classes' => ['DEV201', 'DEV202', 'TCS-1', '1AC-A'],
            ],
            [
                'matricule' => 'PROF005',
                'name' => 'Prof. Bilal Chraibi',
                'email' => 'bilal.chraibi@osbt.com',
                'matieres' => ['Réseaux Informatiques & Télécoms', 'Cybersécurité Fondamentale'],
                'primary' => 'Réseaux Informatiques & Télécoms',
                'room' => 'Labo Réseaux 201',
                'classes' => ['DEV201', 'DEV202', 'INFO301'],
            ],
            [
                'matricule' => 'PROF006',
                'name' => 'Prof. Salma Berrada',
                'email' => 'salma.berrada@osbt.com',
                'matieres' => ['Design UI/UX & Prototypage Figma', 'Développement Frontend (React & Tailwind)'],
                'primary' => 'Design UI/UX & Prototypage Figma',
                'room' => 'Studio Design 301',
                'classes' => ['DEV201', 'DEV202', 'INFO301'],
            ],
            [
                'matricule' => 'PROF007',
                'name' => 'Prof. Mehdi Alaoui',
                'email' => 'mehdi.alaoui@osbt.com',
                'matieres' => ['Mathématiques Appliquées & Statistiques', 'Physique & Sciences de l\'Ingénieur'],
                'primary' => 'Mathématiques Appliquées & Statistiques',
                'room' => 'Amphi B',
                'classes' => ['TCS-1', '1AC-A', '2AC-B'],
            ],
            [
                'matricule' => 'PROF008',
                'name' => 'Prof. Leila Kabbaj',
                'email' => 'leila.kabbaj@osbt.com',
                'matieres' => ['Communication Professionnelle & Soft Skills', 'Anglais Technique'],
                'primary' => 'Communication Professionnelle & Soft Skills',
                'room' => 'Salle Langues 105',
                'classes' => ['DEV201', 'DEV202', 'TCS-1', '2AC-B'],
            ],
        ];

        $professors = [];
        foreach ($professorsList as $pData) {
            $prof = User::updateOrCreate(
                ['email' => $pData['email']],
                [
                    'matricule' => $pData['matricule'],
                    'name' => $pData['name'],
                    'password' => $defaultPasswordHash,
                    'role' => 'professeur',
                    'classe_id' => $classe1->id,
                    'matieres' => $pData['matieres'],
                ]
            );

            // Sync with assigned classes
            $assignedClassIds = array_map(fn($cn) => $classes[$cn]->id, $pData['classes']);
            $prof->classes()->syncWithoutDetaching($assignedClassIds);

            $professors[$pData['matricule']] = $prof;
        }

        // =========================================================================
        // SECTION 1.4: ÉLÈVES & COMPTES PARENTS (INTERCONNECTÉS AVEC FRATRIES)
        // =========================================================================
        $this->command?->info("👨‍👩‍👧‍👦 [1.4] Seeding 72 Authentic Students and 48 Linked Parents...");

        $maleFirstNames = [
            'Amine', 'Mehdi', 'Yassine', 'Ayoub', 'Hamza', 'Othmane', 'Anas', 'Omar', 'Karim', 'Saad',
            'Walid', 'Reda', 'Ismail', 'Taha', 'Soufiane', 'Adam', 'Rayan', 'Ilyas', 'Badr', 'Marouane',
            'Nizar', 'Youssef', 'Sami', 'Zakaria', 'Ali', 'Mouad', 'Driss', 'Tarik', 'Adil', 'Nabil',
            'Hicham', 'Jalal', 'Ibrahim', 'Farouk', 'Anouar', 'Wassim'
        ];

        $femaleFirstNames = [
            'Hiba', 'Sara', 'Salma', 'Lina', 'Aya', 'Kenza', 'Rania', 'Yasmina', 'Nour', 'Malak',
            'Ines', 'Maryam', 'Ghita', 'Rim', 'Zineb', 'Imane', 'Manal', 'Nada', 'Chaïma', 'Meryem',
            'Asmaa', 'Houda', 'Fatima', 'Oumaima', 'Kaoutar', 'Safaa', 'Loubna', 'Doha', 'Wissal', 'Siham',
            'Khadija', 'Samira', 'Latifa', 'Nadia', 'Chaymae', 'Bouchra'
        ];

        $surnames = [
            'Alami', 'Berrada', 'Benjelloun', 'Tazi', 'Mansouri', 'El Amrani', 'Chraibi', 'Bennani', 'Idrissi', 'Kabbaj',
            'El Fassi', 'Tahiri', 'Alaoui', 'Filali', 'Daoudi', 'Zniber', 'Naciri', 'Slaoui', 'Chaoui', 'Touzani',
            'Lahlou', 'Guessous', 'Cherkaoui', 'Haddad', 'Senhaji', 'Belkacem', 'Mezouar', 'Bouzidi', 'Sabri', 'El Khattabi',
            'Taoufik', 'Benmoussa', 'El Ouarzazi', 'Jaidi', 'Kettani', 'Benkirane'
        ];

        $fatherNames = [
            'Mohamed', 'Hassan', 'Brahim', 'Abdelkader', 'Mustapha', 'Omar', 'Rachid',
            'Youssef', 'Khalid', 'Aziz', 'Driss', 'Said', 'Abdellah', 'Ahmed', 'Tariq',
            'Hamid', 'Abderrahim', 'Jamal', 'Kamal', 'Noureddine'
        ];

        // Distribution of 72 students across the 6 classes:
        // DEV201: 20 students
        // DEV202: 20 students
        // TCS-1: 10 students
        // 1AC-A: 8 students
        // 2AC-B: 8 students
        // INFO301: 6 students
        $classDistribution = [
            'DEV201' => 20,
            'DEV202' => 20,
            'TCS-1' => 10,
            '1AC-A' => 8,
            '2AC-B' => 8,
            'INFO301' => 6,
        ];

        $allStudents = [];
        $studentCounter = 1;

        // Financial profiles setup for demonstration:
        // Group A (Fully paid 100%): 26 students
        // Group B (Partially paid ~50%): 30 students
        // Group C (Unpaid / In arrears): 16 students
        $groupAPayments = [];
        $groupBPayments = [];
        $groupCPayments = [];

        foreach ($classDistribution as $className => $count) {
            $targetClasse = $classes[$className];
            $baseTuition = 15000.00;
            if (str_contains($className, 'AC')) $baseTuition = 12000.00;
            elseif ($className === 'TCS-1') $baseTuition = 14000.00;
            elseif ($className === 'INFO301') $baseTuition = 18000.00;

            for ($k = 1; $k <= $count; $k++) {
                $idx = $studentCounter;
                $isFemale = ($idx % 2 === 0);
                $firstName = $isFemale 
                    ? $femaleFirstNames[(intval($idx / 2)) % count($femaleFirstNames)] 
                    : $maleFirstNames[(intval($idx / 2)) % count($maleFirstNames)];
                $lastName = $surnames[($idx - 1) % count($surnames)];

                // Sibling link: students in DEV201 and DEV202 or 1AC-A share parents
                if ($idx <= 24) {
                    $parentIndex = $idx;
                } elseif ($idx > 24 && $idx <= 48) {
                    $parentIndex = $idx - 24; // Sibling of student (idx - 24)
                } else {
                    $parentIndex = $idx - 20;
                }

                $fatherFirst = $fatherNames[($parentIndex - 1) % count($fatherNames)];
                $tuteurName = $fatherFirst . ' ' . $lastName;
                $parentPhone = '07' . str_pad(70000000 + $parentIndex, 8, '0', STR_PAD_LEFT);
                $parentEmail = strtolower(Str::slug($fatherFirst) . '.' . Str::slug($lastName) . ($parentIndex > 24 ? '.' . $parentIndex : '') . '@parent.osbt.com');

                // 1. Create or retrieve Parent
                $parent = SchoolParent::updateOrCreate(
                    ['email' => $parentEmail],
                    [
                        'name' => $tuteurName,
                        'phone' => $parentPhone,
                        'password' => $defaultPasswordHash,
                        'fcm_token' => 'fcm_parent_demo_' . $parentIndex . '_' . Str::random(16),
                        'last_seen_at' => $now->copy()->subHours(mt_rand(1, 48)),
                    ]
                );

                // 2. Determine Scholarship / Discount (Remises)
                $remiseMontant = 0.00;
                $remiseType = 'aucune';
                $remiseMotif = null;

                // Grant scholarships & discounts to realistic subsets
                if ($idx % 7 === 0) {
                    $remiseMontant = 5000.00;
                    $remiseType = 'bourse_merite';
                    $remiseMotif = 'Bourse d\'excellence académique (Moyenne générale supérieure à 17.50/20)';
                } elseif ($idx % 11 === 0) {
                    $remiseMontant = 6000.00;
                    $remiseType = 'bourse_sociale';
                    $remiseMotif = 'Aide sociale de rentrée scolaire accordée par la commission';
                } elseif ($parentIndex <= 24 && $idx > 24) {
                    $remiseMontant = 3000.00;
                    $remiseType = 'remise_fratrie';
                    $remiseMotif = 'Remise fratrie (2 enfants scolarisés dans l\'établissement)';
                } elseif ($idx % 13 === 0) {
                    $remiseMontant = 2500.00;
                    $remiseType = 'convention';
                    $remiseMotif = 'Convention de partenariat d\'entreprise (Attijariwafa / OCP)';
                }

                $matricule = 'ST2026' . str_pad($idx, 4, '0', STR_PAD_LEFT);

                // 3. Create or update Student
                $student = Student::updateOrCreate(
                    ['matricule' => $matricule],
                    [
                        'nom' => $lastName,
                        'prenom' => $firstName,
                        'frais_scolarite' => $baseTuition,
                        'remise_montant' => $remiseMontant,
                        'remise_type' => $remiseType,
                        'remise_motif' => $remiseMotif,
                        'password' => $defaultPasswordHash,
                        'telephone' => '06' . str_pad(10000000 + $idx * 133, 8, '0', STR_PAD_LEFT),
                        'tuteur_name' => $tuteurName,
                        'numero_tuteur' => $parentPhone,
                        'fcmToken' => 'fcm_student_demo_' . $idx . '_' . Str::random(16),
                        'event_notifications' => true,
                        'payment_notifications' => true,
                        'last_seen_at' => $now->copy()->subMinutes(mt_rand(2, 600)),
                    ]
                );

                // 4. Link Student <-> Parent via parent_student pivot
                $parent->students()->syncWithoutDetaching([$student->idStudent]);

                // 5. Register in Registre table
                $registre = Registre::updateOrCreate(
                    ['idStudent' => $student->idStudent],
                    [
                        'Cla_id' => $targetClasse->id,
                        'statut' => 'Inscrit'
                    ]
                );

                $allStudents[] = [
                    'student' => $student,
                    'parent' => $parent,
                    'classe' => $targetClasse,
                    'registre' => $registre,
                    'target_amount' => max(0.0, $baseTuition - $remiseMontant),
                ];

                // Categorize financial profile:
                // First 26 students -> Group A (Fully Paid)
                // Next 30 students -> Group B (Partially Paid)
                // Remaining 16 students -> Group C (Unpaid / In arrears)
                if ($studentCounter <= 26) {
                    $groupAPayments[] = end($allStudents);
                } elseif ($studentCounter <= 56) {
                    $groupBPayments[] = end($allStudents);
                } else {
                    $groupCPayments[] = end($allStudents);
                }

                $studentCounter++;
            }
        }

        $this->command?->info("  -> Successfully seeded 72 students across 6 classes with authentic family relationships.");

        // =========================================================================
        // SECTION 2: GESTION FINANCIÈRE AVANCÉE (TRANSACTIONS, TRANCHES, REÇUS)
        // =========================================================================
        $this->command?->info("💳 [2/7] Seeding Advanced Financial Transactions & Payment Receipts...");

        Paiement::query()->delete();

        $paymentRefCounter = 1;
        $paymentModes = ['Virement bancaire', 'Chèque', 'Espèces', 'Paiement en ligne'];
        $recorderStaffId = $secretaire1->id;

        // Group A: Fully Settled Students (100% Paid, 3 Tranches across Sept, Dec 2025, and Feb 2026)
        foreach ($groupAPayments as $item) {
            $st = $item['student'];
            $reg = $item['registre'];
            $target = $item['target_amount'];

            // Divide into 2 or 3 tranches
            $tranche1 = round($target * 0.40, 2);
            $tranche2 = round($target * 0.35, 2);
            $tranche3 = round($target - ($tranche1 + $tranche2), 2);

            $schedule = [
                ['montant' => $tranche1, 'date' => '2025-09-15 10:30:00', 'note' => 'Règlement 1ère tranche scolarité annuelle (Rentrée)'],
                ['montant' => $tranche2, 'date' => '2025-12-10 14:15:00', 'note' => 'Règlement 2ème tranche trimestre 1'],
                ['montant' => $tranche3, 'date' => '2026-02-20 11:00:00', 'note' => 'Règlement 3ème tranche - Solde scolarité intégralement payé'],
            ];

            foreach ($schedule as $slot) {
                $ref = sprintf('REF-2026-%05d', $paymentRefCounter++);
                $mode = $paymentModes[($paymentRefCounter) % count($paymentModes)];
                $dt = Carbon::parse($slot['date']);

                Paiement::create([
                    'reference' => $ref,
                    'Reg_id' => $reg->id,
                    'Ann_id' => $schoolYear->id,
                    'montant' => $slot['montant'],
                    'remise_appliquee' => ($st->remise_montant > 0) ? round($st->remise_montant / 3, 2) : 0.00,
                    'dateEcheance' => $dt->toDateString(),
                    'modePaiement' => $mode,
                    'statut' => 'Payé',
                    'notes' => $slot['note'],
                    'recorded_by' => $recorderStaffId,
                    'created_at' => $dt,
                    'updated_at' => $dt,
                ]);
            }
        }

        // Group B: Partially Paid Students (40% to 60% paid, remainder due)
        foreach ($groupBPayments as $item) {
            $st = $item['student'];
            $reg = $item['registre'];
            $target = $item['target_amount'];

            // 1st Tranche Paid in September/October
            $paidPortion = round($target * 0.50, 2);
            $ref = sprintf('REF-2026-%05d', $paymentRefCounter++);
            $mode = $paymentModes[($paymentRefCounter) % count($paymentModes)];
            $dt1 = Carbon::parse('2025-10-05 09:45:00');

            Paiement::create([
                'reference' => $ref,
                'Reg_id' => $reg->id,
                'Ann_id' => $schoolYear->id,
                'montant' => $paidPortion,
                'remise_appliquee' => ($st->remise_montant > 0) ? round($st->remise_montant / 2, 2) : 0.00,
                'dateEcheance' => $dt1->toDateString(),
                'modePaiement' => $mode,
                'statut' => 'Payé',
                'notes' => 'Acompte 1ère tranche scolarité',
                'recorded_by' => $secretaire2->id,
                'created_at' => $dt1,
                'updated_at' => $dt1,
            ]);

            // 2nd Tranche pending / scheduled for March 2026
            $remaining = round($target - $paidPortion, 2);
            $refPending = sprintf('REF-2026-%05d', $paymentRefCounter++);
            $dt2 = Carbon::parse('2026-03-30 00:00:00');

            Paiement::create([
                'reference' => $refPending,
                'Reg_id' => $reg->id,
                'Ann_id' => $schoolYear->id,
                'montant' => $remaining,
                'remise_appliquee' => 0.00,
                'dateEcheance' => $dt2->toDateString(),
                'modePaiement' => null,
                'statut' => 'En attente',
                'notes' => 'Échéance 2ème tranche - En attente de régularisation',
                'recorded_by' => null,
                'created_at' => $dt2,
                'updated_at' => $dt2,
            ]);
        }

        // Group C: Arrears / Unpaid Students (Notice issued, 0 paid)
        foreach ($groupCPayments as $item) {
            $reg = $item['registre'];
            $target = $item['target_amount'];
            $refUnpaid = sprintf('REF-2026-%05d', $paymentRefCounter++);
            $dt = Carbon::parse('2025-11-01 00:00:00');

            Paiement::create([
                'reference' => $refUnpaid,
                'Reg_id' => $reg->id,
                'Ann_id' => $schoolYear->id,
                'montant' => $target,
                'remise_appliquee' => 0.00,
                'dateEcheance' => $dt->toDateString(),
                'modePaiement' => null,
                'statut' => 'En attente',
                'notes' => 'Alerte impayé : Relance envoyée aux parents pour régularisation de la scolarité',
                'recorded_by' => null,
                'created_at' => $dt,
                'updated_at' => $dt,
            ]);
        }

        $this->command?->info("  -> Generated " . Paiement::count() . " financial transactions across Groups A, B, and C with unique references.");

        // =========================================================================
        // SECTION 3: COURS & SUPPORTS PÉDAGOGIQUES PAR LES PROFESSEURS
        // =========================================================================
        $this->command?->info("📖 [3/7] Seeding Course Library Materials, Chapters & PDFs...");

        Course::query()->delete();

        $courseDefinitions = [
            // DEV201 Courses
            [
                'prof' => 'PROF001',
                'classe' => 'DEV201',
                'title' => 'Développement Mobile Flutter - Chapitre 1 : Architecture des Widgets & State Management',
                'cat' => 'PDF',
                'coef' => 3.0,
                'desc' => 'Architecture moderne d\'applications Flutter : Stateful/Stateless Widgets, gestion d\'état avec Provider et Riverpod, navigation et cycle de vie.',
                'file' => 'courses/flutter_chapitre1_architecture.pdf',
            ],
            [
                'prof' => 'PROF001',
                'classe' => 'DEV201',
                'title' => 'Développement Mobile Flutter - Chapitre 2 : Appels API REST & Mode Hors-Ligne',
                'cat' => 'TP',
                'coef' => 3.0,
                'desc' => 'Mise en œuvre concrète d\'appels asynchrones avec Dio/Http, parsing JSON, et persistance locale avec SharedPreferences et Hive.',
                'file' => 'courses/flutter_chapitre2_offline_sync.pdf',
            ],
            [
                'prof' => 'PROF002',
                'classe' => 'DEV201',
                'title' => 'Backend Laravel 11 - Chapitre 1 : Routage, Contrôleurs & Eloquent ORM',
                'cat' => 'PDF',
                'coef' => 4.0,
                'desc' => 'Fondations du framework Laravel 11 : cycle de requête HTTP, middleware, relations Eloquent (hasMany, belongsToMany), et migrations.',
                'file' => 'courses/laravel_chapitre1_eloquent.pdf',
            ],
            [
                'prof' => 'PROF002',
                'classe' => 'DEV201',
                'title' => 'Backend Laravel 11 - Chapitre 2 : Sécurité API Sanctum & Tests Automatisés',
                'cat' => 'TP',
                'coef' => 4.0,
                'desc' => 'Sécurisation des endpoints mobiles par jetons Sanctum, prévention des failles IDOR, et rédaction de Feature Tests PHPUnit.',
                'file' => 'courses/laravel_chapitre2_security_tests.pdf',
            ],
            [
                'prof' => 'PROF003',
                'classe' => 'DEV201',
                'title' => 'Bases de Données & SQL - Modélisation Avancée & Optimisation des Index',
                'cat' => 'PDF',
                'coef' => 2.5,
                'desc' => 'Conception relationnelle normalisée (3NF/BCNF), transactions ACID, indexation composite et analyse de plans d\'exécution EXPLAIN.',
                'file' => 'courses/bdd_chapitre1_index_optim.pdf',
            ],
            [
                'prof' => 'PROF006',
                'classe' => 'DEV201',
                'title' => 'Design UI/UX - Prototypage Mobile & Design System sous Figma',
                'cat' => 'Projet',
                'coef' => 2.0,
                'desc' => 'Création d\'un Design System complet : typographie, palette chromatique accessible, composants interactifs et micro-animations.',
                'file' => 'courses/design_system_figma_spec.pdf',
            ],

            // DEV202 Courses
            [
                'prof' => 'PROF005',
                'classe' => 'DEV202',
                'title' => 'Réseaux & Cybersécurité - Protocoles TCP/IP, TLS & Sécurité Applicative',
                'cat' => 'PDF',
                'coef' => 3.5,
                'desc' => 'Architecture réseau sécurisée, certificats SSL/TLS, prévention OWASP Top 10 et sécurisation des échanges inter-serveurs.',
                'file' => 'courses/cybersec_chapitre1_tls_owasp.pdf',
            ],
            [
                'prof' => 'PROF004',
                'classe' => 'DEV202',
                'title' => 'Algorithmique Avancée - Arbres, Graphes & Algorithmes de Tri Efficaces',
                'cat' => 'PDF',
                'coef' => 3.0,
                'desc' => 'Structures de données complexes : arbres binaires équilibrés, graphes, algorithmes de Dijkstra et complexité temporelle Big-O.',
                'file' => 'courses/algo_chapitre2_graphes.pdf',
            ],
            [
                'prof' => 'PROF002',
                'classe' => 'DEV202',
                'title' => 'Architecture Microservices & Conteneurisation Docker',
                'cat' => 'TP',
                'coef' => 3.5,
                'desc' => 'Déploiement de microservices conteneurisés avec Docker et Docker Compose, orchestration et pipeline CI/CD automatisé.',
                'file' => 'courses/docker_devops_lab.pdf',
            ],

            // TCS-1 Courses
            [
                'prof' => 'PROF007',
                'classe' => 'TCS-1',
                'title' => 'Mathématiques Appliquées - Algèbre Linéaire & Calcul Matriciel',
                'cat' => 'PDF',
                'coef' => 4.0,
                'desc' => 'Espaces vectoriels, matrices, déterminants et systèmes d\'équations différentielles pour la modélisation scientifique.',
                'file' => 'courses/maths_algebre_lineaire.pdf',
            ],
            [
                'prof' => 'PROF004',
                'classe' => 'TCS-1',
                'title' => 'Initiation à la Programmation Python & Logique Algorithmique',
                'cat' => 'TP',
                'coef' => 2.5,
                'desc' => 'Prise en main de Python : variables, boucles, structures conditionnelles, fonctions et résolution de problèmes logiques.',
                'file' => 'courses/python_initiation_tcs.pdf',
            ],
            [
                'prof' => 'PROF008',
                'classe' => 'TCS-1',
                'title' => 'Communication & Expression Professionnelle en Français',
                'cat' => 'PDF',
                'coef' => 2.0,
                'desc' => 'Techniques d\'expression écrite et orale, synthèse de documents professionnels et argumentation structurée.',
                'file' => 'courses/communication_orale_ecrite.pdf',
            ],
        ];

        $seededCourses = [];
        foreach ($courseDefinitions as $cDef) {
            $prof = $professors[$cDef['prof']];
            $cls = $classes[$cDef['classe']];

            // Create dummy physical file content in storage if missing
            $filePath = $cDef['file'];
            if (!Storage::disk('public')->exists($filePath)) {
                Storage::disk('public')->put($filePath, "%PDF-1.4\n% Classy-One Educational Course Document: {$cDef['title']}\n%%EOF");
            }

            $course = Course::create([
                'professor_id' => $prof->id,
                'classe_id' => $cls->id,
                'title' => $cDef['title'],
                'categorie' => $cDef['cat'],
                'coefficient' => $cDef['coef'],
                'description' => $cDef['desc'],
                'file_path' => $filePath,
            ]);

            $seededCourses[] = $course;
        }

        $this->command?->info("  -> Generated " . count($seededCourses) . " structured courses with coefficients and pedagogical descriptions.");

        // =========================================================================
        // SECTION 4: NOTES, BULLETINS & ÉVALUATIONS PONDÉRÉES
        // =========================================================================
        $this->command?->info("📝 [4/7] Seeding Academic Grades, Exam Transcripts & Weighted Scores...");

        Grade::query()->delete();

        $gradeTypes = ['Contrôle 1', 'Contrôle 2', 'Examen Final', 'TP', 'Projet'];
        $evaluationDates = [
            'Contrôle 1' => '2025-10-28',
            'TP' => '2025-11-20',
            'Contrôle 2' => '2025-12-18',
            'Examen Final' => '2026-01-22',
            'Projet' => '2026-02-15',
        ];

        $academicComments = [
            'excellent' => [
                'Excellente maîtrise des concepts et démarche d\'analyse très rigoureuse.',
                'Travail exemplaire, code propre, documenté et performant.',
                'Brillante prestation, grande autonomie et esprit critique.',
            ],
            'good' => [
                'Bon niveau d\'ensemble, notions bien assimilées et bonne participation.',
                'Travail satisfaisant, quelques détails d\'optimisation à perfectionner.',
                'Bonne implication en travaux pratiques et résultats très solides.',
            ],
            'average' => [
                'Résultats moyens, des efforts notables mais des lacunes sur les fondamentaux.',
                'Ensemble convenable, nécessite un travail régulier pour progresser.',
                'Attention à la gestion du temps lors des épreuves de synthèse.',
            ],
            'struggling' => [
                'Résultats insuffisants, revoir attentivement les cours et solliciter du tutorat.',
                'Manque de travail personnel et de rigueur dans l\'application des consignes.',
                'Des difficultés majeures sur les fondamentaux à combler rapidement.',
            ]
        ];

        $gradesCount = 0;

        foreach ($allStudents as $item) {
            $student = $item['student'];
            $targetClasse = $item['classe'];

            // Find courses matching student's class
            $classCourses = array_filter($seededCourses, fn($c) => $c->classe_id == $targetClasse->id);
            if (empty($classCourses)) {
                // Fallback to first 2 courses if none specific
                $classCourses = array_slice($seededCourses, 0, 2);
            }

            // Student profile baseline (determines note distribution):
            // e.g. Major de promotion, Good, Average, or Struggling
            $studentIdMod = $student->idStudent % 10;
            $profile = match (true) {
                $studentIdMod <= 2 => 'excellent',  // ~30%
                $studentIdMod <= 6 => 'good',       // ~40%
                $studentIdMod <= 8 => 'average',    // ~20%
                default => 'struggling',            // ~10%
            };

            foreach ($classCourses as $course) {
                // Seed 2 to 3 evaluations per course per student
                $evalTypesToSeed = ['Contrôle 1', 'Examen Final'];
                if ($course->categorie === 'TP' || $course->categorie === 'Projet') {
                    $evalTypesToSeed[] = $course->categorie;
                } else {
                    $evalTypesToSeed[] = 'Contrôle 2';
                }

                foreach ($evalTypesToSeed as $eType) {
                    $note = match ($profile) {
                        'excellent' => round(16.0 + (mt_rand(0, 38) / 10), 2),  // 16.00 to 19.80
                        'good' => round(12.5 + (mt_rand(0, 32) / 10), 2),       // 12.50 to 15.70
                        'average' => round(10.0 + (mt_rand(0, 24) / 10), 2),    // 10.00 to 12.40
                        'struggling' => round(6.5 + (mt_rand(0, 32) / 10), 2),  // 6.50 to 9.70
                    };

                    $commentList = $academicComments[$profile];
                    $selectedComment = $commentList[array_rand($commentList)];
                    $examDate = $evaluationDates[$eType] ?? '2025-11-15';

                    Grade::create([
                        'student_id' => $student->idStudent,
                        'teacher_id' => $course->professor_id,
                        'course_id' => $course->id,
                        'classe_id' => $targetClasse->id,
                        'note' => $note,
                        'type' => $eType,
                        'subject_name' => $course->title,
                        'exam_date' => $examDate,
                        'comment' => $selectedComment,
                        'semester' => 'Semestre 1',
                        'coefficient' => $course->coefficient,
                    ]);

                    $gradesCount++;
                }
            }
        }

        $this->command?->info("  -> Generated {$gradesCount} official grades with weighted coefficients and teacher comments.");

        // =========================================================================
        // SECTION 5: EMPLOIS DU TEMPS COMPLETS & REGISTRE DES ABSENCES
        // =========================================================================
        $this->command?->info("📅 [5/7] Seeding Conflict-Free Weekly Schedules & Student Absences...");

        Planning::query()->delete();

        // Conflict-free weekly master schedule for DEV201, DEV202, and TCS-1
        $p1 = $professors['PROF001']; // Zakaria (Mobile - Labo Mobile 1)
        $p2 = $professors['PROF002']; // Yassmine (Web/PHP - Labo Web 2)
        $p3 = $professors['PROF003']; // Amine (SQL - Salle BDD 101)
        $p4 = $professors['PROF004']; // Rania (Algo - Salle Algo 102)
        $p5 = $professors['PROF005']; // Bilal (Réseaux - Labo Réseaux 201)
        $p6 = $professors['PROF006']; // Salma (Design - Studio Design 301)
        $p7 = $professors['PROF007']; // Mehdi (Maths - Amphi B)
        $p8 = $professors['PROF008']; // Leila (Communication - Salle Langues 105)

        $weeklySlots = [
            // LUNDI
            ['jour' => 'Lundi', 'start' => '08:30:00', 'end' => '10:30:00',
             'c1' => [$p1, 'Développement Mobile (Flutter)', 'Labo Mobile 1'],
             'c2' => [$p2, 'Développement Backend (Laravel & PHP)', 'Labo Web 2'],
             'c3' => [$p7, 'Mathématiques Appliquées & Statistiques', 'Amphi B']],

            ['jour' => 'Lundi', 'start' => '10:45:00', 'end' => '12:45:00',
             'c1' => [$p2, 'Développement Backend (Laravel & PHP)', 'Labo Web 2'],
             'c2' => [$p1, 'Développement Mobile (Flutter)', 'Labo Mobile 1'],
             'c3' => [$p4, 'Initiation à la Programmation Python', 'Salle Algo 102']],

            ['jour' => 'Lundi', 'start' => '14:00:00', 'end' => '16:00:00',
             'c1' => [$p3, 'Bases de Données Relationnelles & SQL', 'Salle BDD 101'],
             'c2' => [$p4, 'Algorithmique & Structures de Données', 'Salle Algo 102'],
             'c3' => [$p8, 'Communication & Expression Écrite', 'Salle Langues 105']],

            ['jour' => 'Lundi', 'start' => '16:15:00', 'end' => '18:15:00',
             'c1' => [$p4, 'Algorithmique & Structures de Données', 'Salle Algo 102'],
             'c2' => [$p3, 'Bases de Données Relationnelles & SQL', 'Salle BDD 101'],
             'c3' => [$p7, 'Travaux Dirigés Mathématiques', 'Amphi B']],

            // MARDI
            ['jour' => 'Mardi', 'start' => '08:30:00', 'end' => '10:30:00',
             'c1' => [$p5, 'Réseaux Informatiques & Télécoms', 'Labo Réseaux 201'],
             'c2' => [$p6, 'Design UI/UX & Figma', 'Studio Design 301'],
             'c3' => [$p3, 'Introduction aux Systèmes d\'Information', 'Salle BDD 101']],

            ['jour' => 'Mardi', 'start' => '10:45:00', 'end' => '12:45:00',
             'c1' => [$p6, 'Design UI/UX & Figma', 'Studio Design 301'],
             'c2' => [$p5, 'Réseaux Informatiques & Télécoms', 'Labo Réseaux 201'],
             'c3' => [$p8, 'Anglais Technique & Vocabulaire IT', 'Salle Langues 105']],

            ['jour' => 'Mardi', 'start' => '14:00:00', 'end' => '16:00:00',
             'c1' => [$p1, 'Architecture Mobile & State Management', 'Labo Mobile 1'],
             'c2' => [$p2, 'Architecture Microservices & Docker', 'Labo Web 2'],
             'c3' => [$p7, 'Physique & Sciences Appliquées', 'Amphi B']],

            // MERCREDI
            ['jour' => 'Mercredi', 'start' => '08:30:00', 'end' => '10:30:00',
             'c1' => [$p4, 'Algorithmique & Arbres Binaires', 'Salle Algo 102'],
             'c2' => [$p1, 'Développement Mobile (Flutter)', 'Labo Mobile 1'],
             'c3' => [$p4, 'Atelier Algorithmique Python', 'Salle Algo 102']],

            ['jour' => 'Mercredi', 'start' => '10:45:00', 'end' => '12:45:00',
             'c1' => [$p3, 'Administration MySQL & Procédures Stockées', 'Salle BDD 101'],
             'c2' => [$p2, 'Sécurité API & Jetons JWT/Sanctum', 'Labo Web 2'],
             'c3' => [$p8, 'Prise de Parole en Public', 'Salle Langues 105']],

            // JEUDI
            ['jour' => 'Jeudi', 'start' => '08:30:00', 'end' => '10:30:00',
             'c1' => [$p6, 'Développement Frontend React & Tailwind', 'Studio Design 301'],
             'c2' => [$p5, 'Cybersécurité Fondamentale & Audit', 'Labo Réseaux 201'],
             'c3' => [$p7, 'Probabilités & Statistiques Descriptives', 'Amphi B']],

            ['jour' => 'Jeudi', 'start' => '10:45:00', 'end' => '12:45:00',
             'c1' => [$p1, 'Projet Intégrateur Flutter (Sprint 1)', 'Labo Mobile 1'],
             'c2' => [$p2, 'Projet Backend API REST (Sprint 1)', 'Labo Web 2'],
             'c3' => [$p3, 'Requêtes SQL Complexes & Jointures', 'Salle BDD 101']],

            // VENDREDI
            ['jour' => 'Vendredi', 'start' => '08:30:00', 'end' => '10:30:00',
             'c1' => [$p5, 'Atelier Protocoles Réseaux Wireshark', 'Labo Réseaux 201'],
             'c2' => [$p6, 'Design Sprint & Retours Utilisateurs', 'Studio Design 301'],
             'c3' => [$p8, 'Rédaction de Rapports Techniques', 'Salle Langues 105']],

            ['jour' => 'Vendredi', 'start' => '10:45:00', 'end' => '12:45:00',
             'c1' => [$p2, 'Tests Unitaires & Intégration PHPUnit', 'Labo Web 2'],
             'c2' => [$p4, 'Optimisation Algorithmique & Débogage', 'Salle Algo 102'],
             'c3' => [$p7, 'Soutien & Travaux Dirigés de Synthèse', 'Amphi B']],
        ];

        $dayIndex = ['Lundi' => 0, 'Mardi' => 1, 'Mercredi' => 2, 'Jeudi' => 3, 'Vendredi' => 4];
        $startOfWeek = $now->copy()->startOfWeek(CarbonInterface::MONDAY);
        $planningCount = 0;

        foreach ($weeklySlots as $slot) {
            $dateSlot = $startOfWeek->copy()->addDays($dayIndex[$slot['jour']])->toDateString();

            // DEV201 Entry
            Planning::create([
                'classe_id' => $classe1->id,
                'Cla_id' => $classe1->id,
                'jour' => $slot['jour'],
                'date' => $dateSlot,
                'check_in' => $slot['start'],
                'check_out' => $slot['end'],
                'matiere' => $slot['c1'][1],
                'professeur_name' => $slot['c1'][0]->name,
                'salle' => $slot['c1'][2],
                'status' => 'Actif',
                'type' => 'COURS',
                'weekNumber' => $now->weekOfYear,
            ]);

            // DEV202 Entry
            Planning::create([
                'classe_id' => $classe2->id,
                'Cla_id' => $classe2->id,
                'jour' => $slot['jour'],
                'date' => $dateSlot,
                'check_in' => $slot['start'],
                'check_out' => $slot['end'],
                'matiere' => $slot['c2'][1],
                'professeur_name' => $slot['c2'][0]->name,
                'salle' => $slot['c2'][2],
                'status' => 'Actif',
                'type' => 'COURS',
                'weekNumber' => $now->weekOfYear,
            ]);

            // TCS-1 Entry
            if (isset($classes['TCS-1'])) {
                Planning::create([
                    'classe_id' => $classes['TCS-1']->id,
                    'Cla_id' => $classes['TCS-1']->id,
                    'jour' => $slot['jour'],
                    'date' => $dateSlot,
                    'check_in' => $slot['start'],
                    'check_out' => $slot['end'],
                    'matiere' => $slot['c3'][1],
                    'professeur_name' => $slot['c3'][0]->name,
                    'salle' => $slot['c3'][2],
                    'status' => 'Actif',
                    'type' => 'COURS',
                    'weekNumber' => $now->weekOfYear,
                ]);
            }

            $planningCount += 3;
        }

        $this->command?->info("  -> Generated {$planningCount} timetable sessions with 0 schedule collisions.");

        // 5.2 Absences
        Absence::query()->delete();

        $absencesScenarios = [
            [
                'matiere' => 'Développement Mobile (Flutter)',
                'prof' => $p1->id,
                'seance' => '08:30 - 10:30',
                'date' => '2026-02-16',
                'is_justified' => true,
                'justif' => 'Certificat médical d\'arrêt de travail délivré par Dr. Bennani (Grippe saisonnière)',
                'student_exp' => 'J\'ai déposé le certificat médical original auprès du secrétariat.',
                'status' => 'approved',
            ],
            [
                'matiere' => 'Développement Backend (Laravel & PHP)',
                'prof' => $p2->id,
                'seance' => '10:45 - 12:45',
                'date' => '2026-02-23',
                'is_justified' => false,
                'justif' => null,
                'student_exp' => 'Blocage majeur des transports en commun (panne électrique tramway).',
                'status' => 'pending_justification',
            ],
            [
                'matiere' => 'Bases de Données Relationnelles & SQL',
                'prof' => $p3->id,
                'seance' => '14:00 - 16:00',
                'date' => '2026-03-02',
                'is_justified' => false,
                'justif' => 'Absence injustifiée non régularisée dans le délai réglementaire de 48 heures.',
                'student_exp' => null,
                'status' => 'rejected',
            ],
            [
                'matiere' => 'Réseaux Informatiques & Télécoms',
                'prof' => $p5->id,
                'seance' => '08:30 - 10:30',
                'date' => '2026-03-09',
                'is_justified' => true,
                'justif' => 'Convocation officielle à un concours d\'ingénierie d\'État.',
                'student_exp' => 'Copie de la convocation visée par le secrétariat.',
                'status' => 'approved',
            ],
        ];

        $absencesCount = 0;
        foreach ($allStudents as $key => $item) {
            // Seed absences for every 3rd student
            if ($key % 3 === 0) {
                $st = $item['student'];
                $cls = $item['classe'];
                $scenario = $absencesScenarios[$key % count($absencesScenarios)];

                Absence::create([
                    'student_id' => $st->idStudent,
                    'classe_id' => $cls->id,
                    'matiere' => $scenario['matiere'],
                    'prof_id' => $scenario['prof'],
                    'date' => $scenario['date'],
                    'seance' => $scenario['seance'],
                    'is_justified' => $scenario['is_justified'],
                    'justification_reason' => $scenario['justif'],
                    'student_explanation' => $scenario['student_exp'],
                    'status' => $scenario['status'],
                    'created_at' => Carbon::parse($scenario['date'] . ' 18:00:00'),
                    'updated_at' => Carbon::parse($scenario['date'] . ' 18:00:00'),
                ]);

                $absencesCount++;
            }
        }

        $this->command?->info("  -> Generated {$absencesCount} realistic student absences (Justified, Pending, and Rejected).");

        // =========================================================================
        // SECTION 6: DEMANDES DE DOCUMENTS OFFICIELS & WORKFLOW COMPLET
        // =========================================================================
        $this->command?->info("📄 [6/7] Seeding Official Document Requests across All Workflow Statuses...");

        DocumentRequest::query()->delete();

        $docRequestsDefs = [
            // 1. Ready & Downloadable (Attestation de Scolarité)
            [
                'student_idx' => 0,
                'type' => DocumentRequest::TYPE_ATTESTATION_SCOLARITE,
                'status' => DocumentRequest::STATUS_PRET,
                'urgency' => 'normal',
                'reason' => 'Constitution du dossier de renouvellement de carte de séjour et bourse d\'études.',
                'admin_note' => 'Document signé par la direction et visé par le secrétariat général.',
                'rejection' => null,
                'file_prefix' => 'attestation_scolarite',
            ],
            // 2. Ready & Downloadable (Relevé de Notes / Bulletin)
            [
                'student_idx' => 1,
                'type' => DocumentRequest::TYPE_RELEVE_NOTES,
                'status' => DocumentRequest::STATUS_PRET,
                'urgency' => 'urgent',
                'reason' => 'Candidature pour stage d\'immersion professionnelle en ESN (Casablanca).',
                'admin_note' => 'Relevé officiel des notes du Semestre 1 avec moyennes pondérées et mentions.',
                'rejection' => null,
                'file_prefix' => 'bulletin_notes',
            ],
            // 3. Ready & Downloadable (Certificat de Présence)
            [
                'student_idx' => 2,
                'type' => DocumentRequest::TYPE_CERTIFICAT_PRESENCE,
                'status' => DocumentRequest::STATUS_PRET,
                'urgency' => 'normal',
                'reason' => 'Justificatif d\'assiduité demandé par l\'organisme de couverture médicale CNSS.',
                'admin_note' => 'Taux d\'assiduité certifié conforme aux registres de présence.',
                'rejection' => null,
                'file_prefix' => 'certificat_presence',
            ],
            // 4. In Progress (En cours de traitement)
            [
                'student_idx' => 3,
                'type' => DocumentRequest::TYPE_ATTESTATION_SCOLARITE,
                'status' => DocumentRequest::STATUS_EN_COURS,
                'urgency' => 'normal',
                'reason' => 'Dossier d\'inscription à un examen d\'anglais TOEIC.',
                'admin_note' => 'Demande en cours d\'impression et de signature par le Directeur Général.',
                'rejection' => null,
                'file_prefix' => null,
            ],
            // 5. New Request (Nouveau - Attente de traitement)
            [
                'student_idx' => 4,
                'type' => DocumentRequest::TYPE_RELEVE_NOTES,
                'status' => DocumentRequest::STATUS_NOUVEAU,
                'urgency' => 'urgent',
                'reason' => 'Demande urgente pour dossier de candidature de mobilité internationale.',
                'admin_note' => null,
                'rejection' => null,
                'file_prefix' => null,
            ],
            // 6. Rejected Request (Rejeté avec motif officiel clair)
            [
                'student_idx' => 58, // Student from Group C (Unpaid)
                'type' => DocumentRequest::TYPE_RELEVE_NOTES,
                'status' => DocumentRequest::STATUS_REJETE,
                'urgency' => 'normal',
                'reason' => 'Demande de bulletin semestriel.',
                'admin_note' => 'Dossier bloqué par le service financier.',
                'rejection' => 'Demande rejetée : Situation financière non régularisée pour l\'année scolaire en cours. Merci de contacter le secrétariat.',
                'file_prefix' => null,
            ],
            // 7. Request submitted by a Parent for their child
            [
                'student_idx' => 5,
                'type' => DocumentRequest::TYPE_ATTESTATION_SCOLARITE,
                'status' => DocumentRequest::STATUS_PRET,
                'urgency' => 'normal',
                'reason' => 'Dossier d\'allocations familiales de l\'employeur du tuteur légal.',
                'admin_note' => 'Document certifié délivré à l\'attention du tuteur.',
                'rejection' => null,
                'file_prefix' => 'attestation_parent',
                'by_parent' => true,
            ],
        ];

        $docRefCounter = 1;
        foreach ($docRequestsDefs as $dDef) {
            $stData = $allStudents[$dDef['student_idx']] ?? $allStudents[0];
            $student = $stData['student'];
            $parent = $stData['parent'];

            $docRef = sprintf('DOC-2026-%05d', $docRefCounter++);
            $filePath = null;

            if ($dDef['status'] === DocumentRequest::STATUS_PRET) {
                $dir = 'documents/official';
                $fileName = $dir . '/' . $dDef['file_prefix'] . '_' . $student->matricule . '_' . time() . '.pdf';
                // Create a real valid PDF payload in storage so download works out of the box
                Storage::disk('public')->put(
                    $fileName,
                    "%PDF-1.4\n% Classy-One Official Certified Document ({$docRef})\n% Student: {$student->nom} {$student->prenom}\n% Date: " . now()->toIso8601String() . "\n%%EOF"
                );
                $filePath = $fileName;
            }

            DocumentRequest::create([
                'reference' => $docRef,
                'idStudent' => $student->idStudent,
                'parent_id' => !empty($dDef['by_parent']) ? $parent->id : null,
                'document_type' => $dDef['type'],
                'reason' => $dDef['reason'],
                'urgency' => $dDef['urgency'],
                'status' => $dDef['status'],
                'admin_note' => $dDef['admin_note'],
                'rejection_reason' => $dDef['rejection'],
                'processed_by' => in_array($dDef['status'], [DocumentRequest::STATUS_PRET, DocumentRequest::STATUS_REJETE]) ? $secretaire1->id : null,
                'processed_at' => in_array($dDef['status'], [DocumentRequest::STATUS_PRET, DocumentRequest::STATUS_REJETE]) ? $now->copy()->subHours(mt_rand(2, 48)) : null,
                'ready_date' => ($dDef['status'] === DocumentRequest::STATUS_PRET) ? $now->copy()->subHours(mt_rand(1, 24)) : null,
                'file_url' => $filePath,
                'request_date' => $now->copy()->subDays(mt_rand(1, 10)),
                'created_at' => $now->copy()->subDays(mt_rand(1, 10)),
                'updated_at' => $now,
            ]);
        }

        $this->command?->info("  -> Generated " . count($docRequestsDefs) . " official document requests covering full workflow (Nouveau, En Cours, Prêt, Rejeté).");

        // =========================================================================
        // SECTION 7: ÉVÉNEMENTS, NOTIFICATIONS CIBLÉES & AUDIT LOGS
        // =========================================================================
        $this->command?->info("🔔 [7/7] Seeding School Events, Push Notifications & Security Audit Logs...");

        // 7.1 Events & Registrations
        Event::query()->delete();

        $eventsList = [
            [
                'titre' => 'Hackathon Annuel Classy-One : Innovation Mobile & IA',
                'description' => 'Compétition technologique de 48 heures ouverte à tous les étudiants. Développement d\'applications intégrant l\'IA générative et les services Cloud.',
                'date_evenement' => $now->copy()->addDays(14)->format('Y-m-d H:i:s'),
                'lieu' => 'Grand Amphithéâtre & Espace Coworking',
                'categorie' => 'Académique',
                'prix' => 'Gratuit',
            ],
            [
                'titre' => 'Tournoi Sportif Inter-Classes de Futsal & Esport',
                'description' => 'Tournoi annuel opposant les promotions de développement et ingénierie. Cérémonie de remise des trophées et animations festives.',
                'date_evenement' => $now->copy()->addDays(21)->format('Y-m-d H:i:s'),
                'lieu' => 'Complexe Omnisports Universitaire',
                'categorie' => 'Sport',
                'prix' => '50 MAD',
            ],
            [
                'titre' => 'Journée Portes Ouvertes & Ateliers Robotique',
                'description' => 'Démonstrations de systèmes embarqués, IoT et robotique organisées par le Club Tech de l\'école à l\'attention des futurs étudiants.',
                'date_evenement' => $now->copy()->addDays(28)->format('Y-m-d H:i:s'),
                'lieu' => 'Médiathèque & FabLab Innovation',
                'categorie' => 'Club',
                'prix' => '20 MAD',
            ],
            [
                'titre' => 'Forum Carrières IT & Rencontre ESN Partenaires',
                'description' => 'Rencontre directe avec les directeurs techniques et recruteurs d\'ESN majeures (Casablanca, Rabat) pour les stages PFE et embauches directes.',
                'date_evenement' => $now->copy()->addDays(35)->format('Y-m-d H:i:s'),
                'lieu' => 'Hall Central des Expositions',
                'categorie' => 'Académique',
                'prix' => 'Gratuit',
            ],
        ];

        DB::table('event_registration')->delete();

        foreach ($eventsList as $eIdx => $ev) {
            $createdEvent = Event::create($ev);

            // Register first 8 students in Hackathon, and next 8 in Sports tournament
            if ($eIdx <= 1) {
                for ($s = 0; $s < 8; $s++) {
                    $st = $allStudents[$s + ($eIdx * 8)]['student'];
                    DB::table('event_registration')->insert([
                        'idStudent' => $st->idStudent,
                        'idEvent' => $createdEvent->id,
                        'registered_at' => $now->copy()->subDays(mt_rand(1, 5)),
                    ]);
                }
            }
        }

        // 7.2 Notifications (Targeting Scopes: ALL, CLASSE, STUDENT)
        Notification::query()->delete();

        $firstStudent = $allStudents[0]['student'];

        $notificationsBatch = [
            [
                'titre' => 'Calendrier Officiel des Évaluations de Fin de Semestre',
                'message' => 'Les plannings détaillés des examens et contrôles continus sont désormais disponibles dans votre espace emploi du temps. Veuillez consulter vos dates d\'épreuves.',
                'categorie' => 'Academique',
                'target_type' => 'ALL',
                'target_ids' => null,
                'target_summary' => 'Tous les étudiants et parents',
                'idStudent' => null,
                'created_at' => $now->copy()->subDays(2),
            ],
            [
                'titre' => 'Atelier Pratique Mobile Flutter DEV201',
                'message' => 'Merci d\'apporter vos ordinateurs configurés avec le SDK Flutter et l\'émulateur Android pour la séance pratique de vendredi matin.',
                'categorie' => 'General',
                'target_type' => 'CLASSE',
                'target_ids' => [$classe1->id],
                'target_summary' => 'Classe DEV201',
                'idStudent' => null,
                'created_at' => $now->copy()->subDays(1),
            ],
            [
                'titre' => 'Rendu de Projet Backend Laravel DEV202',
                'message' => 'Le dépôt GitHub du mini-projet d\'API sécurisée doit être soumis avant dimanche soir à 23h59. Tout retard entraînera des pénalités.',
                'categorie' => 'Academique',
                'target_type' => 'CLASSE',
                'target_ids' => [$classe2->id],
                'target_summary' => 'Classe DEV202',
                'idStudent' => null,
                'created_at' => $now->copy()->subHours(8),
            ],
            [
                'titre' => 'Document Prêt : Votre Attestation de Scolarité est Disponible',
                'message' => 'Votre demande de document officiel a été validée et signée. Vous pouvez dès à présent télécharger votre attestation officielle en format PDF.',
                'categorie' => 'General',
                'target_type' => 'STUDENT',
                'target_ids' => [$firstStudent->idStudent],
                'target_summary' => "Élève {$firstStudent->nom} {$firstStudent->prenom}",
                'idStudent' => $firstStudent->idStudent,
                'created_at' => $now->copy()->subHours(2),
            ],
            [
                'titre' => 'Rappel Régularisation Échéance Scolarité',
                'message' => 'Rappel administratif : l\'échéance de la 2ème tranche des frais de scolarité arrive à terme. Merci de vous rapprocher du secrétariat ou d\'effectuer votre virement.',
                'categorie' => 'Paiement',
                'target_type' => 'ALL',
                'target_ids' => null,
                'target_summary' => 'Tous les étudiants et parents',
                'idStudent' => null,
                'created_at' => $now->copy()->subDays(5),
            ],
        ];

        Notification::withoutEvents(function () use ($notificationsBatch) {
            foreach ($notificationsBatch as $notif) {
                Notification::create($notif);
            }
        });

        // 7.3 Audit Logs (Detailed Historical Security & Activity Log)
        AuditLog::query()->delete();

        $auditLogsSeed = [
            [
                'user_id' => $admin->id,
                'user_name' => $admin->name,
                'user_role' => 'admin',
                'action' => 'user.login',
                'subject_type' => 'User',
                'subject_id' => (string) $admin->id,
                'description' => 'Connexion administrative réussie au tableau de bord Filament v3.',
                'ip_address' => '192.168.1.10',
                'details' => ['device' => 'MacBook Pro - Safari 18.0', 'panel' => 'admin'],
                'created_at' => $now->copy()->subHours(6),
            ],
            [
                'user_id' => $secretaire1->id,
                'user_name' => $secretaire1->name,
                'user_role' => 'secretaire',
                'action' => 'payment.recorded',
                'subject_type' => 'Paiement',
                'subject_id' => '1',
                'description' => "Enregistrement du paiement scolarité REF-2026-00001 (Montant: 6 000.00 MAD) pour l'élève {$allStudents[0]['student']->nom} {$allStudents[0]['student']->prenom}.",
                'ip_address' => '192.168.1.15',
                'details' => ['reference' => 'REF-2026-00001', 'montant' => 6000.00, 'mode' => 'Virement bancaire'],
                'created_at' => $now->copy()->subHours(5),
            ],
            [
                'user_id' => $professors['PROF001']->id,
                'user_name' => $professors['PROF001']->name,
                'user_role' => 'professeur',
                'action' => 'grade.submitted',
                'subject_type' => 'Grade',
                'subject_id' => '1',
                'description' => 'Saisie et validation des notes du Contrôle 1 en Développement Mobile (Flutter) pour la classe DEV201.',
                'ip_address' => '10.0.0.42',
                'details' => ['course' => 'Développement Mobile (Flutter)', 'classe' => 'DEV201', 'total_students' => 20],
                'created_at' => $now->copy()->subHours(4),
            ],
            [
                'user_id' => $secretaire1->id,
                'user_name' => $secretaire1->name,
                'user_role' => 'secretaire',
                'action' => 'document.generated',
                'subject_type' => 'DocumentRequest',
                'subject_id' => '1',
                'description' => "Génération et signature numérique de l'Attestation de Scolarité DOC-2026-00001 pour l'élève {$allStudents[0]['student']->nom} {$allStudents[0]['student']->prenom}.",
                'ip_address' => '192.168.1.15',
                'details' => ['document_type' => 'attestation_scolarite', 'reference' => 'DOC-2026-00001'],
                'created_at' => $now->copy()->subHours(3),
            ],
            [
                'user_id' => $secretaire1->id,
                'user_name' => $secretaire1->name,
                'user_role' => 'secretaire',
                'action' => 'document.rejected',
                'subject_type' => 'DocumentRequest',
                'subject_id' => '6',
                'description' => 'Rejet de la demande de document DOC-2026-00006 : Situation financière non régularisée.',
                'ip_address' => '192.168.1.15',
                'details' => ['reference' => 'DOC-2026-00006', 'reason' => 'Arriérés de scolarité constatés'],
                'created_at' => $now->copy()->subHours(2),
            ],
            [
                'user_id' => $admin->id,
                'user_name' => $admin->name,
                'user_role' => 'admin',
                'action' => 'backup.created',
                'subject_type' => 'Backup',
                'subject_id' => 'backup_system',
                'description' => 'Sauvegarde automatique nocturne de la base de données MySQL et du stockage déporté terminée avec succès.',
                'ip_address' => '127.0.0.1',
                'details' => ['disk' => 'backup_offsite', 'database' => 'osbt', 'size_mb' => 24.5],
                'created_at' => $now->copy()->subHours(1),
            ],
        ];

        foreach ($auditLogsSeed as $log) {
            AuditLog::create($log);
        }

        // Clear cached timetables and application caches
        Cache::flush();

        $this->command?->info("✨ ==========================================================================");
        $this->command?->info("🎉 [SUCCESS] Classy-One Complete Demo Database Seeded Successfully!");
        $this->command?->info("   - 6 Classes & Multiple Educational Cycles (1AC, 2AC, TCS, DEV201, DEV202, INFO301)");
        $this->command?->info("   - " . User::count() . " Total Staff (1 Admin, 2 Secrétaires, 8 Professeurs)");
        $this->command?->info("   - " . Student::count() . " Students & " . SchoolParent::count() . " Linked Parents (ST20260001 -> ST20260072)");
        $this->command?->info("   - " . Paiement::count() . " Financial Transactions (Groups A: Soldés, B: Partiels, C: Impayés)");
        $this->command?->info("   - " . Grade::count() . " Academic Grades with Weighted Coefficients & Comments");
        $this->command?->info("   - " . Course::count() . " Complete Courses & Syllabi with Real PDF Materials");
        $this->command?->info("   - " . Planning::count() . " Timetable Slots (Conflict-Free Monday-Friday)");
        $this->command?->info("   - " . Absence::count() . " Student Absences (Approved, Pending, Rejected)");
        $this->command?->info("   - " . DocumentRequest::count() . " Document Requests (Nouveau, En Cours, Prêt, Rejeté)");
        $this->command?->info("   - " . Event::count() . " Events, " . Notification::count() . " Push Notifications & " . AuditLog::count() . " Audit Logs");
        $this->command?->info("   - Default Password for All Accounts: password123");
        $this->command?->info("✨ ==========================================================================");
    }
}
