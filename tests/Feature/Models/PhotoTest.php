<?php

use App\Models\Photo;
use App\Models\Produit;

/*
 * Tests du modèle Photo : rattachement au produit via la référence métier.
 */

it('belongs to the product it illustrates through the product reference', function () {
    $produit = Produit::factory()->create();
    $photo = Photo::factory()->create(['reference_produit' => $produit->reference_produit]);

    expect($photo->produit->is($produit))->toBeTrue();
});

it('only mass assigns its url and its product reference', function () {
    $produit = Produit::factory()->create();

    $photo = Photo::create([
        'url_photo' => 'https://exemple.test/photo.jpg',
        'reference_produit' => $produit->reference_produit,
        'id' => 999,
    ]);

    expect($photo->id)->not->toBe(999)
        ->and($photo->url_photo)->toBe('https://exemple.test/photo.jpg');
});
