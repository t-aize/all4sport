<?php

use App\Models\Client;

/*
 * Tests de l'inscription d'un client : création du compte, connexion
 * automatique et refus des données invalides ou en doublon.
 */

test('registration screen can be rendered', function () {
    $response = $this->get(route('register'));

    $response->assertOk();
});

test('new clients can register', function () {
    $response = $this->post(route('register'), [
        'nom' => 'Dupont',
        'prenom' => 'Marie',
        'email' => 'marie.dupont@example.com',
        'telephone' => '0611223344',
        'adresse' => '12 rue des Sports',
        'code_postal' => '59000',
        'ville' => 'Lille',
        'date_naissance' => '1995-06-12',
        'password' => 'password',
        'password_confirmation' => 'password',
    ]);

    $this->assertAuthenticated();
    $response->assertRedirect(route('home'));

    $this->assertDatabaseHas('clients', [
        'email' => 'marie.dupont@example.com',
        'nom' => 'Dupont',
    ]);
});

test('registration fails when the email is already used', function () {
    Client::factory()->create(['email' => 'marie.dupont@example.com']);

    $response = $this->post(route('register'), [
        'nom' => 'Dupont',
        'prenom' => 'Marie',
        'email' => 'marie.dupont@example.com',
        'adresse' => '12 rue des Sports',
        'code_postal' => '59000',
        'ville' => 'Lille',
        'password' => 'password',
        'password_confirmation' => 'password',
    ]);

    $response->assertSessionHasErrors('email');
    $this->assertGuest();
});

test('registration fails when the password confirmation does not match', function () {
    $response = $this->post(route('register'), [
        'nom' => 'Dupont',
        'prenom' => 'Marie',
        'email' => 'marie.dupont@example.com',
        'adresse' => '12 rue des Sports',
        'code_postal' => '59000',
        'ville' => 'Lille',
        'password' => 'password',
        'password_confirmation' => 'autre-mot-de-passe',
    ]);

    $response->assertSessionHasErrors('password');
    $this->assertGuest();
});

test('registration fails when required fields are missing', function () {
    $response = $this->post(route('register'), []);

    $response->assertSessionHasErrors(['nom', 'prenom', 'email', 'adresse', 'code_postal', 'ville', 'password']);
});
