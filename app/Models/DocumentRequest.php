<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DocumentRequest extends Model
{
    protected $table = 'document_requests';
    public $timestamps = false; // Using request_date manually

    protected $fillable = [
        'idStudent',
        'document_type',
        'reason',
        'urgency',
        'status',
        'request_date',
        'ready_date',
        'admin_note',
        'file_url'
    ];

    public function student()
    {
        return $this->belongsTo(Student::class, 'idStudent', 'idStudent');
    }
}
