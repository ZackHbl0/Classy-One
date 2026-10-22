<?php
require __DIR__.'/../vendor/autoload.php';
$app = require_once __DIR__.'/../bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\Classe;
use App\Models\User;
use App\Models\Student;
use App\Models\SchoolParent;
use App\Models\Paiement;
use App\Models\Grade;
use App\Models\Course;
use App\Models\Planning;
use App\Models\Absence;
use App\Models\DocumentRequest;
use App\Models\Event;
use App\Models\Notification;
use App\Models\AuditLog;

echo "=== CLASSY-ONE DATABASE AUDIT SUMMARY ===\n";
echo "Classes: " . Classe::count() . " (" . Classe::pluck('nomClasse')->implode(', ') . ")\n";
echo "Users (Staff): " . User::count() . " (Admins: " . User::where('role', 'admin')->count() . ", Secrétaires: " . User::where('role', 'secretaire')->count() . ", Professeurs: " . User::where('role', 'professeur')->count() . ")\n";
echo "Students: " . Student::count() . "\n";
echo "Parents: " . SchoolParent::count() . "\n";
echo "Parent-Student Links: " . DB::table('parent_student')->count() . "\n";
echo "Paiements: " . Paiement::count() . " (Payés: " . Paiement::where('statut', 'Payé')->count() . ", En attente: " . Paiement::where('statut', 'En attente')->count() . ")\n";
echo "Total Encaissement: " . number_format(Paiement::where('statut', 'Payé')->sum('montant'), 2) . " MAD\n";
echo "Courses: " . Course::count() . "\n";
echo "Grades: " . Grade::count() . " (Moyenne générale: " . round(Grade::avg('note'), 2) . "/20)\n";
echo "Planning (Créneaux): " . Planning::count() . "\n";
echo "Absences: " . Absence::count() . " (Justifiées: " . Absence::where('is_justified', 1)->count() . ", Non justifiées: " . Absence::where('is_justified', 0)->count() . ")\n";
echo "Document Requests: " . DocumentRequest::count() . " (Prêts: " . DocumentRequest::where('status', 'pret')->count() . ", En cours: " . DocumentRequest::where('status', 'en_cours')->count() . ", Nouveaux: " . DocumentRequest::where('status', 'nouveau')->count() . ", Rejetés: " . DocumentRequest::where('status', 'rejete')->count() . ")\n";
echo "Events: " . Event::count() . "\n";
echo "Event Registrations: " . DB::table('event_registration')->count() . "\n";
echo "Notifications: " . Notification::count() . "\n";
echo "Audit Logs: " . AuditLog::count() . "\n";

// Sample Student Financial Profiles
$sampleA = Student::where('matricule', 'ST20260001')->first();
$sampleB = Student::where('matricule', 'ST20260030')->first();
$sampleC = Student::where('matricule', 'ST20260060')->first();

echo "\n--- Profil Financier Groupe A (Soldé 100%) : {$sampleA->matricule} ({$sampleA->nom} {$sampleA->prenom}) ---\n";
echo " - Frais scolarité : " . number_format($sampleA->frais_scolarite, 2) . " MAD\n";
echo " - Remise appliquée : {$sampleA->remise_label}\n";
echo " - Montant cible dû : " . number_format($sampleA->frais_scolarite_total, 2) . " MAD\n";
echo " - Total encaissé : " . number_format($sampleA->total_paye, 2) . " MAD\n";
echo " - Reste à payer : " . number_format($sampleA->reste_a_payer, 2) . " MAD\n";
echo " - Statut / Badge : {$sampleA->statut_financier['badge']}\n";

echo "\n--- Profil Financier Groupe B (Paiement Partiel) : {$sampleB->matricule} ({$sampleB->nom} {$sampleB->prenom}) ---\n";
echo " - Frais scolarité : " . number_format($sampleB->frais_scolarite, 2) . " MAD\n";
echo " - Remise appliquée : {$sampleB->remise_label}\n";
echo " - Montant cible dû : " . number_format($sampleB->frais_scolarite_total, 2) . " MAD\n";
echo " - Total encaissé : " . number_format($sampleB->total_paye, 2) . " MAD\n";
echo " - Reste à payer : " . number_format($sampleB->reste_a_payer, 2) . " MAD\n";
echo " - Statut / Badge : {$sampleB->statut_financier['badge']}\n";

echo "\n--- Profil Financier Groupe C (Impayé / Créance) : {$sampleC->matricule} ({$sampleC->nom} {$sampleC->prenom}) ---\n";
echo " - Frais scolarité : " . number_format($sampleC->frais_scolarite, 2) . " MAD\n";
echo " - Remise appliquée : {$sampleC->remise_label}\n";
echo " - Montant cible dû : " . number_format($sampleC->frais_scolarite_total, 2) . " MAD\n";
echo " - Total encaissé : " . number_format($sampleC->total_paye, 2) . " MAD\n";
echo " - Reste à payer : " . number_format($sampleC->reste_a_payer, 2) . " MAD\n";
echo " - Statut / Badge : {$sampleC->statut_financier['badge']}\n";




