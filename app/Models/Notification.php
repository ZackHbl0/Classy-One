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
        'target_type',
        'target_ids',
        'target_summary',
        'created_at'
    ];

    protected $casts = [
        'target_ids' => 'array',
    ];

    public function reads()
    {
        return $this->hasMany(NotificationRead::class, 'idNotification', 'id');
    }
}
