/**
 * Types partagés par les pages du site marchand et du back-office.
 */

/**
 * Un lien de pagination Laravel.
 */
export type LienPagination = {
    url: string | null;
    label: string;
    active: boolean;
};

/**
 * Une page de résultats paginés renvoyée par `paginate()` côté Laravel.
 */
export type Pagine<T> = {
    data: T[];
    links: LienPagination[];
    current_page: number;
    last_page: number;
    total: number;
    from: number | null;
    to: number | null;
};

/**
 * Un produit tel qu'il apparaît sur une carte du catalogue.
 */
export type ProduitCarte = {
    reference: string;
    description: string;
    prixVente: string;
    rayon: string | null;
    photo: string | null;
    stockInternet?: number;
};

/**
 * Les états de commande (valeurs de l'enum PHP EtatCommande).
 */
export type EtatCommande =
    | 'transmise'
    | 'validee'
    | 'en_preparation'
    | 'expediee'
    | 'livree'
    | 'retiree';

/**
 * Une option { valeur, libellé } envoyée par Laravel.
 */
export type Option = {
    value: string;
    label: string;
};

/**
 * Une ligne de commande (produit commandé).
 */
export type LigneCommande = {
    reference: string;
    description: string;
    photo: string | null;
    quantite: number;
    prixUnitaire: string;
    sousTotal: string;
};

/**
 * Une étape du suivi d'une commande.
 */
export type EtapeSuivi = {
    etat: EtatCommande;
    libelle: string;
    date: string | null;
};

/**
 * Le détail complet d'une commande (client et back-office).
 */
export type CommandeDetail = {
    id: number;
    numero: string;
    date: string;
    modeLivraison: 'retrait_magasin' | 'livraison_domicile';
    modeLivraisonLibelle: string;
    etat: EtatCommande;
    etatLibelle: string;
    lieuExpedition: string | null;
    adresseLivraison: string | null;
    magasin: { ville: string; adresse: string } | null;
    fraisLivraison: string;
    sousTotal: string;
    montantTotal: string;
    lignes: LigneCommande[];
    suivi: EtapeSuivi[];
};
