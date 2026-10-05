<?php

/*
|--------------------------------------------------------------------------
| Routes du back-office (collaborateurs)
|--------------------------------------------------------------------------
|
| Ce fichier regroupe les routes réservées aux collaborateurs d'ALL4SPORT
| (opérateur, vendeur, livreur, service client). Elles sont préfixées par
| /backoffice et leurs noms commencent par « backoffice. ».
|
| Elles utilisent le guard « employe » (voir config/auth.php) : les clients
| et les collaborateurs ont chacun leur propre connexion.
|
| Pour réserver une nouvelle route à certains rôles, ajoutez-la dans le groupe
| « auth:employe » avec le middleware « role » :
|
|     Route::get('commandes', ...)->middleware('role:operateur,vendeur');
|
*/

use App\Http\Controllers\BackOffice\AuthenticatedSessionController;
use App\Http\Controllers\BackOffice\DashboardController;
use App\Http\Controllers\BackOffice\ProduitController;
use Illuminate\Support\Facades\Route;

Route::prefix('backoffice')->name('backoffice.')->group(function () {
    // « guest:employe » : réservé aux visiteurs non connectés comme collaborateur.
    // Un collaborateur déjà connecté est renvoyé vers son tableau de bord.
    Route::middleware('guest:employe')->group(function () {
        Route::get('login', [AuthenticatedSessionController::class, 'create'])->name('login');
        // La limitation des tentatives est gérée dans BackOffice\LoginRequest.
        Route::post('login', [AuthenticatedSessionController::class, 'store']);
    });

    // « auth:employe » : réservé aux collaborateurs connectés. Un visiteur est
    // renvoyé vers la connexion du back-office (voir bootstrap/app.php).
    Route::middleware('auth:employe')->group(function () {
        Route::get('/', DashboardController::class)->name('dashboard');
        Route::post('logout', [AuthenticatedSessionController::class, 'destroy'])->name('logout');

        // Gestion des produits (stock Internet, stock magasin, photos) : réservée
        // aux opérateurs et aux vendeurs. Le produit est identifié par sa référence.
        Route::resource('produits', ProduitController::class)
            ->except('show')
            ->middleware('role:operateur,vendeur');
    });
});
