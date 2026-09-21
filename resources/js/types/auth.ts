/**
 * Le client connecté, tel que l'envoie Laravel au navigateur.
 *
 * Les champs reprennent les colonnes de la table `clients`. Le mot de passe
 * n'y figure volontairement pas : le modèle PHP `Client` le masque (attribut
 * #[Hidden]) avant l'envoi.
 */
export type User = {
    id: number;
    nom: string;
    prenom: string;
    email: string;
    telephone: string | null;
    adresse: string;
    code_postal: string;
    ville: string;
    date_naissance: string | null;
    date_dernier_achat: string | null;
    created_at: string;
    updated_at: string;
    [key: string]: unknown; // This allows for additional properties...
};

/**
 * Données d'authentification partagées avec toutes les pages
 * (voir HandleInertiaRequests::share côté PHP).
 *
 * `user` vaut `null` tant que le visiteur n'est pas connecté.
 */
export type Auth = {
    user: User | null;
};
