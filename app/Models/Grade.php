<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Grade extends Model
{
    use HasFactory;

    protected $fillable = [
        'student_id',
        'teacher_id',
        'course_id',
        'classe_id',
        'note',
        'type',
        'subject_name',
        'exam_date',
        'comment',
        'semester',
    ];

    protected $casts = [
        'note' => 'decimal:2',
        'exam_date' => 'date',
    ];

    /**
     * Available grade types
     */
    public const TYPES = [
        'Contrôle 1',
        'Contrôle 2',
        'Examen Final',
        'Examen Blanc',
        'Devoir',
        'TP',
        'Projet',
    ];

    /**
     * Get the student who received this grade.
     */
    public function student()
    {
        return $this->belongsTo(Student::class, 'student_id', 'idStudent');
    }

    /**
     * Get the teacher who assigned this grade.
     */
    public function teacher()
    {
        return $this->belongsTo(User::class, 'teacher_id');
    }

    /**
     * Get the course/subject for this grade.
     */
    public function course()
    {
        return $this->belongsTo(Course::class, 'course_id');
    }

    /**
     * Get the class (optional denormalized field).
     */
    public function classe()
    {
        return $this->belongsTo(Classe::class, 'classe_id', 'id');
    }

    /**
     * Scope to filter grades by student.
     */
    public function scopeForStudent($query, $studentId)
    {
        return $query->where('student_id', $studentId);
    }

    /**
     * Scope to filter grades by teacher.
     */
    public function scopeForTeacher($query, $teacherId)
    {
        return $query->where('teacher_id', $teacherId);
    }

    /**
     * Scope to filter grades by course.
     */
    public function scopeForCourse($query, $courseId)
    {
        return $query->where('course_id', $courseId);
    }

    /**
     * Scope to filter grades by type.
     */
    public function scopeOfType($query, $type)
    {
        return $query->where('type', $type);
    }

    /**
     * Scope to filter grades by semester.
     */
    public function scopeInSemester($query, $semester)
    {
        return $query->where('semester', $semester);
    }

    /**
     * Get formatted grade with color indication.
     */
    public function getFormattedNoteAttribute()
    {
        return number_format($this->note, 2);
    }

    /**
     * Determine if the grade is passing (>= 10).
     */
    public function isPassing()
    {
        return $this->note >= 10;
    }

    /**
     * Get grade status (Excellent, Good, Pass, Fail).
     */
    public function getStatusAttribute()
    {
        if ($this->note >= 16) return 'Excellent';
        if ($this->note >= 14) return 'Très Bien';
        if ($this->note >= 12) return 'Bien';
        if ($this->note >= 10) return 'Passable';
        return 'Insuffisant';
    }

    /**
     * Get color for the grade status.
     */
    public function getColorAttribute()
    {
        if ($this->note >= 16) return 'success';
        if ($this->note >= 14) return 'info';
        if ($this->note >= 12) return 'primary';
        if ($this->note >= 10) return 'warning';
        return 'danger';
    }
}
