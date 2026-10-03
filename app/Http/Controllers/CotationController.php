<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreCotationRequest;
use App\Http\Requests\TransfertDossierRequest;
use App\Models\Dossier;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class CotationController extends Controller
{
    /**
     * Le directeur cote un dossier à un ou plusieurs collaborateurs.
     */
    public function store(StoreCotationRequest $request, Dossier $dossier)
    {
        $user = $request->user();
        $ids = collect($request->validated('collaborateurs'))->map(fn ($v) => (int) $v)->unique();
        $pilote = (int) $request->validated('pilote');

        DB::transaction(function () use ($request, $dossier, $user, $ids, $pilote) {
            $cotation = $dossier->cotations()->create([
                'cotee_par' => $user->id,
                'type' => 'initiale',
                'instruction' => $request->validated('instruction'),
                'echeance' => $request->validated('echeance'),
            ]);

            foreach ($ids as $id) {
                $cotation->users()->attach($id, [
                    'role' => $id === $pilote ? 'pilote' : 'associe',
                ]);
            }

            $ancien = $dossier->statut;
            $nouveau = $ancien === Dossier::STATUT_RECU ? Dossier::STATUT_COTE : $ancien;

            if ($nouveau !== $ancien) {
                $dossier->update(['statut' => $nouveau]);
            }

            $noms = User::whereIn('id', $ids)->orderBy('name')->pluck('name')->implode(', ');
            $nomPilote = User::find($pilote)->name;

            $dossier->journaliser(
                $user,
                'cotation',
                $ancien,
                $nouveau,
                "Coté à : {$noms} (pilote : {$nomPilote})"
            );
        });

        return redirect()
            ->route('dossiers.show', $dossier)
            ->with('succes', 'Dossier coté avec succès.');
    }

    /**
     * Un collaborateur transfère le dossier à un autre collaborateur.
     */
    public function transferer(TransfertDossierRequest $request, Dossier $dossier)
    {
        $user = $request->user();
        $destinataire = User::findOrFail($request->validated('destinataire'));
        $motif = $request->validated('motif');

        DB::transaction(function () use ($dossier, $user, $destinataire, $motif) {
            // Lignes de cotation encore actives de l'expéditeur sur ce dossier
            $lignes = DB::table('cotation_user')
                ->join('cotations', 'cotations.id', '=', 'cotation_user.cotation_id')
                ->where('cotations.dossier_id', $dossier->id)
                ->where('cotation_user.user_id', $user->id)
                ->whereIn('cotation_user.statut', ['a_traiter', 'en_cours'])
                ->orderByDesc('cotations.id')
                ->select('cotation_user.id', 'cotation_user.role', 'cotations.echeance')
                ->get();

            // Le destinataire hérite du rôle (pilote ou associé) de l'expéditeur
            $role = $lignes->contains('role', 'pilote') ? 'pilote' : 'associe';

            DB::table('cotation_user')
                ->whereIn('id', $lignes->pluck('id'))
                ->update(['statut' => 'transfere', 'updated_at' => now()]);

            $cotation = $dossier->cotations()->create([
                'cotee_par' => $user->id,
                'type' => 'transfert',
                'motif_transfert' => $motif,
                'echeance' => $lignes->first()->echeance,
            ]);

            $cotation->users()->attach($destinataire->id, ['role' => $role]);

            $dossier->journaliser(
                $user,
                'transfert',
                $dossier->statut,
                $dossier->statut,
                "Transféré à {$destinataire->name}. Motif : {$motif}"
            );
        });

        return redirect()
            ->route('dossiers.show', $dossier)
            ->with('succes', "Dossier transféré à {$destinataire->name}.");
    }
}