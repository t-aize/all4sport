<?php

use App\Models\Client;
use App\Models\Produit;
use App\Models\Rayon;

/*
 * Tests du modèle Rayon : produits classés dans le rayon et clients qui
 * pratiquent le sport correspondant.
 */

it('lists only the products of the section', function () {
    $rayon = Rayon::factory()->create();
    $produit = Produit::factory()->for($rayon)->create();
    Produit::factory()->create();

    expect($rayon->produits->pluck('reference_produit')->all())->toBe([$produit->reference_produit]);
});

it('lists the clients who practice the sport with the pivot timestamps', function () {
    $rayon = Rayon::factory()->create();
    $clients = Client::factory()->count(2)->create();
    $autreClient = Client::factory()->create();

    $rayon->clients()->attach($clients);

    expect($rayon->clients->pluck('id')->sort()->values()->all())
        ->toBe($clients->pluck('id')->sort()->values()->all())
        ->and($rayon->clients->pluck('id'))->not->toContain($autreClient->id)
        ->and($rayon->clients->first()->pivot->created_at)->not->toBeNull();
});

it('only mass assigns its name and code', function () {
    $rayon = Rayon::create([
        'nom_rayon' => 'Football',
        'code_rayon' => 'FOO',
        'id' => 999,
    ]);

    expect($rayon->id)->not->toBe(999)
        ->and($rayon->nom_rayon)->toBe('Football')
        ->and($rayon->code_rayon)->toBe('FOO');
});
