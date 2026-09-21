<?php

use App\Enums\RoleEmploye;
use App\Models\Employe;
use Inertia\Testing\AssertableInertia;

/*
 * Tests du tableau de bord du back-office : chaque collaborateur y voit son
 * rôle et les états de commande qu'il a le droit d'enregistrer.
 */

it('shows the role and the manageable states of the employee', function (RoleEmploye $role, string $libelle, array $etats) {
    $employe = Employe::factory()->avecRole($role)->create();

    $response = $this->actingAs($employe, 'employe')->get(route('backoffice.dashboard'));

    $response->assertOk()->assertInertia(fn (AssertableInertia $page) => $page
        ->component('backoffice/dashboard')
        ->where('roleLibelle', $libelle)
        ->where('etatsGerables', $etats));
})->with([
    'service client' => [RoleEmploye::ServiceClient, 'Service client', [
        ['value' => 'validee', 'label' => 'Validée'],
    ]],
    'opérateur' => [RoleEmploye::Operateur, 'Opérateur', [
        ['value' => 'en_preparation', 'label' => 'En préparation'],
        ['value' => 'expediee', 'label' => 'Expédiée'],
    ]],
    'livreur' => [RoleEmploye::Livreur, 'Livreur', [
        ['value' => 'livree', 'label' => 'Livrée'],
    ]],
    'vendeur' => [RoleEmploye::Vendeur, 'Vendeur', [
        ['value' => 'retiree', 'label' => 'Retirée'],
    ]],
]);
