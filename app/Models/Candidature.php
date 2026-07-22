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
        'programme_niveau_id',
        'programme_origine_id',
        'agent_admission_id',
        'pris_en_charge_at',
        'edit_token',
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
        'pris_en_charge_at' => 'datetime',
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

    public function programmeNiveau(): BelongsTo
    {
        return $this->belongsTo(ProgrammeNiveau::class);
    }

    public function programmeOrigine(): BelongsTo
    {
        return $this->belongsTo(Programme::class, 'programme_origine_id');
    }

    public function agentAdmission(): BelongsTo
    {
        return $this->belongsTo(User::class, 'agent_admission_id');
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
            CandidatureStatut::COMPLEMENT_ADMISSION,
            CandidatureStatut::COMPLEMENT_JURY,
        ], true);
    }

    public function peutTransitionnerVers(CandidatureStatut $nouveauStatut): bool
    {
        return $this->statut->peutTransitionnerVers($nouveauStatut);
    }

    public function peutEtreReorientee(): bool
    {
        return $this->statut === CandidatureStatut::TRANSMISE_AU_JURY;
    }
}
