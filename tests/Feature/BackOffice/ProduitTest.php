<?php

use App\Enums\RoleEmploye;
use App\Models\Client;
use App\Models\Commande;
use App\Models\Employe;
use App\Models\Fournisseur;
use App\Models\Magasin;
use App\Models\Photo;
use App\Models\Produit;
use App\Models\Rayon;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Inertia\Testing\AssertableInertia;

/*
 * Tests de la gestion des produits du back-office : accès réservé aux
 * opérateurs et aux vendeurs, liste, création, modification, suppression,
 * stock par magasin et photos (de 1 à 5 par produit).
 */

beforeEach(function () {
    Storage::fake('public');
});

/**
 * Les champs valides du formulaire produit, modifiables par chaque test.
 *
 * @param  array<string, mixed>  $surcharges
 * @return array<string, mixed>
 */
function donneesProduit(array $surcharges = []): array
{
    return [
        'description_produit' => 'Raquette de tennis en carbone',
        'cout_unitaire_ht' => '45.50',
        'prix_vente' => '89.90',
        'quantite_stock_internet' => 12,
        'lieu_stockage' => 'M2D3.5',
        'fournisseur_id' => Fournisseur::factory()->create()->id,
        'rayon_id' => Rayon::factory()->create()->id,
        'photos' => [UploadedFile::fake()->image('photo-1.jpg')],
        ...$surcharges,
    ];
}

/**
 * Se connecte au back-office avec le rôle donné.
 */
function connecterCommeEmploye(RoleEmploye $role = RoleEmploye::Operateur): Employe
{
    $employe = Employe::factory()->avecRole($role)->create();
    test()->actingAs($employe, 'employe');

    return $employe;
}

describe('access', function () {
    it('redirects a guest to the back-office login', function () {
        $this->get(route('backoffice.produits.index'))->assertRedirect(route('backoffice.login'));
    });

    it('lets an operator and a seller manage the products', function (RoleEmploye $role) {
        connecterCommeEmploye($role);

        $this->get(route('backoffice.produits.index'))->assertOk();
    })->with([
        'opérateur' => RoleEmploye::Operateur,
        'vendeur' => RoleEmploye::Vendeur,
    ]);

    it('forbids the other roles', function (RoleEmploye $role) {
        connecterCommeEmploye($role);
        $produit = Produit::factory()->create();

        $this->get(route('backoffice.produits.index'))->assertForbidden();
        $this->get(route('backoffice.produits.create'))->assertForbidden();
        $this->post(route('backoffice.produits.store'), donneesProduit())->assertForbidden();
        $this->delete(route('backoffice.produits.destroy', $produit))->assertForbidden();
    })->with([
        'livreur' => RoleEmploye::Livreur,
        'service client' => RoleEmploye::ServiceClient,
    ]);

    it('does not open the back-office products to a logged-in client', function () {
        $this->actingAs(Client::factory()->create())
            ->get(route('backoffice.produits.index'))
            ->assertRedirect(route('backoffice.login'));
    });
});

describe('list', function () {
    it('lists the products with their rayon, supplier, price and stock', function () {
        connecterCommeEmploye();
        $produit = Produit::factory()->create(['prix_vente' => 30, 'quantite_stock_internet' => 4]);
        Photo::factory()->count(2)->create(['reference_produit' => $produit->reference_produit]);

        $this->get(route('backoffice.produits.index'))
            ->assertInertia(fn (AssertableInertia $page) => $page
                ->component('backoffice/produits/index')
                ->where('roleLibelle', 'Opérateur')
                ->where('produits.data.0.reference', $produit->reference_produit)
                ->where('produits.data.0.rayon', $produit->rayon->nom_rayon)
                ->where('produits.data.0.fournisseur', $produit->fournisseur->nom_fournisseur)
                ->where('produits.data.0.prixVente', '30.00')
                ->where('produits.data.0.stockInternet', 4)
                ->where('produits.data.0.nombrePhotos', 2));
    });

    it('searches by description or reference', function () {
        connecterCommeEmploye();
        $velo = Produit::factory()->create(['description_produit' => 'Casque de vélo']);
        Produit::factory()->create(['description_produit' => 'Ballon de football']);

        $this->get(route('backoffice.produits.index', ['recherche' => 'vélo']))
            ->assertInertia(fn (AssertableInertia $page) => $page
                ->has('produits.data', 1)
                ->where('produits.data.0.reference', $velo->reference_produit)
                ->where('recherche', 'vélo'));

        $this->get(route('backoffice.produits.index', ['recherche' => $velo->reference_produit]))
            ->assertInertia(fn (AssertableInertia $page) => $page->has('produits.data', 1));
    });

    it('paginates the products', function () {
        connecterCommeEmploye();
        Produit::factory()->count(16)->for(Rayon::factory())->for(Fournisseur::factory())->create();

        $this->get(route('backoffice.produits.index'))
            ->assertInertia(fn (AssertableInertia $page) => $page
                ->has('produits.data', 15)
                ->where('produits.total', 16));
    });
});

