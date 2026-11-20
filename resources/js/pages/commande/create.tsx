import { Form, Link } from '@inertiajs/react';
import {
    ArrowLeft,
    CircleAlert,
    CreditCard,
    Home,
    Lock,
    MapPin,
    Store,
} from 'lucide-react';
import { useState } from 'react';

import { store } from '@/actions/App/Http/Controllers/CommandeController';
import { Button } from '@/components/ui/button';
import { Field, FieldError, FieldLabel } from '@/components/ui/field';
import { Input } from '@/components/ui/input';
import SiteLayout from '@/layouts/site-layout';
import { formatPrix, pluriel } from '@/lib/format';
import { index as panierIndex } from '@/routes/panier';

type Ligne = {
    reference: string;
    description: string;
    photo: string | null;
    quantite: number;
    prixUnitaire: string;
    sousTotal: string;
};

type Magasin = {
    id: number;
    ville: string;
    adresse: string;
    disponible: boolean;
};

type Mode = {
    value: 'retrait_magasin' | 'livraison_domicile';
    label: string;
    frais: string;
};

/**
 * Validation du panier (route "commande.create") : choix du mode de
 * livraison (retrait magasin gratuit ou livraison à domicile à 10 €), puis
 * paiement. Les stocks sont vérifiés par le serveur à l'envoi ; s'ils sont
 * insuffisants, la validation est annulée et le client revient au panier.
 */
