import { Form, router } from '@inertiajs/react';
import {
    Baby,
    KeyRound,
    MapPin,
    Trash2,
    Trophy,
    UserRound,
} from 'lucide-react';
import { type ReactNode } from 'react';

import {
    store as ajouterEnfant,
    destroy as retirerEnfant,
} from '@/actions/App/Http/Controllers/Compte/EnfantController';
import { update as changerMotDePasse } from '@/actions/App/Http/Controllers/Compte/MotDePasseController';
import { update as enregistrerProfil } from '@/actions/App/Http/Controllers/Compte/ProfilController';
import { Button } from '@/components/ui/button';
import {
    Field,
    FieldError,
    FieldGroup,
    FieldLabel,
} from '@/components/ui/field';
import { Input } from '@/components/ui/input';
import CompteLayout from '@/layouts/compte-layout';
import { formatDate, pluriel } from '@/lib/format';

type Client = {
    codeCli: string;
    nom: string;
    prenom: string;
    email: string;
    telephone: string | null;
    adresse: string;
    codePostal: string;
    ville: string;
    dateNaissance: string | null;
    dateDernierAchat: string | null;
    magasinId: number | null;
    rayons: number[];
    inscritLe: string | null;
    nombreCommandes: number;
};

type Enfant = { id: number; dateNaissance: string; age: number };
type Magasin = { id: number; ville: string; adresse: string };
type Rayon = { id: number; nom: string };

/**
 * Bloc titré de la page de profil.
 */
function Bloc({
    icone: Icone,
    titre,
    description,
    children,
}: {
    icone: typeof UserRound;
    titre: string;
    description: string;
    children: ReactNode;
    delai?: number;
}) {
    return (
        <section className="bg-card rounded-3xl border p-6">
            <div className="mb-5 flex items-start gap-3">
                <span className="bg-primary/10 text-primary flex size-10 shrink-0 items-center justify-center rounded-xl">
                    <Icone className="size-5" />
                </span>
                <div>
                    <h2 className="font-semibold">{titre}</h2>
                    <p className="text-muted-foreground text-sm">
                        {description}
                    </p>
                </div>
            </div>
            {children}
        </section>
    );
}

/**
 * Espace « Mon compte » (route « compte.profil.edit ») : informations
 * personnelles, adresse, magasin par défaut, sports pratiqués, enfants et mot
 * de passe — le fichier client décrit dans le dossier de contexte.
 */
