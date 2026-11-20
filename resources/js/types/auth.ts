/**
 * Le client connecté, tel que l'envoie Laravel au navigateur.
 *
 * Les champs reprennent les colonnes de la table `clients`. Le mot de passe
 * n'y figure volontairement pas : le modèle PHP `Client` le masque (attribut
 * #[Hidden]) avant l'envoi.
 */
export type User = {
    id: number;
    code_cli: string;
    nom: string;
    prenom: string;
    email: string;
    telephone: string | null;
    adresse: string;
    code_postal: string;
    ville: string;
    date_naissance: string | null;
    date_dernier_achat: string | null;
    magasin_id: number | null;
    created_at: string;
    updated_at: string;
    [key: string]: unknown; // This allows for additional properties...
};

/**
 * Les rôles possibles d'un collaborateur (valeurs de l'enum PHP RoleEmploye).
 */
export type RoleEmploye =
    | 'operateur'
    | 'vendeur'
    | 'livreur'
    | 'service_client';

/**
 * Le collaborateur connecté au back-office, tel que l'envoie Laravel.
 *
 * Les champs reprennent les colonnes de la table `employes`, sans le mot de
 * passe ni le jeton "se souvenir de moi" (masqués par le modèle PHP).
 */
export type Employe = {
    id: number;
    nom: string;
    prenom: string;
    identifiant_connexion: string;
    role: RoleEmploye;
    created_at: string;
    updated_at: string;
};

/**
 * Données d'authentification partagées avec toutes les pages
 * (voir HandleInertiaRequests::share côté PHP).
 *
 * `user` (le client) et `employe` (le collaborateur) valent `null` tant que le
 * visiteur n'est pas connecté dans l'espace correspondant. Les deux espaces
 * sont indépendants.
 */
export type Auth = {
    user: User | null;
    employe: Employe | null;
};
