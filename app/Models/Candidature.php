<?php

namespace App\Models;

use App\Enums\CandidatureStatut;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Candidature extends Model
{
    protected $fillable = [
        'candidat_id',
        'programme_id',
        'edit_token',
        'code_suivi',
        'statut',
        'etape_courante',
        'derniere_formation',
        'etablissement_provenance',
        'motivation',
        'submitted_at',
        'locked_identity_at',
    ];

    protected $casts = [
        'statut' => CandidatureStatut::class,
        'submitted_at' => 'datetime',
        'locked_identity_at' => 'datetime',
    ];

    public function candidat(): BelongsTo
    {
        return $this->belongsTo(Candidat::class);
    }

    public function programme(): BelongsTo
    {
        return $this->belongsTo(Programme::class);
    }

    public function documents(): HasMany
    {
        return $this->hasMany(CandidatureDocument::class);
    }

    public function historiques(): HasMany
    {
        return $this->hasMany(CandidatureHistorique::class);
    }

    public function candidateCanEditIdentity(): bool
    {
        return $this->statut === CandidatureStatut::BROUILLON;
    }

    public function candidateCanEditContent(): bool
    {
        return in_array($this->statut, [
            CandidatureStatut::BROUILLON,
            CandidatureStatut::SOUMISE,
            CandidatureStatut::COMPLEMENT_DEMANDE,
        ], true);
    }
}
