<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

class AbsenceController extends Controller
{
    public function index(Request $request)
    {
        $student = $request->user();

        if (!$student) {
            return response()->json(['message' => 'Unauthorized'], 401);
        }

        $absences = Cache::remember('absences_' . $student->idStudent, now()->addMinutes(30), function () use ($student) {
            return $student->absences()
                ->with(['classe', 'prof'])
                ->orderBy('date', 'desc')
                ->get()
                ->map(function ($absence) {
                    return [
                        'id' => $absence->id,
                        'date' => $absence->date->format('Y-m-d'),
                        'seance' => $absence->seance,
                        'matiere' => $absence->matiere,
                        'is_justified' => $absence->is_justified,
                        'justification_reason' => $absence->justification_reason,
                        'student_explanation' => $absence->student_explanation,
                        'status' => $absence->status,
                        'professeur' => $absence->prof ? $absence->prof->name : null,
                        'classe' => $absence->classe ? $absence->classe->nomClasse : null,
                    ];
                });
        });

        return response()->json([
            'status' => 'success',
            'data' => $absences,
        ]);
    }

    public function justify(Request $request, $id)
    {
        $student = $request->user();

        if (!$student) {
            return response()->json(['message' => 'Unauthorized'], 401);
        }

        $request->validate([
            'reason' => 'required|string|max:1000',
        ]);

        $absence = $student->absences()->find($id);

        if (!$absence) {
            return response()->json(['status' => 'error', 'message' => 'Absence introuvable'], 404);
        }

        if ($absence->status === 'approved') {
            return response()->json(['status' => 'error', 'message' => 'Cette absence est déjà approuvée'], 400);
        }

        $absence->update([
            'student_explanation' => $request->input('reason'),
            'status' => 'submitted_by_student',
        ]);

        return response()->json([
            'status' => 'success',
            'message' => 'Justification soumise avec succès',
        ]);
    }
}
