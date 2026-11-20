<?php

/*
|--------------------------------------------------------------------------
| Routes de l'espace client
|--------------------------------------------------------------------------
|
| Routes réservées aux clients connectés (guard "web") : validation du
| panier et passage de commande, espace "Mon compte" et historique des
| commandes. Un visiteur est redirigé vers /login puis ramené ici après sa
| connexion (son panier est alors fusionné avec celui de son compte).
|
*/

use App\Http\Controllers\CommandeController;
use App\Http\Controllers\Compte\CommandeController as CompteCommandeController;
use App\Http\Controllers\Compte\EnfantController;
use App\Http\Controllers\Compte\MotDePasseController;
use App\Http\Controllers\Compte\ProfilController;
use Illuminate\Support\Facades\Route;

Route::middleware('auth')->group(function () {
    // Validation du panier : choix du mode de livraison puis paiement.
    Route::get('commande', [CommandeController::class, 'create'])->name('commande.create');
    Route::post('commande', [CommandeController::class, 'store'])
        ->middleware('throttle:10,1')
        ->name('commande.store');

    Route::prefix('compte')->name('compte.')->group(function () {
        // Informations personnelles, magasin par défaut et sports pratiqués.
        Route::get('/', [ProfilController::class, 'edit'])->name('profil.edit');
        Route::put('/', [ProfilController::class, 'update'])->name('profil.update');

        // Enfants rattachés au compte (âge des enfants).
        Route::post('enfants', [EnfantController::class, 'store'])->name('enfants.store');
        Route::delete('enfants/{enfant}', [EnfantController::class, 'destroy'])->name('enfants.destroy');

        // Changement du mot de passe.
        Route::put('mot-de-passe', [MotDePasseController::class, 'update'])
            ->middleware('throttle:6,1')
            ->name('mot-de-passe.update');

        // Historique des commandes et détail de chacune.
        Route::get('commandes', [CompteCommandeController::class, 'index'])->name('commandes.index');
        Route::get('commandes/{commande}', [CompteCommandeController::class, 'show'])->name('commandes.show');
    });
});
