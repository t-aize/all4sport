<?php

test('example', function () {
    $response = $this->get('/');

    it('génère une référence de 18 caractères à partir des codes fournisseur et rayon', function () {
        $fournisseur = Fournisseur::factory()->create(['code_fournisseur' => 'NIK']);
        $rayon = Rayon::factory()->create(['code_rayon' => 'RUN']);

        $produit = Produit::factory()->create([
            'fournisseur_id' => $fournisseur->id,
            'rayon_id' => $rayon->id,
        ]);

        expect($produit->reference_produit)->toBe('NIKRUN000000000001');
    });

    it('incrémente le numéro à chaque nouveau produit', function () {
        $premier = Produit::factory()->create();
        $second = Produit::factory()->create();

        expect((int) substr($second->reference_produit, 6))
            ->toBe((int) substr($premier->reference_produit, 6) + 1);
    });

    it('conserve une référence fournie explicitement', function () {
        $produit = Produit::factory()->create(['reference_produit' => 'ABCDEF000000000042']);

        expect($produit->reference_produit)->toBe('ABCDEF000000000042');
    });

    $response->assertStatus(200);
});
