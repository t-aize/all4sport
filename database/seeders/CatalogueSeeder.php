<?php

namespace Database\Seeders;

use App\Models\Fournisseur;
use App\Models\Magasin;
use App\Models\Produit;
use App\Models\Rayon;
use Illuminate\Database\Seeder;

/**
 * Catalogue de démonstration : rayons (sports), fournisseurs et produits
 * réalistes, avec leurs photos (1 à 5), leur lieu de stockage en entrepôt
 * (ex : M2D3.5), leur stock Internet et leur stock dans chaque magasin.
 *
 * La référence de chaque produit suit la règle de l'entreprise : 3 lettres du
 * fournisseur + 3 lettres du rayon + 12 chiffres auto-incrémentés.
 */
class CatalogueSeeder extends Seeder
{
    /**
     * Les fournisseurs (noms fictifs). Le code = 3 premières lettres du nom.
     *
     * @var list<string>
     */
    public const FOURNISSEURS = [
        'Altisport', 'Boréal Outdoor', 'Cimalp Pro', 'Dynamo Équipement', 'Elan Sportswear',
        'Fortis Training', 'Gravity Gear', 'Horizon Aqua', 'Kinetik', 'Velocita',
    ];

    /**
     * Les rayons et leurs produits (description, prix de vente minimum, prix
     * de vente maximum). Les photos sont les visuels de public/images/produits
     * (5 variantes par rayon, nommées d'après le code du rayon).
     *
     * @var array<string, array{produits: list<array{0: string, 1: int, 2: int}>}>
     */
    public const RAYONS = [
        'Football' => ['produits' => [
            ['Ballon de football match taille 5, coutures thermosoudées', 25, 45],
            ['Chaussures de football à crampons moulés terrain sec', 49, 139],
            ['Maillot de football respirant manches courtes', 19, 45],
            ['Protège-tibias avec chaussettes intégrées', 9, 25],
            ['Gants de gardien de but avec barrettes de protection', 25, 69],
            ['Short de football léger avec cordon de serrage', 12, 25],
            ['Mini but pliable 120 x 80 cm pour jardin', 35, 59],
            ['Plots d\'entraînement x20 et sac de transport', 9, 19],
            ['Pompe à ballon double action avec aiguilles', 6, 14],
            ['Sac de sport football 45 L compartiment chaussures', 25, 45],
        ]],
        'Running' => ['produits' => [
            ['Chaussures de running amorti route homme', 59, 159],
            ['Chaussures de trail accroche boue et rochers', 79, 169],
            ['Tee-shirt de running respirant anti-odeur', 15, 35],
            ['Collant de running chaud pour l\'hiver', 25, 55],
            ['Ceinture porte-bidons 2 x 250 ml', 15, 29],
            ['Montre GPS cardio poignet multisport', 129, 349],
            ['Veste coupe-vent ultralégère réfléchissante', 39, 89],
            ['Chaussettes de running anti-ampoules x3', 9, 19],
            ['Lampe frontale 300 lumens rechargeable USB', 25, 59],
            ['Gilet d\'hydratation trail 10 L avec flasques', 49, 119],
        ]],
        'Cyclisme' => ['produits' => [
            ['Casque de vélo route ventilé avec visière', 39, 119],
            ['Cuissard à bretelles peau de chamois', 39, 99],
            ['Maillot de cyclisme manches courtes poches dorsales', 29, 69],
            ['Gants de vélo mi-saison avec gel', 15, 35],
            ['Pompe à pied pour vélo avec manomètre', 19, 45],
            ['Éclairage vélo avant et arrière USB', 19, 49],
            ['Antivol en U haute sécurité avec support', 29, 69],
            ['Compteur vélo GPS avec navigation', 99, 299],
            ['Bidon isotherme 650 ml', 8, 19],
            ['Kit de réparation crevaison avec démonte-pneus', 7, 19],
        ]],
        'Natation' => ['produits' => [
            ['Maillot de bain une pièce chloré-résistant', 19, 49],
            ['Lunettes de natation anti-buée verres miroir', 9, 35],
            ['Bonnet de bain en silicone', 5, 15],
            ['Palmes courtes d\'entraînement', 19, 39],
            ['Planche de natation en mousse', 8, 15],
            ['Pull-buoy pour l\'entraînement des bras', 8, 15],
            ['Combinaison de triathlon néoprène', 149, 349],
            ['Serviette microfibre grand format', 15, 29],
        ]],
        'Tennis' => ['produits' => [
            ['Raquette de tennis adulte cordée 285 g', 49, 199],
            ['Balles de tennis tube de 4', 5, 12],
            ['Chaussures de tennis toutes surfaces', 49, 129],
            ['Sac thermobag pour 6 raquettes', 35, 79],
            ['Surgrips de raquette lot de 3', 6, 14],
            ['Polo de tennis respirant', 19, 45],
            ['Bandeau éponge et poignets', 6, 15],
            ['Cordage multifilament 12 m', 9, 25],
        ]],
        'Basketball' => ['produits' => [
            ['Ballon de basket taille 7 intérieur et extérieur', 19, 59],
            ['Chaussures de basket montantes amorties', 69, 159],
            ['Panier de basket sur pied réglable 2,30 à 3,05 m', 149, 349],
            ['Maillot de basket réversible', 15, 35],
            ['Short de basket ample', 15, 35],
            ['Filet de panier de basket tout temps', 6, 15],
        ]],
        'Randonnée' => ['produits' => [
            ['Chaussures de randonnée imperméables mi-hautes', 69, 179],
            ['Sac à dos de randonnée 30 L avec housse de pluie', 39, 99],
            ['Bâtons de randonnée télescopiques la paire', 25, 79],
            ['Veste imperméable trois couches', 89, 249],
            ['Polaire chaude zippée', 25, 69],
            ['Tente de camping 2 places montage rapide', 59, 199],
            ['Sac de couchage confort 5 °C', 39, 99],
            ['Gourde filtrante 1 L', 25, 49],
            ['Pantalon de randonnée modulable', 29, 69],
        ]],
        'Ski' => ['produits' => [
            ['Skis de piste avec fixations', 199, 549],
            ['Chaussures de ski flex 90', 149, 349],
            ['Casque de ski avec visière intégrée', 59, 169],
            ['Masque de ski anti-buée écran sphérique', 29, 129],
            ['Veste de ski imperméable 10 000 mm', 89, 249],
            ['Pantalon de ski isolé', 59, 149],
            ['Gants de ski chauds imperméables', 25, 69],
            ['Sous-vêtement thermique haut et bas', 25, 59],
        ]],
        'Fitness' => ['produits' => [
            ['Haltères réglables 2 x 10 kg', 39, 89],
            ['Tapis de fitness épais 15 mm', 15, 39],
            ['Kettlebell en fonte 12 kg', 25, 49],
            ['Élastiques de résistance lot de 5', 12, 29],
            ['Banc de musculation inclinable', 89, 249],
            ['Corde à sauter roulements à billes', 8, 25],
            ['Rouleau de massage en mousse', 15, 35],
            ['Vélo d\'appartement magnétique', 199, 499],
            ['Brassière de sport maintien fort', 19, 45],
        ]],
        'Arts martiaux' => ['produits' => [
            ['Kimono de judo coton 450 g', 35, 89],
            ['Gants de boxe 12 oz cuir synthétique', 29, 79],
            ['Sac de frappe 120 cm avec chaîne', 79, 179],
            ['Protège-dents thermoformable', 6, 19],
            ['Bandes de boxe 4 m la paire', 6, 15],
            ['Ceinture d\'arts martiaux couleur', 5, 12],
        ]],
        'Rugby' => ['produits' => [
            ['Ballon de rugby match taille 5', 25, 59],
            ['Crampons de rugby vissés', 59, 139],
            ['Maillot de rugby renforcé', 29, 65],
            ['Casque de rugby protection mousse', 29, 69],
            ['Plastron de protection épaules', 35, 79],
        ]],
        'Escalade' => ['produits' => [
            ['Chaussons d\'escalade polyvalents', 59, 139],
            ['Baudrier d\'escalade réglable', 45, 99],
            ['Corde d\'escalade dynamique 60 m', 99, 229],
            ['Sac à magnésie avec ceinture', 12, 29],
            ['Dégaines express lot de 6', 69, 139],
            ['Casque d\'escalade léger', 45, 99],
        ]],
        'Golf' => ['produits' => [
            ['Série de fers de golf 5 à PW', 299, 799],
            ['Balles de golf lot de 12', 15, 49],
            ['Sac de golf trépied 14 compartiments', 99, 249],
            ['Gant de golf cuir cabretta', 12, 29],
            ['Chariot de golf 3 roues pliable', 79, 199],
        ]],
        'Yoga' => ['produits' => [
            ['Tapis de yoga antidérapant 6 mm', 19, 69],
            ['Brique de yoga en liège', 9, 19],
            ['Sangle de yoga coton 2,5 m', 7, 15],
            ['Legging de yoga taille haute', 25, 59],
            ['Coussin de méditation', 25, 49],
        ]],
    ];

