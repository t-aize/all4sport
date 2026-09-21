<?php

use App\Enums\RoleEmploye;
use App\Models\Client;
use App\Models\Employe;
use Illuminate\Support\Facades\Exceptions;
use Illuminate\Support\Facades\Route;

/*
 * Tests du middleware « role » : il réserve une route du back-office aux
 * collaborateurs ayant l'un des rôles indiqués. Les routes ci-dessous n'existent
 * que pour ces tests.
 */

beforeEach(function () {
    Route::middleware(['web', 'auth:employe', 'role:livreur'])
        ->get('/tests/reserve-au-livreur', fn () => 'accès autorisé');

    Route::middleware(['web', 'auth:employe', 'role:operateur,vendeur'])
        ->get('/tests/reserve-operateur-vendeur', fn () => 'accès autorisé');

    Route::middleware(['web', 'auth:employe', 'role:administrateur'])
        ->get('/tests/role-inconnu', fn () => 'accès autorisé');
});

it('lets an employee with the required role through', function () {
    $employe = Employe::factory()->avecRole(RoleEmploye::Livreur)->create();

    $response = $this->actingAs($employe, 'employe')->get('/tests/reserve-au-livreur');

    $response->assertOk()->assertSee('accès autorisé');
});

it('forbids an employee with another role', function (RoleEmploye $role) {
    $employe = Employe::factory()->avecRole($role)->create();

    $response = $this->actingAs($employe, 'employe')->get('/tests/reserve-au-livreur');

    $response->assertForbidden();
})->with(fn () => collect(RoleEmploye::cases())
    ->reject(fn (RoleEmploye $role) => $role === RoleEmploye::Livreur)
    ->all());

it('lets through every role listed in the route', function (RoleEmploye $role) {
    $employe = Employe::factory()->avecRole($role)->create();

    $response = $this->actingAs($employe, 'employe')->get('/tests/reserve-operateur-vendeur');

    $response->assertOk();
})->with([
    'opérateur' => [RoleEmploye::Operateur],
    'vendeur' => [RoleEmploye::Vendeur],
]);

it('forbids the roles missing from the list of the route', function (RoleEmploye $role) {
    $employe = Employe::factory()->avecRole($role)->create();

    $response = $this->actingAs($employe, 'employe')->get('/tests/reserve-operateur-vendeur');

    $response->assertForbidden();
})->with([
    'livreur' => [RoleEmploye::Livreur],
    'service client' => [RoleEmploye::ServiceClient],
]);

it('redirects a guest to the back-office login', function () {
    $response = $this->get('/tests/reserve-au-livreur');

    $response->assertRedirect(route('backoffice.login'));
});

it('redirects a logged in client to the back-office login', function () {
    $client = Client::factory()->create();

    $response = $this->actingAs($client)->get('/tests/reserve-au-livreur');

    $response->assertRedirect(route('backoffice.login'));
});

it('fails loudly when the route declares an unknown role', function () {
    Exceptions::fake();
    $employe = Employe::factory()->create();

    $response = $this->actingAs($employe, 'employe')->get('/tests/role-inconnu');

    $response->assertServerError();
    Exceptions::assertReported(ValueError::class);
});
