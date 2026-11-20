<?php

/*
|--------------------------------------------------------------------------
| API d'échange avec le Lot B « Logistique »
|--------------------------------------------------------------------------
|
| Les deux activités d'ALL4SPORT (vente et logistique) forment deux systèmes
| d'information disjoints : elles échangent des données, sans
| interconnexion. Ces routes (préfixe /api/v1) permettent au SI Logistique :
|   - de lire et de mettre à jour les stocks (Internet et magasins) ;
|   - de récupérer les commandes à préparer et d'en faire évoluer l'état
|     (« en préparation », « expédiée »).
|
| Sécurité : jeton d'API obligatoire (en-tête « Authorization: Bearer … »),
| HTTPS exigé en production, limitation à 60 requêtes par minute.
| Documentation : docs/api-logistique.md.
|
*/

use App\Http\Controllers\Api\V1\CommandeController;
use App\Http\Controllers\Api\V1\StockController;
use App\Http\Middleware\AuthentifierSystemeLogistique;
use Illuminate\Support\Facades\Route;

Route::prefix('v1')
    ->name('api.v1.')
    ->middleware([AuthentifierSystemeLogistique::class, 'throttle:api'])
    ->group(function () {
        Route::get('stocks', [StockController::class, 'index'])->name('stocks.index');
        Route::get('stocks/{produit}', [StockController::class, 'show'])->name('stocks.show');
        Route::put('stocks/{produit}', [StockController::class, 'update'])->name('stocks.update');

        Route::get('commandes', [CommandeController::class, 'index'])->name('commandes.index');
        Route::get('commandes/{commande}', [CommandeController::class, 'show'])->name('commandes.show');
        Route::post('commandes/{commande}/etat', [CommandeController::class, 'changerEtat'])->name('commandes.etat');
    });
