<?php

use App\Enums\EtatCommande;
use App\Enums\ModeLivraison;

/*
 * Ordre des états de commande selon le mode de livraison et frais de
 * livraison (besoins du lot A et dossier de contexte).
 */

test('each state has the expected next state', function (EtatCommande $etat, ModeLivraison $mode, ?EtatCommande $suivant) {
    expect($etat->suivant($mode))->toBe($suivant);
})->with([
    [EtatCommande::Transmise, ModeLivraison::RetraitMagasin, EtatCommande::Validee],
    [EtatCommande::Validee, ModeLivraison::LivraisonDomicile, EtatCommande::EnPreparation],
    [EtatCommande::EnPreparation, ModeLivraison::RetraitMagasin, EtatCommande::Expediee],
    [EtatCommande::Expediee, ModeLivraison::RetraitMagasin, EtatCommande::Retiree],
    [EtatCommande::Expediee, ModeLivraison::LivraisonDomicile, EtatCommande::Livree],
    [EtatCommande::Livree, ModeLivraison::LivraisonDomicile, null],
    [EtatCommande::Retiree, ModeLivraison::RetraitMagasin, null],
]);

test('the route ends with the right final state', function () {
    expect(EtatCommande::parcours(ModeLivraison::RetraitMagasin))->toHaveCount(5)
        ->and(EtatCommande::parcours(ModeLivraison::RetraitMagasin)[4])->toBe(EtatCommande::Retiree)
        ->and(EtatCommande::parcours(ModeLivraison::LivraisonDomicile)[4])->toBe(EtatCommande::Livree);
});

test('only delivered and collected orders are final', function () {
    expect(array_values(array_filter(EtatCommande::cases(), fn (EtatCommande $etat) => $etat->estFinal())))
        ->toBe([EtatCommande::Livree, EtatCommande::Retiree]);
});

test('in-store pickup is free and home delivery costs 10 €', function () {
    expect(ModeLivraison::RetraitMagasin->frais())->toBe(0.0)
        ->and(ModeLivraison::LivraisonDomicile->frais())->toBe(10.0)
        ->and(ModeLivraison::RetraitMagasin->libelle())->toBe('Retrait en magasin')
        ->and(ModeLivraison::LivraisonDomicile->libelle())->toBe('Livraison à domicile');
});
