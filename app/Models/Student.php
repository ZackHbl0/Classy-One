<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Laravel\Sanctum\HasApiTokens;

class Student extends Authenticatable
{
    use HasApiTokens;

    protected $table = 'student';
    protected $primaryKey = 'idStudent';
    public $timestamps = false;

    protected $fillable = [
        'matricule',
        'nom',
        'prenom',
        'password',
        'telephone',
        'fcmToken',
        'event_notifications',
        'payment_notifications',
    ];

    protected $hidden = [
        'password',
    ];

    public function getFullNameAttribute()
    {
        return $this->nom . ' ' . $this->prenom;
    }

    public function registres()
    {
        return $this->hasMany(Registre::class, 'idStudent', 'idStudent');
    }

    public function eventRegistrations()
    {
        return $this->hasMany(EventRegistration::class, 'idStudent', 'idStudent');
    }

    public function documentRequests()
    {
        return $this->hasMany(DocumentRequest::class, 'idStudent', 'idStudent');
    }

    public function notificationReads()
    {
        return $this->hasMany(NotificationRead::class, 'idStudent', 'idStudent');
    }

    // A Student belongs to a Classe through Registre
    public function classe()
    {
        return $this->hasOneThrough(
            Classe::class,
            Registre::class,
            'idStudent', // Foreign key on Registre table...
            'id', // Foreign key on Classe table...
            'idStudent', // Local key on Student table...
            'Cla_id' // Local key on Registre table...
        );
    }

    // A Student has many paiements through Registre
    public function paiements()
    {
        return $this->hasManyThrough(
            Paiement::class,
            Registre::class,
            'idStudent',
            'Reg_id',
            'idStudent',
            'id'
        );
    }
}
