<?php

use App\Models\Client;
use App\Models\Commande;
use Illuminate\Support\Facades\Hash;

/*
 * Sécurité du SI : en-têtes HTTP, cloisonnement des espaces client et
 * collaborateur, mots de passe hachés, HTTPS exigé pour l'API en production.
 */

it('adds the security headers to every page', function () {
    $this->get(route('home'))
        ->assertOk()
        ->assertHeader('X-Frame-Options', 'SAMEORIGIN')
        ->assertHeader('X-Content-Type-Options', 'nosniff')
        ->assertHeader('Referrer-Policy', 'strict-origin-when-cross-origin')
        ->assertHeader('Permissions-Policy', 'camera=(), microphone=(), geolocation=()');
});

it('enables HSTS only over HTTPS', function () {
    $this->get(route('home'))->assertHeaderMissing('Strict-Transport-Security');

    $this->get('https://localhost/')->assertHeader('Strict-Transport-Security');
});

it('stores the client passwords hashed, never in clear', function () {
    $client = Client::factory()->create(['password' => 'mot-de-passe-secret']);

    expect($client->getRawOriginal('password'))->not->toBe('mot-de-passe-secret')
        ->and(Hash::check('mot-de-passe-secret', $client->getRawOriginal('password')))->toBeTrue();
});

it('does not let a client reach the back-office', function () {
    $this->actingAs(Client::factory()->create())
        ->get(route('backoffice.commandes.index'))
        ->assertRedirect(route('backoffice.login'));
});

it('requires HTTPS on the logistics API in production', function () {
    config(['services.logistique.token' => 'jeton']);
    app()->detectEnvironment(fn () => 'production');

    $this->getJson('http://localhost/api/v1/stocks', ['Authorization' => 'Bearer jeton'])->assertStatus(426);
});

it('throttles the order submission', function () {
    $client = Client::factory()->create();

    foreach (range(1, 10) as $tentative) {
        $this->actingAs($client)->post(route('commande.store'));
    }

    $this->actingAs($client)->post(route('commande.store'))->assertTooManyRequests();
});

it('never exposes the orders of a client to another one', function () {
    $commande = Commande::factory()->create();

    $this->actingAs(Client::factory()->create())
        ->get(route('compte.commandes.show', $commande))
        ->assertNotFound();
});
