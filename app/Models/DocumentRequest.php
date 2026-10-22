<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class DocumentRequest extends Model
{
    protected $table = 'document_requests';

    public const STATUS_NOUVEAU = 'nouveau';
    public const STATUS_EN_COURS = 'en_cours';
    public const STATUS_PRET = 'pret';
    public const STATUS_REJETE = 'rejete';

    public const TYPE_ATTESTATION_SCOLARITE = 'attestation_scolarite';
    public const TYPE_RELEVE_NOTES = 'releve_notes';
    public const TYPE_CERTIFICAT_PRESENCE = 'certificat_presence';

    protected $fillable = [
        'idStudent',
        'parent_id',
        'reference',
        'document_type',
        'reason',
        'urgency',
        'status',
        'rejection_reason',
        'processed_by',
        'processed_at',
        'request_date',
        'ready_date',
        'admin_note',
        'file_url',
    ];

    protected $casts = [
        'request_date' => 'datetime',
        'ready_date' => 'datetime',
        'processed_at' => 'datetime',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    protected static function booted(): void
    {
        static::creating(function ($model) {
            if (empty($model->reference)) {
                $year = date('Y');
                $random = strtoupper(Str::random(5));
                $model->reference = 'DOC-' . $year . '-' . $random;
            }
            if (empty($model->request_date)) {
                $model->request_date = now();
            }
            if (empty($model->status)) {
                $model->status = self::STATUS_NOUVEAU;
            }
        });
    }

    /**
     * Relationship to the Student.
     */
    public function student()
    {
        return $this->belongsTo(Student::class, 'idStudent', 'idStudent');
    }

    /**
     * Relationship to the Parent (if requested by parent).
     */
    public function parent()
    {
        return $this->belongsTo(SchoolParent::class, 'parent_id', 'id');
    }

    /**
     * Relationship to the staff/admin user who processed the request.
     */
    public function processor()
    {
        return $this->belongsTo(User::class, 'processed_by', 'id');
    }

    /**
     * Check if the document is ready for download.
     */
    public function isReady(): bool
    {
        $st = strtolower(trim((string) $this->status));
        return in_array($st, ['pret', 'ready', 'approved']) || !empty($this->file_url);
    }

    /**
     * Check if the document was rejected.
     */
    public function isRejected(): bool
    {
        $st = strtolower(trim((string) $this->status));
        return in_array($st, ['rejete', 'rejected']);
    }

    /**
     * Human-readable French status label.
     */
    public function getStatusLabelAttribute(): string
    {
        $st = strtolower(trim((string) $this->status));
        return match ($st) {
            'nouveau', 'pending', 'new' => 'Nouveau',
            'en_cours', 'processing', 'in_progress' => 'En cours',
            'pret', 'ready', 'approved' => 'Prêt',
            'rejete', 'rejected' => 'Rejeté',
            default => ucfirst($st),
        };
    }

    /**
     * Filament badge color for status.
     */
    public function getBadgeColorAttribute(): string
    {
        $st = strtolower(trim((string) $this->status));
        return match ($st) {
            'nouveau', 'pending', 'new' => 'gray',
            'en_cours', 'processing', 'in_progress' => 'warning',
            'pret', 'ready', 'approved' => 'success',
            'rejete', 'rejected' => 'danger',
            default => 'gray',
        };
    }

    /**
     * Human-readable document type title.
     */
    public function getDocumentTypeTitleAttribute(): string
    {
        $type = strtolower((string) $this->document_type);
        if (str_contains($type, 'scolarit') || str_contains($type, 'attestation')) {
            return 'Attestation de Scolarité';
        }
        if (str_contains($type, 'releve') || str_contains($type, 'bulletin') || str_contains($type, 'note')) {
            return 'Relevé de Notes / Bulletin';
        }
        if (str_contains($type, 'presence') || str_contains($type, 'assiduit')) {
            return 'Certificat de Présence';
        }
        return ucfirst($this->document_type ?? 'Document Officiel');
    }
}
