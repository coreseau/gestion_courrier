<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreTraitementRequest;
use App\Models\Dossier;
use App\Models\Traitement;
use Illuminate\Support\Facades\DB;

class TraitementController extends Controller
{
    public function store(StoreTraitementRequest $request, Dossier $dossier)
    {
        $user = $request->user();
        $soumettre = $request->validated('action') === 'soumettre';

        DB::transaction(function () use ($request, $dossier, $user, $soumettre) {
            $traitement = Traitement::firstOrNew([
                'dossier_id' => $dossier->id,
                'collaborateur_id' => $user->id,
            ]);
            $premiereFois = ! $traitement->exists;

            // Référence : saisie libre, sinon suggestion automatique (CI-2026-0004-T1)
            $reference = $request->validated('reference_traitement');
            if (! $reference) {
                $reference = $traitement->reference_traitement
                    ?: $dossier->reference . '-T' . ($dossier->traitements()->count() + 1);
            }

            $traitement->reference_traitement = $reference;
            $traitement->contenu = $request->validated('contenu');
            $traitement->statut = $soumettre ? 'soumis' : 'brouillon';
            $traitement->soumis_le = $soumettre ? now() : null;
            $traitement->save();

            // Pièces jointes
            foreach ($request->file('pieces', []) as $piece) {
                $dossier->ajouterFichier($piece, 'piece_traitement', $user);
            }

            // Mise à jour de mes lignes de cotation actives sur ce dossier
            $lignes = DB::table('cotation_user')
                ->join('cotations', 'cotations.id', '=', 'cotation_user.cotation_id')
                ->where('cotations.dossier_id', $dossier->id)
                ->where('cotation_user.user_id', $user->id)
                ->whereIn('cotation_user.statut', ['a_traiter', 'en_cours'])
                ->pluck('cotation_user.id');

            DB::table('cotation_user')
                ->whereIn('id', $lignes)
                ->update([
                    'statut' => $soumettre ? 'soumis' : 'en_cours',
                    'updated_at' => now(),
                ]);

            // Statut du dossier
            $ancien = $dossier->statut;
            $nouveau = $ancien;

            if (in_array($ancien, [Dossier::STATUT_COTE, Dossier::STATUT_A_REPRENDRE])) {
                $nouveau = Dossier::STATUT_EN_TRAITEMENT;
            }

            // Tous les collaborateurs cotés ont soumis : retour au directeur
            if ($soumettre && $dossier->participantsActifs()->isEmpty()) {
                $nouveau = Dossier::STATUT_SOUMIS;
            }

            if ($nouveau !== $ancien) {
                $dossier->update(['statut' => $nouveau]);
            }

            if ($soumettre) {
                $dossier->journaliser($user, 'soumission', $ancien, $nouveau,
                    "Traitement soumis (réf. {$reference})");
            } elseif ($premiereFois) {
                $dossier->journaliser($user, 'début de traitement', $ancien, $nouveau,
                    "Traitement commencé (réf. {$reference})");
            }
        });

        return redirect()
            ->route('dossiers.show', $dossier)
            ->with('succes', $soumettre
                ? 'Traitement soumis au directeur.'
                : 'Brouillon enregistré.');
    }
}