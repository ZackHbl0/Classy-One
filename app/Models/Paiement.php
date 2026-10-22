<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Paiement extends Model
{
    protected $table = 'paiement';
    public $timestamps = true;

    protected $fillable = [
        'reference',
        'Reg_id',
        'Ann_id',
        'montant',
        'remise_appliquee',
        'dateEcheance',
        'modePaiement',
        'statut',
        'notes',
        'recorded_by'
    ];

    protected $casts = [
        'montant' => 'decimal:2',
        'remise_appliquee' => 'decimal:2',
        'dateEcheance' => 'date',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    protected static function booted()
    {
        static::creating(function ($paiement) {
            if (empty($paiement->reference)) {
                $year = date('Y');
                $random = strtoupper(bin2hex(random_bytes(2)));
                $count = static::whereYear('created_at', $year)->count() + 1;
                $paiement->reference = sprintf('REF-%s-%04d%s', $year, $count, $random);
            }
            if (auth()->check() && empty($paiement->recorded_by)) {
                $paiement->recorded_by = auth()->id();
            }
        });
    }

    public function registre()
    {
        return $this->belongsTo(Registre::class, 'Reg_id', 'id');
    }

    public function recordedBy()
    {
        return $this->belongsTo(User::class, 'recorded_by', 'id');
    }

    public function student()
    {
        return $this->hasOneThrough(
            Student::class,
            Registre::class,
            'id',        // Foreign key on Registre table (registre.id)
            'idStudent', // Foreign key on Student table (student.idStudent)
            'Reg_id',    // Local key on Paiement table (paiement.Reg_id)
            'idStudent'  // Local key on Registre table (registre.idStudent)
        );
    }

    public function isPaye(): bool
    {
        $st = mb_strtolower(trim($this->statut ?? ''));
        return in_array($st, ['payé', 'paye', 'validé', 'valide']);
    }

    public function getFormattedMontantAttribute(): string
    {
        return number_format((float) $this->montant, 2, ',', ' ') . ' MAD';
    }
}
