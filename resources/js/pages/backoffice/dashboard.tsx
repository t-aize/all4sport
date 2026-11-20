import { Link } from '@inertiajs/react';
import {
    ArrowRight,
    ClipboardCheck,
    Euro,
    Package,
    ShoppingCart,
    TriangleAlert,
    Users,
} from 'lucide-react';

import { EtatBadge } from '@/components/etat-badge';
import BackOfficeLayout from '@/layouts/back-office-layout';
import { formatDate, formatPrix } from '@/lib/format';
import {
    index as commandesIndex,
    show as commandeShow,
} from '@/routes/backoffice/commandes';
import { edit as produitEdit } from '@/routes/backoffice/produits';
import type { EtatCommande, Option } from '@/types/catalogue';

type Indicateurs = {
    aTraiter: number;
    commandes: number;
    chiffreAffaires: string;
    panierMoyen: string;
    clients: number;
    produits: number;
};

type CommandeATraiter = {
    id: number;
    numero: string;
    date: string;
    client: string;
    etat: EtatCommande;
    etatLibelle: string;
    modeLivraison: string;
    montantTotal: string;
};

/**
 * Tableau de bord du back-office : indicateurs clés, commandes à traiter par
 * le collaborateur (selon son rôle), répartition des commandes par état,
 * ventes des 6 derniers mois, meilleurs rayons et stocks faibles.
 */
