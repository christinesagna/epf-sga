<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CandidatureHistorique extends Model
{
    protected $fillable = [
        'candidature_id',
        'ancien_statut',
        'nouveau_statut',
        'acteur_type',
        'acteur_id',
        'commentaire',
        'metadata',
    ];

    protected $casts = [
        'metadata' => 'array',
    ];

    public function candidature(): BelongsTo
    {
        return $this->belongsTo(Candidature::class);
    }
}
