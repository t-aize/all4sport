<?php

use App\Models\Client;
use App\Models\Enfant;
use Carbon\CarbonInterface;

/*
 * Tests du modèle Enfant : conversion de la date de naissance et rattachement
 * au client parent.
 */

it('casts the birth date to a date', function () {
    $enfant = Enfant::factory()->create(['date_naissance' => '2015-09-30']);

    $enfant->refresh();

    expect($enfant->date_naissance)->toBeInstanceOf(CarbonInterface::class)
        ->and($enfant->date_naissance->toDateString())->toBe('2015-09-30');
});

it('belongs to its parent client', function () {
    $client = Client::factory()->create();
    $enfant = Enfant::factory()->for($client)->create();

    expect($enfant->client->is($client))->toBeTrue();
});
