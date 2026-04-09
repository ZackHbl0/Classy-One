<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Event extends Model
{
    protected $table = 'events';
    public $timestamps = false;

    protected $fillable = [
        'titre',
        'description',
        'date_evenement',
        'lieu',
        'pieceJointe',
        'categorie'
    ];

    public function registrations()
    {
        return $this->hasMany(EventRegistration::class, 'idEvent', 'id');
    }
}
