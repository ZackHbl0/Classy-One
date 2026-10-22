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
        $paiements = Paiement::select(
                'paiement.id',
                'paiement.reference',
                'paiement.montant',
                'paiement.remise_appliquee',
                'paiement.modePaiement',
                'paiement.dateEcheance',
                'paiement.statut as statut',
                'paiement.created_at'
            )
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
                "id"            => $p->id,
                "reference"     => $p->reference,
                "title"         => "Tranche " . $counter,
                "dueDate"       => $date,
                "amount"        => $formatted,
                "raw_amount"    => $montant,
                "mode_paiement" => $p->modePaiement ?? 'Espèce',
                "status"        => $displayStatus,
                "receipt_url"   => route('paiements.receipt.download', ['id' => $p->id]),
            ];
            $counter++;
        }

        $baseTuition = (float) $student->base_tuition;
        $remiseMontant = (float) $student->remise_montant_total;
        $totalDue = (float) $student->frais_scolarite_total;
        $totalRemaining = max(0.0, $totalDue - $totalPaid);
        $progression = $totalDue > 0 ? round(($totalPaid / $totalDue) * 100, 1) : 0.0;

        return response()->json([
            "success" => true,
            "data" => [
                "base_tuition"    => $baseTuition,
                "remise_montant"  => $remiseMontant,
                "remise_type"     => $student->remise_type ?? 'aucune',
                "remise_label"    => $student->remise_label,
                "target_amount"   => $totalDue,
                "frais_scolarite" => $totalDue,
                "total_paid"      => $totalPaid,
                "total_remaining" => $totalRemaining,
                "progression"     => $progression,
                "tranches"        => $tranches
            ]
        ]);
    }
}
