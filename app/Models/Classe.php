<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Classe extends Model
{
    protected $table = 'classe';
    public $timestamps = false;

    protected $fillable = [
        'Ann_id',
        'Not_id',
        'nomClasse'
    ];

    public function anneescolaire()
    {
        return $this->belongsTo(Anneescolaire::class, 'Ann_id', 'id');
    }

    public function registres()
    {
        return $this->hasMany(Registre::class, 'Cla_id', 'id');
    }

    public function plannings()
    {
        return $this->hasMany(Planning::class, 'classe_id', 'id');
    }
}
