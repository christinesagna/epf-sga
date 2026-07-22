<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ProgrammeNiveau extends Model
{
    protected $fillable = [
        'programme_id',
        'niveau_id',
        'ordre',
        'actif',
    ];

    protected $casts = [
        'actif' => 'boolean',
    ];

    public function programme(): BelongsTo
    {
        return $this->belongsTo(Programme::class);
    }

    public function niveau(): BelongsTo
    {
        return $this->belongsTo(Niveau::class);
    }

    public function typesDocuments(): BelongsToMany
    {
        return $this->belongsToMany(TypeDocument::class, 'programme_niveau_type_document')
            ->withPivot(['obligatoire', 'ordre'])
            ->withTimestamps()
            ->orderBy('programme_niveau_type_document.ordre');
    }

    public function candidatures(): HasMany
    {
        return $this->hasMany(Candidature::class);
    }
}
