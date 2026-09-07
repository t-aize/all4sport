<?php

namespace App\Enums;

/**
 * Le rôle d'un collaborateur ALL4SPORT, utilisé pour savoir qui peut faire
 * évoluer manuellement l'état d'une commande.
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
}
