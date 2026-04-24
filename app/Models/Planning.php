<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Planning extends Model
{
    protected $table = 'planning';
    public $timestamps = false;

    protected $fillable = [
        'idStudent',
        'date',
        'check_in',
        'check_out',
        'status',
        'total_hours',
        'matiere',
        'salle',
        'Cla_id',
        'classe_id',
        'professeur_name',
        'fileUrl',
        'weekNumber',
        'jour',
        'type'
    ];

    protected static function booted()
    {
        static::saving(function ($planning) {
            if ($planning->jour && !$planning->date) {
                $dayMapping = [
                    'Lundi' => 1,
                    'Mardi' => 2,
                    'Mercredi' => 3,
                    'Jeudi' => 4,
                    'Vendredi' => 5,
                    'Samedi' => 6,
                    'Dimanche' => 7
                ];

                if (isset($dayMapping[$planning->jour])) {
                    $targetDay = $dayMapping[$planning->jour];
                    // Ensure we start on Monday (1) and add appropriate days
                    $planning->date = \Carbon\Carbon::now()->startOfWeek(\Carbon\CarbonInterface::MONDAY)->addDays($targetDay - 1)->toDateString();
                }
            }
        });
    }

    public function classe()
    {
        return $this->belongsTo(Classe::class, 'classe_id', 'id');
    }
}
