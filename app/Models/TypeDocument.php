<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class TypeDocument extends Model
{
    protected $table = 'types_documents';

    protected $fillable = [
        'code',
        'libelle',
        'description',
        'extensions_autorisees',
        'taille_max_mb',
        'actif',
    ];

    protected $casts = [
        'extensions_autorisees' => 'array',
        'actif' => 'boolean',
    ];

    public function programmes(): BelongsToMany
    {
        return $this->belongsToMany(Programme::class, 'programme_type_document')
            ->withPivot(['obligatoire', 'ordre'])
            ->withTimestamps();
    }

    public function candidatureDocuments(): HasMany
    {
        return $this->hasMany(CandidatureDocument::class);
    }
}
