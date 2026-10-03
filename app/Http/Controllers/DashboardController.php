<?php

namespace App\Http\Controllers;

use App\Models\Dossier;
use Illuminate\Support\Facades\DB;
use App\Models\User;

class DashboardController extends Controller
{
    public function index()
    {
        $user = auth()->user();
        if ($user->hasRole('admin')) {
    $stats = [
        'total' => User::count(),
        'actifs' => User::where('actif', true)->count(),
        'admins' => User::role('admin')->count(),
        'directeurs' => User::role('directeur')->count(),
        'collaborateurs' => User::role('collaborateur')->count(),
    ];

    return view('dashboard.admin', compact('stats'));
}

        if ($user->hasAnyRole(['directeur', 'admin'])) {
            $stats = [
                'recus' => Dossier::count(),
                'a_coter' => Dossier::where('statut', Dossier::STATUT_RECU)->count(),
                'en_cours' => Dossier::whereIn('statut', [
                    Dossier::STATUT_COTE,
                    Dossier::STATUT_EN_TRAITEMENT,
                    Dossier::STATUT_A_REPRENDRE,
                ])->count(),
                'en_retard' => Dossier::whereNotIn('statut', [Dossier::STATUT_VALIDE, Dossier::STATUT_CLOTURE])
                    ->whereDate('echeance', '<', today())
                    ->count(),
                'a_valider' => Dossier::where('statut', Dossier::STATUT_SOUMIS)->count(),
                'clotures' => Dossier::where('statut', Dossier::STATUT_CLOTURE)->count(),
            ];

            return view('dashboard.directeur', compact('stats'));
        }

        $mes = fn () => DB::table('cotation_user')
            ->join('cotations', 'cotations.id', '=', 'cotation_user.cotation_id')
            ->where('cotation_user.user_id', $user->id);

        $stats = [
            'a_traiter' => $mes()
                ->whereIn('cotation_user.statut', ['a_traiter', 'en_cours'])
                ->distinct()->count('cotations.dossier_id'),
            'en_retard' => $mes()
                ->whereIn('cotation_user.statut', ['a_traiter', 'en_cours'])
                ->whereDate('cotations.echeance', '<', today())
                ->distinct()->count('cotations.dossier_id'),
            'traites' => $mes()
                ->where('cotation_user.statut', 'soumis')
                ->distinct()->count('cotations.dossier_id'),
        ];

        return view('dashboard.collaborateur', compact('stats'));
    }
}