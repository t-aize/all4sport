<?php

use App\Enums\ModeLivraison;
use App\Models\Client;
use App\Models\Commande;
use App\Models\Employe;
use App\Models\Entrepot;
use App\Models\HistoriqueEtat;
use App\Models\Magasin;
use App\Models\Produit;
use Carbon\CarbonInterface;

/*
 * Tests du modèle Commande : conversions de types (dates, montants, mode de
 * livraison), relations et lignes de commande (table pivot commande_produit).
 */

it('casts the delivery mode to the ModeLivraison enum', function (ModeLivraison $mode) {
    $commande = Commande::factory()->create(['mode_livraison' => $mode]);

    expect($commande->refresh()->mode_livraison)->toBe($mode);
})->with(ModeLivraison::cases());

it('casts the order date to a date', function () {
    $commande = Commande::factory()->create(['date_commande' => '2026-05-17']);

    $commande->refresh();

    expect($commande->date_commande)->toBeInstanceOf(CarbonInterface::class)
        ->and($commande->date_commande->toDateString())->toBe('2026-05-17');
});

it('formats the delivery fee and the total amount with two decimals', function () {
    $commande = Commande::factory()->create([
        'frais_livraison' => 10,
        'montant_total' => 149.9,
    ]);

    $commande->refresh();

    expect($commande->frais_livraison)->toBe('10.00')
        ->and($commande->montant_total)->toBe('149.90');
});

it('has no delivery fee by default', function () {
    $commande = Commande::query()->create([
        'date_commande' => '2026-05-17',
        'mode_livraison' => ModeLivraison::RetraitMagasin,
        'montant_total' => 50,
        'client_id' => Client::factory()->create()->id,
    ]);

    expect($commande->refresh()->frais_livraison)->toBe('0.00');
});

it('belongs to its client', function () {
    $client = Client::factory()->create();
    $commande = Commande::factory()->for($client)->create();

    expect($commande->client->is($client))->toBeTrue();
});

it('belongs to the employee, the shop and the warehouse that handle it', function () {
    $employe = Employe::factory()->create();
    $magasin = Magasin::factory()->create();
    $entrepot = Entrepot::factory()->create();

    $commande = Commande::factory()->create([
        'employe_id' => $employe->id,
        'magasin_id' => $magasin->id,
        'entrepot_id' => $entrepot->id,
    ]);

    expect($commande->employe->is($employe))->toBeTrue()
        ->and($commande->magasin->is($magasin))->toBeTrue()
        ->and($commande->entrepot->is($entrepot))->toBeTrue();
});

it('has no employee, shop or warehouse until one is assigned', function () {
    $commande = Commande::factory()->create();

    expect($commande->employe)->toBeNull()
        ->and($commande->magasin)->toBeNull()
        ->and($commande->entrepot)->toBeNull();
});

it('lists its order lines with the quantity and the unit price at the time of the sale', function () {
    $commande = Commande::factory()->create();
    $produit = Produit::factory()->create(['prix_vente' => 30]);

    $commande->produits()->attach($produit->reference_produit, [
        'quantite_commandee' => 2,
        'prix_unitaire_vente' => 25.5,
    ]);

    $produit->update(['prix_vente' => 45]);

    $ligne = $commande->fresh()->produits->first();

    expect($ligne->is($produit))->toBeTrue()
        ->and((int) $ligne->pivot->quantite_commandee)->toBe(2)
        ->and((float) $ligne->pivot->prix_unitaire_vente)->toBe(25.5);
});

it('lists its state changes', function () {
    $commande = Commande::factory()->create();
    $historique = HistoriqueEtat::factory()->create(['commande_id' => $commande->id]);
    HistoriqueEtat::factory()->create();

    expect($commande->historiqueEtats->pluck('id')->all())->toBe([$historique->id]);
});

it('builds a home delivery order with the delivery fee and an address', function () {
    $commande = Commande::factory()->livraisonDomicile()->create();

    expect($commande->mode_livraison)->toBe(ModeLivraison::LivraisonDomicile)
        ->and($commande->frais_livraison)->toBe('10.00')
        ->and($commande->adresse_livraison)->not->toBeNull()
        ->and($commande->magasin_id)->toBeNull();
});

it('builds a shop pickup order with no delivery fee and no address', function () {
    $commande = Commande::factory()->retraitMagasin()->create();

    expect($commande->mode_livraison)->toBe(ModeLivraison::RetraitMagasin)
        ->and($commande->frais_livraison)->toBe('0.00')
        ->and($commande->adresse_livraison)->toBeNull()
        ->and($commande->entrepot_id)->toBeNull();
});
