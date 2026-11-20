import { Link, router, usePage } from '@inertiajs/react';
import {
    ArrowRight,
    CircleAlert,
    ImageOff,
    Lock,
    Minus,
    Plus,
    ShoppingBag,
    Trash2,
} from 'lucide-react';

import {
    modifier,
    retirer,
    vider,
} from '@/actions/App/Http/Controllers/PanierController';
import { Button } from '@/components/ui/button';
import SiteLayout from '@/layouts/site-layout';
import { formatPrix, pluriel } from '@/lib/format';
import { login } from '@/routes';
import { index as catalogue, show } from '@/routes/catalogue';
import { create as validerCommande } from '@/routes/commande';
import type { Auth } from '@/types';

/**
 * Une ligne du panier, telle que l'envoie PanierController.
 */
type Ligne = {
    reference: string;
    description: string;
    rayon: string | null;
    photo: string | null;
    quantite: number;
    prixUnitaire: string;
    sousTotal: string;
    stockDisponible: number;
};

/**
 * Le panier (route « panier.index ») : modification des quantités, retrait
 * d'articles et validation. Un visiteur peut remplir son panier mais doit se
 * connecter pour le valider (processus de vente en ligne).
 */
export default function PanierIndex({
    panier,
}: {
    panier: { lignes: Ligne[]; total: string; nombreArticles: number };
}) {
    const { auth, errors } = usePage<{
        auth: Auth;
        errors: Record<string, string | string[]>;
    }>().props;
    const erreursStock = ([] as string[]).concat(errors.stock ?? []);

    const changerQuantite = (ligne: Ligne, quantite: number) => {
        router.visit(modifier.url(ligne.reference), {
            method: 'patch',
            data: { quantite },
            preserveScroll: true,
        });
    };

    return (
        <SiteLayout title="Mon panier">
            <div className="animate-apparition mb-8">
                <p className="text-primary text-sm font-semibold uppercase">
                    Panier
                </p>
                <h1 className="text-3xl font-bold tracking-tight sm:text-4xl">
                    Mon panier
                </h1>
                <p className="text-muted-foreground">
                    {pluriel(panier.nombreArticles, 'article')}
                </p>
            </div>

            {erreursStock.length > 0 && (
                <div className="border-destructive/30 bg-destructive/5 animate-in fade-in mb-6 rounded-2xl border p-4">
                    <p className="text-destructive mb-2 flex items-center gap-2 font-semibold">
                        <CircleAlert className="size-4" /> Validation annulée :
                        stock insuffisant
                    </p>
                    <ul className="text-sm">
                        {erreursStock.map((erreur) => (
                            <li key={erreur}>• {erreur}</li>
                        ))}
                    </ul>
                    <p className="text-muted-foreground mt-2 text-xs">
                        Ajustez les quantités ou choisissez un autre mode de
                        livraison, puis validez à nouveau votre panier.
                    </p>
                </div>
            )}

            {panier.lignes.length === 0 ? (
                <div className="animate-apparition flex flex-col items-center gap-4 rounded-3xl border border-dashed py-20 text-center">
                    <span className="bg-primary/10 text-primary flex size-20 items-center justify-center rounded-full">
                        <ShoppingBag className="size-9" />
                    </span>
                    <p className="text-xl font-semibold">
                        Votre panier est vide
                    </p>
                    <p className="text-muted-foreground max-w-sm text-sm">
                        Parcourez nos rayons et ajoutez vos articles préférés.
                    </p>
                    <Button
                        render={<Link href={catalogue()} />}
                        nativeButton={false}
                        size="lg"
                        className="rounded-full"
                    >
                        Découvrir le catalogue <ArrowRight />
                    </Button>
                </div>
            ) : (
                <div className="grid gap-8 lg:grid-cols-[1fr_360px]">
                    <ul className="flex flex-col gap-3">
                        {panier.lignes.map((ligne, position) => (
                            <li
                                key={ligne.reference}
                                className="animate-apparition bg-card flex gap-4 rounded-2xl border p-3 sm:p-4"
                                style={{ animationDelay: `${position * 60}ms` }}
                            >
                                <Link
                                    href={show(ligne.reference)}
                                    className="bg-muted size-24 shrink-0 overflow-hidden rounded-xl sm:size-28"
                                >
                                    {ligne.photo ? (
                                        <img
                                            src={ligne.photo}
                                            alt={ligne.description}
                                            className="size-full object-cover transition-transform hover:scale-110"
                                        />
                                    ) : (
                                        <ImageOff className="text-muted-foreground m-auto mt-9 size-6" />
                                    )}
                                </Link>
                                <div className="flex min-w-0 flex-1 flex-col gap-2">
                                    <div className="flex items-start justify-between gap-2">
                                        <div className="min-w-0">
                                            <p className="text-muted-foreground text-xs uppercase">
                                                {ligne.rayon}
                                            </p>
                                            <Link
                                                href={show(ligne.reference)}
                                                className="line-clamp-2 font-medium hover:underline"
                                            >
                                                {ligne.description}
                                            </Link>
                                            <p className="text-muted-foreground font-mono text-[11px]">
                                                {ligne.reference}
                                            </p>
                                        </div>
                                        <button
                                            type="button"
                                            onClick={() =>
                                                router.delete(
                                                    retirer.url(
                                                        ligne.reference,
                                                    ),
                                                    { preserveScroll: true },
                                                )
                                            }
                                            className="text-muted-foreground hover:text-destructive hover:bg-destructive/10 rounded-lg p-2 transition-colors"
                                            aria-label={`Retirer ${ligne.description}`}
                                        >
                                            <Trash2 className="size-4" />
                                        </button>
                                    </div>
                                    <div className="mt-auto flex flex-wrap items-end justify-between gap-2">
                                        <div className="flex items-center rounded-lg border">
                                            <button
                                                type="button"
                                                onClick={() =>
                                                    changerQuantite(
                                                        ligne,
                                                        ligne.quantite - 1,
                                                    )
                                                }
                                                className="hover:bg-muted flex size-8 items-center justify-center rounded-l-lg"
                                                aria-label="Diminuer"
                                            >
                                                <Minus className="size-3.5" />
                                            </button>
                                            <span className="w-9 text-center text-sm font-semibold">
                                                {ligne.quantite}
                                            </span>
                                            <button
                                                type="button"
                                                disabled={
                                                    ligne.quantite >=
                                                    ligne.stockDisponible
                                                }
                                                onClick={() =>
                                                    changerQuantite(
                                                        ligne,
                                                        ligne.quantite + 1,
                                                    )
                                                }
                                                className="hover:bg-muted flex size-8 items-center justify-center rounded-r-lg disabled:opacity-40"
                                                aria-label="Augmenter"
                                            >
                                                <Plus className="size-3.5" />
                                            </button>
                                        </div>
                                        <div className="text-right">
                                            <p className="text-muted-foreground text-xs">
                                                {formatPrix(ligne.prixUnitaire)}{' '}
                                                / unité
                                            </p>
                                            <p className="text-lg font-bold">
                                                {formatPrix(ligne.sousTotal)}
                                            </p>
                                        </div>
                                    </div>
                                </div>
                            </li>
                        ))}
                        <li>
                            <button
                                type="button"
                                onClick={() =>
                                    router.delete(vider.url(), {
                                        preserveScroll: true,
                                    })
                                }
                                className="text-muted-foreground hover:text-destructive text-sm underline-offset-4 hover:underline"
                            >
                                Vider le panier
                            </button>
                        </li>
                    </ul>

                    <aside className="bg-card animate-apparition h-fit rounded-3xl border p-6 shadow-sm lg:sticky lg:top-24">
                        <h2 className="mb-4 text-lg font-semibold">
                            Récapitulatif
                        </h2>
                        <dl className="flex flex-col gap-2 text-sm">
                            <div className="flex justify-between">
                                <dt className="text-muted-foreground">
                                    Sous-total (
                                    {pluriel(panier.nombreArticles, 'article')})
                                </dt>
                                <dd className="font-medium">
                                    {formatPrix(panier.total)}
                                </dd>
                            </div>
                            <div className="flex justify-between">
                                <dt className="text-muted-foreground">
                                    Livraison
                                </dt>
                                <dd className="text-muted-foreground">
                                    Calculée à l'étape suivante
                                </dd>
                            </div>
                        </dl>
                        <div className="my-4 border-t" />
                        <div className="mb-6 flex items-baseline justify-between">
                            <span className="font-semibold">Total</span>
                            <span className="text-2xl font-black">
                                {formatPrix(panier.total)}
                            </span>
                        </div>
                        <Button
                            render={
                                <Link
                                    href={
                                        auth.user ? validerCommande() : login()
                                    }
                                />
                            }
                            nativeButton={false}
                            size="lg"
                            className="group h-12 w-full rounded-xl text-base"
                        >
                            {auth.user
                                ? 'Valider mon panier'
                                : 'Se connecter pour valider'}
                            <ArrowRight className="transition-transform group-hover:translate-x-1" />
                        </Button>
                        {!auth.user && (
                            <p className="text-muted-foreground mt-3 text-center text-xs">
                                Votre panier sera conservé après la connexion.
                            </p>
                        )}
                        <p className="text-muted-foreground mt-4 flex items-center justify-center gap-1.5 text-xs">
                            <Lock className="size-3" /> Paiement sécurisé
                            (HTTPS)
                        </p>
                    </aside>
                </div>
            )}
        </SiteLayout>
    );
}