describe('create', function () {
    it('shows the form with the suppliers, rayons and shops', function () {
        connecterCommeEmploye();
        Fournisseur::factory()->count(2)->create();
        Rayon::factory()->count(3)->create();
        Magasin::factory()->count(4)->create();

        $this->get(route('backoffice.produits.create'))
            ->assertInertia(fn (AssertableInertia $page) => $page
                ->component('backoffice/produits/create')
                ->has('fournisseurs', 2)
                ->has('rayons', 3)
                ->has('magasins', 4));
    });

    it('creates a product with a generated reference, its shop stock and its photos', function () {
        connecterCommeEmploye();
        $lille = Magasin::factory()->create();
        $arras = Magasin::factory()->create();
        $donnees = donneesProduit([
            'stocks' => [$lille->id => 7, $arras->id => ''],
            'photos' => [UploadedFile::fake()->image('a.jpg'), UploadedFile::fake()->image('b.png')],
        ]);

        $this->post(route('backoffice.produits.store'), $donnees)
            ->assertRedirect(route('backoffice.produits.index'))
            ->assertSessionHasNoErrors();

        $produit = Produit::query()->with(['photos', 'magasins'])->firstOrFail();

        expect($produit->reference_produit)->toMatch('/^[A-Z]{6}\d{12}$/')
            ->and($produit->description_produit)->toBe('Raquette de tennis en carbone')
            ->and($produit->prix_vente)->toBe('89.90')
            ->and($produit->quantite_stock_internet)->toBe(12)
            ->and($produit->lieu_stockage)->toBe('M2D3.5')
            ->and($produit->photos)->toHaveCount(2)
            ->and($produit->magasins->pluck('id')->all())->toBe([$lille->id])
            ->and($produit->magasins->first()->pivot->quantite_stock_magasin)->toBe(7);

        $produit->photos->each(fn (Photo $photo) => Storage::disk('public')->assertExists(
            substr($photo->url_photo, strlen(Storage::disk('public')->url(''))),
        ));
    });

    it('accepts a product without storage location', function () {
        connecterCommeEmploye();

        $this->post(route('backoffice.produits.store'), donneesProduit(['lieu_stockage' => null]))
            ->assertSessionHasNoErrors();

        expect(Produit::query()->firstOrFail()->lieu_stockage)->toBeNull();
    });

    it('requires the main fields', function () {
        connecterCommeEmploye();

        $this->post(route('backoffice.produits.store'), [])->assertSessionHasErrors([
            'description_produit',
            'cout_unitaire_ht',
            'prix_vente',
            'quantite_stock_internet',
            'fournisseur_id',
            'rayon_id',
        ]);

        expect(Produit::query()->count())->toBe(0);
    });

    it('rejects invalid values', function (array $surcharges, string $champ) {
        connecterCommeEmploye();

        $this->post(route('backoffice.produits.store'), donneesProduit($surcharges))->assertSessionHasErrors($champ);

        expect(Produit::query()->count())->toBe(0);
    })->with([
        'negative price' => [['prix_vente' => -1], 'prix_vente'],
        'negative cost' => [['cout_unitaire_ht' => -5], 'cout_unitaire_ht'],
        'negative stock' => [['quantite_stock_internet' => -1], 'quantite_stock_internet'],
        'fractional stock' => [['quantite_stock_internet' => 1.5], 'quantite_stock_internet'],
        'unknown supplier' => [['fournisseur_id' => 9999], 'fournisseur_id'],
        'unknown rayon' => [['rayon_id' => 9999], 'rayon_id'],
        'too long description' => [['description_produit' => str_repeat('a', 501)], 'description_produit'],
        'malformed storage location' => [['lieu_stockage' => 'Z9'], 'lieu_stockage'],
        'storage location out of the warehouse' => [['lieu_stockage' => 'M7A1.1'], 'lieu_stockage'],
        'negative shop stock' => [['stocks' => [1 => -3]], 'stocks.1'],
    ]);
});

