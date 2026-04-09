<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Notification extends Model
{
    protected $table = 'notification';
    public $timestamps = true;

    protected $fillable = [
        'titre',
        'message',
        'categorie',
        'pieceJointe',
        'created_at'
    ];

    public function reads()
    {
        return $this->hasMany(NotificationRead::class, 'idNotification', 'id');
    }
}
