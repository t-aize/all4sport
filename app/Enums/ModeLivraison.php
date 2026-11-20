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

    /**
     * Forfait de livraison à domicile, en euros (dossier de contexte).
     */
    public const FORFAIT_DOMICILE = 10.0;

    /**
     * Le nom du mode de livraison tel qu'il est affiché aux utilisateurs.
     */
    public function libelle(): string
    {
        return match ($this) {
            self::RetraitMagasin => 'Retrait en magasin',
            self::LivraisonDomicile => 'Livraison à domicile',
        };
    }

    /**
     * Les frais de livraison facturés : retrait gratuit, domicile 10 €.
     */
    public function frais(): float
    {
        return match ($this) {
            self::RetraitMagasin => 0.0,
            self::LivraisonDomicile => self::FORFAIT_DOMICILE,
        };
    }
}