    /**
     * Crée les rayons, les fournisseurs et les produits avec leurs stocks.
     */
    public function run(): void
    {
        $fournisseurs = collect(self::FOURNISSEURS)->map(fn (string $nom) => Fournisseur::query()->create([
            'nom_fournisseur' => $nom,
            'code_fournisseur' => Fournisseur::codeDepuisNom($nom),
        ]));

        $magasins = Magasin::query()->get();

        foreach (self::RAYONS as $nomRayon => ['produits' => $produits]) {
            $rayon = Rayon::query()->create([
                'nom_rayon' => $nomRayon,
                'code_rayon' => strtoupper(substr(Fournisseur::codeDepuisNom($nomRayon), 0, 3)),
            ]);

            foreach ($produits as [$description, $prixMin, $prixMax]) {
                $prixVente = fake()->randomFloat(0, $prixMin, $prixMax) - 0.01;

                /** @var Produit $produit */
                $produit = Produit::factory()->create([
                    'description_produit' => $description,
                    'prix_vente' => $prixVente,
                    'cout_unitaire_ht' => round($prixVente / fake()->randomFloat(2, 1.6, 2.4), 2),
                    'quantite_stock_internet' => fake()->randomElement([0, 2, 4, ...range(8, 120, 7)]),
                    'lieu_stockage' => sprintf('M%d%s%d.%d', random_int(1, 6), fake()->randomElement(range('A', 'L')), random_int(1, 12), random_int(1, 8)),
                    'fournisseur_id' => $fournisseurs->random()->id,
                    'rayon_id' => $rayon->id,
                    'created_at' => now()->subDays(random_int(0, 365)),
                ]);

                $produit->photos()->createMany(
                    collect(range(1, 5))->shuffle()->take(random_int(1, 5))->map(fn (int $variante): array => [
                        'url_photo' => '/images/produits/'.strtolower($rayon->code_rayon)."-{$variante}.svg",
                    ])->values()
                );

                $produit->magasins()->attach(
                    $magasins->random(random_int(4, $magasins->count()))->mapWithKeys(fn (Magasin $magasin) => [
                        $magasin->id => ['quantite_stock_magasin' => fake()->randomElement([0, 1, 3, ...range(5, 40, 5)])],
                    ])
                );
            }
        }
    }
}
