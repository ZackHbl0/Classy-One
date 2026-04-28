<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class EventRegistration extends Model
{
    protected $table = 'event_registration';
    public $timestamps = false;
    protected $primaryKey = 'idStudent';
    public $incrementing = false;

    protected $fillable = [
        'idStudent',
        'idEvent'
    ];

    public function student()
    {
        return $this->belongsTo(Student::class, 'idStudent', 'idStudent');
    }

    public function event()
    {
        return $this->belongsTo(Event::class, 'idEvent', 'id');
    }
}
