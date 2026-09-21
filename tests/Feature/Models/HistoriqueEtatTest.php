<?php

use App\Enums\EtatCommande;
use App\Models\Commande;
use App\Models\Employe;
use App\Models\HistoriqueEtat;
use Carbon\CarbonInterface;

/*
 * Tests du modèle HistoriqueEtat : état typé, date et heure du changement,
 * commande concernée et employé qui l'a enregistré.
 */

it('casts the state to the EtatCommande enum', function (EtatCommande $etat) {
    $historique = HistoriqueEtat::factory()->create(['etat' => $etat]);

    expect($historique->refresh()->etat)->toBe($etat);
})->with(EtatCommande::cases());

it('casts the state date to a date and time', function () {
    $historique = HistoriqueEtat::factory()->create(['date_etat' => '2026-05-17 14:30:00']);

    $historique->refresh();

    expect($historique->date_etat)->toBeInstanceOf(CarbonInterface::class)
        ->and($historique->date_etat->format('Y-m-d H:i:s'))->toBe('2026-05-17 14:30:00');
});

it('belongs to the order it concerns', function () {
    $commande = Commande::factory()->create();
    $historique = HistoriqueEtat::factory()->create(['commande_id' => $commande->id]);

    expect($historique->commande->is($commande))->toBeTrue();
});

it('belongs to the employee who recorded it', function () {
    $employe = Employe::factory()->create();
    $historique = HistoriqueEtat::factory()->create(['employe_id' => $employe->id]);

    expect($historique->employe->is($employe))->toBeTrue();
});

it('has no employee when the state is recorded automatically', function () {
    $historique = HistoriqueEtat::factory()->create(['employe_id' => null]);

    expect($historique->employe)->toBeNull();
});
