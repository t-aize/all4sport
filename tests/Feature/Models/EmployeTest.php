<?php

use App\Enums\RoleEmploye;
use App\Models\Commande;
use App\Models\Employe;
use App\Models\HistoriqueEtat;
use Illuminate\Support\Facades\Hash;

/*
 * Tests du modèle Employe : rôle typé, mot de passe haché, données masquées
 * et relations avec les commandes et l'historique des états.
 */

it('casts the role to the RoleEmploye enum', function (RoleEmploye $role) {
    $employe = Employe::factory()->create(['role' => $role]);

    expect($employe->refresh()->role)->toBe($role);
})->with(RoleEmploye::cases());

it('stores the role as its string value', function () {
    $employe = Employe::factory()->create(['role' => RoleEmploye::Livreur]);

    $this->assertDatabaseHas('employes', ['id' => $employe->id, 'role' => 'livreur']);
});

it('hashes the password when an employee is created', function () {
    $employe = Employe::factory()->create(['password' => 'mot-de-passe-clair']);

    expect($employe->password)->not->toBe('mot-de-passe-clair')
        ->and(Hash::check('mot-de-passe-clair', $employe->password))->toBeTrue();
});

it('hides the password and the remember token when serialized', function () {
    $employe = Employe::factory()->create();

    expect($employe->toArray())->not->toHaveKeys(['password', 'remember_token']);
});

it('lists only the orders handled by the employee', function () {
    $employe = Employe::factory()->create();
    $commande = Commande::factory()->create(['employe_id' => $employe->id]);
    Commande::factory()->create();

    expect($employe->commandes->pluck('id')->all())->toBe([$commande->id]);
});

it('lists only the state changes recorded by the employee', function () {
    $employe = Employe::factory()->create();
    $historique = HistoriqueEtat::factory()->create(['employe_id' => $employe->id]);
    HistoriqueEtat::factory()->create();

    expect($employe->historiqueEtats->pluck('id')->all())->toBe([$historique->id]);
});