export default function Dashboard({
    roleLibelle,
    etatsGerables,
    indicateurs,
    commandesParEtat,
    ventesMensuelles,
    aTraiter,
    stocksFaibles,
    meilleursRayons,
}: {
    roleLibelle: string;
    etatsGerables: Option[];
    indicateurs: Indicateurs;
    commandesParEtat: (Option & { total: number })[];
    ventesMensuelles: { mois: string; total: string; commandes: number }[];
    aTraiter: CommandeATraiter[];
    stocksFaibles: {
        reference: string;
        description: string;
        rayon: string | null;
        stockInternet: number;
    }[];
    meilleursRayons: { nom: string; total: string }[];
}) {
    const venteMax = Math.max(
        1,
        ...ventesMensuelles.map((mois) => Number(mois.total)),
    );
    const etatMax = Math.max(1, ...commandesParEtat.map((etat) => etat.total));
    const rayonMax = Math.max(
        1,
        ...meilleursRayons.map((rayon) => Number(rayon.total)),
    );

    const cartes = [
        {
            libelle: 'À traiter par vous',
            valeur: String(indicateurs.aTraiter),
            icone: ClipboardCheck,
            accent: true,
        },
        {
            libelle: "Chiffre d'affaires",
            valeur: formatPrix(indicateurs.chiffreAffaires),
            icone: Euro,
        },
        {
            libelle: 'Commandes',
            valeur: String(indicateurs.commandes),
            icone: ShoppingCart,
        },
        {
            libelle: 'Panier moyen',
            valeur: formatPrix(indicateurs.panierMoyen),
            icone: Package,
        },
        {
            libelle: 'Clients',
            valeur: String(indicateurs.clients),
            icone: Users,
        },
    ];

    return (
        <BackOfficeLayout title="Tableau de bord" roleLibelle={roleLibelle}>
            <div className="mb-6 flex flex-wrap items-center gap-2 text-sm">
                <span className="text-muted-foreground">
                    Vous enregistrez les états :
                </span>
                {etatsGerables.map((etat) => (
                    <EtatBadge
                        key={etat.value}
                        etat={etat.value as EtatCommande}
                        libelle={etat.label}
                    />
                ))}
            </div>

            <div className="mb-6 grid grid-cols-2 gap-3 lg:grid-cols-5">
                {cartes.map(({ libelle, valeur, icone: Icone, accent }) => (
                    <div
                        key={libelle}
                        className={`rounded-2xl border p-4 ${
                            accent
                                ? 'bg-primary text-primary-foreground col-span-2 border-transparent lg:col-span-1'
                                : 'bg-card'
                        }`}
                    >
                        <Icone
                            className={`mb-3 size-5 ${accent ? '' : 'text-primary'}`}
                        />
                        <p
                            className={`text-xs ${accent ? 'text-primary-foreground/80' : 'text-muted-foreground'}`}
                        >
                            {libelle}
                        </p>
                        <p className="text-2xl font-bold tracking-tight">
                            {valeur}
                        </p>
                    </div>
                ))}
            </div>

            <div className="grid grid-cols-1 gap-4 sm:gap-6 lg:grid-cols-[minmax(0,1fr)_380px]">
                <section className="bg-card min-w-0 rounded-2xl border p-4 sm:rounded-3xl sm:p-6">
                    <div className="mb-4 flex items-center justify-between">
                        <h2 className="font-semibold">Commandes à traiter</h2>
                        <Link
                            href={commandesIndex()}
                            className="text-primary flex items-center gap-1 text-sm font-medium hover:underline"
                        >
                            Tout voir <ArrowRight className="size-4" />
                        </Link>
                    </div>
                    {aTraiter.length === 0 ? (
                        <p className="text-muted-foreground py-8 text-center text-sm">
                            Rien à traiter pour le moment. Bravo ! 🎉
                        </p>
                    ) : (
                        <ul className="divide-y">
                            {aTraiter.map((commande) => (
                                <li key={commande.id}>
                                    <Link
                                        href={commandeShow(commande.id)}
                                        className="hover:bg-muted/50 -mx-2 flex flex-wrap items-center gap-x-3 gap-y-1 rounded-xl px-2 py-3 transition-colors"
                                    >
                                        <span className="font-mono text-sm font-semibold">
                                            {commande.numero}
                                        </span>
                                        <EtatBadge
                                            etat={commande.etat}
                                            libelle={commande.etatLibelle}
                                        />
                                        <span className="ml-auto font-semibold sm:order-last">
                                            {formatPrix(commande.montantTotal)}
                                        </span>
                                        <span className="text-muted-foreground w-full truncate text-xs sm:w-auto sm:flex-1 sm:text-sm">
                                            {commande.client} ·{' '}
                                            {commande.modeLivraison} ·{' '}
                                            {formatDate(commande.date)}
                                        </span>
                                    </Link>
                                </li>
                            ))}
                        </ul>
                    )}
                </section>

                <section className="bg-card min-w-0 rounded-2xl border p-4 sm:rounded-3xl sm:p-6">
                    <h2 className="mb-4 font-semibold">Commandes par état</h2>
                    <ul className="flex flex-col gap-3">
                        {commandesParEtat.map((etat) => (
                            <li key={etat.value} className="text-sm">
                                <div className="mb-1 flex justify-between">
                                    <span>{etat.label}</span>
                                    <span className="font-semibold">
                                        {etat.total}
                                    </span>
                                </div>
                                <div className="bg-muted h-2 overflow-hidden rounded-full">
                                    <div
                                        className="bg-primary h-full origin-left rounded-full transition-all duration-700"
                                        style={{
                                            width: `${(etat.total / etatMax) * 100}%`,
                                        }}
                                    />
                                </div>
                            </li>
                        ))}
                    </ul>
                </section>

                <section className="bg-card min-w-0 rounded-2xl border p-4 sm:rounded-3xl sm:p-6">
                    <h2 className="mb-1 font-semibold">
                        Ventes des 6 derniers mois
                    </h2>
                    <p className="text-muted-foreground mb-6 text-xs">
                        Chiffre d'affaires TTC par mois
                    </p>
                    <div className="flex h-48 items-end gap-1.5 sm:gap-3">
                        {ventesMensuelles.map((mois) => (
                            <div
                                key={mois.mois}
                                className="group flex flex-1 flex-col items-center gap-2"
                            >
                                <span className="text-muted-foreground text-[9px] whitespace-nowrap sm:text-[10px]">
                                    {Math.round(
                                        Number(mois.total),
                                    ).toLocaleString('fr-FR')}{' '}
                                    €
                                </span>
                                <div
                                    className="from-primary to-primary/60 w-full rounded-t-lg bg-linear-to-t"
                                    style={{
                                        height: `${Math.max(4, (Number(mois.total) / venteMax) * 140)}px`,
                                    }}
                                    title={`${mois.commandes} commande(s)`}
                                />
                                <span className="text-muted-foreground text-center text-[10px] leading-tight sm:text-xs">
                                    {mois.mois}
                                </span>
                            </div>
                        ))}
                    </div>
                </section>

                <section className="bg-card min-w-0 rounded-2xl border p-4 sm:rounded-3xl sm:p-6">
                    <h2 className="mb-4 font-semibold">Meilleurs rayons</h2>
                    <ol className="flex flex-col gap-3">
                        {meilleursRayons.map((rayon, position) => (
                            <li
                                key={rayon.nom}
                                className="flex items-center gap-3 text-sm"
                            >
                                <span className="bg-navy text-navy-foreground flex size-6 shrink-0 items-center justify-center rounded-full text-xs font-bold">
                                    {position + 1}
                                </span>
                                <span className="flex-1">
                                    <span className="flex justify-between">
                                        <span>{rayon.nom}</span>
                                        <span className="font-semibold">
                                            {formatPrix(rayon.total)}
                                        </span>
                                    </span>
                                    <span className="bg-muted mt-1 block h-1.5 overflow-hidden rounded-full">
                                        <span
                                            className="bg-navy block h-full rounded-full"
                                            style={{
                                                width: `${(Number(rayon.total) / rayonMax) * 100}%`,
                                            }}
                                        />
                                    </span>
                                </span>
                            </li>
                        ))}
                    </ol>
                </section>

                {stocksFaibles.length > 0 && (
                    <section className="bg-card min-w-0 rounded-2xl border p-4 sm:rounded-3xl sm:p-6 lg:col-span-2">
                        <h2 className="mb-4 flex items-center gap-2 font-semibold">
                            <TriangleAlert className="size-5 text-amber-500" />{' '}
                            Stock Internet faible
                        </h2>
                        <div className="grid grid-cols-1 gap-2 sm:grid-cols-2 lg:grid-cols-3">
                            {stocksFaibles.map((produit) => (
                                <Link
                                    key={produit.reference}
                                    href={produitEdit(produit.reference)}
                                    className="hover:border-primary/40 flex min-w-0 items-center justify-between gap-3 rounded-xl border p-3 text-sm transition-colors"
                                >
                                    <span className="min-w-0">
                                        <span className="block truncate font-medium">
                                            {produit.description}
                                        </span>
                                        <span className="text-muted-foreground font-mono text-[11px]">
                                            {produit.reference}
                                        </span>
                                    </span>
                                    <span
                                        className={`shrink-0 rounded-full px-2 py-0.5 text-xs font-bold ${
                                            produit.stockInternet === 0
                                                ? 'bg-destructive/10 text-destructive'
                                                : 'bg-amber-500/10 text-amber-700'
                                        }`}
                                    >
                                        {produit.stockInternet}
                                    </span>
                                </Link>
                            ))}
                        </div>
                    </section>
                )}
            </div>
        </BackOfficeLayout>
    );
}
