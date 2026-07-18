<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Programme extends Model
{
    protected $fillable = [
        'nom',
        'slug',
        'niveau',
        'capacite_accueil',
        'date_ouverture',
        'date_fermeture',
        'frais_scolarite',
        'echeancier_paiement',
        'description',
        'actif',
    ];

    protected $casts = [
        'date_ouverture' => 'date',
        'date_fermeture' => 'date',
        'actif' => 'boolean',
        'frais_scolarite' => 'decimal:2',
    ];

    public function typesDocuments(): BelongsToMany
    {
        return $this->belongsToMany(TypeDocument::class, 'programme_type_document')
            ->withPivot(['obligatoire', 'ordre'])
            ->withTimestamps()
            ->orderBy('programme_type_document.ordre');
    }

    public function candidatures(): HasMany
    {
        return $this->hasMany(Candidature::class);
    }
}
