<?php

namespace App\Enums;

/**
 * Les états successifs du traitement d'une commande, indiqués manuellement
 * par les collaborateurs sur l'application.
 */
enum EtatCommande: string
{
    /**
     * Commande validée et payée par le client, transmise au service client et
     * en attente de validation.
     */
    case Transmise = 'transmise';

    /**
     * Commande confirmée par le service client, en attente de mise en traitement.
     */
    case Validee = 'validee';

    /**
     * Commande prise en charge par un opérateur et en cours de préparation.
     */
    case EnPreparation = 'en_preparation';

    /**
     * Commande expédiée par un opérateur.
     */
    case Expediee = 'expediee';

    /**
     * Commande confirmée comme livrée chez le client par le livreur.
     */
    case Livree = 'livree';

    /**
     * Commande confirmée comme retirée en magasin par le client, par le vendeur.
     */
    case Retiree = 'retiree';
}
