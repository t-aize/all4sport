import { Link, router } from '@inertiajs/react';
import { ArrowRight, Search, Zap } from 'lucide-react';
import { type FormEvent, useState } from 'react';

import { EtatBadge } from '@/components/etat-badge';
import { Pagination } from '@/components/pagination';
import { Input } from '@/components/ui/input';
import BackOfficeLayout from '@/layouts/back-office-layout';
import { formatDate, formatPrix } from '@/lib/format';
import { index, show } from '@/routes/backoffice/commandes';
import type { EtatCommande, Option, Pagine } from '@/types/catalogue';

type Commande = {
    id: number;
    numero: string;
    date: string;
    client: string;
    codeCli: string;
    modeLivraison: string;
    modeLivraisonLibelle: string;
    lieuExpedition: string | null;
    etat: EtatCommande;
    etatLibelle: string;
    montantTotal: string;
    nombreArticles: number;
    actionPossible: boolean;
};

/**
 * Liste des commandes du back-office (route « backoffice.commandes.index ») :
 * filtre « à traiter » (selon le rôle), filtre par état et par mode de
 * livraison, recherche par numéro ou client.
 */
export default function CommandesIndex({
    commandes,
    filtres,
    compteurs,
    etats,
    modes,
}: {
    commandes: Pagine<Commande>;
    filtres: { filtre: string; mode: string | null; recherche: string };
    compteurs: Record<string, number>;
    etats: Option[];
    modes: Option[];
}) {
    const [recherche, setRecherche] = useState(filtres.recherche);

    const filtrer = (changements: Record<string, string | null>) => {
        const query = Object.fromEntries(
            Object.entries({ ...filtres, ...changements }).filter(
                ([, valeur]) => valeur !== null && valeur !== '',
            ),
        );
        router.get(
            index.url({ query }),
            {},
            { preserveState: true, preserveScroll: true },
        );
    };

    const onglets = [
        { value: 'a_traiter', label: 'À traiter' },
        { value: 'toutes', label: 'Toutes' },
        ...etats,
    ];

    return (
        <BackOfficeLayout title="Commandes">
            <div className="-mx-4 mb-4 overflow-x-auto px-4">
                <div className="flex w-max gap-2">
                    {onglets.map((onglet) => (
                        <button
                            key={onglet.value}
                            type="button"
                            onClick={() => filtrer({ filtre: onglet.value })}
                            className={`flex items-center gap-2 rounded-full border px-4 py-2 text-sm font-medium transition-all ${
                                filtres.filtre === onglet.value
                                    ? 'bg-navy text-navy-foreground border-transparent shadow-md'
                                    : 'bg-card hover:border-primary/40'
                            }`}
                        >
                            {onglet.value === 'a_traiter' && (
                                <Zap className="text-primary size-3.5" />
                            )}
                            {onglet.label}
                            <span
                                className={`rounded-full px-1.5 text-xs ${filtres.filtre === onglet.value ? 'bg-white/15' : 'bg-muted'}`}
                            >
                                {compteurs[onglet.value] ?? 0}
                            </span>
                        </button>
                    ))}
                </div>
            </div>

            <div className="mb-4 flex flex-col gap-3 sm:flex-row">
                <form
                    onSubmit={(evenement: FormEvent) => {
                        evenement.preventDefault();
                        filtrer({ recherche: recherche.trim() || null });
                    }}
                    className="relative flex-1"
                >
                    <Search className="text-muted-foreground pointer-events-none absolute top-1/2 left-3 size-4 -translate-y-1/2" />
                    <Input
                        value={recherche}
                        onChange={(evenement) =>
                            setRecherche(evenement.target.value)
                        }
                        placeholder="N° de commande, nom, email ou code client…"
                        className="bg-card pl-9"
                        aria-label="Rechercher une commande"
                    />
                </form>
                <select
                    value={filtres.mode ?? ''}
                    onChange={(evenement) =>
                        filtrer({ mode: evenement.target.value || null })
                    }
                    className="border-input bg-card h-9 rounded-lg border px-3 text-sm"
                    aria-label="Mode de livraison"
                >
                    <option value="">Tous les modes</option>
                    {modes.map((mode) => (
                        <option key={mode.value} value={mode.value}>
                            {mode.label}
                        </option>
                    ))}
                </select>
            </div>

            <div className="bg-card overflow-hidden rounded-2xl border">
                {commandes.data.length === 0 ? (
                    <p className="text-muted-foreground p-10 text-center text-sm">
                        Aucune commande ne correspond.
                    </p>
                ) : (
                    <>
                        {/* Mobile : une carte par commande. */}
                        <ul className="divide-y md:hidden">
                            {commandes.data.map((commande) => (
                                <li key={commande.id}>
                                    <Link
                                        href={show(commande.id)}
                                        className="active:bg-muted/60 flex flex-col gap-2 p-4"
                                    >
                                        <div className="flex items-center justify-between gap-2">
                                            <span className="font-mono text-sm font-semibold">
                                                {commande.numero}
                                            </span>
                                            <EtatBadge
                                                etat={commande.etat}
                                                libelle={commande.etatLibelle}
                                            />
                                        </div>
                                        <div className="flex items-end justify-between gap-3 text-sm">
                                            <div className="min-w-0">
                                                <p className="truncate font-medium">
                                                    {commande.client}
                                                </p>
                                                <p className="text-muted-foreground truncate text-xs">
                                                    {formatDate(commande.date)}{' '}
                                                    ·{' '}
                                                    {
                                                        commande.modeLivraisonLibelle
                                                    }
                                                </p>
                                            </div>
                                            <span className="shrink-0 font-semibold">
                                                {formatPrix(
                                                    commande.montantTotal,
                                                )}
                                            </span>
                                        </div>
                                        {commande.actionPossible && (
                                            <span className="bg-primary text-primary-foreground flex items-center justify-center gap-1 rounded-lg py-2 text-xs font-semibold">
                                                Traiter{' '}
                                                <ArrowRight className="size-3.5" />
                                            </span>
                                        )}
                                    </Link>
                                </li>
                            ))}
                        </ul>
                        <div className="hidden overflow-x-auto md:block">
                            <table className="w-full text-sm">
                                <thead className="bg-muted/50 text-muted-foreground text-left text-xs uppercase">
                                    <tr>
                                        <th className="p-3 font-medium">
                                            Commande
                                        </th>
                                        <th className="p-3 font-medium">
                                            Client
                                        </th>
                                        <th className="p-3 font-medium">
                                            Livraison
                                        </th>
                                        <th className="p-3 font-medium">
                                            État
                                        </th>
                                        <th className="p-3 text-right font-medium">
                                            Total
                                        </th>
                                        <th className="p-3" />
                                    </tr>
                                </thead>
                                <tbody className="divide-y">
                                    {commandes.data.map((commande) => (
                                        <tr
                                            key={commande.id}
                                            className="group hover:bg-muted/40 transition-colors"
                                        >
                                            <td className="p-3">
                                                <Link
                                                    href={show(commande.id)}
                                                    className="font-mono font-semibold hover:underline"
                                                >
                                                    {commande.numero}
                                                </Link>
                                                <p className="text-muted-foreground text-xs">
                                                    {formatDate(commande.date)}
                                                </p>
                                            </td>
                                            <td className="p-3">
                                                <p className="font-medium">
                                                    {commande.client}
                                                </p>
                                                <p className="text-muted-foreground font-mono text-xs">
                                                    {commande.codeCli}
                                                </p>
                                            </td>
                                            <td className="p-3">
                                                <p>
                                                    {
                                                        commande.modeLivraisonLibelle
                                                    }
                                                </p>
                                                <p className="text-muted-foreground text-xs">
                                                    {commande.lieuExpedition}
                                                </p>
                                            </td>
                                            <td className="p-3">
                                                <EtatBadge
                                                    etat={commande.etat}
                                                    libelle={
                                                        commande.etatLibelle
                                                    }
                                                />
                                            </td>
                                            <td className="p-3 text-right font-semibold whitespace-nowrap">
                                                {formatPrix(
                                                    commande.montantTotal,
                                                )}
                                                <p className="text-muted-foreground text-xs font-normal">
                                                    {commande.nombreArticles}{' '}
                                                    art.
                                                </p>
                                            </td>
                                            <td className="p-3 text-right">
                                                <Link
                                                    href={show(commande.id)}
                                                    className={`inline-flex items-center gap-1 rounded-lg px-3 py-1.5 text-xs font-semibold transition-all ${
                                                        commande.actionPossible
                                                            ? 'bg-primary text-primary-foreground'
                                                            : 'text-muted-foreground hover:text-foreground'
                                                    }`}
                                                >
                                                    {commande.actionPossible
                                                        ? 'Traiter'
                                                        : 'Voir'}
                                                    <ArrowRight className="size-3.5" />
                                                </Link>
                                            </td>
                                        </tr>
                                    ))}
                                </tbody>
                            </table>
                        </div>
                    </>
                )}
            </div>
            <Pagination liens={commandes.links} />
        </BackOfficeLayout>
    );
}
