<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class SchoolParent extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    /**
     * The table associated with the model.
     * Note: Named SchoolParent because 'Parent' is a reserved keyword in PHP.
     */
    protected $table = 'parents';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'phone',
        'email',
        'password',
        'remember_token',
        'last_seen_at',
        'fcm_token',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'password' => 'hashed',
            'last_seen_at' => 'datetime',
        ];
    }

    public function isOnline(): bool
    {
        return $this->last_seen_at && $this->last_seen_at->diffInMinutes(now()) < 5;
    }

    /**
     * The students (children) associated with this parent.
     */
    public function students()
    {
        return $this->belongsToMany(
            Student::class,
            'parent_student',
            'parent_id',
            'student_id'
        )->withTimestamps();
    }

    /**
     * Document requests made by this parent.
     */
    public function documentRequests()
    {
        return $this->hasMany(DocumentRequest::class, 'parent_id');
    }
}
