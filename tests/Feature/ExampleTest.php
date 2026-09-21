<?php

/*
 * Test de fumée : la page d'accueil répond correctement.
 */

test('returns a successful response', function () {
    $response = $this->get(route('home'));

    $response->assertOk();
});