describe('photos', function () {
    it('requires at least one photo', function () {
        connecterCommeEmploye();

        $this->post(route('backoffice.produits.store'), donneesProduit(['photos' => []]))->assertSessionHasErrors('photos');

        expect(Produit::query()->count())->toBe(0);
    });

    it('accepts up to five photos', function () {
        connecterCommeEmploye();
        $photos = array_map(fn (int $numero) => UploadedFile::fake()->image("photo-{$numero}.jpg"), range(1, 5));

        $this->post(route('backoffice.produits.store'), donneesProduit(['photos' => $photos]))->assertSessionHasNoErrors();

        expect(Photo::query()->count())->toBe(5);
    });

    it('rejects more than five photos', function () {
        connecterCommeEmploye();
        $photos = array_map(fn (int $numero) => UploadedFile::fake()->image("photo-{$numero}.jpg"), range(1, 6));

        $this->post(route('backoffice.produits.store'), donneesProduit(['photos' => $photos]))->assertSessionHasErrors('photos');

        expect(Produit::query()->count())->toBe(0);
    });

    it('rejects files that are not images or are too heavy', function (UploadedFile $fichier) {
        connecterCommeEmploye();

        $this->post(route('backoffice.produits.store'), donneesProduit(['photos' => [$fichier]]))->assertSessionHasErrors('photos.0');
    })->with([
        'pdf' => fn () => UploadedFile::fake()->create('fiche.pdf', 100, 'application/pdf'),
        'too heavy' => fn () => UploadedFile::fake()->image('lourde.jpg')->size(5000),
    ]);
});

describe('edit', function () {
    it('shows the form filled with the product, its photos and its shop stock', function () {
        connecterCommeEmploye();
        $produit = Produit::factory()->create(['quantite_stock_internet' => 9, 'lieu_stockage' => 'M1A1.1']);
        $photo = Photo::factory()->create(['reference_produit' => $produit->reference_produit, 'url_photo' => 'https://exemple.test/p.jpg']);
        $magasin = Magasin::factory()->create();
        $produit->magasins()->attach($magasin->id, ['quantite_stock_magasin' => 3]);

        $this->get(route('backoffice.produits.edit', $produit))
            ->assertInertia(fn (AssertableInertia $page) => $page
                ->component('backoffice/produits/edit')
                ->where('produit.reference', $produit->reference_produit)
                ->where('produit.quantite_stock_internet', 9)
                ->where('produit.lieu_stockage', 'M1A1.1')
                ->where('produit.photos.0.id', $photo->id)
                ->where('produit.photos.0.url', 'https://exemple.test/p.jpg')
                ->where("produit.stocks.{$magasin->id}", 3));
    });

    it('answers 404 for an unknown reference', function () {
        connecterCommeEmploye();

        $this->get(route('backoffice.produits.edit', 'INCONNUE000000000000'))->assertNotFound();
    });
});

