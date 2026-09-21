<?php

use App\Enums\ModeLivraison;

/*
 * Les valeurs texte des enums sont écrites en base de données : les modifier
 * casserait les données existantes. Ces tests protègent ce contrat.
 */

test('the delivery modes are stored with their expected value', function (ModeLivraison $mode, string $valeur) {
    expect($mode->value)->toBe($valeur)
        ->and(ModeLivraison::from($valeur))->toBe($mode);
})->with([
    'retrait magasin' => [ModeLivraison::RetraitMagasin, 'retrait_magasin'],
    'livraison domicile' => [ModeLivraison::LivraisonDomicile, 'livraison_domicile'],
]);

test('there are exactly two delivery modes', function () {
    expect(ModeLivraison::cases())->toHaveCount(2);
});

test('an unknown delivery mode is rejected', function () {
    expect(ModeLivraison::tryFrom('drone'))->toBeNull();
});
