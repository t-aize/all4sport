<?php

use App\Models\Client;
use App\Models\Commande;
use App\Models\Enfant;
use App\Models\Rayon;
use Carbon\CarbonInterface;
use Illuminate\Support\Facades\Hash;

/*
 * Tests du modèle Client : hachage du mot de passe, données masquées,
 * conversions de types (casts), assignation en masse et relations.
 */

it('hashes the password when a client is created', function () {
    $client = Client::factory()->create(['password' => 'mot-de-passe-clair']);

    expect($client->password)->not->toBe('mot-de-passe-clair')
        ->and(Hash::check('mot-de-passe-clair', $client->password))->toBeTrue();
});

it('hides the password and the remember token when serialized', function () {
    $client = Client::factory()->create();

    expect($client->toArray())->not->toHaveKeys(['password', 'remember_token']);
});

it('casts the birth date and the last purchase date to dates', function () {
    $client = Client::factory()->create([
        'date_naissance' => '1995-06-12',
        'date_dernier_achat' => null,
    ]);
    $client->forceFill(['date_dernier_achat' => '2026-03-01'])->save();

    $client->refresh();

    expect($client->date_naissance)->toBeInstanceOf(CarbonInterface::class)
        ->and($client->date_naissance->toDateString())->toBe('1995-06-12')
        ->and($client->date_dernier_achat->toDateString())->toBe('2026-03-01');
});

it('does not mass assign the last purchase date', function () {
    $client = Client::create([
        'nom' => 'Dupont',
        'prenom' => 'Marie',
        'email' => 'marie.dupont@example.com',
        'adresse' => '12 rue des Sports',
        'code_postal' => '59000',
        'ville' => 'Lille',
        'password' => 'password',
        'date_dernier_achat' => '2026-03-01',
    ]);

    expect($client->refresh()->date_dernier_achat)->toBeNull();
});

it('lists only the children of the client', function () {
    $client = Client::factory()->create();
    $enfants = Enfant::factory()->count(2)->for($client)->create();
    Enfant::factory()->create();

    expect($client->enfants->pluck('id')->sort()->values()->all())
        ->toBe($enfants->pluck('id')->sort()->values()->all());
});

it('lists only the orders placed by the client', function () {
    $client = Client::factory()->create();
    $commande = Commande::factory()->for($client)->create();
    Commande::factory()->create();

    expect($client->commandes->pluck('id')->all())->toBe([$commande->id]);
});

it('lists the sports the client practices with the pivot timestamps', function () {
    $client = Client::factory()->create();
    $rayons = Rayon::factory()->count(2)->create();
    $autreRayon = Rayon::factory()->create();

    $client->rayons()->attach($rayons);

    expect($client->rayons->pluck('id')->sort()->values()->all())
        ->toBe($rayons->pluck('id')->sort()->values()->all())
        ->and($client->rayons->pluck('id'))->not->toContain($autreRayon->id)
        ->and($client->rayons->first()->pivot->created_at)->not->toBeNull();
});


it('generates a unique code_cli when a client is created', function () {
    $client = Client::factory()->create();

    expect($client->code_cli)->toMatch('/^CLI\d{6}$/');
});

it('keeps an explicitly provided code_cli', function () {
    $client = Client::factory()->create(['code_cli' => 'CLI000042']);

    expect($client->code_cli)->toBe('CLI000042');
});