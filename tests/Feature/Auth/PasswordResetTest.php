<?php

use App\Models\Client;
use Illuminate\Auth\Notifications\ResetPassword;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Notification;

/*
 * Tests du parcours "mot de passe oublié" : demande du lien par email
 * (notification simulée) puis choix d'un nouveau mot de passe avec le jeton.
 */

test('reset password link screen can be rendered', function () {
    $response = $this->get(route('password.request'));

    $response->assertOk();
});

test('reset password link can be requested', function () {
    Notification::fake();

    $client = Client::factory()->create();

    $response = $this->post(route('password.email'), ['email' => $client->email]);

    $response->assertSessionHas('status');
    Notification::assertSentTo($client, ResetPassword::class);
});

test('reset password link is not sent for an unknown email', function () {
    Notification::fake();

    $response = $this->post(route('password.email'), ['email' => 'inconnu@example.com']);

    $response->assertSessionHasErrors('email');
    Notification::assertNothingSent();
});

test('reset password screen can be rendered', function () {
    Notification::fake();

    $client = Client::factory()->create();
    $this->post(route('password.email'), ['email' => $client->email]);

    Notification::assertSentTo($client, ResetPassword::class, function (ResetPassword $notification) {
        $response = $this->get(route('password.reset', $notification->token));

        $response->assertOk();

        return true;
    });
});

test('password can be reset with a valid token', function () {
    Notification::fake();

    $client = Client::factory()->create();
    $this->post(route('password.email'), ['email' => $client->email]);

    Notification::assertSentTo($client, ResetPassword::class, function (ResetPassword $notification) use ($client) {
        $response = $this->post(route('password.store'), [
            'token' => $notification->token,
            'email' => $client->email,
            'password' => 'nouveau-mot-de-passe',
            'password_confirmation' => 'nouveau-mot-de-passe',
        ]);

        $response->assertRedirect(route('login'));

        $this->assertTrue(
            Hash::check('nouveau-mot-de-passe', $client->fresh()->password)
        );

        return true;
    });
});
