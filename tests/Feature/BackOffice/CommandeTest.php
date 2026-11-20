<?php

use App\Enums\EtatCommande;
use App\Enums\RoleEmploye;
use App\Models\Commande;
use App\Models\Employe;
use App\Models\Magasin;
use Inertia\Testing\AssertableInertia;

/*
 * Back-office de gestion des commandes par rôle : chaque collaborateur voit
 * les commandes qu'il doit traiter et n'enregistre que les états dont son
 * rôle est responsable, dans l'ordre du workflow.
 */

/**
 * Un collaborateur du rôle donné.
 */
function collaborateur(RoleEmploye $role): Employe
{
    return Employe::factory()->avecRole($role)->create();
}

it('requires a logged in employee', function () {
    $this->get(route('backoffice.commandes.index'))->assertRedirect(route('backoffice.login'));
});

it('lists by default the orders that the role has to process', function (RoleEmploye $role, EtatCommande $etatATraiter, string $mode) {
    $aTraiter = Commande::factory()->{$mode}()->etat($etatATraiter)->create();
    Commande::factory()->livraisonDomicile()->etat(EtatCommande::Livree)->create();

    $this->actingAs(collaborateur($role), 'employe')
        ->get(route('backoffice.commandes.index'))
        ->assertOk()
        ->assertInertia(fn (AssertableInertia $page) => $page
            ->component('backoffice/commandes/index')
            ->where('filtres.filtre', 'a_traiter')
            ->has('commandes.data', 1)
            ->where('commandes.data.0.id', $aTraiter->id)
            ->where('commandes.data.0.actionPossible', true)
            ->where('compteurs.a_traiter', 1)
            ->where('compteurs.toutes', 2));
})->with([
    'service client -> transmise' => [RoleEmploye::ServiceClient, EtatCommande::Transmise, 'retraitMagasin'],
    'opérateur -> validée' => [RoleEmploye::Operateur, EtatCommande::Validee, 'livraisonDomicile'],
    'opérateur -> en préparation' => [RoleEmploye::Operateur, EtatCommande::EnPreparation, 'retraitMagasin'],
    'livreur -> expédiée à domicile' => [RoleEmploye::Livreur, EtatCommande::Expediee, 'livraisonDomicile'],
    'vendeur -> expédiée en magasin' => [RoleEmploye::Vendeur, EtatCommande::Expediee, 'retraitMagasin'],
]);

it('filters by state, delivery mode and search', function () {
    $commande = Commande::factory()->retraitMagasin()->etat(EtatCommande::Validee)->create();
    Commande::factory()->livraisonDomicile()->etat(EtatCommande::Validee)->create();
    Commande::factory()->retraitMagasin()->etat(EtatCommande::Transmise)->create();

    $this->actingAs(collaborateur(RoleEmploye::Vendeur), 'employe')
        ->get(route('backoffice.commandes.index', ['filtre' => 'validee', 'mode' => 'retrait_magasin']))
        ->assertInertia(fn (AssertableInertia $page) => $page
            ->has('commandes.data', 1)
            ->where('commandes.data.0.id', $commande->id));

    $this->actingAs(collaborateur(RoleEmploye::Vendeur), 'employe')
        ->get(route('backoffice.commandes.index', ['filtre' => 'toutes', 'recherche' => $commande->client->code_cli]))
        ->assertInertia(fn (AssertableInertia $page) => $page
            ->has('commandes.data', 1)
            ->where('commandes.data.0.id', $commande->id));
});

it('shows an order with the next state and whether the employee can record it', function () {
    $commande = Commande::factory()->etat(EtatCommande::Transmise)->create();

    $this->actingAs(collaborateur(RoleEmploye::Operateur), 'employe')
        ->get(route('backoffice.commandes.show', $commande))
        ->assertOk()
        ->assertInertia(fn (AssertableInertia $page) => $page
            ->component('backoffice/commandes/show')
            ->where('etatSuivant.value', 'validee')
            ->where('responsableSuivant', 'Service client')
            ->where('peutAvancer', false)
            ->where('commande.client.codeCli', $commande->client->code_cli));
});

