<?php

use App\Enums\RoleEmploye;
use App\Models\Client;
use App\Models\Employe;
use Illuminate\Auth\Events\Lockout;
use Illuminate\Support\Facades\Event;
use Inertia\Testing\AssertableInertia;

/*
 * Tests de la connexion et de la déconnexion des collaborateurs au back-office :
 * formulaire, identifiants valides ou invalides, limitation des tentatives,
 * séparation entre l'espace client et l'espace collaborateur.
 */

test('the back-office login screen can be rendered', function () {
    $response = $this->get(route('backoffice.login'));

    $response->assertOk()
        ->assertInertia(fn (AssertableInertia $page) => $page->component('backoffice/login'));
});

test('employees can authenticate with their login and password', function () {
    $employe = Employe::factory()->create(['identifiant_connexion' => 'jdupont']);

    $response = $this->post(route('backoffice.login'), [
        'identifiant_connexion' => 'jdupont',
        'password' => 'password',
    ]);

    $this->assertAuthenticatedAs($employe, 'employe');
    $response->assertRedirect(route('backoffice.dashboard'));
});

test('employees are not authenticated as clients', function () {
    $employe = Employe::factory()->create();

    $this->post(route('backoffice.login'), [
        'identifiant_connexion' => $employe->identifiant_connexion,
        'password' => 'password',
    ]);

    $this->assertGuest('web');
});

test('employees cannot authenticate with an invalid password', function () {
    $employe = Employe::factory()->create();

    $response = $this->post(route('backoffice.login'), [
        'identifiant_connexion' => $employe->identifiant_connexion,
        'password' => 'mauvais-mot-de-passe',
    ]);

    $response->assertSessionHasErrors('identifiant_connexion');
    $this->assertGuest('employe');
});

test('an unknown login gets the same error message as a wrong password', function () {
    $employe = Employe::factory()->create();

    $mauvaisMotDePasse = $this->post(route('backoffice.login'), [
        'identifiant_connexion' => $employe->identifiant_connexion,
        'password' => 'mauvais-mot-de-passe',
    ]);
    $identifiantInconnu = $this->post(route('backoffice.login'), [
        'identifiant_connexion' => 'inconnu',
        'password' => 'password',
    ]);

    $mauvaisMotDePasse->assertSessionHasErrors([
        'identifiant_connexion' => 'Ces identifiants ne correspondent à aucun compte.',
    ]);
    $identifiantInconnu->assertSessionHasErrors([
        'identifiant_connexion' => 'Ces identifiants ne correspondent à aucun compte.',
    ]);
});

test('a client email and password do not open the back-office', function () {
    $client = Client::factory()->create();

    $response = $this->post(route('backoffice.login'), [
        'identifiant_connexion' => $client->email,
        'password' => 'password',
    ]);

    $response->assertSessionHasErrors('identifiant_connexion');
    $this->assertGuest('employe');
});

test('back-office login fails when the required fields are missing', function () {
    $response = $this->post(route('backoffice.login'), []);

    $response->assertSessionHasErrors(['identifiant_connexion', 'password']);
    $this->assertGuest('employe');
});

test('back-office login is throttled after too many failed attempts', function () {
    Event::fake([Lockout::class]);
    $employe = Employe::factory()->create();

    for ($i = 0; $i < 5; $i++) {
        $this->post(route('backoffice.login'), [
            'identifiant_connexion' => $employe->identifiant_connexion,
            'password' => 'mauvais-mot-de-passe',
        ]);
    }

    $response = $this->post(route('backoffice.login'), [
        'identifiant_connexion' => $employe->identifiant_connexion,
        'password' => 'password',
    ]);

    $response->assertSessionHasErrors('identifiant_connexion');
    expect(session('errors')->first('identifiant_connexion'))->toContain('Trop de tentatives');
    $this->assertGuest('employe');
    Event::assertDispatched(Lockout::class);
});

test('guests are redirected to the back-office login from the dashboard', function () {
    $response = $this->get(route('backoffice.dashboard'));

    $response->assertRedirect(route('backoffice.login'));
});

test('a logged in client cannot open the back-office', function () {
    $client = Client::factory()->create();

    $response = $this->actingAs($client)->get(route('backoffice.dashboard'));

    $response->assertRedirect(route('backoffice.login'));
});

test('a logged in employee is redirected from the login screen to the dashboard', function () {
    $employe = Employe::factory()->create();

    $response = $this->actingAs($employe, 'employe')->get(route('backoffice.login'));

    $response->assertRedirect(route('backoffice.dashboard'));
});

test('employees can logout', function () {
    $employe = Employe::factory()->avecRole(RoleEmploye::Livreur)->create();

    $response = $this->actingAs($employe, 'employe')->post(route('backoffice.logout'));

    $this->assertGuest('employe');
    $response->assertRedirect(route('backoffice.login'));
});

test('guests cannot use the back-office logout', function () {
    $response = $this->post(route('backoffice.logout'));

    $response->assertRedirect(route('backoffice.login'));
});
