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
        'frais_scolarite',
        'password',
        'password_plain',
        'telephone',
        'numero_tuteur',
        'fcmToken',
        'event_notifications',
        'payment_notifications',
    ];

    protected $hidden = [
        'password',
        'password_plain',
    ];

    public function getFullNameAttribute()
    {
        return $this->nom . ' ' . $this->prenom;
    }

    protected function casts(): array
    {
        return [
            'last_seen_at' => 'datetime',
        ];
    }

    public function isOnline(): bool
    {
        return $this->last_seen_at && $this->last_seen_at->diffInMinutes(now()) < 5;
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

    public function absences()
    {
        return $this->hasMany(Absence::class, 'student_id', 'idStudent');
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

    // A Student has many grades
    public function grades()
    {
        return $this->hasMany(Grade::class, 'student_id', 'idStudent');
    }

    public function conversations()
    {
        return $this->morphToMany(Conversation::class, 'participant', 'conversation_participants', 'participant_id', 'conversation_id', 'idStudent', 'id')->withTimestamps();
    }

    /**
     * The parents associated with this student.
     */
    public function parents()
    {
        return $this->belongsToMany(
            SchoolParent::class,
            'parent_student',
            'student_id',
            'parent_id'
        )->withTimestamps();
    }

    public const FRAIS_SCOLARITE_DEFAUT = 15000.00;

    public function getFraisScolariteTotalAttribute(): float
    {
        return (float) ($this->attributes['frais_scolarite'] ?? self::FRAIS_SCOLARITE_DEFAUT);
    }

    public function getTotalPayeAttribute(): float
    {
        $total = 0.0;
        foreach ($this->paiements as $p) {
            $st = mb_strtolower(trim($p->statut ?? ''));
            if ($st === 'payé' || $st === 'paye') {
                $total += (float) $p->montant;
            }
        }
        return $total;
    }

    public function getResteAPayerAttribute(): float
    {
        $totalDu = $this->frais_scolarite_total;
        $totalPaye = $this->total_paye;
        return max(0.0, $totalDu - $totalPaye);
    }

    public function getPourcentagePayeAttribute(): float
    {
        $totalDu = $this->frais_scolarite_total;
        if ($totalDu <= 0) return 100.0;
        return min(100.0, round(($this->total_paye / $totalDu) * 100, 1));
    }

    public function getStatutFinancierAttribute(): array
    {
        $totalDu = $this->frais_scolarite_total;
        $totalPaye = $this->total_paye;
        $reste = $this->reste_a_payer;
        $pourcentage = $this->pourcentage_paye;

        if ($reste <= 0.001 && $totalPaye >= $totalDu) {
            return [
                'code' => 'en_regle',
                'label' => 'Scolarité entièrement réglée',
                'badge' => 'En règle (0 MAD)',
                'color' => 'success',
                'is_en_regle' => true,
                'pourcentage' => 100.0,
            ];
        }

        if ($totalPaye <= 0) {
            return [
                'code' => 'impaye_total',
                'label' => 'Aucun versement — Reste : ' . number_format($reste, 2) . ' MAD',
                'badge' => 'Reste: ' . number_format($reste, 2) . ' MAD',
                'color' => 'danger',
                'is_en_regle' => false,
                'pourcentage' => 0.0,
            ];
        }

        return [
            'code' => 'partiel',
            'label' => 'Partiel (' . $pourcentage . '%) — Reste : ' . number_format($reste, 2) . ' MAD',
            'badge' => 'Reste: ' . number_format($reste, 2) . ' MAD',
            'color' => 'warning',
            'is_en_regle' => false,
            'pourcentage' => $pourcentage,
        ];
    }

}
