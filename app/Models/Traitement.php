<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Traitement extends Model
{
    protected $fillable = [
        'dossier_id', 'collaborateur_id', 'reference_traitement',
        'contenu', 'statut', 'soumis_le',
    ];

    protected function casts(): array
    {
        return ['soumis_le' => 'datetime'];
    }

    public function dossier(): BelongsTo
    {
        return $this->belongsTo(Dossier::class);
    }

    public function collaborateur(): BelongsTo
    {
        return $this->belongsTo(User::class, 'collaborateur_id');
    }
}