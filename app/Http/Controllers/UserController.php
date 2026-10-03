<?php

namespace App\Http\Controllers;

use App\Http\Requests\UserRequest;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class UserController extends Controller
{
    private const ROLES = ['admin', 'directeur', 'collaborateur'];

    public function index(Request $request)
    {
        $utilisateurs = User::with('roles')
            ->when($request->filled('q'), function ($query) use ($request) {
                $terme = '%' . $request->q . '%';
                $query->where(fn ($w) => $w
                    ->where('name', 'like', $terme)
                    ->orWhere('email', 'like', $terme)
                    ->orWhere('fonction', 'like', $terme));
            })
            ->when(
                $request->filled('role') && in_array($request->role, self::ROLES),
                fn ($query) => $query->role($request->role)
            )
            ->when($request->filled('etat'), fn ($query) => $query->where('actif', $request->etat === 'actif'))
            ->orderBy('name')
            ->paginate(15)
            ->withQueryString();

        return view('admin.users.index', compact('utilisateurs'));
    }

    public function create()
    {
        return view('admin.users.create');
    }

    public function store(UserRequest $request)
    {
        $motDePasse = $request->filled('password')
            ? $request->input('password')
            : Str::password(12, true, true, false);

        $utilisateur = DB::transaction(function () use ($request, $motDePasse) {
            $utilisateur = User::create([
                'name' => $request->input('name'),
                'email' => $request->input('email'),
                'fonction' => $request->input('fonction'),
                'password' => $motDePasse,
                'actif' => true,
                'doit_changer_mot_de_passe' => true,
            ]);

            $utilisateur->syncRoles([$request->input('role')]);

            return $utilisateur;
        });

        return redirect()
            ->route('admin.utilisateurs.index')
            ->with('succes', "Compte créé pour {$utilisateur->name}.")
            ->with('identifiants', [
                'nom' => $utilisateur->name,
                'email' => $utilisateur->email,
                'mot_de_passe' => $motDePasse,
            ]);
    }

    public function edit(User $user)
    {
        return view('admin.users.edit', compact('user'));
    }

    public function update(UserRequest $request, User $user)
    {
        if ($user->id === $request->user()->id && $request->input('role') !== 'admin') {
            return back()->withInput()->withErrors([
                'role' => "Vous ne pouvez pas retirer votre propre rôle d'administrateur.",
            ]);
        }

        $user->update($request->safe()->only(['name', 'email', 'fonction']));
        $user->syncRoles([$request->input('role')]);

        return redirect()
            ->route('admin.utilisateurs.index')
            ->with('succes', "Compte de {$user->name} mis à jour.");
    }

    public function basculerActivation(Request $request, User $user)
    {
        if ($user->id === $request->user()->id) {
            return back()->withErrors(['activation' => 'Vous ne pouvez pas désactiver votre propre compte.']);
        }

        if ($user->actif) {
            $enCours = DB::table('cotation_user')
                ->where('user_id', $user->id)
                ->whereIn('statut', ['a_traiter', 'en_cours'])
                ->count();

            if ($enCours > 0) {
                return back()->withErrors([
                    'activation' => "{$user->name} a encore {$enCours} cotation(s) en cours. "
                        . 'Ses dossiers doivent être soumis ou transférés avant la désactivation.',
                ]);
            }
        }

        $user->update(['actif' => ! $user->actif]);

        return back()->with('succes', $user->actif
            ? "Compte de {$user->name} réactivé."
            : "Compte de {$user->name} désactivé.");
    }

    public function reinitialiserMotDePasse(User $user)
    {
        $motDePasse = Str::password(12, true, true, false);

        $user->update([
            'password' => $motDePasse,
            'doit_changer_mot_de_passe' => true,
        ]);

        return back()
            ->with('succes', "Mot de passe réinitialisé pour {$user->name}.")
            ->with('identifiants', [
                'nom' => $user->name,
                'email' => $user->email,
                'mot_de_passe' => $motDePasse,
            ]);
    }
}