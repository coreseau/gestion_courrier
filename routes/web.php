<?php

use App\Http\Controllers\CotationController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\DecisionController;
use App\Http\Controllers\DossierController;
use App\Http\Controllers\FichierController;
use App\Http\Controllers\MotDePasseTemporaireController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\TraitementController;
use App\Http\Controllers\UserController;
use Illuminate\Support\Facades\Route;

Route::get('/', fn () => redirect()->route('login'));

// Changement de mot de passe obligatoire (accessible même si le flag est actif)
Route::middleware(['auth', 'actif'])->group(function () {
    Route::get('/mot-de-passe/changer', [MotDePasseTemporaireController::class, 'edit'])->name('mot-de-passe.edit');
    Route::post('/mot-de-passe/changer', [MotDePasseTemporaireController::class, 'update'])->name('mot-de-passe.update');
});

Route::middleware(['auth', 'actif', 'mdp'])->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    // Dossiers
    Route::resource('dossiers', DossierController::class)->only(['index', 'create', 'store', 'show']);

    Route::post('/dossiers/{dossier}/cotations', [CotationController::class, 'store'])->name('cotations.store');
    Route::post('/dossiers/{dossier}/transfert', [CotationController::class, 'transferer'])->name('dossiers.transfert');

    Route::post('/dossiers/{dossier}/traitement', [TraitementController::class, 'store'])->name('traitements.store');

    Route::post('/dossiers/{dossier}/valider', [DecisionController::class, 'valider'])->name('dossiers.valider');
    Route::post('/dossiers/{dossier}/reprendre', [DecisionController::class, 'reprendre'])->name('dossiers.reprendre');
    Route::post('/dossiers/{dossier}/cloturer', [DecisionController::class, 'cloturer'])->name('dossiers.cloturer');

    Route::get('/fichiers/{fichier}', [FichierController::class, 'afficher'])->name('fichiers.afficher');
    Route::get('/fichiers/{fichier}/telecharger', [FichierController::class, 'telecharger'])->name('fichiers.telecharger');

    // Profil
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    // Administration des utilisateurs
    Route::middleware('role:admin')->prefix('administration')->name('admin.')->group(function () {
        Route::resource('utilisateurs', UserController::class)
            ->parameters(['utilisateurs' => 'user'])
            ->except(['show', 'destroy']);

        Route::post('utilisateurs/{user}/activation', [UserController::class, 'basculerActivation'])->name('utilisateurs.activation');
        Route::post('utilisateurs/{user}/reinitialiser', [UserController::class, 'reinitialiserMotDePasse'])->name('utilisateurs.reinitialiser');
    });
});

require __DIR__.'/auth.php';