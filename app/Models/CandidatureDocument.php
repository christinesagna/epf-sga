<?php

namespace App\Models;

use App\Enums\DocumentStatutValidation;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CandidatureDocument extends Model
{
    protected $fillable = [
        'candidature_id',
        'type_document_id',
        'original_name',
        'stored_name',
        'path',
        'mime_type',
        'size',
        'statut_validation',
        'commentaire_validation',
    ];

    protected $casts = [
        'statut_validation' => DocumentStatutValidation::class,
    ];

    public function candidature(): BelongsTo
    {
        return $this->belongsTo(Candidature::class);
    }

    public function typeDocument(): BelongsTo
    {
        return $this->belongsTo(TypeDocument::class);
    }
}
