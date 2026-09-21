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
use Illuminate\Support\Facades\Hash;

/*
 * Tests des factories : chaque factory doit produire des enregistrements
 * valides, dont les textes respectent la taille des colonnes. SQLite ne
 * contrôle pas la longueur des chaînes : sans ces tests, une donnée trop
 * longue ne serait détectée qu'en production avec MySQL.
 */

it('persists a valid {0} record', function (string $modele) {
    $enregistrement = $modele::factory()->create();

    $this->assertModelExists($enregistrement);
})->with([
    'Client' => [Client::class],
    'Commande' => [Commande::class],
    'Employe' => [Employe::class],
    'Enfant' => [Enfant::class],
    'Entrepot' => [Entrepot::class],
    'Fournisseur' => [Fournisseur::class],
    'HistoriqueEtat' => [HistoriqueEtat::class],
    'Magasin' => [Magasin::class],
    'Photo' => [Photo::class],
    'Produit' => [Produit::class],
    'Rayon' => [Rayon::class],
]);

it('keeps the text of {0}.{1} within {2} characters', function (string $modele, string $colonne, int $tailleMax) {
    $enregistrements = $modele::factory()->count(10)->create();

    $enregistrements->each(fn ($enregistrement) => expect(mb_strlen((string) $enregistrement->{$colonne}))
        ->toBeLessThanOrEqual($tailleMax));
})->with([
    'clients.nom' => [Client::class, 'nom', 30],
    'clients.prenom' => [Client::class, 'prenom', 30],
    'clients.email' => [Client::class, 'email', 80],
    'clients.telephone' => [Client::class, 'telephone', 15],
    'clients.adresse' => [Client::class, 'adresse', 50],
    'clients.code_postal' => [Client::class, 'code_postal', 5],
    'clients.ville' => [Client::class, 'ville', 30],
    'employes.nom' => [Employe::class, 'nom', 30],
    'employes.prenom' => [Employe::class, 'prenom', 30],
    'employes.identifiant_connexion' => [Employe::class, 'identifiant_connexion', 20],
    'entrepots.ville' => [Entrepot::class, 'ville', 30],
    'entrepots.zone_expedition' => [Entrepot::class, 'zone_expedition', 50],
    'fournisseurs.nom_fournisseur' => [Fournisseur::class, 'nom_fournisseur', 40],
    'rayons.nom_rayon' => [Rayon::class, 'nom_rayon', 30],
    'magasins.ville' => [Magasin::class, 'ville', 30],
    'magasins.adresse' => [Magasin::class, 'adresse', 60],
    'produits.reference_produit' => [Produit::class, 'reference_produit', 18],
    'produits.description_produit' => [Produit::class, 'description_produit', 500],
    'produits.lieu_stockage' => [Produit::class, 'lieu_stockage', 15],
    'photos.url_photo' => [Photo::class, 'url_photo', 255],
    'commandes.adresse_livraison' => [Commande::class, 'adresse_livraison', 100],
]);

it('generates a five digit postal code for a client', function () {
    $clients = Client::factory()->count(10)->create();

    $clients->each(fn (Client $client) => expect($client->code_postal)->toMatch('/^\d{5}$/'));
});

it('generates a product reference of six letters followed by twelve digits', function () {
    $produits = Produit::factory()->count(10)->create();

    $produits->each(fn (Produit $produit) => expect($produit->reference_produit)->toMatch('/^[A-Z]{6}\d{12}$/'));
});

it('generates a sale price higher than the unit cost', function () {
    $produits = Produit::factory()
        ->count(20)
        ->recycle(Fournisseur::factory()->create())
        ->recycle(Rayon::factory()->create())
        ->create();

    $produits->each(fn (Produit $produit) => expect((float) $produit->prix_vente)
        ->toBeGreaterThan((float) $produit->cout_unitaire_ht));
});

it('generates children who are minors', function () {
    $enfants = Enfant::factory()->count(10)->create();

    $enfants->each(fn (Enfant $enfant) => expect($enfant->date_naissance->age)->toBeLessThan(18));
});

it('generates adult clients', function () {
    $clients = Client::factory()->count(10)->create();

    $clients->each(fn (Client $client) => expect($client->date_naissance->age)->toBeGreaterThanOrEqual(18));
});

it('gives every generated client and employee the same known password', function () {
    $client = Client::factory()->create();
    $employe = Employe::factory()->create();

    expect(Hash::check('password', $client->password))->toBeTrue()
        ->and(Hash::check('password', $employe->password))->toBeTrue();
});

it('can create fifteen different sections in a row', function () {
    $rayons = Rayon::factory()->count(15)->create();

    expect($rayons->pluck('nom_rayon')->unique())->toHaveCount(15);
});

it('creates the parent records needed by a child factory', function () {
    $photo = Photo::factory()->create();
    $commande = Commande::factory()->create();
    $magasin = Magasin::factory()->create();

    expect($photo->produit)->toBeInstanceOf(Produit::class)
        ->and($commande->client)->toBeInstanceOf(Client::class)
        ->and($magasin->entrepot)->toBeInstanceOf(Entrepot::class);
});
