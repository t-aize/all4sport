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

    /**
     * Le nom de l'état tel qu'il est affiché aux utilisateurs.
     */
    public function libelle(): string
    {
        return match ($this) {
            self::Transmise => 'Transmise',
            self::Validee => 'Validée',
            self::EnPreparation => 'En préparation',
            self::Expediee => 'Expédiée',
            self::Livree => 'Livrée',
            self::Retiree => 'Retirée',
        };
    }

    /**
     * L'état qui suit celui-ci dans le traitement d'une commande, selon son
     * mode de livraison, ou null si la commande est terminée.
     *
     * Ordre : transmise -> validée -> en préparation -> expédiée -> livrée (livraison
     * à domicile) ou retirée (retrait en magasin).
     */
    public function suivant(ModeLivraison $modeLivraison): ?self
    {
        return match ($this) {
            self::Transmise => self::Validee,
            self::Validee => self::EnPreparation,
            self::EnPreparation => self::Expediee,
            self::Expediee => $modeLivraison === ModeLivraison::RetraitMagasin ? self::Retiree : self::Livree,
            self::Livree, self::Retiree => null,
        };
    }

    /**
     * Indique si la commande est terminée (livrée ou retirée).
     */
    public function estFinal(): bool
    {
        return $this === self::Livree || $this === self::Retiree;
    }

    /**
     * La liste ordonnée des états traversés par une commande selon son mode
     * de livraison.
     *
     * @return list<self>
     */
    public static function parcours(ModeLivraison $modeLivraison): array
    {
        return [
            self::Transmise,
            self::Validee,
            self::EnPreparation,
            self::Expediee,
            $modeLivraison === ModeLivraison::RetraitMagasin ? self::Retiree : self::Livree,
        ];
    }

    /**
     * Les états sous forme de liste { value, label } pour le front-end.
     *
     * @return list<array{value: string, label: string}>
     */
    public static function options(): array
    {
        return array_map(
            fn (self $etat): array => ['value' => $etat->value, 'label' => $etat->libelle()],
            self::cases(),
        );
    }
}
