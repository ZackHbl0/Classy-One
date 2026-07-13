<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Planning;
use Illuminate\Support\Facades\Cache;

class PlanningController extends Controller
{
    public function index(Request $request)
    {
        $student = $request->user();

        // Find the class ID for the student
        $registre = \App\Models\Registre::where('idStudent', $student->idStudent)->first();
        $classe_id = $registre ? $registre->Cla_id : null;

        if (!$classe_id) {
            return response()->json([
                "success" => true,
                "data" => []
            ]);
        }

        // Global Class Schedule entries + Student specific entries
        $planning = Cache::remember('planning_class_' . $classe_id . '_student_' . $student->idStudent, now()->addHour(), function () use ($student, $classe_id) {
            return Planning::where(function ($q) use ($student, $classe_id) {
                $q->where('classe_id', $classe_id)
                    ->whereNull('idStudent')
                    ->orWhere('idStudent', $student->idStudent);
            })
                ->orderBy('date', 'asc')
                ->orderBy('check_in', 'asc')
                ->get(['id', 'date', 'jour', 'check_in', 'check_out', 'status', 'fileUrl', 'weekNumber', 'matiere', 'salle', 'professeur_name', 'type']);
        });

        return response()->json([
            "success" => true,
            "data" => $planning
        ]);
    }
}
