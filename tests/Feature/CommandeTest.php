<?php

use App\Enums\EtatCommande;
use App\Enums\ModeLivraison;
use App\Models\Client;
use App\Models\Commande;
use App\Models\Entrepot;
use App\Models\Magasin;
use App\Models\Panier;
use App\Models\Produit;
use Inertia\Testing\AssertableInertia;

/*
 * Tests du passage de commande (processus de vente en ligne) : validation du
 * panier, choix du mode de livraison, vérification des stocks, ajout de la
 * commande au compte client et état "transmise".
 */

/**
 * Remplit le panier du client avec un produit.
 */
function remplirPanier(Client $client, Produit $produit, int $quantite = 1): Panier
{
    $panier = Panier::query()->firstOrCreate(['client_id' => $client->id]);
    $panier->lignes()->create([
        'reference_produit' => $produit->reference_produit,
        'quantite' => $quantite,
        'prix_unitaire' => $produit->prix_vente,
    ]);

    return $panier;
}

beforeEach(function () {
    $this->entrepot = Entrepot::factory()->create(['ville' => 'Lyon']);
    $this->magasin = Magasin::factory()->create(['ville' => 'Lille', 'entrepot_id' => $this->entrepot->id]);
    $this->client = Client::factory()->create(['magasin_id' => $this->magasin->id]);
    $this->produit = Produit::factory()->create(['prix_vente' => 20, 'quantite_stock_internet' => 10]);
    $this->produit->magasins()->attach($this->magasin->id, ['quantite_stock_magasin' => 3]);
});

describe('accès', function () {
    it('redirects a guest to the login page', function () {
        $this->get(route('commande.create'))->assertRedirect(route('login'));
        $this->post(route('commande.store'))->assertRedirect(route('login'));
    });

    it('sends back to the cart when it is empty', function () {
        $this->actingAs($this->client)
            ->get(route('commande.create'))
            ->assertRedirect(route('panier.index'))
            ->assertSessionHasErrors('panier');
    });

    it('shows the delivery step with the shops and their availability', function () {
        $autreMagasin = Magasin::factory()->create(['ville' => 'Nice']);
        remplirPanier($this->client, $this->produit, 2);

        $this->actingAs($this->client)
            ->get(route('commande.create'))
            ->assertOk()
            ->assertInertia(fn (AssertableInertia $page) => $page
                ->component('commande/create')
                ->where('panier.total', '40.00')
                ->where('client.magasinId', $this->magasin->id)
                ->where('disponibleInternet', true)
                ->where('entrepotExpediteur', 'Lyon')
                ->where('magasins.0.ville', 'Lille')
                ->where('magasins.0.disponible', true)
                ->where('magasins.1.id', $autreMagasin->id)
                ->where('magasins.1.disponible', false)
                ->has('modes', 2));
    });
});

describe('retrait en magasin', function () {
    it('creates a free order, decrements the shop stock and empties the cart', function () {
        remplirPanier($this->client, $this->produit, 2);

        $reponse = $this->actingAs($this->client)->post(route('commande.store'), [
            'mode_livraison' => ModeLivraison::RetraitMagasin->value,
            'magasin_id' => $this->magasin->id,
            'accepte_conditions' => '1',
        ]);

        $commande = Commande::query()->sole();
        $reponse->assertRedirect(route('compte.commandes.show', ['commande' => $commande, 'confirmation' => 1]));

        expect($commande->mode_livraison)->toBe(ModeLivraison::RetraitMagasin)
            ->and($commande->magasin_id)->toBe($this->magasin->id)
            ->and($commande->entrepot_id)->toBeNull()
            ->and((float) $commande->frais_livraison)->toBe(0.0)
            ->and((float) $commande->montant_total)->toBe(40.0)
            ->and($commande->etat)->toBe(EtatCommande::Transmise)
            ->and($commande->client_id)->toBe($this->client->id)
            ->and($this->produit->stockMagasin($this->magasin->id))->toBe(1)
            ->and($this->produit->refresh()->quantite_stock_internet)->toBe(10)
            ->and(Panier::where('client_id', $this->client->id)->first()->lignes()->count())->toBe(0)
            ->and($this->client->refresh()->date_dernier_achat?->isToday())->toBeTrue();

        $this->assertDatabaseHas('commande_produit', [
            'commande_id' => $commande->id,
            'reference_produit' => $this->produit->reference_produit,
            'quantite_commandee' => 2,
            'prix_unitaire_vente' => 20,
        ]);
        $this->assertDatabaseHas('historique_etats', [
            'commande_id' => $commande->id,
            'etat' => EtatCommande::Transmise->value,
            'employe_id' => null,
        ]);
    });

    it('requires a shop', function () {
        remplirPanier($this->client, $this->produit);

        $this->actingAs($this->client)
            ->post(route('commande.store'), ['mode_livraison' => 'retrait_magasin', 'accepte_conditions' => '1'])
            ->assertSessionHasErrors('magasin_id');

        expect(Commande::count())->toBe(0);
    });

    it('cancels the validation and goes back to the cart when the shop stock is too low', function () {
        remplirPanier($this->client, $this->produit, 5);

        $this->actingAs($this->client)
            ->post(route('commande.store'), [
                'mode_livraison' => 'retrait_magasin',
                'magasin_id' => $this->magasin->id,
                'accepte_conditions' => '1',
            ])
            ->assertRedirect(route('panier.index'))
            ->assertSessionHasErrors('stock');

        expect(Commande::count())->toBe(0)
            ->and($this->produit->stockMagasin($this->magasin->id))->toBe(3)
            ->and(Panier::where('client_id', $this->client->id)->first()->lignes()->count())->toBe(1);
    });
});

