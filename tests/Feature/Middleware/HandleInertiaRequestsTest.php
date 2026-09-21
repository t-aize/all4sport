<?php

use App\Enums\RoleEmploye;
use App\Models\Client;
use App\Models\Employe;
use Inertia\Testing\AssertableInertia;

/*
 * Tests des données partagées avec toutes les pages React : le client et le
 * collaborateur connectés sont indépendants, et aucun mot de passe n'est envoyé
 * au navigateur.
 */

it('shares the logged in employee and no client on the back-office', function () {
    $employe = Employe::factory()->avecRole(RoleEmploye::Livreur)->create();

    $response = $this->actingAs($employe, 'employe')->get(route('backoffice.dashboard'));

    $response->assertInertia(fn (AssertableInertia $page) => $page
        ->where('auth.user', null)
        ->where('auth.employe.id', $employe->id)
        ->where('auth.employe.identifiant_connexion', $employe->identifiant_connexion)
        ->where('auth.employe.role', 'livreur'));
});

it('does not share the password nor the remember token of the employee', function () {
    $employe = Employe::factory()->create();

    $response = $this->actingAs($employe, 'employe')->get(route('backoffice.dashboard'));

    $response->assertInertia(fn (AssertableInertia $page) => $page
        ->missing('auth.employe.password')
        ->missing('auth.employe.remember_token'));
});

it('shares the logged in client and no employee on the public site', function () {
    $client = Client::factory()->create();

    $response = $this->actingAs($client)->get(route('home'));

    $response->assertInertia(fn (AssertableInertia $page) => $page
        ->where('auth.user.id', $client->id)
        ->where('auth.employe', null));
});

it('shares no user for a guest', function () {
    $response = $this->get(route('home'));

    $response->assertInertia(fn (AssertableInertia $page) => $page
        ->where('auth.user', null)
        ->where('auth.employe', null));
});
