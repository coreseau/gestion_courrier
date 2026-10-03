<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Http\UploadedFile;

class Dossier extends Model
{
    public const STATUT_RECU = 'recu';
    public const STATUT_COTE = 'cote';
    public const STATUT_EN_TRAITEMENT = 'en_traitement';
    public const STATUT_SOUMIS = 'soumis';
    public const STATUT_VALIDE = 'valide';
    public const STATUT_A_REPRENDRE = 'a_reprendre';
    public const STATUT_CLOTURE = 'cloture';
    public const STATUTS = [
    'recu' => 'Reçu',
    'cote' => 'Coté',
    'en_traitement' => 'En traitement',
    'soumis' => 'Soumis au directeur',
    'valide' => 'Validé',
    'a_reprendre' => 'À reprendre',
    'cloture' => 'Clôturé',
        ];
    public const PRIORITES = [
    'normale' => 'Normale',
    'urgente' => 'Urgente',
    'tres_urgente' => 'Très urgente',
    ];


    protected $fillable = [
        'reference', 'objet', 'resume', 'origine_type', 'origine_nom',
        'date_reception', 'priorite', 'statut', 'echeance', 'enregistre_par',
    ];

    protected function casts(): array
    {
        return [
            'date_reception' => 'date',
            'echeance' => 'date',
        ];
    }

    // --- Relations ---

    public function enregistrePar(): BelongsTo
    {
        return $this->belongsTo(User::class, 'enregistre_par');
    }

    public function fichiers(): HasMany
    {
        return $this->hasMany(Fichier::class);
    }

    public function cotations(): HasMany
    {
        return $this->hasMany(Cotation::class);
    }

    public function traitements(): HasMany
    {
        return $this->hasMany(Traitement::class);
    }

    public function historiques(): HasMany
    {
        return $this->hasMany(Historique::class)->latest();
    }

    // --- Portée : quels dossiers un utilisateur peut-il voir ? ---

    public function scopeVisiblesPour(Builder $query, User $user): Builder
    {
        if ($user->hasAnyRole(['directeur', 'admin'])) {
            return $query;
        }

        return $query->where(function (Builder $q) use ($user) {
            $q->where('enregistre_par', $user->id)
              ->orWhereHas('cotations.users', fn (Builder $u) => $u->where('users.id', $user->id));
        });
    }

    // --- Utilitaires ---

    protected function statutLibelle(): Attribute
    {
        return Attribute::get(fn () => self::STATUTS[$this->statut] ?? $this->statut);
    }

    protected function prioriteLibelle(): Attribute
    {
        return Attribute::get(fn () => self::PRIORITES[$this->priorite] ?? $this->priorite);
    }

    public function ajouterFichier(UploadedFile $fichier, string $categorie, User $user): Fichier
{
    $nomOriginal = $fichier->getClientOriginalName();
    $mime = $fichier->getMimeType();
    $taille = $fichier->getSize();

    $chemin = $fichier->store("dossiers/{$this->id}", 'local');

    return $this->fichiers()->create([
        'chemin' => $chemin,
        'nom_original' => $nomOriginal,
        'mime' => $mime,
        'taille' => $taille,
        'categorie' => $categorie,
        'televerse_par' => $user->id,
    ]);
}
public function participantsActifs()
{
    return User::whereHas('cotations', function ($q) {
        $q->where('cotations.dossier_id', $this->id)
          ->whereIn('cotation_user.statut', ['a_traiter', 'en_cours']);
    })->orderBy('name')->get();
}

public function estCoteActif(User $user): bool
{
    return $this->participantsActifs()->contains('id', $user->id);
}

    public static function genererReference(): string
    {
        $annee = now()->year;

        $derniere = static::where('reference', 'like', "CI-{$annee}-%")
            ->orderByDesc('reference')
            ->value('reference');

        $numero = $derniere ? ((int) substr($derniere, -4)) + 1 : 1;

        return sprintf('CI-%d-%04d', $annee, $numero);
    }

    public function journaliser(
        User $user,
        string $action,
        ?string $ancienStatut = null,
        ?string $nouveauStatut = null,
        ?string $commentaire = null
    ): Historique {
        return $this->historiques()->create([
            'user_id' => $user->id,
            'action' => $action,
            'ancien_statut' => $ancienStatut,
            'nouveau_statut' => $nouveauStatut,
            'commentaire' => $commentaire,
        ]);
    }
    
}