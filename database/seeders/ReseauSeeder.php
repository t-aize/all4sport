<?php

namespace Database\Seeders;

use App\Models\Entrepot;
use App\Models\Magasin;
use Illuminate\Database\Seeder;

/**
 * Réseau logistique et commercial d'ALL4SPORT, conforme au dossier de
 * contexte : 3 entrepôts (Le Havre, Lyon, Marseille) et 12 magasins (Lille,
 * Arras, Amiens, Paris, Reims, Metz, Rennes, Tours, Lyon, Bordeaux, Toulouse,
 * Nice), chaque magasin dépendant de l'entrepôt de sa zone d'expédition.
 */
class ReseauSeeder extends Seeder
{
    /**
     * Les entrepôts et leur zone d'expédition.
     *
     * @var array<string, string>
     */
    public const ENTREPOTS = [
        'Le Havre' => 'Nord-Ouest',
        'Lyon' => 'Centre-Est',
        'Marseille' => 'Sud',
    ];

    /**
     * Les magasins : ville => [adresse, entrepôt qui le dessert].
     *
     * @var array<string, array{0: string, 1: string}>
     */
    public const MAGASINS = [
        'Lille' => ['42 rue Nationale', 'Le Havre'],
        'Arras' => ['8 place des Héros', 'Le Havre'],
        'Amiens' => ['15 rue des Trois Cailloux', 'Le Havre'],
        'Paris' => ['120 boulevard Haussmann', 'Le Havre'],
        'Rennes' => ['3 quai Lamartine', 'Le Havre'],
        'Tours' => ['27 rue Nationale', 'Le Havre'],
        'Reims' => ['11 place Drouet d\'Erlon', 'Lyon'],
        'Metz' => ['5 rue Serpenoise', 'Lyon'],
        'Lyon' => ['64 rue de la République', 'Lyon'],
        'Bordeaux' => ['90 rue Sainte-Catherine', 'Marseille'],
        'Toulouse' => ['18 rue d\'Alsace-Lorraine', 'Marseille'],
        'Nice' => ['2 avenue Jean Médecin', 'Marseille'],
    ];

    /**
     * Crée les entrepôts puis les magasins.
     */
    public function run(): void
    {
        $entrepots = collect(self::ENTREPOTS)->mapWithKeys(fn (string $zone, string $ville) => [
            $ville => Entrepot::query()->create(['ville' => $ville, 'zone_expedition' => $zone]),
        ]);

        foreach (self::MAGASINS as $ville => [$adresse, $entrepot]) {
            Magasin::query()->create([
                'ville' => $ville,
                'adresse' => $adresse,
                'entrepot_id' => $entrepots[$entrepot]->id,
            ]);
        }
    }
}
