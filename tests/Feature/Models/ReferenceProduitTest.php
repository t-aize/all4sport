<?php

use App\Models\Fournisseur;
use App\Models\Produit;
use App\Models\Rayon;

it('génère une référence avec les codes du fournisseur et du rayon', function () {
    $fournisseur = Fournisseur::factory()->create(['code_fournisseur' => 'NIK']);
    $rayon = Rayon::factory()->create(['code_rayon' => 'RUN']);

    $produit = Produit::factory()->create([
        'fournisseur_id' => $fournisseur->id,
        'rayon_id' => $rayon->id,
    ]);

    expect($produit->reference_produit)
        ->toStartWith('NIKRUN')
        ->toMatch('/^NIKRUN\d{12}$/');
});

it('génère une référence unique pour chaque produit', function () {
    $produits = Produit::factory()->count(10)->create();

    expect($produits->pluck('reference_produit')->unique())->toHaveCount(10);
});

it('conserve une référence fournie explicitement', function () {
    $produit = Produit::factory()->create([
        'reference_produit' => 'ABCDEF000000000042',
    ]);

    expect($produit->reference_produit)->toBe('ABCDEF000000000042');
});
