<?php

use App\Enums\RoleEmploye;
use App\Models\Employe;
use App\Models\Fournisseur;
use App\Models\Produit;
use Inertia\Testing\AssertableInertia;

/*
 * CRUD des fournisseurs du back-office (opérateurs et vendeurs).
 */

beforeEach(function () {
    $this->operateur = Employe::factory()->avecRole(RoleEmploye::Operateur)->create();
});

it('is reserved to operators and sellers', function (RoleEmploye $role, int $statut) {
    $this->actingAs(Employe::factory()->avecRole($role)->create(), 'employe')
        ->get(route('backoffice.fournisseurs.index'))
        ->assertStatus($statut);
})->with([
    'opérateur' => [RoleEmploye::Operateur, 200],
    'vendeur' => [RoleEmploye::Vendeur, 200],
    'livreur' => [RoleEmploye::Livreur, 403],
    'service client' => [RoleEmploye::ServiceClient, 403],
]);

it('lists the suppliers with their number of products and searches them', function () {
    $fournisseur = Fournisseur::factory()->create(['nom_fournisseur' => 'Velocita', 'code_fournisseur' => 'VEL']);
    Produit::factory(2)->create(['fournisseur_id' => $fournisseur->id]);
    Fournisseur::factory()->create(['nom_fournisseur' => 'Nordik', 'code_fournisseur' => 'NOR']);

    $this->actingAs($this->operateur, 'employe')
        ->get(route('backoffice.fournisseurs.index', ['recherche' => 'velo']))
        ->assertInertia(fn (AssertableInertia $page) => $page
            ->component('backoffice/fournisseurs/index')
            ->has('fournisseurs.data', 1)
            ->where('fournisseurs.data.0.code', 'VEL')
            ->where('fournisseurs.data.0.nombreProduits', 2));
});

it('creates a supplier and deduces its code from the name', function () {
    $this->actingAs($this->operateur, 'employe')
        ->post(route('backoffice.fournisseurs.store'), ['nom_fournisseur' => 'Équipe Sud', 'code_fournisseur' => ''])
        ->assertRedirect(route('backoffice.fournisseurs.index'));

    $this->assertDatabaseHas('fournisseurs', ['nom_fournisseur' => 'Équipe Sud', 'code_fournisseur' => 'EQU']);
});

it('rejects invalid values', function (array $donnees, string $champ) {
    Fournisseur::factory()->create(['code_fournisseur' => 'PRI']);

    $this->actingAs($this->operateur, 'employe')
        ->post(route('backoffice.fournisseurs.store'), $donnees)
        ->assertSessionHasErrors($champ);
})->with([
    'nom manquant' => [['nom_fournisseur' => '', 'code_fournisseur' => 'ABC'], 'nom_fournisseur'],
    'code déjà pris' => [['nom_fournisseur' => 'Prisma', 'code_fournisseur' => 'PRI'], 'code_fournisseur'],
    'code trop long' => [['nom_fournisseur' => 'Abcd', 'code_fournisseur' => 'ABCD'], 'code_fournisseur'],
    'code avec chiffre' => [['nom_fournisseur' => 'Abcd', 'code_fournisseur' => 'AB1'], 'code_fournisseur'],
]);

it('updates a supplier', function () {
    $fournisseur = Fournisseur::factory()->create(['code_fournisseur' => 'ANC']);

    $this->actingAs($this->operateur, 'employe')
        ->put(route('backoffice.fournisseurs.update', $fournisseur), ['nom_fournisseur' => 'Nouveau nom', 'code_fournisseur' => 'NOU'])
        ->assertRedirect(route('backoffice.fournisseurs.index'));

    expect($fournisseur->refresh()->nom_fournisseur)->toBe('Nouveau nom')
        ->and($fournisseur->code_fournisseur)->toBe('NOU');
});

it('freezes the code once products use it', function () {
    $fournisseur = Fournisseur::factory()->create(['code_fournisseur' => 'FIG']);
    Produit::factory()->create(['fournisseur_id' => $fournisseur->id]);

    $this->actingAs($this->operateur, 'employe')
        ->put(route('backoffice.fournisseurs.update', $fournisseur), ['nom_fournisseur' => 'Figé', 'code_fournisseur' => 'AUT'])
        ->assertSessionHasErrors('code_fournisseur');
});

it('deletes a supplier without products but refuses otherwise', function () {
    $libre = Fournisseur::factory()->create();
    $utilise = Fournisseur::factory()->create();
    Produit::factory()->create(['fournisseur_id' => $utilise->id]);

    $this->actingAs($this->operateur, 'employe')->delete(route('backoffice.fournisseurs.destroy', $libre));
    $this->actingAs($this->operateur, 'employe')
        ->delete(route('backoffice.fournisseurs.destroy', $utilise))
        ->assertSessionHasErrors('fournisseur');

    $this->assertModelMissing($libre);
    $this->assertModelExists($utilise);
});
