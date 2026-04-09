<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Paiement extends Model
{
    protected $table = 'paiement';
    public $timestamps = false;

    protected $fillable = [
        'Reg_id',
        'Ann_id',
        'montant',
        'dateEcheance',
        'modePaiement',
        'statut'
    ];

    public function registre()
    {
        return $this->belongsTo(Registre::class, 'Reg_id', 'id');
    }
}
