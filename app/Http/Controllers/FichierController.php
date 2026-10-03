<?php

namespace App\Http\Controllers;

use App\Models\Fichier;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Storage;

class FichierController extends Controller
{
    public function afficher(Fichier $fichier)
    {
        Gate::authorize('view', $fichier->dossier);

        return Storage::disk('local')->response(
            $fichier->chemin,
            $fichier->nom_original,
            ['Content-Type' => $fichier->mime]
        );
    }

    public function telecharger(Fichier $fichier)
    {
        Gate::authorize('view', $fichier->dossier);

        return Storage::disk('local')->download($fichier->chemin, $fichier->nom_original);
    }
}