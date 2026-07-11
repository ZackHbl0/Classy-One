<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Absence extends Model
{
    use HasFactory;

    protected $fillable = [
        'student_id',
        'classe_id',
        'matiere',
        'prof_id',
        'date',
        'seance',
        'is_justified',
        'justification_reason',
        'student_explanation',
        'status',
    ];

    protected $casts = [
        'date' => 'date',
        'is_justified' => 'boolean',
    ];

    public function student()
    {
        return $this->belongsTo(Student::class, 'student_id', 'idStudent');
    }

    public function classe()
    {
        return $this->belongsTo(Classe::class, 'classe_id', 'id');
    }

    public function prof()
    {
        return $this->belongsTo(User::class, 'prof_id');
    }
}
