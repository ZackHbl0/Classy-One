<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use App\Models\Event;
use App\Models\EventRegistration;

class EventController extends Controller
{
    public function index(Request $request)
    {
        $student = $request->user();
        $category = $request->input('category', 'Tout');

        $query = Event::query();

        if ($category !== 'Tout') {
            $query->where('categorie', $category);
        }

        $events = $query->orderBy('date_evenement', 'desc')->get();

        $mapped = $events->map(function ($e) use ($student) {
            $isRegistered = EventRegistration::where('idEvent', $e->id)
                ->where('idStudent', $student->idStudent)
                ->exists();

            $participantCount = EventRegistration::where('idEvent', $e->id)->count();

            return [
                "id" => (int) $e->id,
                "title" => $e->titre,
                "description" => $e->description,
                "date_event" => $e->date_evenement,
                "location" => $e->lieu ?? '',
                "image_url" => $e->pieceJointe ? Storage::disk('public')->url($e->pieceJointe) : null,
                "category" => $e->categorie ?? 'Académique',
                "isConfirmed" => $isRegistered,
                "participants" => $participantCount,
            ];
        });

        return response()->json([
            "success" => true,
            "data" => $mapped
        ]);
    }

    public function register(Request $request)
    {
        $student = $request->user();

        // Handle legacy PHP param names
        $idEvent = $request->input('idNotification', $request->input('idEvent', 0));

        if ($idEvent <= 0) {
            return response()->json(["success" => false, "message" => "Paramètres incomplets."]);
        }

        $exists = EventRegistration::where('idStudent', $student->idStudent)
            ->where('idEvent', $idEvent)
            ->exists();

        if ($exists) {
            return response()->json(["success" => false, "message" => "Déjà inscrit à cet événement."]);
        }

        EventRegistration::create([
            'idStudent' => $student->idStudent,
            'idEvent' => $idEvent
        ]);

        return response()->json(["success" => true, "message" => "Inscription réussie."]);
    }
}
