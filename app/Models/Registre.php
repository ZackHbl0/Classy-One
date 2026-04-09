<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Registre extends Model
{
    protected $table = 'registre';
    protected $primaryKey = 'id'; // actual PK in DB
    public $timestamps = false;

    protected $fillable = [
        'idStudent',
        'Cla_id'
    ];

    public function student()
    {
        return $this->belongsTo(Student::class, 'idStudent', 'idStudent');
    }

    public function classe()
    {
        return $this->belongsTo(Classe::class, 'Cla_id', 'id');
    }

    public function paiements()
    {
        return $this->hasMany(Paiement::class, 'Reg_id', 'id');
    }
}
