<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Course extends Model
{
    use HasFactory;

    protected $fillable = [
        'title',
        'categorie',
        'description',
        'file_path',
        'professor_id',
        'classe_id',
    ];

    /**
     * Get the professor who uploaded the course.
     */
    public function professor()
    {
        return $this->belongsTo(User::class, 'professor_id');
    }

    /**
     * Get the class assigned to the course.
     */
    public function classe()
    {
        return $this->belongsTo(Classe::class, 'classe_id', 'id');
    }

    /**
     * Get all grades for this course.
     */
    public function grades()
    {
        return $this->hasMany(Grade::class, 'course_id');
    }
}
