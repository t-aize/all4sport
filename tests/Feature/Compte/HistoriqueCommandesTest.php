<?php

use App\Enums\EtatCommande;
use App\Models\Client;
use App\Models\Commande;
use App\Models\Entrepot;
use App\Models\Magasin;
use App\Models\Produit;
use App\Services\WorkflowCommande;
use Inertia\Testing\AssertableInertia;

/*
 * Historique des commandes du client : liste et détail (date, produits,
 * quantités, prix unitaire, sous-total, total, lieu d'expédition, adresse).
 */

beforeEach(function () {
    $this->client = Client::factory()->create();
});

it('requires a logged in client', function () {
    $this->get(route('compte.commandes.index'))->assertRedirect(route('login'));
});

it('lists only the orders of the client, most recent first', function () {
    $ancienne = Commande::factory()->for($this->client)->create(['date_commande' => now()->subMonth()]);
    $recente = Commande::factory()->for($this->client)->create(['date_commande' => now()]);
    Commande::factory()->create();

    $this->actingAs($this->client)
        ->get(route('compte.commandes.index'))
        ->assertOk()
        ->assertInertia(fn (AssertableInertia $page) => $page
            ->component('compte/commandes/index')
            ->has('commandes.data', 2)
            ->where('commandes.data.0.id', $recente->id)
            ->where('commandes.data.0.numero', $recente->numero())
            ->where('commandes.data.1.id', $ancienne->id));
});

it('shows every detail required by the specifications', function () {
    $entrepot = Entrepot::factory()->create(['ville' => 'Marseille']);
    $commande = Commande::factory()->for($this->client)->livraisonDomicile()->create([
        'entrepot_id' => $entrepot->id,
        'adresse_livraison' => '1 Canebière, 13001 Marseille',
        'frais_livraison' => 10,
        'montant_total' => 60,
        'date_commande' => '2026-10-01',
    ]);
    $produit = Produit::factory()->create();
    $commande->produits()->attach($produit->reference_produit, ['quantite_commandee' => 2, 'prix_unitaire_vente' => 25]);
    app(WorkflowCommande::class)->transmettre($commande);

    $this->actingAs($this->client)
        ->get(route('compte.commandes.show', $commande))
        ->assertOk()
        ->assertInertia(fn (AssertableInertia $page) => $page
            ->component('compte/commandes/show')
            ->where('commande.date', '2026-10-01')
            ->where('commande.lignes.0.reference', $produit->reference_produit)
            ->where('commande.lignes.0.quantite', 2)
            ->where('commande.lignes.0.prixUnitaire', '25.00')
            ->where('commande.lignes.0.sousTotal', '50.00')
            ->where('commande.sousTotal', '50.00')
            ->where('commande.fraisLivraison', '10.00')
            ->where('commande.montantTotal', '60.00')
            ->where('commande.lieuExpedition', 'Entrepôt de Marseille')
            ->where('commande.adresseLivraison', '1 Canebière, 13001 Marseille')
            ->where('commande.etat', 'transmise')
            ->has('commande.suivi', 5)
            ->where('commande.suivi.4.etat', 'livree')
            ->where('commande.suivi.4.date', null));
});

it('shows the shop for an in-store pickup', function () {
    $magasin = Magasin::factory()->create(['ville' => 'Reims', 'adresse' => '4 place Royale']);
    $commande = Commande::factory()->for($this->client)->retraitMagasin()->create(['magasin_id' => $magasin->id, 'etat' => EtatCommande::Retiree]);

    $this->actingAs($this->client)
        ->get(route('compte.commandes.show', $commande))
        ->assertInertia(fn (AssertableInertia $page) => $page
            ->where('commande.lieuExpedition', 'Magasin de Reims')
            ->where('commande.magasin.adresse', '4 place Royale')
            ->where('commande.suivi.4.etat', 'retiree'));
});

it('hides the orders of another client', function () {
    $commande = Commande::factory()->create();

    $this->actingAs($this->client)->get(route('compte.commandes.show', $commande))->assertNotFound();
});
