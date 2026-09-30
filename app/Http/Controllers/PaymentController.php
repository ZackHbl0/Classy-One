<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Paiement;

class PaymentController extends Controller
{
    public function index(Request $request)
    {
        $student = $request->user();

        // Paiements link through Registre
        $paiements = Paiement::select('paiement.id', 'paiement.montant', 'paiement.dateEcheance', 'paiement.statut as statut')
            ->join('registre as r', 'paiement.Reg_id', '=', 'r.id')
            ->where('r.idStudent', $student->idStudent)
            ->orderBy('paiement.dateEcheance', 'desc')
            ->get();

        $tranches = [];
        $counter = 1;

        $totalPaid = 0.0;
        $totalTranches = 0.0;

        foreach ($paiements as $p) {
            $montant = (float) $p->montant;
            $totalTranches += $montant;
            $formatted = number_format($montant, 0, ',', ' ') . ' MAD';
            $date = date('d/m/Y', strtotime($p->dateEcheance));

            $statut = $p->statut;
            $displayStatus = 'En attente';
            
            if (strtolower($statut) === 'payé' || strtolower($statut) === 'paye') {
                $displayStatus = 'Payé';
                $totalPaid += $montant;
            } else if (strtolower($statut) === 'en retard') {
                $displayStatus = 'En retard';
            } else if (strtolower($statut) === 'en attente') {
                $displayStatus = 'En attente';
            }

            $tranches[] = [
                "title"   => "Tranche " . $counter,
                "dueDate" => $date,
                "amount"  => $formatted,
                "status"  => $displayStatus,
            ];
            $counter++;
        }

        $fraisScolarite = (float) ($student->frais_scolarite ?? $student->frais_scolarite_total ?? 15000.00);
        $totalDue = $fraisScolarite > 0 ? $fraisScolarite : max($totalTranches, 15000.00);
        $totalRemaining = max(0.0, $totalDue - $totalPaid);
        $progression = $totalDue > 0 ? round(($totalPaid / $totalDue) * 100, 1) : 0.0;

        return response()->json([
            "success" => true,
            "data" => [
                "target_amount" => $totalDue,
                "frais_scolarite" => $totalDue,
                "total_paid" => $totalPaid,
                "total_remaining" => $totalRemaining,
                "progression" => $progression,
                "tranches" => $tranches
            ]
        ]);
    }
}
