<?php

namespace App\Http\Controllers;

use App\Models\Dossier;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;

class DecisionController extends Controller
{
    public function valider(Request $request, Dossier $dossier)
    {
        Gate::authorize('decider', $dossier);

        $data = $request->validate([
            'commentaire' => ['nullable', 'string', 'max:1000'],
        ]);

        $ancien = $dossier->statut;
        $dossier->update(['statut' => Dossier::STATUT_VALIDE]);
        $dossier->journaliser($request->user(), 'validation', $ancien, Dossier::STATUT_VALIDE,
            $data['commentaire'] ?? 'Traitement validé');

        return redirect()->route('dossiers.show', $dossier)->with('succes', 'Dossier validé.');
    }

    public function reprendre(Request $request, Dossier $dossier)
    {
        Gate::authorize('decider', $dossier);

        $data = $request->validate([
            'collaborateurs' => ['required', 'array', 'min:1'],
            'collaborateurs.*' => ['integer'],
            'motif' => ['required', 'string', 'min:5', 'max:1000'],
        ], [
            'collaborateurs.required' => 'Sélectionnez au moins un collaborateur.',
            'collaborateurs.min' => 'Sélectionnez au moins un collaborateur.',
        ]);

        $ids = collect($data['collaborateurs'])->map(fn ($v) => (int) $v)->unique();

        DB::transaction(function () use ($request, $dossier, $data, $ids) {
            $lignes = DB::table('cotation_user')
                ->join('cotations', 'cotations.id', '=', 'cotation_user.cotation_id')
                ->where('cotations.dossier_id', $dossier->id)
                ->whereIn('cotation_user.user_id', $ids)
                ->where('cotation_user.statut', 'soumis')
                ->select('cotation_user.id', 'cotation_user.user_id')
                ->get();

            // Chaque collaborateur choisi doit réellement avoir soumis un traitement
            abort_unless($lignes->pluck('user_id')->unique()->count() === $ids->count(), 422);

            DB::table('cotation_user')
                ->whereIn('id', $lignes->pluck('id'))
                ->update(['statut' => 'en_cours', 'updated_at' => now()]);

            // Le traitement redevient modifiable (le contenu est conservé)
            $dossier->traitements()
                ->whereIn('collaborateur_id', $ids)
                ->update(['statut' => 'brouillon', 'soumis_le' => null]);

            $noms = $dossier->traitements()
                ->whereIn('collaborateur_id', $ids)
                ->with('collaborateur')
                ->get()
                ->pluck('collaborateur.name')
                ->implode(', ');

            $ancien = $dossier->statut;
            $dossier->update(['statut' => Dossier::STATUT_A_REPRENDRE]);
            $dossier->journaliser($request->user(), 'reprise', $ancien, Dossier::STATUT_A_REPRENDRE,
                "Reprise demandée à : {$noms}. Motif : {$data['motif']}");
        });

        return redirect()->route('dossiers.show', $dossier)->with('succes', 'Reprise demandée.');
    }

    public function cloturer(Request $request, Dossier $dossier)
    {
        Gate::authorize('cloturer', $dossier);

        $depuisRecu = $dossier->statut === Dossier::STATUT_RECU;

        $data = $request->validate([
            'commentaire' => $depuisRecu
                ? ['required', 'string', 'min:5', 'max:1000']
                : ['nullable', 'string', 'max:1000'],
        ], [
            'commentaire.required' => 'Indiquez la décision ou le traitement réalisé avant de clôturer.',
        ]);

        $ancien = $dossier->statut;
        $dossier->update(['statut' => Dossier::STATUT_CLOTURE]);

        $dossier->journaliser(
            $request->user(),
            $depuisRecu ? 'traité par le directeur et clôturé' : 'clôture',
            $ancien,
            Dossier::STATUT_CLOTURE,
            $data['commentaire'] ?? 'Dossier clôturé'
        );

        return redirect()->route('dossiers.show', $dossier)->with('succes', 'Dossier clôturé.');
    }
}