describe('livraison à domicile', function () {
    it('adds the 10 € fee, ships from the warehouse of the default shop and decrements the online stock', function () {
        remplirPanier($this->client, $this->produit, 3);

        $this->actingAs($this->client)->post(route('commande.store'), [
            'mode_livraison' => ModeLivraison::LivraisonDomicile->value,
            'adresse' => '3 rue Nationale',
            'code_postal' => '59000',
            'ville' => 'Lille',
            'accepte_conditions' => '1',
        ])->assertSessionHasNoErrors();

        $commande = Commande::query()->sole();

        expect((float) $commande->frais_livraison)->toBe(10.0)
            ->and((float) $commande->montant_total)->toBe(70.0)
            ->and($commande->entrepot_id)->toBe($this->entrepot->id)
            ->and($commande->magasin_id)->toBeNull()
            ->and($commande->adresse_livraison)->toBe('3 rue Nationale, 59000 Lille')
            ->and($commande->lieuExpedition())->toBe('Entrepôt de Lyon')
            ->and($this->produit->refresh()->quantite_stock_internet)->toBe(7);
    });

    it('requires a complete address', function () {
        remplirPanier($this->client, $this->produit);

        $this->actingAs($this->client)
            ->post(route('commande.store'), ['mode_livraison' => 'livraison_domicile', 'code_postal' => '590', 'accepte_conditions' => '1'])
            ->assertSessionHasErrors(['adresse', 'code_postal', 'ville']);
    });

    it('refuses when the online stock is too low', function () {
        $this->produit->update(['quantite_stock_internet' => 1]);
        remplirPanier($this->client, $this->produit, 2);

        $this->actingAs($this->client)
            ->post(route('commande.store'), [
                'mode_livraison' => 'livraison_domicile',
                'adresse' => '3 rue Nationale',
                'code_postal' => '59000',
                'ville' => 'Lille',
                'accepte_conditions' => '1',
            ])
            ->assertRedirect(route('panier.index'))
            ->assertSessionHasErrors('stock');

        expect(Commande::count())->toBe(0)
            ->and($this->produit->refresh()->quantite_stock_internet)->toBe(1);
    });
});

it('requires the terms of sale to be accepted', function () {
    remplirPanier($this->client, $this->produit);

    $this->actingAs($this->client)
        ->post(route('commande.store'), ['mode_livraison' => 'retrait_magasin', 'magasin_id' => $this->magasin->id])
        ->assertSessionHasErrors('accepte_conditions');
});

it('rejects an unknown delivery mode', function () {
    remplirPanier($this->client, $this->produit);

    $this->actingAs($this->client)
        ->post(route('commande.store'), ['mode_livraison' => 'drone', 'accepte_conditions' => '1'])
        ->assertSessionHasErrors('mode_livraison');
});

it('keeps the price frozen in the cart even if the product price changes', function () {
    remplirPanier($this->client, $this->produit, 1);
    $this->produit->update(['prix_vente' => 99]);

    $this->actingAs($this->client)->post(route('commande.store'), [
        'mode_livraison' => 'retrait_magasin',
        'magasin_id' => $this->magasin->id,
        'accepte_conditions' => '1',
    ]);

    expect((float) Commande::query()->sole()->montant_total)->toBe(20.0);
});
