<?php

use App\Enums\EtatCommande;
use App\Models\Commande;
use App\Models\Magasin;
use App\Models\Produit;

/*
 * API d'échange sécurisée avec le SI du Lot B "Logistique" : jeton
 * obligatoire, synchronisation des stocks, commandes à préparer.
 */

beforeEach(function () {
    config(['services.logistique.token' => 'jeton-de-test-logistique']);
    $this->entetes = ['Authorization' => 'Bearer jeton-de-test-logistique', 'Accept' => 'application/json'];
});

it('rejects a request without a valid token', function (?string $jeton) {
    $entetes = ['Accept' => 'application/json'];

    if ($jeton !== null) {
        $entetes['Authorization'] = "Bearer {$jeton}";
    }

    $this->getJson(route('api.v1.stocks.index'), $entetes)->assertUnauthorized();
})->with(['sans jeton' => [null], 'mauvais jeton' => ['faux']]);

it('rejects every request when no token is configured', function () {
    config(['services.logistique.token' => '']);

    $this->getJson(route('api.v1.stocks.index'), ['Authorization' => 'Bearer '])->assertUnauthorized();
});

it('lists the stocks of the products', function () {
    $produit = Produit::factory()->create(['quantite_stock_internet' => 42]);
    $magasin = Magasin::factory()->create();
    $produit->magasins()->attach($magasin->id, ['quantite_stock_magasin' => 7]);

    $this->getJson(route('api.v1.stocks.index'), $this->entetes)
        ->assertOk()
        ->assertJsonPath('data.0.reference_produit', $produit->reference_produit)
        ->assertJsonPath('data.0.quantite_stock_internet', 42)
        ->assertJsonPath('data.0.stocks_magasins.0.magasin_id', $magasin->id)
        ->assertJsonPath('data.0.stocks_magasins.0.quantite_stock_magasin', 7)
        ->assertJsonStructure(['data', 'links', 'meta']);
});

it('updates the online and shop stock of a product', function () {
    $produit = Produit::factory()->create(['quantite_stock_internet' => 1]);
    $magasin = Magasin::factory()->create();

    $this->putJson(route('api.v1.stocks.update', $produit), [
        'quantite_stock_internet' => 150,
        'lieu_stockage' => 'M2D3.5',
        'stocks_magasins' => [['magasin_id' => $magasin->id, 'quantite_stock_magasin' => 30]],
    ], $this->entetes)
        ->assertOk()
        ->assertJsonPath('data.quantite_stock_internet', 150)
        ->assertJsonPath('data.lieu_stockage', 'M2D3.5');

    expect($produit->refresh()->quantite_stock_internet)->toBe(150)
        ->and($produit->stockMagasin($magasin->id))->toBe(30);
});

it('validates the stock update', function () {
    $produit = Produit::factory()->create();

    $this->putJson(route('api.v1.stocks.update', $produit), [
        'quantite_stock_internet' => -3,
        'lieu_stockage' => 'Z9',
        'stocks_magasins' => [['magasin_id' => 999, 'quantite_stock_magasin' => 1]],
    ], $this->entetes)
        ->assertUnprocessable()
        ->assertJsonValidationErrors(['quantite_stock_internet', 'lieu_stockage', 'stocks_magasins.0.magasin_id']);
});

it('lists the validated orders to prepare, without personal data', function () {
    $validee = Commande::factory()->etat(EtatCommande::Validee)->create();
    Commande::factory()->etat(EtatCommande::Transmise)->create();
    $produit = Produit::factory()->create();
    $validee->produits()->attach($produit->reference_produit, ['quantite_commandee' => 3, 'prix_unitaire_vente' => 10]);

    $reponse = $this->getJson(route('api.v1.commandes.index'), $this->entetes)
        ->assertOk()
        ->assertJsonCount(1, 'data')
        ->assertJsonPath('data.0.numero', $validee->numero())
        ->assertJsonPath('data.0.code_client', $validee->client->code_cli)
        ->assertJsonPath('data.0.lignes.0.quantite', 3);

    expect($reponse->json('data.0'))->not->toHaveKeys(['email', 'telephone', 'date_naissance']);
});

it('lets the logistics record "en préparation" then "expédiée"', function () {
    $commande = Commande::factory()->etat(EtatCommande::Validee)->create();

    $this->postJson(route('api.v1.commandes.etat', $commande), ['etat' => 'en_preparation'], $this->entetes)
        ->assertOk()
        ->assertJsonPath('data.etat', 'en_preparation');
    $this->postJson(route('api.v1.commandes.etat', $commande), ['etat' => 'expediee'], $this->entetes)
        ->assertOk();

    expect($commande->refresh()->etat)->toBe(EtatCommande::Expediee);
    $this->assertDatabaseHas('historique_etats', ['commande_id' => $commande->id, 'etat' => 'expediee', 'employe_id' => null]);
});

it('refuses the states that belong to the sales staff', function (string $etat) {
    $commande = Commande::factory()->etat(EtatCommande::Transmise)->create();

    $this->postJson(route('api.v1.commandes.etat', $commande), ['etat' => $etat], $this->entetes)
        ->assertUnprocessable();
})->with(['validee', 'livree', 'retiree']);

it('refuses a transition out of order', function () {
    $commande = Commande::factory()->etat(EtatCommande::Transmise)->create();

    $this->postJson(route('api.v1.commandes.etat', $commande), ['etat' => 'expediee'], $this->entetes)
        ->assertUnprocessable()
        ->assertJsonValidationErrors('etat');
});
