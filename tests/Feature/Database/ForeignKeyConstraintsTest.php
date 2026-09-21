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
 * Tests des clés étrangères : intégrité référentielle et comportement à la
 * suppression (restrict = suppression refusée, cascade = suppression des
 * dépendances, nullOnDelete = le lien est simplement vidé).
 */

describe('references to missing records', function () {
    it('rejects an order for a client that does not exist', function () {
        expect(fn () => Commande::factory()->create(['client_id' => 999]))
            ->toThrow(QueryException::class);

        expect(Commande::count())->toBe(0);
    });

    it('rejects a product for a supplier that does not exist', function () {
        expect(fn () => Produit::factory()->create(['fournisseur_id' => 999]))
            ->toThrow(QueryException::class);

        expect(Produit::count())->toBe(0);
    });

    it('rejects a photo for a product reference that does not exist', function () {
        expect(fn () => Photo::factory()->create(['reference_produit' => 'INCONNUE0000000000']))
            ->toThrow(QueryException::class);

        expect(Photo::count())->toBe(0);
    });
});

describe('restricted deletions', function () {
    it('refuses to delete a warehouse that still supplies a shop', function () {
        $magasin = Magasin::factory()->create();

        expect(fn () => $magasin->entrepot->delete())->toThrow(QueryException::class);

        $this->assertModelExists($magasin->entrepot);
    });

    it('refuses to delete a supplier that still has products', function () {
        $produit = Produit::factory()->create();

        expect(fn () => $produit->fournisseur->delete())->toThrow(QueryException::class);

        $this->assertModelExists($produit->fournisseur);
    });

    it('refuses to delete a section that still has products', function () {
        $produit = Produit::factory()->create();

        expect(fn () => $produit->rayon->delete())->toThrow(QueryException::class);

        $this->assertModelExists($produit->rayon);
    });

    it('refuses to delete a client who has placed orders', function () {
        $commande = Commande::factory()->create();

        expect(fn () => $commande->client->delete())->toThrow(QueryException::class);

        $this->assertModelExists($commande->client);
    });

    it('refuses to delete a product that appears on an order', function () {
        $produit = Produit::factory()->create();
        $commande = Commande::factory()->create();
        $commande->produits()->attach($produit->reference_produit, [
            'quantite_commandee' => 1,
            'prix_unitaire_vente' => 10,
        ]);

        expect(fn () => $produit->delete())->toThrow(QueryException::class);

        $this->assertModelExists($produit);
    });
});

describe('cascading deletions', function () {
    it('deletes the children of a deleted client', function () {
        $client = Client::factory()->create();
        $enfant = Enfant::factory()->for($client)->create();
        $autreEnfant = Enfant::factory()->create();

        $client->delete();

        $this->assertModelMissing($enfant);
        $this->assertModelExists($autreEnfant);
    });

    it('deletes the sports of a deleted client without deleting the sections', function () {
        $client = Client::factory()->create();
        $rayon = Rayon::factory()->create();
        $client->rayons()->attach($rayon);

        $client->delete();

        $this->assertDatabaseMissing('client_rayon', ['rayon_id' => $rayon->id]);
        $this->assertModelExists($rayon);
    });

    it('deletes the practicing links of a deleted section without deleting the clients', function () {
        $client = Client::factory()->create();
        $rayon = Rayon::factory()->create();
        $client->rayons()->attach($rayon);

        $rayon->delete();

        $this->assertDatabaseMissing('client_rayon', ['client_id' => $client->id]);
        $this->assertModelExists($client);
    });

    it('deletes the photos and the shop stocks of a deleted product', function () {
        $produit = Produit::factory()->create();
        $photo = Photo::factory()->create(['reference_produit' => $produit->reference_produit]);
        $magasin = Magasin::factory()->create();
        $magasin->produits()->attach($produit->reference_produit, ['quantite_stock_magasin' => 4]);

        $produit->delete();

        $this->assertModelMissing($photo);
        $this->assertDatabaseMissing('magasin_produit', ['reference_produit' => $produit->reference_produit]);
        $this->assertModelExists($magasin);
    });

    it('deletes the stock lines of a deleted shop without deleting the products', function () {
        $produit = Produit::factory()->create();
        $magasin = Magasin::factory()->create();
        $magasin->produits()->attach($produit->reference_produit, ['quantite_stock_magasin' => 4]);

        $magasin->delete();

        $this->assertDatabaseMissing('magasin_produit', ['magasin_id' => $magasin->id]);
        $this->assertModelExists($produit);
    });

    it('deletes the lines and the state history of a deleted order', function () {
        $produit = Produit::factory()->create();
        $commande = Commande::factory()->create();
        $commande->produits()->attach($produit->reference_produit, [
            'quantite_commandee' => 1,
            'prix_unitaire_vente' => 10,
        ]);
        $historique = HistoriqueEtat::factory()->create(['commande_id' => $commande->id]);

        $commande->delete();

        $this->assertDatabaseMissing('commande_produit', ['commande_id' => $commande->id]);
        $this->assertModelMissing($historique);
        $this->assertModelExists($produit);
    });
});

describe('nullified links', function () {
    it('keeps the orders and the state history of a deleted employee', function () {
        $employe = Employe::factory()->create();
        $commande = Commande::factory()->create(['employe_id' => $employe->id]);
        $historique = HistoriqueEtat::factory()->create(['employe_id' => $employe->id]);

        $employe->delete();

        expect($commande->refresh()->employe_id)->toBeNull()
            ->and($historique->refresh()->employe_id)->toBeNull();
    });

    it('keeps the orders of a deleted shop', function () {
        $magasin = Magasin::factory()->create();
        $commande = Commande::factory()->retraitMagasin()->create(['magasin_id' => $magasin->id]);

        $magasin->delete();

        expect($commande->refresh()->magasin_id)->toBeNull();
    });

    it('keeps the orders of a deleted warehouse', function () {
        $entrepot = Entrepot::factory()->create();
        $commande = Commande::factory()->livraisonDomicile()->create(['entrepot_id' => $entrepot->id]);

        $entrepot->delete();

        expect($commande->refresh()->entrepot_id)->toBeNull();
    });
});

it('keeps the products of another supplier when a supplier without products is deleted', function () {
    $produit = Produit::factory()->create();
    $fournisseurInutilise = Fournisseur::factory()->create();

    $fournisseurInutilise->delete();

    $this->assertModelMissing($fournisseurInutilise);
    $this->assertModelExists($produit);
});
