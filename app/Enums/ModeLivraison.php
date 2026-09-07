<?php

namespace App\Enums;

/**
 * Le mode de livraison choisi par le client pour une commande.
 */
enum ModeLivraison: string
{
    /**
     * La commande est retirée par le client dans un magasin (association RETIRER).
     */
    case RetraitMagasin = 'retrait_magasin';

    /**
     * La commande est livrée au domicile du client, expédiée depuis un entrepôt
     * (association EXPEDIER).
     */
    case LivraisonDomicile = 'livraison_domicile';
}
