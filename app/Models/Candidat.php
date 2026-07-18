<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Candidat extends Model
{
    protected $fillable = [
        'nom',
        'prenom',
        'date_naissance',
        'email',
        'telephone',
        'pays',
        'adresse',
        'sexe',
    ];

    protected $casts = [
        'date_naissance' => 'date',
    ];

    public function candidatures(): HasMany
    {
        return $this->hasMany(Candidature::class);
    }
}
