<?php

/*
|--------------------------------------------------------------------------
| Routes web
|--------------------------------------------------------------------------
|
| Une route associe une URL à une action. Ces routes sont chargées par
| bootstrap/app.php et passent par le groupe de middlewares « web » : session,
| cookies, protection CSRF, etc.
|
| Astuce : `php artisan route:list` affiche toutes les routes de l'application.
|
*/

use App\Http\Controllers\AccueilController;
use App\Http\Controllers\CatalogueController;
use App\Http\Controllers\PanierController;
use Illuminate\Support\Facades\Route;

// Page d'accueil du site marchand (rayons, nouveautés, magasins).
Route::get('/', AccueilController::class)->name('home');

// Routes d'authentification (inscription, connexion, mot de passe oublié).
require __DIR__.'/auth.php';

// Routes du back-office réservées aux collaborateurs (préfixe /backoffice).
require __DIR__.'/backoffice.php';

// Espace client : commande, compte et historique (clients connectés).
require __DIR__.'/client.php';

// Catalogue public du site marchand : produits par rayon et fiche produit.
// Le produit est identifié dans l'URL par sa référence (clé primaire).
Route::get('catalogue', [CatalogueController::class, 'index'])->name('catalogue.index');
Route::get('catalogue/{produit}', [CatalogueController::class, 'show'])->name('catalogue.show');

// Panier : accessible aux visiteurs (panier rattaché à la session) comme aux
// clients connectés (panier rattaché au compte). Seule sa validation exige
// d'être connecté (voir routes/client.php).
Route::prefix('panier')->name('panier.')->group(function () {
    Route::get('/', [PanierController::class, 'index'])->name('index');
    Route::post('produits/{produit}', [PanierController::class, 'ajouter'])->name('ajouter');
    Route::patch('produits/{produit}', [PanierController::class, 'modifier'])->name('modifier');
    Route::delete('produits/{produit}', [PanierController::class, 'retirer'])->name('retirer');
    Route::delete('/', [PanierController::class, 'vider'])->name('vider');
});
