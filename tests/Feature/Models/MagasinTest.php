<?php

use App\Models\Commande;
use App\Models\Entrepot;
use App\Models\Magasin;
use App\Models\Produit;

/*
 * Tests du modèle Magasin : entrepôt qui le dessert, stock par produit et
 * commandes retirées dans le magasin.
 */

it('belongs to the warehouse that supplies it', function () {
    $entrepot = Entrepot::factory()->create();
    $magasin = Magasin::factory()->for($entrepot)->create();

    expect($magasin->entrepot->is($entrepot))->toBeTrue();
});

it('exposes the stock of each product through the pivot table', function () {
    $magasin = Magasin::factory()->create();
    $produit = Produit::factory()->create();
    $autreProduit = Produit::factory()->create();

    $magasin->produits()->attach($produit->reference_produit, ['quantite_stock_magasin' => 7]);

    expect($magasin->produits->pluck('reference_produit')->all())->toBe([$produit->reference_produit])
        ->and($magasin->produits->pluck('reference_produit'))->not->toContain($autreProduit->reference_produit)
        ->and((int) $magasin->produits->first()->pivot->quantite_stock_magasin)->toBe(7);
});

it('lists only the orders picked up in the shop', function () {
    $magasin = Magasin::factory()->create();
    $commande = Commande::factory()->retraitMagasin()->create(['magasin_id' => $magasin->id]);
    Commande::factory()->retraitMagasin()->create();

    expect($magasin->commandes->pluck('id')->all())->toBe([$commande->id]);
});
