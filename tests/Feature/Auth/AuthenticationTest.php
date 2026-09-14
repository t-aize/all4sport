<?php

use App\Models\Client;

test('login screen can be rendered', function () {
    $response = $this->get(route('login'));

    $response->assertOk();
});

test('clients can authenticate using the login screen', function () {
    $client = Client::factory()->create();

    $response = $this->post(route('login'), [
        'email' => $client->email,
        'password' => 'password',
    ]);

    $this->assertAuthenticatedAs($client);
    $response->assertRedirect(route('home'));
});

test('clients cannot authenticate with an invalid password', function () {
    $client = Client::factory()->create();

    $response = $this->post(route('login'), [
        'email' => $client->email,
        'password' => 'mauvais-mot-de-passe',
    ]);

    $response->assertSessionHasErrors('email');
    $this->assertGuest();
});

test('login is throttled after too many failed attempts', function () {
    $client = Client::factory()->create();

    for ($i = 0; $i < 5; $i++) {
        $this->post(route('login'), [
            'email' => $client->email,
            'password' => 'mauvais-mot-de-passe',
        ]);
    }

    $response = $this->post(route('login'), [
        'email' => $client->email,
        'password' => 'password',
    ]);

    $response->assertSessionHasErrors('email');
    $this->assertGuest();
});

test('clients can logout', function () {
    $client = Client::factory()->create();

    $response = $this->actingAs($client)->post(route('logout'));

    $this->assertGuest();
    $response->assertRedirect(route('home'));
});
