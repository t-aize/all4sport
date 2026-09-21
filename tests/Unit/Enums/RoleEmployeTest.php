<?php

use App\Enums\EtatCommande;
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

test('each role has its display label', function (RoleEmploye $role, string $libelle) {
    expect($role->libelle())->toBe($libelle);
})->with([
    'operateur' => [RoleEmploye::Operateur, 'Opérateur'],
    'vendeur' => [RoleEmploye::Vendeur, 'Vendeur'],
    'livreur' => [RoleEmploye::Livreur, 'Livreur'],
    'service client' => [RoleEmploye::ServiceClient, 'Service client'],
]);

test('each role can only record its own order states', function (RoleEmploye $role, array $etats) {
    expect($role->etatsGerables())->toBe($etats);
})->with([
    'operateur' => [RoleEmploye::Operateur, [EtatCommande::EnPreparation, EtatCommande::Expediee]],
    'vendeur' => [RoleEmploye::Vendeur, [EtatCommande::Retiree]],
    'livreur' => [RoleEmploye::Livreur, [EtatCommande::Livree]],
    'service client' => [RoleEmploye::ServiceClient, [EtatCommande::Validee]],
]);

test('each order state is handled by the expected role', function (EtatCommande $etat, ?RoleEmploye $role) {
    expect(RoleEmploye::responsableDe($etat))->toBe($role);
})->with([
    'transmise (automatique)' => [EtatCommande::Transmise, null],
    'validee' => [EtatCommande::Validee, RoleEmploye::ServiceClient],
    'en preparation' => [EtatCommande::EnPreparation, RoleEmploye::Operateur],
    'expediee' => [EtatCommande::Expediee, RoleEmploye::Operateur],
    'livree' => [EtatCommande::Livree, RoleEmploye::Livreur],
    'retiree' => [EtatCommande::Retiree, RoleEmploye::Vendeur],
]);

test('no order state is shared by two roles', function () {
    $etats = collect(RoleEmploye::cases())
        ->flatMap(fn (RoleEmploye $role) => $role->etatsGerables());

    expect($etats->unique()->count())->toBe($etats->count());
});
