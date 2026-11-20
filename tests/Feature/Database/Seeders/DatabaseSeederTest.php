<?php

use App\Enums\EtatCommande;
use App\Enums\ModeLivraison;
use App\Enums\RoleEmploye;
use App\Models\Client;
use App\Models\Commande;
use App\Models\Employe;
use App\Models\Entrepot;
use App\Models\Fournisseur;
use App\Models\HistoriqueEtat;
use App\Models\Magasin;
use App\Models\Produit;
use App\Models\Rayon;
use Database\Seeders\CatalogueSeeder;
use Database\Seeders\DatabaseSeeder;
use Database\Seeders\ReseauSeeder;

/*
 * Tests du seeder de démonstration. Les données sont générées au hasard : les
 * tests vérifient donc des invariants (des règles vraies quelle que soit la
 * valeur tirée) et jamais des valeurs précises.
 */

it('creates the expected volume of demo data', function () {
    $this->seed(DatabaseSeeder::class);

    expect(Fournisseur::count())->toBe(count(CatalogueSeeder::FOURNISSEURS))
        ->and(Rayon::count())->toBe(count(CatalogueSeeder::RAYONS))
        ->and(Entrepot::count())->toBe(3)
        ->and(Magasin::count())->toBe(12)
        ->and(Employe::count())->toBe(16)
        ->and(Client::count())->toBe(DatabaseSeeder::NOMBRE_CLIENTS + 1)
        ->and(Produit::count())->toBeGreaterThanOrEqual(100)
        ->and(Commande::count())->toBe(DatabaseSeeder::NOMBRE_COMMANDES + 7);
});

it('creates at least one employee for each role', function (RoleEmploye $role) {
    $this->seed(DatabaseSeeder::class);

    expect(Employe::where('role', $role)->count())->toBeGreaterThanOrEqual(1);
})->with(RoleEmploye::cases());

it('gives each client between one and three sports', function () {
    $this->seed(DatabaseSeeder::class);

    $clients = Client::withCount('rayons')->get();

    expect($clients->min('rayons_count'))->toBeGreaterThanOrEqual(1)
        ->and($clients->max('rayons_count'))->toBeLessThanOrEqual(3);
});

it('gives each product between one and five photos', function () {
    $this->seed(DatabaseSeeder::class);

    $produits = Produit::withCount('photos')->get();

    expect($produits->min('photos_count'))->toBeGreaterThanOrEqual(1)
        ->and($produits->max('photos_count'))->toBeLessThanOrEqual(5);
});

it('stocks each product in at least one shop', function () {
    $this->seed(DatabaseSeeder::class);

    expect(Produit::withCount('magasins')->get()->min('magasins_count'))->toBeGreaterThanOrEqual(1);
});

it('gives each order at least one line priced like the product', function () {
    $this->seed(DatabaseSeeder::class);

    Commande::with('produits')->get()->each(function (Commande $commande): void {
        expect($commande->produits)->not->toBeEmpty();

        $commande->produits->each(fn (Produit $produit) => expect((float) $produit->pivot->prix_unitaire_vente)
            ->toEqualWithDelta((float) $produit->prix_vente, 0.001));
    });
});

it('sets each order total to the sum of its lines plus the delivery fee', function () {
    $this->seed(DatabaseSeeder::class);

    Commande::with('produits')->get()->each(function (Commande $commande): void {
        $sousTotal = $commande->produits->sum(
            fn (Produit $produit) => $produit->pivot->quantite_commandee * (float) $produit->pivot->prix_unitaire_vente
        );

        expect((float) $commande->montant_total)
            ->toEqualWithDelta($sousTotal + (float) $commande->frais_livraison, 0.001);
    });
});

it('charges ten euros and ships from a warehouse for home deliveries', function () {
    $this->seed(DatabaseSeeder::class);

    $commandes = Commande::where('mode_livraison', ModeLivraison::LivraisonDomicile)->get();

    expect($commandes)->not->toBeEmpty();

    $commandes->each(fn (Commande $commande) => expect((float) $commande->frais_livraison)->toBe(10.0)
        ->and($commande->adresse_livraison)->not->toBeNull()
        ->and($commande->entrepot_id)->not->toBeNull()
        ->and($commande->magasin_id)->toBeNull());
});

