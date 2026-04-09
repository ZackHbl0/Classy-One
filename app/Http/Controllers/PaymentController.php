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

        foreach ($paiements as $p) {
            $montant = (float) $p->montant;
            $formatted = number_format($montant, 0, ',', ' ') . ' MAD';
            $date = date('d/m/Y', strtotime($p->dateEcheance));

            $statut = $p->statut;
            $displayStatus = 'En attente';
            
            if (strtolower($statut) === 'payé' || strtolower($statut) === 'paye') {
                $displayStatus = 'Payé';
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

        return response()->json([
            "success" => true,
            "data" => [
                "tranches" => $tranches
            ]
        ]);
    }
}
