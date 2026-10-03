<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Cotation extends Model
{
    protected $fillable = [
        'dossier_id', 'cotee_par', 'type', 'instruction',
        'motif_transfert', 'echeance',
    ];

    protected function casts(): array
    {
        return ['echeance' => 'date'];
    }

    public function dossier(): BelongsTo
    {
        return $this->belongsTo(Dossier::class);
    }

    public function coteePar(): BelongsTo
    {
        return $this->belongsTo(User::class, 'cotee_par');
    }

    public function users(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'cotation_user')
            ->withPivot('role', 'statut', 'lu_le')
            ->withTimestamps();
    }
}