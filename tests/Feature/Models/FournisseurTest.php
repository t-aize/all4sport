<?php

use App\Models\Fournisseur;
use App\Models\Produit;

/*
 * Tests du modèle Fournisseur : produits fournis et assignation en masse.
 */

it('lists only the products supplied by the supplier', function () {
    $fournisseur = Fournisseur::factory()->create();
    $produit = Produit::factory()->for($fournisseur)->create();
    Produit::factory()->create();

    expect($fournisseur->produits->pluck('reference_produit')->all())->toBe([$produit->reference_produit]);
});

it('only mass assigns its name', function () {
    $fournisseur = Fournisseur::create(['nom_fournisseur' => 'Decathlon', 'id' => 999]);

    expect($fournisseur->id)->not->toBe(999)
        ->and($fournisseur->nom_fournisseur)->toBe('Decathlon');
});
