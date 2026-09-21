<?php

use App\Models\Commande;
use App\Models\Fournisseur;
use App\Models\Magasin;
use App\Models\Photo;
use App\Models\Produit;
use App\Models\Rayon;

/*
 * Tests du modèle Produit : clé primaire métier (la référence), conversions
 * de types, relations et données des tables pivots (stock magasin, lignes de
 * commande).
 */

it('uses its reference as a string primary key', function () {
    $produit = Produit::factory()->create(['reference_produit' => 'ABCDEF000000000042']);

    expect($produit->getKey())->toBe('ABCDEF000000000042')
        ->and($produit->getIncrementing())->toBeFalse()
        ->and(Produit::find('ABCDEF000000000042')->is($produit))->toBeTrue();
});

it('formats the cost and the price with two decimals', function () {
    $produit = Produit::factory()->create([
        'cout_unitaire_ht' => 10,
        'prix_vente' => 24.5,
    ]);

    $produit->refresh();

    expect($produit->cout_unitaire_ht)->toBe('10.00')
        ->and($produit->prix_vente)->toBe('24.50');
});

it('starts with an empty internet stock when none is given', function () {
    $sansStock = Produit::query()->create([
        'reference_produit' => 'ZZZZZZ000000000001',
        'description_produit' => 'Ballon de football',
        'cout_unitaire_ht' => 5,
        'prix_vente' => 10,
        'fournisseur_id' => Fournisseur::factory()->create()->id,
        'rayon_id' => Rayon::factory()->create()->id,
    ]);

    expect($sansStock->refresh()->quantite_stock_internet)->toBe(0);
});

it('belongs to its supplier and its section', function () {
    $fournisseur = Fournisseur::factory()->create();
    $rayon = Rayon::factory()->create();

    $produit = Produit::factory()->for($fournisseur)->for($rayon)->create();

    expect($produit->fournisseur->is($fournisseur))->toBeTrue()
        ->and($produit->rayon->is($rayon))->toBeTrue();
});

it('lists only its own photos', function () {
    $produit = Produit::factory()->create();
    $photos = Photo::factory()->count(3)->create(['reference_produit' => $produit->reference_produit]);
    Photo::factory()->create();

    expect($produit->photos->pluck('id')->sort()->values()->all())
        ->toBe($photos->pluck('id')->sort()->values()->all());
});

it('exposes the stock of each shop through the pivot table', function () {
    $produit = Produit::factory()->create();
    $magasin = Magasin::factory()->create();

    $produit->magasins()->attach($magasin->id, ['quantite_stock_magasin' => 12]);

    $produit->load('magasins');

    expect($produit->magasins)->toHaveCount(1)
        ->and($produit->magasins->first()->is($magasin))->toBeTrue()
        ->and((int) $produit->magasins->first()->pivot->quantite_stock_magasin)->toBe(12);
});

it('exposes the ordered quantity and the sale price of each order line', function () {
    $produit = Produit::factory()->create();
    $commande = Commande::factory()->create();

    $produit->commandes()->attach($commande->id, [
        'quantite_commandee' => 3,
        'prix_unitaire_vente' => 19.9,
    ]);

    $ligne = $produit->commandes()->first()->pivot;

    expect((int) $ligne->quantite_commandee)->toBe(3)
        ->and((float) $ligne->prix_unitaire_vente)->toBe(19.9);
});
