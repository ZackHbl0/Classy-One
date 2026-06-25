<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Conversation extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'type',
        'classe_id',
    ];

    public function messages()
    {
        return $this->hasMany(Message::class, 'conversation_id');
    }

    public function classe()
    {
        return $this->belongsTo(Classe::class, 'classe_id');
    }

    /**
     * Get all of the users (professors/admins) that are assigned this conversation.
     */
    public function users()
    {
        return $this->morphedByMany(User::class, 'participant', 'conversation_participants')->withTimestamps();
    }

    /**
     * Get all of the students that are assigned this conversation.
     */
    public function students()
    {
        return $this->morphedByMany(Student::class, 'participant', 'conversation_participants', 'conversation_id', 'participant_id', 'id', 'idStudent')->withTimestamps();
    }
}
