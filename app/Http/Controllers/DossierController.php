<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreDossierRequest;
use App\Models\Dossier;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;

class DossierController extends Controller
{
    public function index(Request $request)
    {
        Gate::authorize('viewAny', Dossier::class);

        $dossiers = Dossier::visiblesPour($request->user())
            ->when($request->filled('q'), function ($query) use ($request) {
                $terme = '%' . $request->q . '%';
                $query->where(fn ($w) => $w
                    ->where('reference', 'like', $terme)
                    ->orWhere('objet', 'like', $terme)
                    ->orWhere('origine_nom', 'like', $terme));
            })
            ->when($request->filled('statut'), fn ($q) => $q->where('statut', $request->statut))
            ->when($request->filled('priorite'), fn ($q) => $q->where('priorite', $request->priorite))
            ->latest()
            ->paginate(15)
            ->withQueryString();

        return view('dossiers.index', compact('dossiers'));
    }

    public function create()
    {
        Gate::authorize('create', Dossier::class);

        return view('dossiers.create');
    }

    public function store(StoreDossierRequest $request)
    {
        $user = $request->user();

        $dossier = DB::transaction(function () use ($request, $user) {
            $dossier = Dossier::create([
                ...$request->safe()->except('scan'),
                'reference' => Dossier::genererReference(),
                'statut' => Dossier::STATUT_RECU,
                'enregistre_par' => $user->id,
            ]);

            $this->enregistrerFichier($dossier, $request->file('scan'), 'scan_initial', $user);

            $dossier->journaliser($user, 'enregistrement', null, Dossier::STATUT_RECU, 'Dossier enregistré');

            return $dossier;
        });

        return redirect()
            ->route('dossiers.show', $dossier)
            ->with('succes', "Dossier {$dossier->reference} enregistré.");
    }

    public function show(Request $request, Dossier $dossier)
{
    Gate::authorize('view', $dossier);

    $user = $request->user();

    // Marquer comme lu pour l'utilisateur coté
    DB::table('cotation_user')
        ->where('user_id', $user->id)
        ->whereNull('lu_le')
        ->whereIn('cotation_id', $dossier->cotations()->pluck('id'))
        ->update(['lu_le' => now(), 'updated_at' => now()]);

    $dossier->load([
        'enregistrePar',
        'fichiers.televersePar',
        'cotations.coteePar',
        'cotations.users',
        'traitements.collaborateur',
        'historiques.user',
    ]);

    $participantsActifs = $dossier->participantsActifs();
    $collaborateurs = User::role('collaborateur')->where('actif', true)->orderBy('name')->get();

    return view('dossiers.show', compact('dossier', 'participantsActifs', 'collaborateurs'));
}

    private function enregistrerFichier(Dossier $dossier, UploadedFile $fichier, string $categorie, User $user): void
    {
        $nomOriginal = $fichier->getClientOriginalName();
        $mime = $fichier->getMimeType();
        $taille = $fichier->getSize();

        $chemin = $fichier->store("dossiers/{$dossier->id}", 'local');

        $dossier->fichiers()->create([
            'chemin' => $chemin,
            'nom_original' => $nomOriginal,
            'mime' => $mime,
            'taille' => $taille,
            'categorie' => $categorie,
            'televerse_par' => $user->id,
        ]);
    }
}