export default function CommandeCreate({
    panier,
    magasins,
    disponibleInternet,
    client,
    entrepotExpediteur,
    modes,
}: {
    panier: { lignes: Ligne[]; total: string; nombreArticles: number };
    magasins: Magasin[];
    disponibleInternet: boolean;
    client: {
        magasinId: number | null;
        adresse: string;
        codePostal: string;
        ville: string;
    };
    entrepotExpediteur: string | null;
    modes: Mode[];
}) {
    const [mode, setMode] = useState<Mode['value']>('retrait_magasin');
    const [magasinId, setMagasinId] = useState<number | null>(
        client.magasinId ??
            magasins.find((magasin) => magasin.disponible)?.id ??
            null,
    );
    const frais = Number(
        modes.find((option) => option.value === mode)?.frais ?? 0,
    );
    const total = Number(panier.total) + frais;
    const magasinChoisi = magasins.find((magasin) => magasin.id === magasinId);

    return (
        <SiteLayout title="Livraison et paiement">
            <Link
                href={panierIndex()}
                className="text-muted-foreground hover:text-foreground mb-6 inline-flex items-center gap-1 text-sm"
            >
                <ArrowLeft className="size-4" /> Retour au panier
            </Link>

            <ol
                className="mb-8 flex items-center gap-2 text-sm font-medium"
                aria-label="Étapes"
            >
                <li className="text-muted-foreground">1. Panier</li>
                <li className="bg-border h-px w-8" />
                <li className="text-primary">2. Livraison & paiement</li>
                <li className="bg-border h-px w-8" />
                <li className="text-muted-foreground">3. Confirmation</li>
            </ol>

            <Form
                {...store.form()}
                className="grid gap-8 lg:grid-cols-[1fr_380px]"
            >
                {({ errors, processing }) => (
                    <>
                        <div className="flex flex-col gap-6">
                            <section className="animate-apparition">
                                <h1 className="mb-4 text-2xl font-bold tracking-tight">
                                    Mode de livraison
                                </h1>
                                <div className="grid gap-3 sm:grid-cols-2">
                                    {modes.map((option) => {
                                        const Icone =
                                            option.value === 'retrait_magasin'
                                                ? Store
                                                : Home;
                                        const actif = mode === option.value;

                                        return (
                                            <label
                                                key={option.value}
                                                className={`relative flex cursor-pointer flex-col gap-2 rounded-2xl border-2 p-5 transition-all ${
                                                    actif
                                                        ? 'border-primary bg-primary/5 shadow-primary/10 shadow-lg'
                                                        : 'hover:border-primary/40'
                                                }`}
                                            >
                                                <input
                                                    type="radio"
                                                    name="mode_livraison"
                                                    value={option.value}
                                                    checked={actif}
                                                    onChange={() =>
                                                        setMode(option.value)
                                                    }
                                                    className="sr-only"
                                                />
                                                <Icone
                                                    className={`size-6 ${actif ? 'text-primary' : 'text-muted-foreground'}`}
                                                />
                                                <span className="font-semibold">
                                                    {option.label}
                                                </span>
                                                <span className="text-sm font-bold">
                                                    {Number(option.frais) === 0
                                                        ? 'Gratuit'
                                                        : formatPrix(
                                                              option.frais,
                                                          )}
                                                </span>
                                                <span className="text-muted-foreground text-xs">
                                                    {option.value ===
                                                    'retrait_magasin'
                                                        ? 'Récupérez votre commande dans le magasin de votre choix.'
                                                        : `Expédiée depuis l'entrepôt de ${entrepotExpediteur ?? 'votre zone'}.`}
                                                </span>
                                            </label>
                                        );
                                    })}
                                </div>
                                <FieldError>{errors.mode_livraison}</FieldError>
                            </section>

                            {mode === 'retrait_magasin' ? (
                                <section
                                    key="retrait"
                                    className="animate-in fade-in slide-in-from-bottom-2 duration-300"
                                >
                                    <h2 className="mb-1 text-lg font-semibold">
                                        Magasin de retrait
                                    </h2>
                                    <p className="text-muted-foreground mb-4 text-sm">
                                        Le stock du magasin choisi est vérifié
                                        pour chaque article.
                                    </p>
                                    <input
                                        type="hidden"
                                        name="magasin_id"
                                        value={magasinId ?? ''}
                                    />
                                    <div className="grid gap-2 sm:grid-cols-2">
                                        {magasins.map((magasin) => (
                                            <button
                                                key={magasin.id}
                                                type="button"
                                                onClick={() =>
                                                    setMagasinId(magasin.id)
                                                }
                                                className={`flex items-start gap-3 rounded-xl border p-3 text-left transition-all ${
                                                    magasinId === magasin.id
                                                        ? 'border-primary bg-primary/5 ring-primary/30 ring-2'
                                                        : 'hover:border-primary/40'
                                                }`}
                                            >
                                                <MapPin className="text-primary mt-0.5 size-4 shrink-0" />
                                                <span className="flex-1">
                                                    <span className="flex items-center justify-between gap-2 font-medium">
                                                        {magasin.ville}
                                                        {client.magasinId ===
                                                            magasin.id && (
                                                            <span className="bg-navy text-navy-foreground rounded-full px-2 py-0.5 text-[10px]">
                                                                Par défaut
                                                            </span>
                                                        )}
                                                    </span>
                                                    <span className="text-muted-foreground block text-xs">
                                                        {magasin.adresse}
                                                    </span>
                                                    <span
                                                        className={`mt-1 block text-xs font-semibold ${magasin.disponible ? 'text-emerald-600' : 'text-amber-600'}`}
                                                    >
                                                        {magasin.disponible
                                                            ? 'Tout est disponible'
                                                            : 'Stock insuffisant pour certains articles'}
                                                    </span>
                                                </span>
                                            </button>
                                        ))}
                                    </div>
                                    <FieldError>{errors.magasin_id}</FieldError>
                                </section>
                            ) : (
                                <section
                                    key="domicile"
                                    className="animate-in fade-in slide-in-from-bottom-2 flex flex-col gap-4 duration-300"
                                >
                                    <div>
                                        <h2 className="mb-1 text-lg font-semibold">
                                            Adresse de livraison
                                        </h2>
                                        <p className="text-muted-foreground text-sm">
                                            Pré-remplie avec l'adresse de votre
                                            compte.
                                        </p>
                                    </div>
                                    {!disponibleInternet && (
                                        <p className="flex items-center gap-2 rounded-xl bg-amber-500/10 p-3 text-sm text-amber-700">
                                            <CircleAlert className="size-4" />{' '}
                                            Certains articles ne sont pas
                                            disponibles en stock Internet.
                                        </p>
                                    )}
                                    <Field data-invalid={!!errors.adresse}>
                                        <FieldLabel htmlFor="adresse">
                                            Adresse
                                        </FieldLabel>
                                        <Input
                                            id="adresse"
                                            name="adresse"
                                            defaultValue={client.adresse}
                                            maxLength={50}
                                            aria-invalid={!!errors.adresse}
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
                                                inputMode="numeric"
                                                aria-invalid={
                                                    !!errors.code_postal
                                                }
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
                                                aria-invalid={!!errors.ville}
                                            />
                                            <FieldError>
                                                {errors.ville}
                                            </FieldError>
                                        </Field>
                                    </div>
                                </section>
                            )}

                            <section className="bg-muted/40 rounded-2xl border p-5">
                                <h2 className="mb-2 flex items-center gap-2 text-lg font-semibold">
                                    <CreditCard className="text-primary size-5" />{' '}
                                    Paiement
                                </h2>
                                <p className="text-muted-foreground text-sm">
                                    Le paiement est simulé dans cette version de
                                    démonstration : la commande est considérée
                                    comme payée dès sa validation, puis
                                    transmise au service client.
                                </p>
                            </section>
                        </div>

                        <aside className="bg-card h-fit rounded-3xl border p-6 shadow-sm lg:sticky lg:top-24">
                            <h2 className="mb-4 text-lg font-semibold">
                                Votre commande (
                                {pluriel(panier.nombreArticles, 'article')})
                            </h2>
                            <ul className="mb-4 flex max-h-64 flex-col gap-3 overflow-y-auto pr-1">
                                {panier.lignes.map((ligne) => (
                                    <li
                                        key={ligne.reference}
                                        className="flex items-center gap-3 text-sm"
                                    >
                                        <span className="bg-muted relative size-12 shrink-0 overflow-hidden rounded-lg">
                                            {ligne.photo && (
                                                <img
                                                    src={ligne.photo}
                                                    alt=""
                                                    className="size-full object-cover"
                                                />
                                            )}
                                            <span className="bg-navy text-navy-foreground absolute -top-0 -right-0 rounded-bl-md px-1 text-[10px] font-bold">
                                                x{ligne.quantite}
                                            </span>
                                        </span>
                                        <span className="line-clamp-2 flex-1">
                                            {ligne.description}
                                        </span>
                                        <span className="font-medium">
                                            {formatPrix(ligne.sousTotal)}
                                        </span>
                                    </li>
                                ))}
                            </ul>
                            <dl className="flex flex-col gap-2 border-t pt-4 text-sm">
                                <div className="flex justify-between">
                                    <dt className="text-muted-foreground">
                                        Sous-total
                                    </dt>
                                    <dd>{formatPrix(panier.total)}</dd>
                                </div>
                                <div className="flex justify-between">
                                    <dt className="text-muted-foreground">
                                        {mode === 'retrait_magasin'
                                            ? `Retrait${magasinChoisi ? ` à ${magasinChoisi.ville}` : ''}`
                                            : 'Livraison à domicile'}
                                    </dt>
                                    <dd>
                                        {frais === 0
                                            ? 'Gratuit'
                                            : formatPrix(frais)}
                                    </dd>
                                </div>
                            </dl>
                            <div className="my-4 flex items-baseline justify-between border-t pt-4">
                                <span className="font-semibold">Total TTC</span>
                                <span
                                    key={total}
                                    className="animate-in fade-in text-2xl font-black"
                                >
                                    {formatPrix(total)}
                                </span>
                            </div>
                            <label className="mb-4 flex items-start gap-2 text-xs">
                                <input
                                    type="checkbox"
                                    name="accepte_conditions"
                                    value="1"
                                    className="accent-primary mt-0.5"
                                />
                                <span>
                                    J'accepte les conditions générales de vente.
                                </span>
                            </label>
                            <FieldError>{errors.accepte_conditions}</FieldError>
                            <Button
                                type="submit"
                                size="lg"
                                disabled={processing}
                                className="h-12 w-full rounded-xl text-base"
                            >
                                <Lock />{' '}
                                {processing
                                    ? 'Vérification des stocks...'
                                    : `Payer ${formatPrix(total)}`}
                            </Button>
                        </aside>
                    </>
                )}
            </Form>
        </SiteLayout>
    );
}