describe('update', function () {
    it('updates the product without changing its reference', function () {
        connecterCommeEmploye();
        $produit = Produit::factory()->create();
        Photo::factory()->create(['reference_produit' => $produit->reference_produit]);
        $reference = $produit->reference_produit;

        $this->put(route('backoffice.produits.update', $produit), donneesProduit([
            'description_produit' => 'Nouvelle description',
            'prix_vente' => '19.99',
            'photos' => [],
        ]))->assertRedirect(route('backoffice.produits.index'))->assertSessionHasNoErrors();

        $produit->refresh();

        expect($produit->reference_produit)->toBe($reference)
            ->and($produit->description_produit)->toBe('Nouvelle description')
            ->and($produit->prix_vente)->toBe('19.99');
    });

    it('replaces the shop stock and removes the shops left empty', function () {
        connecterCommeEmploye();
        $produit = Produit::factory()->create();
        Photo::factory()->create(['reference_produit' => $produit->reference_produit]);
        $lille = Magasin::factory()->create();
        $arras = Magasin::factory()->create();
        $produit->magasins()->attach($lille->id, ['quantite_stock_magasin' => 3]);

        $this->put(route('backoffice.produits.update', $produit), donneesProduit([
            'stocks' => [$lille->id => '', $arras->id => 0],
            'photos' => [],
        ]))->assertSessionHasNoErrors();

        $magasins = $produit->fresh()->magasins;

        expect($magasins->pluck('id')->all())->toBe([$arras->id])
            ->and($magasins->first()->pivot->quantite_stock_magasin)->toBe(0);
    });

    it('adds new photos to the existing ones', function () {
        connecterCommeEmploye();
        $produit = Produit::factory()->create();
        Photo::factory()->create(['reference_produit' => $produit->reference_produit]);

        $this->put(route('backoffice.produits.update', $produit), donneesProduit([
            'photos' => [UploadedFile::fake()->image('nouvelle.jpg')],
        ]))->assertSessionHasNoErrors();

        expect($produit->photos()->count())->toBe(2);
    });

    it('removes the photos asked for and deletes the uploaded files', function () {
        connecterCommeEmploye();
        $produit = Produit::factory()->create();
        $chemin = UploadedFile::fake()->image('ancienne.jpg')->store('produits', 'public');
        $aSupprimer = Photo::factory()->create([
            'reference_produit' => $produit->reference_produit,
            'url_photo' => Storage::disk('public')->url($chemin),
        ]);
        $aConserver = Photo::factory()->create(['reference_produit' => $produit->reference_produit]);

        $this->put(route('backoffice.produits.update', $produit), donneesProduit([
            'photos' => [],
            'photos_supprimees' => [$aSupprimer->id],
        ]))->assertSessionHasNoErrors();

        expect($produit->photos()->pluck('id')->all())->toBe([$aConserver->id]);
        Storage::disk('public')->assertMissing($chemin);
    });

    it('refuses to remove every photo', function () {
        connecterCommeEmploye();
        $produit = Produit::factory()->create();
        $photo = Photo::factory()->create(['reference_produit' => $produit->reference_produit]);

        $this->put(route('backoffice.produits.update', $produit), donneesProduit([
            'photos' => [],
            'photos_supprimees' => [$photo->id],
        ]))->assertSessionHasErrors('photos');

        expect($produit->photos()->count())->toBe(1);
    });

    it('refuses to go over five photos', function () {
        connecterCommeEmploye();
        $produit = Produit::factory()->create();
        Photo::factory()->count(4)->create(['reference_produit' => $produit->reference_produit]);

        $this->put(route('backoffice.produits.update', $produit), donneesProduit([
            'photos' => [UploadedFile::fake()->image('a.jpg'), UploadedFile::fake()->image('b.jpg')],
        ]))->assertSessionHasErrors('photos');

        expect($produit->photos()->count())->toBe(4);
    });

    it('ignores the photos of another product', function () {
        connecterCommeEmploye();
        $produit = Produit::factory()->create();
        Photo::factory()->create(['reference_produit' => $produit->reference_produit]);
        $autrePhoto = Photo::factory()->create();

        $this->put(route('backoffice.produits.update', $produit), donneesProduit([
            'photos' => [],
            'photos_supprimees' => [$autrePhoto->id],
        ]))->assertSessionHasNoErrors();

        expect(Photo::query()->whereKey($autrePhoto->id)->exists())->toBeTrue();
    });
});

describe('delete', function () {
    it('deletes the product with its photos, its shop stock and the uploaded files', function () {
        connecterCommeEmploye();
        $produit = Produit::factory()->create();
        $chemin = UploadedFile::fake()->image('photo.jpg')->store('produits', 'public');
        Photo::factory()->create(['reference_produit' => $produit->reference_produit, 'url_photo' => Storage::disk('public')->url($chemin)]);
        $produit->magasins()->attach(Magasin::factory()->create()->id, ['quantite_stock_magasin' => 2]);

        $this->delete(route('backoffice.produits.destroy', $produit))->assertRedirect(route('backoffice.produits.index'));

        expect(Produit::query()->count())->toBe(0)
            ->and(Photo::query()->count())->toBe(0)
            ->and(DB::table('magasin_produit')->count())->toBe(0);
        Storage::disk('public')->assertMissing($chemin);
    });

    it('refuses to delete a product that has been ordered', function () {
        connecterCommeEmploye();
        $produit = Produit::factory()->create();
        $commande = Commande::factory()->create();
        $commande->produits()->attach($produit->reference_produit, ['quantite_commandee' => 1, 'prix_unitaire_vente' => 10]);

        $this->delete(route('backoffice.produits.destroy', $produit))->assertSessionHasErrors('produit');

        expect(Produit::query()->count())->toBe(1);
    });
});
