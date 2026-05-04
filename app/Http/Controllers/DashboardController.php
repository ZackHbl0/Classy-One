<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Planning;
use App\Models\Notification;
use App\Models\Event;
use App\Models\Paiement;
use App\Models\DocumentRequest;
use Carbon\Carbon;

class DashboardController extends Controller
{
    public function index(Request $request)
    {
        $student = $request->user();

        // Stats: count planning entries for current month
        $curMonth = Carbon::now()->month;
        $curYear = Carbon::now()->year;

        $plannings = Planning::where('idStudent', $student->idStudent)
            ->whereMonth('date', $curMonth)
            ->whereYear('date', $curYear)
            ->get();

        $absences = $plannings->where('status', 'Absents')->count();
        $retards = $plannings->where('status', 'Late in')->count();
        $conges = $plannings->where('status', 'Leaves')->count();
        $total = $plannings->count();

        // Find the class ID for the student for filtering
        $registre = \App\Models\Registre::where('idStudent', $student->idStudent)->first();
        $classe_id = $registre ? (string) $registre->Cla_id : null;

        // Urgent notifications (unread + filtered)
        $notifications = Notification::whereDoesntHave('reads', function ($query) use ($student) {
            $query->where('idStudent', $student->idStudent);
        })
            ->where(function ($query) use ($student, $classe_id) {
                // 1. All Students
                $query->where('target_type', 'all')
                    // 2. Specific Student
                    ->orWhere(function ($q) use ($student) {
                    $q->where('target_type', 'students')
                        ->where('idStudent', $student->idStudent);
                });

                // 3. Specific Classes
                if ($classe_id) {
                    $query->orWhere(function ($q) use ($classe_id) {
                        $q->where('target_type', 'classes')
                            ->whereJsonContains('target_ids', $classe_id);
                    });
                }
            })
            ->orderBy('id', 'desc')
            ->limit(5)
            ->get(['id', 'titre', 'message', 'categorie', 'pieceJointe']);

        // Map notification description to message 
        $mappedNotifications = $notifications->map(function ($n) {
            return [
                'id' => $n->id,
                'titre' => $n->titre,
                'message' => $n->message,
                'categorie' => $n->categorie,
                'pieceJointe' => $n->pieceJointe,
                'isRead' => false
            ];
        });

        // Find the class ID for the student
        $registre = \App\Models\Registre::where('idStudent', $student->idStudent)->first();
        $classe_id = $registre ? $registre->Cla_id : null;

        // Today's planning - using class-based logic
        $today = Carbon::today()->toDateString();
        $nowTime = Carbon::now()->toTimeString();
        $dayNames = [
            'Monday' => 'Lundi',
            'Tuesday' => 'Mardi',
            'Wednesday' => 'Mercredi',
            'Thursday' => 'Jeudi',
            'Friday' => 'Vendredi',
            'Saturday' => 'Samedi',
            'Sunday' => 'Dimanche'
        ];
        $todayDay = $dayNames[Carbon::now()->format('l')];

        $todayPlanningQuery = Planning::where(function ($q) use ($today, $todayDay) {
            $q->whereDate('date', $today)
                ->orWhere('jour', $todayDay);
        });

        if ($classe_id) {
            $todayPlanningQuery->where(function ($q) use ($student, $classe_id) {
                $q->where('idStudent', $student->idStudent)
                    ->orWhere(function ($sq) use ($classe_id) {
                        $sq->where('classe_id', $classe_id)
                            ->whereNull('idStudent');
                    });
            });
        } else {
            $todayPlanningQuery->where('idStudent', $student->idStudent);
        }

        $todayPlanning = $todayPlanningQuery->orderBy('check_in', 'asc')
            ->get(['id', 'check_in', 'check_out', 'status', 'total_hours', 'matiere', 'salle', 'professeur_name']);

        $todaySessions = [];
        foreach ($todayPlanning as $plan) {
            $label = 'Cours';
            if ($nowTime >= $plan->check_in && $nowTime <= $plan->check_out) {
                $label = 'Cours Actuel';
            } elseif ($nowTime < $plan->check_in) {
                $label = 'Prochain Cours';
            } elseif ($nowTime > $plan->check_out) {
                $label = 'Cours Terminé';
            }

            $todaySessions[] = [
                'id' => $plan->id,
                'label' => $label,
                'matiere' => $plan->matiere ?? 'Matière Non Spécifiée',
                'salle' => $plan->salle ?? 'Salle Non Spécifiée',
                'prof' => $plan->professeur_name ?? 'Prof Non Assigné',
                'time' => substr($plan->check_in ?? '00:00:00', 0, 5) . ' - ' . substr($plan->check_out ?? '00:00:00', 0, 5),
                'is_current' => ($label === 'Cours Actuel')
            ];
        }

        // Keep currentSession as the first "waiting" or "active" session for backward compat if needed
        $currentSession = !empty($todaySessions) ? $todaySessions[0] : null;
        // Try to find the actual current one
        foreach ($todaySessions as $s) {
            if ($s['is_current']) {
                $currentSession = $s;
                break;
            }
        }

        // Next Event
        $nextEvent = Event::where('date_evenement', '>=', now())
            ->orderBy('date_evenement', 'asc')
            ->first();

        // Latest Payment
        $latestPayment = Paiement::whereHas('registre', function ($q) use ($student) {
            $q->where('idStudent', $student->idStudent);
        })->orderBy('dateEcheance', 'desc')->first();

        $paymentStatusText = $latestPayment ? ($latestPayment->statut === 'Payé' ? 'Validé' : 'En attente') : 'Aucun paiement';

        // Build Activity Feed
        $activity = collect();

        // 1. Documents
        $docs = DocumentRequest::where('idStudent', $student->idStudent)->get();
        foreach ($docs as $d) {
            $statusStr = strtolower($d->status);
            $statusText = $statusStr === 'approved' ? 'Approuvée' : ($statusStr === 'rejected' ? 'Refusée' : 'En attente');
            $date = Carbon::parse($d->request_date);
            $subtitleStr = $statusStr === 'approved' ? 'Votre document est prêt' : "Votre demande a été {$statusText}";
            $activity->push([
                'type' => 'document',
                'title' => 'Demande de ' . $d->document_type,
                'subtitle' => $subtitleStr,
                'date' => $date,
                'date_val' => $date->timestamp,
            ]);
        }

        // 2. Payments
        $payments = Paiement::whereHas('registre', function ($q) use ($student) {
            $q->where('idStudent', $student->idStudent);
        })->get();
        foreach ($payments as $p) {
            $date = Carbon::parse($p->dateEcheance);
            $activity->push([
                'type' => 'payment',
                'title' => 'Paiement',
                'subtitle' => "Nouveau paiement de {$p->montant} DH",
                'date' => $date,
                'date_val' => $date->timestamp,
            ]);
        }

        // 3. Attendance
        $allPlannings = Planning::where('idStudent', $student->idStudent)->get();
        foreach ($allPlannings as $plan) {
            if (in_array($plan->status, ['Absents', 'Late in', 'Leaves'])) {
                $statusFr = $plan->status === 'Absents' ? 'Absent(e)' : ($plan->status === 'Late in' ? 'En retard' : 'Congé');
                $date = Carbon::parse($plan->date);
                $activity->push([
                    'type' => 'attendance',
                    'title' => 'Assiduité',
                    'subtitle' => "Marqué {$statusFr} pour le cours",
                    'date' => $date,
                    'date_val' => $date->timestamp,
                ]);
            }
        }

        // 4. Events
        $events = Event::orderBy('date_evenement', 'desc')->limit(10)->get();
        foreach ($events as $e) {
            $date = Carbon::parse($e->date_evenement);
            $activity->push([
                'type' => 'event',
                'title' => 'Événement',
                'subtitle' => "Nouvel événement: {$e->titre}",
                'date' => $date,
                'date_val' => $date->timestamp,
            ]);
        }

        // Sort by timestamp DESC, take 5
        $recentActivityFeed = $activity->sortByDesc('date_val')->values()->take(5)->map(function ($item) {
            return [
                'type' => $item['type'],
                'title' => $item['title'],
                'subtitle' => $item['subtitle'],
                'date_formattee' => $item['date']->diffForHumans(),
            ];
        });

        return response()->json([
            "success" => true,
            "data" => [
                "student" => $student,
                "stats" => [
                    "absences" => $absences,
                    "retards" => $retards,
                    "conges" => $conges,
                    "totalJours" => $total,
                    "dernier_paiement" => $paymentStatusText,
                ],
                "urgentNotifications" => $mappedNotifications,
                "planning" => $todaySessions,
                "current_session" => $currentSession, // for backward compatibility
                "nextEvent" => $nextEvent ? [
                    "id" => $nextEvent->id,
                    "titre" => $nextEvent->titre,
                    "description" => $nextEvent->description,
                    "date_evenement" => $nextEvent->date_evenement,
                    "lieu" => $nextEvent->lieu,
                ] : null,
                "recentActivityFeed" => $recentActivityFeed,
            ]
        ]);
    }
}