it('lets each role record its own state, in the right order, with history', function () {
    $commande = Commande::factory()->retraitMagasin(Magasin::factory()->create())->etat(EtatCommande::Transmise)->create();

    $parcours = [
        [RoleEmploye::ServiceClient, EtatCommande::Validee],
        [RoleEmploye::Operateur, EtatCommande::EnPreparation],
        [RoleEmploye::Operateur, EtatCommande::Expediee],
        [RoleEmploye::Vendeur, EtatCommande::Retiree],
    ];

    foreach ($parcours as [$role, $etat]) {
        $employe = collaborateur($role);

        $this->actingAs($employe, 'employe')
            ->post(route('backoffice.commandes.etat', $commande), ['etat' => $etat->value])
            ->assertSessionHasNoErrors()
            ->assertRedirect();

        expect($commande->refresh()->etat)->toBe($etat);
        $this->assertDatabaseHas('historique_etats', [
            'commande_id' => $commande->id,
            'etat' => $etat->value,
            'employe_id' => $employe->id,
        ]);
    }
});

it('refuses a state that belongs to another role', function () {
    $commande = Commande::factory()->etat(EtatCommande::Transmise)->create();

    $this->actingAs(collaborateur(RoleEmploye::Operateur), 'employe')
        ->post(route('backoffice.commandes.etat', $commande), ['etat' => 'validee'])
        ->assertSessionHasErrors('etat');

    expect($commande->refresh()->etat)->toBe(EtatCommande::Transmise);
});

it('refuses to skip a state', function () {
    $commande = Commande::factory()->etat(EtatCommande::Validee)->create();

    $this->actingAs(collaborateur(RoleEmploye::Operateur), 'employe')
        ->post(route('backoffice.commandes.etat', $commande), ['etat' => 'expediee'])
        ->assertSessionHasErrors('etat');
});

it('refuses "livrée" for an in-store pickup and "retirée" for a home delivery', function () {
    $retrait = Commande::factory()->retraitMagasin()->etat(EtatCommande::Expediee)->create();
    $domicile = Commande::factory()->livraisonDomicile()->etat(EtatCommande::Expediee)->create();

    $this->actingAs(collaborateur(RoleEmploye::Livreur), 'employe')
        ->post(route('backoffice.commandes.etat', $retrait), ['etat' => 'livree'])
        ->assertSessionHasErrors('etat');

    $this->actingAs(collaborateur(RoleEmploye::Vendeur), 'employe')
        ->post(route('backoffice.commandes.etat', $domicile), ['etat' => 'retiree'])
        ->assertSessionHasErrors('etat');
});

it('refuses any change on a finished order', function () {
    $commande = Commande::factory()->livraisonDomicile()->etat(EtatCommande::Livree)->create();

    $this->actingAs(collaborateur(RoleEmploye::Livreur), 'employe')
        ->post(route('backoffice.commandes.etat', $commande), ['etat' => 'livree'])
        ->assertSessionHasErrors('etat');
});

it('shows the indicators on the dashboard', function () {
    Commande::factory()->etat(EtatCommande::Transmise)->create(['montant_total' => 100]);
    Commande::factory()->etat(EtatCommande::Validee)->create(['montant_total' => 50]);

    $this->actingAs(collaborateur(RoleEmploye::ServiceClient), 'employe')
        ->get(route('backoffice.dashboard'))
        ->assertInertia(fn (AssertableInertia $page) => $page
            ->where('indicateurs.aTraiter', 1)
            ->where('indicateurs.commandes', 2)
            ->where('indicateurs.chiffreAffaires', '150.00')
            ->has('commandesParEtat', 6)
            ->has('ventesMensuelles', 6)
            ->has('aTraiter', 1));
});
