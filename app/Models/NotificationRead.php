<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class NotificationRead extends Model
{
    protected $table = 'notification_read';
    public $timestamps = false;

    protected $fillable = [
        'idStudent',
        'idNotification'
    ];

    public function student()
    {
        return $this->belongsTo(Student::class, 'idStudent', 'idStudent');
    }

    public function notification()
    {
        return $this->belongsTo(Notification::class, 'idNotification', 'id');
    }
}
