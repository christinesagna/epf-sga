<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Niveau extends Model
{
    protected $fillable = [
        'code',
        'libelle',
    ];

    public function programmesNiveaux(): HasMany
    {
        return $this->hasMany(ProgrammeNiveau::class);
    }
}
