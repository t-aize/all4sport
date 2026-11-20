<?php

use App\Models\Magasin;
use App\Models\Produit;
use App\Models\Rayon;
use Inertia\Testing\AssertableInertia;

/*
 * Page d'accueil du site marchand.
 */

it('shows the rayons, the latest products and the shops', function () {
    $rayon = Rayon::factory()->create();
    Produit::factory(10)->create(['rayon_id' => $rayon->id]);
    Magasin::factory(3)->create();

    $this->get(route('home'))
        ->assertOk()
        ->assertInertia(fn (AssertableInertia $page) => $page
            ->component('welcome')
            ->has('rayons', 1)
            ->where('rayons.0.nombreProduits', 10)
            ->has('nouveautes', 8)
            ->has('magasins', 3)
            ->where('chiffres.produits', 10)
            ->where('chiffres.magasins', 3));
});

it('searches and sorts the catalogue', function () {
    Produit::factory()->create(['description_produit' => 'Ballon de basket taille 7', 'prix_vente' => 30]);
    Produit::factory()->create(['description_produit' => 'Raquette de tennis', 'prix_vente' => 80]);
    Produit::factory()->create(['description_produit' => 'Ballon de football', 'prix_vente' => 20]);

    $this->get(route('catalogue.index', ['recherche' => 'ballon', 'tri' => 'prix_croissant']))
        ->assertInertia(fn (AssertableInertia $page) => $page
            ->where('recherche', 'ballon')
            ->where('tri', 'prix_croissant')
            ->has('produits.data', 2)
            ->where('produits.data.0.description', 'Ballon de football'));
});

it('suggests products of the same rayon on the product page', function () {
    $rayon = Rayon::factory()->create();
    $produit = Produit::factory()->create(['rayon_id' => $rayon->id]);
    Produit::factory(5)->create(['rayon_id' => $rayon->id]);
    Produit::factory()->create();

    $this->get(route('catalogue.show', $produit))
        ->assertInertia(fn (AssertableInertia $page) => $page
            ->has('similaires', 4)
            ->where('produit.stockTotal', $produit->stockTotal()));
});