it('is free and picked up in a shop for shop pickups', function () {
    $this->seed(DatabaseSeeder::class);

    $commandes = Commande::where('mode_livraison', ModeLivraison::RetraitMagasin)->get();

    expect($commandes)->not->toBeEmpty();

    $commandes->each(fn (Commande $commande) => expect((float) $commande->frais_livraison)->toBe(0.0)
        ->and($commande->adresse_livraison)->toBeNull()
        ->and($commande->magasin_id)->not->toBeNull()
        ->and($commande->entrepot_id)->toBeNull());
});

it('starts every order history with the transmitted state and never repeats a state', function () {
    $this->seed(DatabaseSeeder::class);

    Commande::with('historiqueEtats')->get()->each(function (Commande $commande): void {
        $etats = $commande->historiqueEtats->sortBy('id')->pluck('etat');

        expect($etats->first())->toBe(EtatCommande::Transmise)
            ->and($etats->unique()->count())->toBe($etats->count());
    });
});

it('records the states of an order in chronological order', function () {
    $this->seed(DatabaseSeeder::class);

    Commande::with('historiqueEtats')->get()->each(function (Commande $commande): void {
        $dates = $commande->historiqueEtats->sortBy('id')->pluck('date_etat');

        expect($dates->sort()->values()->all())->toEqual($dates->values()->all());
    });
});

it('never ends a home delivery as picked up nor a shop pickup as delivered', function () {
    $this->seed(DatabaseSeeder::class);

    HistoriqueEtat::with('commande')->get()->each(function (HistoriqueEtat $historique): void {
        match ($historique->commande->mode_livraison) {
            ModeLivraison::LivraisonDomicile => expect($historique->etat)->not->toBe(EtatCommande::Retiree),
            ModeLivraison::RetraitMagasin => expect($historique->etat)->not->toBe(EtatCommande::Livree),
        };
    });
});

it('lets only the employee whose role matches record a state', function () {
    $this->seed(DatabaseSeeder::class);

    $roleAttendu = [
        EtatCommande::Transmise->value => null,
        EtatCommande::Validee->value => RoleEmploye::ServiceClient,
        EtatCommande::EnPreparation->value => RoleEmploye::Operateur,
        EtatCommande::Expediee->value => RoleEmploye::Operateur,
        EtatCommande::Livree->value => RoleEmploye::Livreur,
        EtatCommande::Retiree->value => RoleEmploye::Vendeur,
    ];

    HistoriqueEtat::with('employe')->get()->each(function (HistoriqueEtat $historique) use ($roleAttendu): void {
        expect($historique->employe?->role)->toBe($roleAttendu[$historique->etat->value]);
    });
});

it('creates a demo account for each role, named after the role, with a known password', function (RoleEmploye $role) {
    $this->seed(DatabaseSeeder::class);

    $employe = Employe::where('identifiant_connexion', $role->value)->first();

    expect($employe)->not->toBeNull()
        ->and($employe->role)->toBe($role)
        ->and(Hash::check('password', $employe->password))->toBeTrue();
})->with(RoleEmploye::cases());

it('creates the 3 warehouses and the 12 shops of the context file', function () {
    $this->seed(DatabaseSeeder::class);

    expect(Entrepot::pluck('ville')->sort()->values()->all())->toBe(['Le Havre', 'Lyon', 'Marseille'])
        ->and(Magasin::pluck('ville')->sort()->values()->all())->toBe(collect(array_keys(ReseauSeeder::MAGASINS))->sort()->values()->all());
});

it('gives every product a reference built from the supplier and rayon codes', function () {
    $this->seed(DatabaseSeeder::class);

    Produit::with(['fournisseur', 'rayon'])->get()->each(fn (Produit $produit) => expect($produit->reference_produit)
        ->toMatch('/^'.$produit->fournisseur->code_fournisseur.$produit->rayon->code_rayon.'\d{12}$/'));
});

it('keeps the current state of each order equal to its last recorded state', function () {
    $this->seed(DatabaseSeeder::class);

    Commande::with('historiqueEtats')->get()->each(fn (Commande $commande) => expect($commande->historiqueEtats->sortBy('id')->last()->etat)
        ->toBe($commande->etat));
});

it('creates a demo client with orders in every state and a cart', function () {
    $this->seed(DatabaseSeeder::class);

    $client = Client::where('email', DatabaseSeeder::EMAIL_CLIENT_DEMO)->firstOrFail();

    expect(Hash::check('password', $client->password))->toBeTrue()
        ->and($client->magasin_id)->not->toBeNull()
        ->and($client->commandes()->distinct()->pluck('etat')->count())->toBe(6)
        ->and($client->paniers()->first()?->lignes()->count())->toBe(3);
});
