<?php

namespace App\Enums;

/**
 * Le rôle d'un collaborateur ALL4SPORT, utilisé pour savoir qui peut faire
 * évoluer manuellement l'état d'une commande.
 *
 * Chaque rôle est responsable d'un ou plusieurs états de la commande
 * (voir `etatsGerables`). L'état "transmise" n'appartient à aucun rôle : il
 * est enregistré automatiquement quand le client valide sa commande.
 */
enum RoleEmploye: string
{
    /**
     * Prend en charge la préparation et l'expédition d'une commande.
     */
    case Operateur = 'operateur';

    /**
     * Confirme le retrait d'une commande en magasin.
     */
    case Vendeur = 'vendeur';

    /**
     * Confirme la livraison d'une commande à domicile.
     */
    case Livreur = 'livreur';

    /**
     * Valide une commande transmise par le client.
     */
    case ServiceClient = 'service_client';

    /**
     * Le nom du rôle tel qu'il est affiché aux utilisateurs.
     */
    public function libelle(): string
    {
        return match ($this) {
            self::Operateur => 'Opérateur',
            self::Vendeur => 'Vendeur',
            self::Livreur => 'Livreur',
            self::ServiceClient => 'Service client',
        };
    }

    /**
     * Les états de commande que ce rôle a le droit d'enregistrer.
     *
     * @return list<EtatCommande>
     */
    public function etatsGerables(): array
    {
        return match ($this) {
            self::ServiceClient => [EtatCommande::Validee],
            self::Operateur => [EtatCommande::EnPreparation, EtatCommande::Expediee],
            self::Livreur => [EtatCommande::Livree],
            self::Vendeur => [EtatCommande::Retiree],
        };
    }

    /**
     * Le rôle responsable d'un état de commande, ou null si l'état est
     * enregistré automatiquement (cas de "transmise").
     */
    public static function responsableDe(EtatCommande $etat): ?self
    {
        foreach (self::cases() as $role) {
            if (in_array($etat, $role->etatsGerables(), true)) {
                return $role;
            }
        }

        return null;
    }
}
