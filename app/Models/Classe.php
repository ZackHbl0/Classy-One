<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Classe extends Model
{
    protected $table = 'classe';
    public $timestamps = false;

    protected $fillable = [
        'nom_classe',
        'filiere',
        'niveau'
    ];

    public function registres()
    {
        return $this->hasMany(Registre::class, 'Cla_id', 'id');
    }

    public function plannings()
    {
        return $this->hasMany(Planning::class, 'classe_id', 'id');
    }
}
