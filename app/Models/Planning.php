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
        'weekNumber'
    ];

    public function classe()
    {
        return $this->belongsTo(Classe::class, 'classe_id', 'id');
    }
}
