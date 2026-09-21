<?php

use App\Enums\EtatCommande;

/*
 * Les valeurs texte des enums sont écrites en base de données : les modifier
 * casserait les données existantes. Ces tests protègent ce contrat.
 */

test('the order states are stored with their expected value', function (EtatCommande $etat, string $valeur) {
    expect($etat->value)->toBe($valeur)
        ->and(EtatCommande::from($valeur))->toBe($etat);
})->with([
    'transmise' => [EtatCommande::Transmise, 'transmise'],
    'validee' => [EtatCommande::Validee, 'validee'],
    'en preparation' => [EtatCommande::EnPreparation, 'en_preparation'],
    'expediee' => [EtatCommande::Expediee, 'expediee'],
    'livree' => [EtatCommande::Livree, 'livree'],
    'retiree' => [EtatCommande::Retiree, 'retiree'],
]);

test('there are exactly six order states', function () {
    expect(EtatCommande::cases())->toHaveCount(6);
});

test('an unknown state is rejected', function () {
    expect(EtatCommande::tryFrom('annulee'))->toBeNull();
});
