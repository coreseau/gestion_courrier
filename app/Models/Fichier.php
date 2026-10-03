<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Fichier extends Model
{
    protected $fillable = [
        'dossier_id', 'chemin', 'nom_original', 'mime', 'taille',
        'categorie', 'televerse_par',
    ];

    public function dossier(): BelongsTo
    {
        return $this->belongsTo(Dossier::class);
    }

    public function televersePar(): BelongsTo
    {
        return $this->belongsTo(User::class, 'televerse_par');
    }
}