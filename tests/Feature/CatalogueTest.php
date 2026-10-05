<?php

use App\Models\Magasin;
use App\Models\Photo;
use App\Models\Produit;
use App\Models\Rayon;
use Inertia\Testing\AssertableInertia;

/*
 * Tests du catalogue public : liste des produits par rayon et fiche produit
 * (prix de vente, photos, stock Internet et stock de chaque magasin).
 */

it('lists the products without requiring a login', function () {
    Produit::factory()->count(3)->create();

    $this->get(route('catalogue.index'))
        ->assertOk()
        ->assertInertia(fn (AssertableInertia $page) => $page
            ->component('catalogue/index')
            ->has('produits.data', 3));
});

it('sorts the products by rayon name', function () {
    $tennis = Rayon::factory()->create(['nom_rayon' => 'Tennis']);
    $athletisme = Rayon::factory()->create(['nom_rayon' => 'Athlétisme']);
    Produit::factory()->create(['rayon_id' => $tennis->id]);
    Produit::factory()->create(['rayon_id' => $athletisme->id]);

    $this->get(route('catalogue.index'))
        ->assertInertia(fn (AssertableInertia $page) => $page
            ->where('produits.data.0.rayon', 'Athlétisme')
            ->where('produits.data.1.rayon', 'Tennis'));
});

it('only lists the products of the requested rayon', function () {
    $rayon = Rayon::factory()->create();
    Produit::factory()->count(2)->create(['rayon_id' => $rayon->id]);
    Produit::factory()->create();

    $this->get(route('catalogue.index', ['rayon' => $rayon->id]))
        ->assertInertia(fn (AssertableInertia $page) => $page
            ->where('rayonActif', $rayon->id)
            ->has('produits.data', 2));
});

it('paginates the products', function () {
    Produit::factory()->count(13)->create();

    $this->get(route('catalogue.index'))
        ->assertInertia(fn (AssertableInertia $page) => $page
            ->has('produits.data', 12)
            ->where('produits.total', 13));
});

it('offers every rayon as a filter', function () {
    Rayon::factory()->count(4)->create();

    $this->get(route('catalogue.index'))
        ->assertInertia(fn (AssertableInertia $page) => $page->has('rayons', 4));
});

it('shows the first photo, the price and the rayon on each product card', function () {
    $produit = Produit::factory()->create(['prix_vente' => 49.9]);
    Photo::factory()->create(['reference_produit' => $produit->reference_produit, 'url_photo' => 'https://exemple.test/photo.jpg']);

    $this->get(route('catalogue.index'))
        ->assertInertia(fn (AssertableInertia $page) => $page
            ->where('produits.data.0.reference', $produit->reference_produit)
            ->where('produits.data.0.prixVente', '49.90')
            ->where('produits.data.0.rayon', $produit->rayon->nom_rayon)
            ->where('produits.data.0.photo', 'https://exemple.test/photo.jpg'));
});

it('shows the details of a product from its reference', function () {
    $produit = Produit::factory()->create(['prix_vente' => 120, 'quantite_stock_internet' => 8]);
    Photo::factory()->count(2)->create(['reference_produit' => $produit->reference_produit]);

    $this->get(route('catalogue.show', $produit))
        ->assertOk()
        ->assertInertia(fn (AssertableInertia $page) => $page
            ->component('catalogue/show')
            ->where('produit.reference', $produit->reference_produit)
            ->where('produit.description', $produit->description_produit)
            ->where('produit.prixVente', '120.00')
            ->where('produit.stockInternet', 8)
            ->has('produit.photos', 2));
});

it('shows the stock of each shop that holds the product', function () {
    $produit = Produit::factory()->create();
    $lille = Magasin::factory()->create(['ville' => 'Lille']);
    $arras = Magasin::factory()->create(['ville' => 'Arras']);
    $produit->magasins()->attach($lille->id, ['quantite_stock_magasin' => 5]);
    $produit->magasins()->attach($arras->id, ['quantite_stock_magasin' => 2]);
    Magasin::factory()->create();

    $this->get(route('catalogue.show', $produit))
        ->assertInertia(fn (AssertableInertia $page) => $page
            ->has('produit.stocksMagasins', 2)
            ->where('produit.stocksMagasins.0.ville', 'Arras')
            ->where('produit.stocksMagasins.0.quantite', 2)
            ->where('produit.stocksMagasins.1.ville', 'Lille')
            ->where('produit.stocksMagasins.1.quantite', 5));
});

it('answers 404 for an unknown product reference', function () {
    $this->get(route('catalogue.show', 'INCONNUE000000000000'))->assertNotFound();
});
