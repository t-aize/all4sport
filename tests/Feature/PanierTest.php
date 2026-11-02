<?php

namespace Tests\Feature;

use App\Models\Client;
use App\Models\LignePanier;
use App\Models\Panier;
use App\Models\Produit;
use App\Services\ServicePanier;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PanierTest extends TestCase
{
    use RefreshDatabase;

    private Client $client;

    protected function setUp(): void
    {
        parent::setUp();

        $this->client = Client::factory()->create();
    }

    public function test_un_visiteur_peut_ajouter_un_produit(): void
    {
        $produit = Produit::factory()->create(['prix_vente' => 10, 'quantite_stock_internet' => 5]);

        $this->post(route('panier.ajouter', $produit), ['quantite' => 2])
            ->assertRedirect();

        $this->assertDatabaseHas('paniers', ['client_id' => null]);
        $this->assertDatabaseHas('lignes_panier', ['reference_produit' => $produit->reference_produit, 'quantite' => 2]);
    }

    public function test_ajouter_un_produit_deja_present_cumule_les_quantites(): void
    {
        $produit = Produit::factory()->create(['quantite_stock_internet' => 10]);

        $this->actingAs($this->client)->post(route('panier.ajouter', $produit), ['quantite' => 2]);
        $this->actingAs($this->client)->post(route('panier.ajouter', $produit), ['quantite' => 3]);

        $this->assertDatabaseCount('lignes_panier', 1);
        $this->assertDatabaseHas('lignes_panier', ['reference_produit' => $produit->reference_produit, 'quantite' => 5]);
    }

    public function test_le_prix_est_fige_a_l_ajout(): void
    {
        $produit = Produit::factory()->create(['prix_vente' => 10, 'quantite_stock_internet' => 10]);

        $this->actingAs($this->client)->post(route('panier.ajouter', $produit));

        $produit->forceFill(['prix_vente' => 99])->save();

        $this->assertDatabaseHas('lignes_panier', ['reference_produit' => $produit->reference_produit, 'prix_unitaire' => 10]);
    }

    public function test_on_peut_modifier_la_quantite(): void
    {
        $produit = Produit::factory()->create(['quantite_stock_internet' => 10]);
        $this->actingAs($this->client)->post(route('panier.ajouter', $produit));

        $this->actingAs($this->client)
            ->patch(route('panier.modifier', $produit), ['quantite' => 4])
            ->assertRedirect();

        $this->assertDatabaseHas('lignes_panier', ['reference_produit' => $produit->reference_produit, 'quantite' => 4]);
    }

    public function test_une_quantite_a_zero_supprime_la_ligne(): void
    {
        $produit = Produit::factory()->create(['quantite_stock_internet' => 10]);
        $this->actingAs($this->client)->post(route('panier.ajouter', $produit));

        $this->actingAs($this->client)->patch(route('panier.modifier', $produit), ['quantite' => 0]);

        $this->assertDatabaseMissing('lignes_panier', ['reference_produit' => $produit->reference_produit]);
    }

    public function test_on_peut_retirer_un_produit(): void
    {
        $produit = Produit::factory()->create(['quantite_stock_internet' => 10]);
        $this->actingAs($this->client)->post(route('panier.ajouter', $produit));

        $this->actingAs($this->client)->delete(route('panier.retirer', $produit));

        $this->assertDatabaseCount('lignes_panier', 0);
    }

    public function test_on_peut_vider_le_panier(): void
    {
        Produit::factory()->count(3)->create(['quantite_stock_internet' => 10])->each(
            fn (Produit $p) => $this->actingAs($this->client)->post(route('panier.ajouter', $p))
        );

        $this->actingAs($this->client)->delete(route('panier.vider'));

        $this->assertDatabaseCount('lignes_panier', 0);
    }

    public function test_le_stock_insuffisant_est_refuse(): void
    {
        $produit = Produit::factory()->create(['quantite_stock_internet' => 2]);

        $this->actingAs($this->client)
            ->post(route('panier.ajouter', $produit), ['quantite' => 5])
            ->assertSessionHasErrors('quantite');

        $this->assertDatabaseCount('lignes_panier', 0);
    }

    public function test_le_total_et_le_nombre_d_articles_sont_corrects(): void
    {
        $a = Produit::factory()->create(['prix_vente' => 10, 'quantite_stock_internet' => 10]);
        $b = Produit::factory()->create(['prix_vente' => 5, 'quantite_stock_internet' => 10]);

        $this->actingAs($this->client)->post(route('panier.ajouter', $a), ['quantite' => 2]); // 20
        $this->actingAs($this->client)->post(route('panier.ajouter', $b), ['quantite' => 3]); // 15

        $panier = Panier::where('client_id', $this->client->id)->with('lignes')->first();

        $this->assertEquals(35.0, $panier->total);
        $this->assertEquals(5, $panier->nombre_articles);
    }

    public function test_le_panier_invite_est_fusionne_a_la_connexion(): void
    {
        $a = Produit::factory()->create(['prix_vente' => 10, 'quantite_stock_internet' => 10]);
        $b = Produit::factory()->create(['prix_vente' => 5, 'quantite_stock_internet' => 10]);

        // Panier existant du client : 1 x A
        $panierClient = Panier::create(['client_id' => $this->client->id]);
        LignePanier::create(['panier_id' => $panierClient->id, 'reference_produit' => $a->reference_produit, 'quantite' => 1, 'prix_unitaire' => 10]);

        // Panier visiteur : 2 x A + 1 x B
        $panierInvite = Panier::create(['session_id' => 'ancienne-session']);
        LignePanier::create(['panier_id' => $panierInvite->id, 'reference_produit' => $a->reference_produit, 'quantite' => 2, 'prix_unitaire' => 10]);
        LignePanier::create(['panier_id' => $panierInvite->id, 'reference_produit' => $b->reference_produit, 'quantite' => 1, 'prix_unitaire' => 5]);

        app(ServicePanier::class)->fusionnerPanierInvite('ancienne-session', $this->client->id);

        $this->assertDatabaseMissing('paniers', ['id' => $panierInvite->id]);
        $this->assertDatabaseHas('lignes_panier', ['panier_id' => $panierClient->id, 'reference_produit' => $a->reference_produit, 'quantite' => 3]);
        $this->assertDatabaseHas('lignes_panier', ['panier_id' => $panierClient->id, 'reference_produit' => $b->reference_produit, 'quantite' => 1]);
    }
}