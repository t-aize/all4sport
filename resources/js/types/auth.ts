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

export type Auth = {
    user: User | null;
};
