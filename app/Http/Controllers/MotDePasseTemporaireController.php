<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;

class MotDePasseTemporaireController extends Controller
{
    public function edit(Request $request)
    {
        if (! $request->user()->doit_changer_mot_de_passe) {
            return redirect()->route('dashboard');
        }

        return view('auth.changer-mot-de-passe');
    }

    public function update(Request $request)
    {
        $request->validate([
            'password' => ['required', 'confirmed', Password::defaults()],
        ]);

        $user = $request->user();

        if (Hash::check($request->password, $user->password)) {
            return back()->withErrors([
                'password' => 'Choisissez un mot de passe différent du mot de passe temporaire.',
            ]);
        }

        $user->update([
            'password' => $request->password,
            'doit_changer_mot_de_passe' => false,
        ]);

        return redirect()->route('dashboard')->with('succes', 'Mot de passe mis à jour.');
    }
}