<?php

namespace Tests\Feature;

use App\Models\Client;
use App\Models\LignePanier;
use App\Models\Magasin;
use App\Models\Panier;
use App\Models\Produit;
use App\Services\ServicePanier;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Inertia\Testing\AssertableInertia;
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

    public function test_la_page_du_panier_affiche_les_lignes_et_le_total(): void
    {
        $produit = Produit::factory()->create(['prix_vente' => 12.5, 'quantite_stock_internet' => 10]);
        $this->actingAs($this->client)->post(route('panier.ajouter', $produit), ['quantite' => 2]);

        $this->actingAs($this->client)->get(route('panier.index'))
            ->assertOk()
            ->assertInertia(fn (AssertableInertia $page) => $page
                ->component('panier/index')
                ->has('panier.lignes', 1)
                ->where('panier.lignes.0.reference', $produit->reference_produit)
                ->where('panier.lignes.0.quantite', 2)
                ->where('panier.total', '25.00')
                ->where('panier.nombreArticles', 2));
    }

    public function test_le_nombre_d_articles_est_partage_avec_toutes_les_pages(): void
    {
        $produit = Produit::factory()->create(['quantite_stock_internet' => 10]);
        $this->post(route('panier.ajouter', $produit), ['quantite' => 3]);

        $this->avecSessionDuVisiteur()->get(route('catalogue.index'))
            ->assertInertia(fn (AssertableInertia $page) => $page->where('nombreArticlesPanier', 3));
    }

    public function test_un_produit_disponible_seulement_en_magasin_peut_etre_ajoute(): void
    {
        $produit = Produit::factory()->create(['quantite_stock_internet' => 0]);
        $magasin = Magasin::factory()->create();
        $produit->magasins()->attach($magasin->id, ['quantite_stock_magasin' => 4]);

        $this->actingAs($this->client)
            ->post(route('panier.ajouter', $produit), ['quantite' => 4])
            ->assertSessionHasNoErrors();

        $this->assertDatabaseHas('lignes_panier', ['reference_produit' => $produit->reference_produit, 'quantite' => 4]);
    }

    public function test_la_quantite_est_limitee(): void
    {
        $produit = Produit::factory()->create(['quantite_stock_internet' => 500]);

        $this->actingAs($this->client)
            ->post(route('panier.ajouter', $produit), ['quantite' => 100])
            ->assertSessionHasErrors('quantite');
    }

    public function test_le_panier_du_visiteur_rejoint_le_compte_a_la_connexion(): void
    {
        $produit = Produit::factory()->create(['quantite_stock_internet' => 10]);
        $client = Client::factory()->create(['password' => 'secret-123']);

        $this->post(route('panier.ajouter', $produit), ['quantite' => 2]);
        $this->avecSessionDuVisiteur()->post(route('login'), ['email' => $client->email, 'password' => 'secret-123']);

        $this->assertAuthenticatedAs($client);
        $this->assertDatabaseHas('paniers', ['client_id' => $client->id]);
        $this->assertSame(2, Panier::where('client_id', $client->id)->first()->lignes()->sum('quantite'));
        $this->assertDatabaseMissing('paniers', ['client_id' => null]);
    }

    public function test_le_panier_du_visiteur_rejoint_le_compte_a_l_inscription(): void
    {
        $produit = Produit::factory()->create(['quantite_stock_internet' => 10]);
        $this->post(route('panier.ajouter', $produit));

        $this->avecSessionDuVisiteur()->post(route('register'), [
            'nom' => 'Martin', 'prenom' => 'Léa', 'email' => 'lea@example.com',
            'adresse' => '12 rue de Lille', 'code_postal' => '59000', 'ville' => 'Lille',
            'password' => 'password', 'password_confirmation' => 'password',
        ]);

        $client = Client::where('email', 'lea@example.com')->firstOrFail();
        $this->assertDatabaseHas('lignes_panier', [
            'panier_id' => Panier::where('client_id', $client->id)->value('id'),
            'reference_produit' => $produit->reference_produit,
        ]);
    }

    public function test_les_paniers_de_deux_clients_sont_separes(): void
    {
        $produit = Produit::factory()->create(['quantite_stock_internet' => 10]);
        $autre = Client::factory()->create();

        $this->actingAs($this->client)->post(route('panier.ajouter', $produit), ['quantite' => 2]);

        $this->actingAs($autre)->get(route('panier.index'))
            ->assertInertia(fn (AssertableInertia $page) => $page->has('panier.lignes', 0));
    }

    /**
     * Rejoue la session du visiteur (comme le ferait le cookie du navigateur) :
     * les requêtes de test ne partagent pas automatiquement leur session.
     */
    private function avecSessionDuVisiteur(): static
    {
        $sessionId = (string) Panier::query()->whereNull('client_id')->value('session_id');

        return $this->withCookie(config('session.cookie'), $sessionId);
    }
}
