<?php

use App\Models\Client;
use App\Models\Enfant;
use App\Models\Magasin;
use App\Models\Rayon;
use Illuminate\Support\Facades\Hash;
use Inertia\Testing\AssertableInertia;

/*
 * Espace "Mon compte" : informations personnelles, magasin par défaut,
 * sports pratiqués, enfants et mot de passe.
 */

beforeEach(function () {
    $this->client = Client::factory()->create(['password' => 'ancien-mdp']);
});

/**
 * Données valides du formulaire de profil.
 *
 * @return array<string, mixed>
 */
function profilValide(array $surcharges = []): array
{
    return [
        'nom' => 'Durand',
        'prenom' => 'Camille',
        'email' => 'camille.durand@example.com',
        'telephone' => '06 12 34 56 78',
        'adresse' => '8 rue Esquermoise',
        'code_postal' => '59000',
        'ville' => 'Lille',
        'date_naissance' => '1990-05-12',
        ...$surcharges,
    ];
}

it('requires a logged in client', function () {
    $this->get(route('compte.profil.edit'))->assertRedirect(route('login'));
});

it('shows the client file with shops, sports and children', function () {
    Magasin::factory(2)->create();
    Rayon::factory(3)->create();
    Enfant::factory()->for($this->client)->create(['date_naissance' => now()->subYears(7)]);

    $this->actingAs($this->client)
        ->get(route('compte.profil.edit'))
        ->assertOk()
        ->assertInertia(fn (AssertableInertia $page) => $page
            ->component('compte/profil')
            ->where('client.codeCli', $this->client->code_cli)
            ->has('magasins', 2)
            ->has('rayons', 3)
            ->has('enfants', 1)
            ->where('enfants.0.age', 7));
});

it('updates the personal information, the default shop and the sports', function () {
    $magasin = Magasin::factory()->create();
    $rayons = Rayon::factory(2)->create();

    $this->actingAs($this->client)
        ->put(route('compte.profil.update'), profilValide([
            'magasin_id' => $magasin->id,
            'rayons' => $rayons->pluck('id')->all(),
        ]))
        ->assertSessionHasNoErrors()
        ->assertRedirect();

    $this->client->refresh();

    expect($this->client->nom)->toBe('Durand')
        ->and($this->client->email)->toBe('camille.durand@example.com')
        ->and($this->client->magasin_id)->toBe($magasin->id)
        ->and($this->client->rayons()->pluck('rayons.id')->sort()->values()->all())->toBe($rayons->pluck('id')->sort()->values()->all());
});

it('rejects invalid values', function (array $donnees, string $champ) {
    Client::factory()->create(['email' => 'pris@example.com']);

    $this->actingAs($this->client)
        ->put(route('compte.profil.update'), profilValide($donnees))
        ->assertSessionHasErrors($champ);
})->with([
    'email déjà pris' => [['email' => 'pris@example.com'], 'email'],
    'code postal' => [['code_postal' => '59'], 'code_postal'],
    'naissance future' => [['date_naissance' => '2999-01-01'], 'date_naissance'],
    'magasin inconnu' => [['magasin_id' => 999], 'magasin_id'],
    'sport inconnu' => [['rayons' => [999]], 'rayons.0'],
    'nom manquant' => [['nom' => ''], 'nom'],
]);

it('adds and removes children', function () {
    $this->actingAs($this->client)
        ->post(route('compte.enfants.store'), ['date_naissance' => now()->subYears(5)->toDateString()])
        ->assertSessionHasNoErrors();

    $enfant = $this->client->enfants()->sole();

    $this->actingAs($this->client)->delete(route('compte.enfants.destroy', $enfant))->assertRedirect();

    expect($this->client->enfants()->count())->toBe(0);
});

it('refuses to remove the child of another client', function () {
    $enfant = Enfant::factory()->create();

    $this->actingAs($this->client)->delete(route('compte.enfants.destroy', $enfant))->assertNotFound();

    $this->assertModelExists($enfant);
});

it('changes the password when the current one is correct', function () {
    $this->actingAs($this->client)
        ->put(route('compte.mot-de-passe.update'), [
            'mot_de_passe_actuel' => 'ancien-mdp',
            'password' => 'nouveau-mdp',
            'password_confirmation' => 'nouveau-mdp',
        ])
        ->assertSessionHasNoErrors();

    expect(Hash::check('nouveau-mdp', $this->client->refresh()->password))->toBeTrue();
});

it('refuses a wrong current password', function () {
    $this->actingAs($this->client)
        ->put(route('compte.mot-de-passe.update'), [
            'mot_de_passe_actuel' => 'faux',
            'password' => 'nouveau-mdp',
            'password_confirmation' => 'nouveau-mdp',
        ])
        ->assertSessionHasErrors('mot_de_passe_actuel');
});
