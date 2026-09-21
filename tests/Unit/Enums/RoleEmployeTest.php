<?php

use App\Enums\RoleEmploye;

/*
 * Les valeurs texte des enums sont écrites en base de données : les modifier
 * casserait les données existantes. Ces tests protègent ce contrat.
 */

test('the employee roles are stored with their expected value', function (RoleEmploye $role, string $valeur) {
    expect($role->value)->toBe($valeur)
        ->and(RoleEmploye::from($valeur))->toBe($role);
})->with([
    'operateur' => [RoleEmploye::Operateur, 'operateur'],
    'vendeur' => [RoleEmploye::Vendeur, 'vendeur'],
    'livreur' => [RoleEmploye::Livreur, 'livreur'],
    'service client' => [RoleEmploye::ServiceClient, 'service_client'],
]);

test('there are exactly four employee roles', function () {
    expect(RoleEmploye::cases())->toHaveCount(4);
});

test('an unknown role is rejected', function () {
    expect(RoleEmploye::tryFrom('administrateur'))->toBeNull();
});