export default function Profil({
    client,
    enfants,
    magasins,
    rayons,
}: {
    client: Client;
    enfants: Enfant[];
    magasins: Magasin[];
    rayons: Rayon[];
}) {
    return (
        <CompteLayout title="Mon compte">
            <div className="mb-6 grid gap-3 sm:grid-cols-3">
                {[
                    {
                        libelle: 'Client depuis',
                        valeur: formatDate(client.inscritLe),
                    },
                    {
                        libelle: 'Commandes passées',
                        valeur: String(client.nombreCommandes),
                    },
                    {
                        libelle: 'Dernier achat',
                        valeur: formatDate(client.dateDernierAchat),
                    },
                ].map((statistique) => (
                    <div
                        key={statistique.libelle}
                        className="bg-card rounded-2xl border p-4"
                    >
                        <p className="text-muted-foreground text-xs">
                            {statistique.libelle}
                        </p>
                        <p className="text-lg font-bold">
                            {statistique.valeur}
                        </p>
                    </div>
                ))}
            </div>

            <div className="grid gap-6 lg:grid-cols-[1fr_380px]">
                <Form
                    {...enregistrerProfil.form()}
                    options={{ preserveScroll: true }}
                    className="flex flex-col gap-6"
                >
                    {({ errors, processing, recentlySuccessful }) => (
                        <>
                            <Bloc
                                icone={UserRound}
                                titre="Informations personnelles"
                                description="Vos coordonnées et votre adresse complète."
                            >
                                <FieldGroup>
                                    <div className="grid gap-4 sm:grid-cols-2">
                                        <Field data-invalid={!!errors.prenom}>
                                            <FieldLabel htmlFor="prenom">
                                                Prénom
                                            </FieldLabel>
                                            <Input
                                                id="prenom"
                                                name="prenom"
                                                defaultValue={client.prenom}
                                                maxLength={30}
                                            />
                                            <FieldError>
                                                {errors.prenom}
                                            </FieldError>
                                        </Field>
                                        <Field data-invalid={!!errors.nom}>
                                            <FieldLabel htmlFor="nom">
                                                Nom
                                            </FieldLabel>
                                            <Input
                                                id="nom"
                                                name="nom"
                                                defaultValue={client.nom}
                                                maxLength={30}
                                            />
                                            <FieldError>
                                                {errors.nom}
                                            </FieldError>
                                        </Field>
                                        <Field data-invalid={!!errors.email}>
                                            <FieldLabel htmlFor="email">
                                                Email
                                            </FieldLabel>
                                            <Input
                                                id="email"
                                                name="email"
                                                type="email"
                                                defaultValue={client.email}
                                                maxLength={80}
                                            />
                                            <FieldError>
                                                {errors.email}
                                            </FieldError>
                                        </Field>
                                        <Field
                                            data-invalid={!!errors.telephone}
                                        >
                                            <FieldLabel htmlFor="telephone">
                                                Téléphone
                                            </FieldLabel>
                                            <Input
                                                id="telephone"
                                                name="telephone"
                                                defaultValue={
                                                    client.telephone ?? ''
                                                }
                                                maxLength={15}
                                            />
                                            <FieldError>
                                                {errors.telephone}
                                            </FieldError>
                                        </Field>
                                        <Field
                                            data-invalid={
                                                !!errors.date_naissance
                                            }
                                        >
                                            <FieldLabel htmlFor="date_naissance">
                                                Date de naissance
                                            </FieldLabel>
                                            <Input
                                                id="date_naissance"
                                                name="date_naissance"
                                                type="date"
                                                defaultValue={
                                                    client.dateNaissance ?? ''
                                                }
                                            />
                                            <FieldError>
                                                {errors.date_naissance}
                                            </FieldError>
                                        </Field>
                                    </div>
                                    <Field data-invalid={!!errors.adresse}>
                                        <FieldLabel htmlFor="adresse">
                                            Adresse
                                        </FieldLabel>
                                        <Input
                                            id="adresse"
                                            name="adresse"
                                            defaultValue={client.adresse}
                                            maxLength={50}
                                        />
                                        <FieldError>
                                            {errors.adresse}
                                        </FieldError>
                                    </Field>
                                    <div className="grid gap-4 sm:grid-cols-[160px_1fr]">
                                        <Field
                                            data-invalid={!!errors.code_postal}
                                        >
                                            <FieldLabel htmlFor="code_postal">
                                                Code postal
                                            </FieldLabel>
                                            <Input
                                                id="code_postal"
                                                name="code_postal"
                                                defaultValue={client.codePostal}
                                                maxLength={5}
                                            />
                                            <FieldError>
                                                {errors.code_postal}
                                            </FieldError>
                                        </Field>
                                        <Field data-invalid={!!errors.ville}>
                                            <FieldLabel htmlFor="ville">
                                                Ville
                                            </FieldLabel>
                                            <Input
                                                id="ville"
                                                name="ville"
                                                defaultValue={client.ville}
                                                maxLength={30}
                                            />
                                            <FieldError>
                                                {errors.ville}
                                            </FieldError>
                                        </Field>
                                    </div>
                                </FieldGroup>
                            </Bloc>

                            <Bloc
                                icone={MapPin}
                                titre="Magasin par défaut"
                                description="Proposé en premier pour le retrait de vos commandes."
                                delai={80}
                            >
                                <select
                                    name="magasin_id"
                                    defaultValue={client.magasinId ?? ''}
                                    className="border-input bg-background h-10 w-full rounded-lg border px-3 text-sm"
                                >
                                    <option value="">
                                        Aucun magasin par défaut
                                    </option>
                                    {magasins.map((magasin) => (
                                        <option
                                            key={magasin.id}
                                            value={magasin.id}
                                        >
                                            {magasin.ville} — {magasin.adresse}
                                        </option>
                                    ))}
                                </select>
                                <FieldError>{errors.magasin_id}</FieldError>
                            </Bloc>

                            <Bloc
                                icone={Trophy}
                                titre="Sports pratiqués"
                                description="Pour vous proposer les articles qui vous correspondent."
                                delai={160}
                            >
                                <div className="flex flex-wrap gap-2">
                                    {rayons.map((rayon) => (
                                        <label
                                            key={rayon.id}
                                            className="cursor-pointer"
                                        >
                                            <input
                                                type="checkbox"
                                                name="rayons[]"
                                                value={rayon.id}
                                                defaultChecked={client.rayons.includes(
                                                    rayon.id,
                                                )}
                                                className="peer sr-only"
                                            />
                                            <span className="peer-checked:bg-primary peer-checked:text-primary-foreground peer-checked:border-primary hover:border-primary/50 peer-focus-visible:ring-ring/50 inline-flex rounded-full border px-3 py-1.5 text-sm transition-all peer-focus-visible:ring-3">
                                                {rayon.nom}
                                            </span>
                                        </label>
                                    ))}
                                </div>
                            </Bloc>

                            <div className="flex items-center justify-end gap-3">
                                {recentlySuccessful && (
                                    <span className="animate-in fade-in text-sm text-emerald-600">
                                        Enregistré ✓
                                    </span>
                                )}
                                <Button
                                    type="submit"
                                    size="lg"
                                    disabled={processing}
                                    className="rounded-xl"
                                >
                                    {processing
                                        ? 'Enregistrement…'
                                        : 'Enregistrer mes informations'}
                                </Button>
                            </div>
                        </>
                    )}
                </Form>

                <div className="flex flex-col gap-6">
                    <Bloc
                        icone={Baby}
                        titre="Mes enfants"
                        description={pluriel(
                            enfants.length,
                            'enfant déclaré',
                            'enfants déclarés',
                        )}
                        delai={120}
                    >
                        <ul className="mb-4 flex flex-col gap-2">
                            {enfants.map((enfant) => (
                                <li
                                    key={enfant.id}
                                    className="bg-muted/60 animate-in fade-in flex items-center justify-between rounded-xl px-3 py-2 text-sm"
                                >
                                    <span>
                                        <span className="font-semibold">
                                            {pluriel(enfant.age, 'an')}
                                        </span>
                                        <span className="text-muted-foreground">
                                            {' '}
                                            · né(e) le{' '}
                                            {formatDate(enfant.dateNaissance)}
                                        </span>
                                    </span>
                                    <button
                                        type="button"
                                        onClick={() =>
                                            router.delete(
                                                retirerEnfant.url(enfant.id),
                                                { preserveScroll: true },
                                            )
                                        }
                                        className="text-muted-foreground hover:text-destructive rounded-md p-1"
                                        aria-label="Retirer cet enfant"
                                    >
                                        <Trash2 className="size-4" />
                                    </button>
                                </li>
                            ))}
                        </ul>
                        <Form
                            {...ajouterEnfant.form()}
                            options={{ preserveScroll: true }}
                            resetOnSuccess
                            className="flex gap-2"
                        >
                            {({ errors, processing }) => (
                                <div className="flex w-full flex-col gap-1">
                                    <div className="flex gap-2">
                                        <Input
                                            name="date_naissance"
                                            type="date"
                                            aria-label="Date de naissance de l'enfant"
                                        />
                                        <Button
                                            type="submit"
                                            variant="outline"
                                            disabled={processing}
                                        >
                                            Ajouter
                                        </Button>
                                    </div>
                                    <FieldError>
                                        {errors.date_naissance}
                                    </FieldError>
                                </div>
                            )}
                        </Form>
                    </Bloc>

                    <Bloc
                        icone={KeyRound}
                        titre="Mot de passe"
                        description="Votre mot de passe est stocké chiffré (haché)."
                        delai={200}
                    >
                        <Form
                            {...changerMotDePasse.form()}
                            options={{ preserveScroll: true }}
                            resetOnSuccess
                        >
                            {({ errors, processing, recentlySuccessful }) => (
                                <FieldGroup>
                                    <Field
                                        data-invalid={
                                            !!errors.mot_de_passe_actuel
                                        }
                                    >
                                        <FieldLabel htmlFor="mot_de_passe_actuel">
                                            Mot de passe actuel
                                        </FieldLabel>
                                        <Input
                                            id="mot_de_passe_actuel"
                                            name="mot_de_passe_actuel"
                                            type="password"
                                            autoComplete="current-password"
                                        />
                                        <FieldError>
                                            {errors.mot_de_passe_actuel}
                                        </FieldError>
                                    </Field>
                                    <Field data-invalid={!!errors.password}>
                                        <FieldLabel htmlFor="password">
                                            Nouveau mot de passe
                                        </FieldLabel>
                                        <Input
                                            id="password"
                                            name="password"
                                            type="password"
                                            autoComplete="new-password"
                                        />
                                        <FieldError>
                                            {errors.password}
                                        </FieldError>
                                    </Field>
                                    <Field>
                                        <FieldLabel htmlFor="password_confirmation">
                                            Confirmation
                                        </FieldLabel>
                                        <Input
                                            id="password_confirmation"
                                            name="password_confirmation"
                                            type="password"
                                            autoComplete="new-password"
                                        />
                                    </Field>
                                    <Button
                                        type="submit"
                                        variant="outline"
                                        disabled={processing}
                                    >
                                        {recentlySuccessful
                                            ? 'Mot de passe modifié ✓'
                                            : 'Changer le mot de passe'}
                                    </Button>
                                </FieldGroup>
                            )}
                        </Form>
                    </Bloc>
                </div>
            </div>
        </CompteLayout>
    );
}
