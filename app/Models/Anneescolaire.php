<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Anneescolaire extends Model
{
    protected $table = 'anneescolaire';
    public $timestamps = false;

    protected $fillable = [
        'libelle',
        'dateDebut',
        'dateFin'
    ];

    public function classes()
    {
        return $this->hasMany(Classe::class, 'Ann_id', 'id');
    }
}
