<?php

use App\Models\Client;
use App\Models\Commande;
use App\Models\Employe;
use App\Models\Magasin;
use App\Models\Produit;
use App\Models\Rayon;
use Illuminate\Database\UniqueConstraintViolationException;

/*
 * Tests des contraintes d'unicité de la base : la base doit refuser les
 * doublons même si l'application oublie de les vérifier avant l'insertion.
 */

it('rejects two clients with the same email', function () {
    Client::factory()->create(['email' => 'marie.dupont@example.com']);

    expect(fn () => Client::factory()->create(['email' => 'marie.dupont@example.com']))
        ->toThrow(UniqueConstraintViolationException::class);

    expect(Client::where('email', 'marie.dupont@example.com')->count())->toBe(1);
});

it('accepts two clients with different emails', function () {
    Client::factory()->create(['email' => 'marie.dupont@example.com']);
    Client::factory()->create(['email' => 'paul.martin@example.com']);

    expect(Client::count())->toBe(2);
});

it('rejects two employees with the same login', function () {
    Employe::factory()->create(['identifiant_connexion' => 'jdupont']);

    expect(fn () => Employe::factory()->create(['identifiant_connexion' => 'jdupont']))
        ->toThrow(UniqueConstraintViolationException::class);

    expect(Employe::where('identifiant_connexion', 'jdupont')->count())->toBe(1);
});

it('rejects two products with the same reference', function () {
    Produit::factory()->create(['reference_produit' => 'ABCDEF000000000001']);

    expect(fn () => Produit::factory()->create(['reference_produit' => 'ABCDEF000000000001']))
        ->toThrow(UniqueConstraintViolationException::class);

    expect(Produit::count())->toBe(1);
});

it('rejects the same product twice in the same shop', function () {
    $magasin = Magasin::factory()->create();
    $produit = Produit::factory()->create();
    $magasin->produits()->attach($produit->reference_produit, ['quantite_stock_magasin' => 5]);

    expect(fn () => $magasin->produits()->attach($produit->reference_produit, ['quantite_stock_magasin' => 9]))
        ->toThrow(UniqueConstraintViolationException::class);

    expect((int) $magasin->produits()->first()->pivot->quantite_stock_magasin)->toBe(5);
});

it('accepts the same product in two different shops', function () {
    $produit = Produit::factory()->create();
    $magasins = Magasin::factory()->count(2)->create();

    $magasins->each(fn (Magasin $magasin) => $magasin->produits()->attach($produit->reference_produit));

    expect($produit->magasins()->count())->toBe(2);
});

it('rejects the same sport twice for the same client', function () {
    $client = Client::factory()->create();
    $rayon = Rayon::factory()->create();
    $client->rayons()->attach($rayon);

    expect(fn () => $client->rayons()->attach($rayon))
        ->toThrow(UniqueConstraintViolationException::class);

    expect($client->rayons()->count())->toBe(1);
});

it('rejects the same product twice on the same order', function () {
    $commande = Commande::factory()->create();
    $produit = Produit::factory()->create();
    $ligne = ['quantite_commandee' => 1, 'prix_unitaire_vente' => 10];
    $commande->produits()->attach($produit->reference_produit, $ligne);

    expect(fn () => $commande->produits()->attach($produit->reference_produit, $ligne))
        ->toThrow(UniqueConstraintViolationException::class);

    expect($commande->produits()->count())->toBe(1);
});

it('accepts the same product on two different orders', function () {
    $produit = Produit::factory()->create();
    $commandes = Commande::factory()->count(2)->create();
    $ligne = ['quantite_commandee' => 1, 'prix_unitaire_vente' => 10];

    $commandes->each(fn (Commande $commande) => $commande->produits()->attach($produit->reference_produit, $ligne));

    expect($produit->commandes()->count())->toBe(2);
});
