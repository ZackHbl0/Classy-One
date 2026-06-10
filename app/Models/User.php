<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Filament\Models\Contracts\FilamentUser;
use Filament\Panel;

class User extends Authenticatable implements FilamentUser
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'role',
        'classe_id',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
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
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    // ─── Role Helpers ────────────────────────────────────────────

    public function isAdmin(): bool
    {
        return $this->role === 'admin';
    }

    public function isSecretaire(): bool
    {
        return $this->role === 'secretaire';
    }

    public function isProfesseur(): bool
    {
        return $this->role === 'professeur';
    }

    public function classe()
    {
        return $this->belongsTo(Classe::class, 'classe_id', 'id');
    }

    public function courses()
    {
        return $this->hasMany(Course::class, 'professor_id');
    }

    // Grades assigned by this teacher
    public function assignedGrades()
    {
        return $this->hasMany(Grade::class, 'teacher_id');
    }

    // ─── Filament Panel Access ───────────────────────────────────

    public function canAccessPanel(Panel $panel): bool
    {
        // Allow both admins and secretaires with an OSBT email domain
        return str_ends_with($this->email, '@osbt.ma') ||
            str_ends_with($this->email, '@osbt.com');
    }
}
