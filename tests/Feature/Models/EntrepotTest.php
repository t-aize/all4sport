<?php

use App\Models\Commande;
use App\Models\Entrepot;
use App\Models\Magasin;

/*
 * Tests du modèle Entrepot : magasins desservis et commandes expédiées.
 */

it('lists only the shops it supplies', function () {
    $entrepot = Entrepot::factory()->create();
    $magasin = Magasin::factory()->for($entrepot)->create();
    Magasin::factory()->create();

    expect($entrepot->magasins->pluck('id')->all())->toBe([$magasin->id]);
});

it('lists only the orders shipped from the warehouse', function () {
    $entrepot = Entrepot::factory()->create();
    $commande = Commande::factory()->livraisonDomicile()->create(['entrepot_id' => $entrepot->id]);
    Commande::factory()->livraisonDomicile()->create();

    expect($entrepot->commandes->pluck('id')->all())->toBe([$commande->id]);
});
