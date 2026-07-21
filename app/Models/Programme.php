<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
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

    public function niveaux(): HasMany
    {
        return $this->hasMany(ProgrammeNiveau::class)
            ->orderBy('ordre');
    }

    public function candidatures(): HasMany
    {
        return $this->hasMany(Candidature::class);
    }
}
