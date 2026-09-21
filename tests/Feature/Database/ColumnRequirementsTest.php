<?php

use App\Models\Client;
use App\Models\Commande;
use App\Models\Employe;
use App\Models\Enfant;
use App\Models\Entrepot;
use App\Models\Fournisseur;
use App\Models\HistoriqueEtat;
use App\Models\Magasin;
use App\Models\Photo;
use App\Models\Produit;
use App\Models\Rayon;
use Illuminate\Database\QueryException;

/*
 * Tests des colonnes obligatoires, facultatives et des valeurs par défaut :
 * la base doit refuser une ligne incomplète et accepter les champs vides
 * prévus par le modèle de données.
 */

it('rejects a row without the required column {1} in {0}', function (string $modele, string $colonne) {
    $enregistrement = $modele::factory()->make();
    unset($enregistrement->{$colonne});

    expect(fn () => $enregistrement->save())->toThrow(QueryException::class);

    expect($modele::count())->toBe(0);
})->with([
    'fournisseurs.nom_fournisseur' => [Fournisseur::class, 'nom_fournisseur'],
    'rayons.nom_rayon' => [Rayon::class, 'nom_rayon'],
    'entrepots.ville' => [Entrepot::class, 'ville'],
    'magasins.ville' => [Magasin::class, 'ville'],
    'magasins.adresse' => [Magasin::class, 'adresse'],
    'magasins.entrepot_id' => [Magasin::class, 'entrepot_id'],
    'employes.nom' => [Employe::class, 'nom'],
    'employes.prenom' => [Employe::class, 'prenom'],
    'employes.identifiant_connexion' => [Employe::class, 'identifiant_connexion'],
    'employes.password' => [Employe::class, 'password'],
    'employes.role' => [Employe::class, 'role'],
    'clients.nom' => [Client::class, 'nom'],
    'clients.prenom' => [Client::class, 'prenom'],
    'clients.email' => [Client::class, 'email'],
    'clients.adresse' => [Client::class, 'adresse'],
    'clients.code_postal' => [Client::class, 'code_postal'],
    'clients.ville' => [Client::class, 'ville'],
    'clients.password' => [Client::class, 'password'],
    'enfants.date_naissance' => [Enfant::class, 'date_naissance'],
    'enfants.client_id' => [Enfant::class, 'client_id'],
    'produits.description_produit' => [Produit::class, 'description_produit'],
    'produits.cout_unitaire_ht' => [Produit::class, 'cout_unitaire_ht'],
    'produits.prix_vente' => [Produit::class, 'prix_vente'],
    'produits.fournisseur_id' => [Produit::class, 'fournisseur_id'],
    'produits.rayon_id' => [Produit::class, 'rayon_id'],
    'photos.url_photo' => [Photo::class, 'url_photo'],
    'photos.reference_produit' => [Photo::class, 'reference_produit'],
    'commandes.date_commande' => [Commande::class, 'date_commande'],
    'commandes.mode_livraison' => [Commande::class, 'mode_livraison'],
    'commandes.montant_total' => [Commande::class, 'montant_total'],
    'commandes.client_id' => [Commande::class, 'client_id'],
    'historique_etats.etat' => [HistoriqueEtat::class, 'etat'],
    'historique_etats.date_etat' => [HistoriqueEtat::class, 'date_etat'],
    'historique_etats.commande_id' => [HistoriqueEtat::class, 'commande_id'],
]);

it('accepts an empty value for the optional column {1} in {0}', function (string $modele, string $colonne) {
    $enregistrement = $modele::factory()->create([$colonne => null]);

    $this->assertModelExists($enregistrement);
    expect($enregistrement->refresh()->{$colonne})->toBeNull();
})->with([
    'clients.telephone' => [Client::class, 'telephone'],
    'clients.date_naissance' => [Client::class, 'date_naissance'],
    'clients.date_dernier_achat' => [Client::class, 'date_dernier_achat'],
    'entrepots.zone_expedition' => [Entrepot::class, 'zone_expedition'],
    'produits.lieu_stockage' => [Produit::class, 'lieu_stockage'],
    'commandes.adresse_livraison' => [Commande::class, 'adresse_livraison'],
    'commandes.employe_id' => [Commande::class, 'employe_id'],
    'commandes.magasin_id' => [Commande::class, 'magasin_id'],
    'commandes.entrepot_id' => [Commande::class, 'entrepot_id'],
    'historique_etats.employe_id' => [HistoriqueEtat::class, 'employe_id'],
]);

it('starts with an empty stock when a product is added to a shop without a quantity', function () {
    $magasin = Magasin::factory()->create();
    $produit = Produit::factory()->create();

    $magasin->produits()->attach($produit->reference_produit);

    expect((int) $magasin->produits()->first()->pivot->quantite_stock_magasin)->toBe(0);
});

it('rejects a product added to an order without a quantity', function () {
    $commande = Commande::factory()->create();
    $produit = Produit::factory()->create();

    expect(fn () => $commande->produits()->attach($produit->reference_produit, ['prix_unitaire_vente' => 10]))
        ->toThrow(QueryException::class);

    expect($commande->produits()->count())->toBe(0);
});

it('rejects a product added to an order without a unit price', function () {
    $commande = Commande::factory()->create();
    $produit = Produit::factory()->create();

    expect(fn () => $commande->produits()->attach($produit->reference_produit, ['quantite_commandee' => 1]))
        ->toThrow(QueryException::class);

    expect($commande->produits()->count())->toBe(0);
});
