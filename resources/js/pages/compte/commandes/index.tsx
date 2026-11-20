import { Link } from '@inertiajs/react';
import { ChevronRight, PackageOpen } from 'lucide-react';

import { EtatBadge } from '@/components/etat-badge';
import { Pagination } from '@/components/pagination';
import { Button } from '@/components/ui/button';
import CompteLayout from '@/layouts/compte-layout';
import { formatDate, formatPrix, pluriel } from '@/lib/format';
import { index as catalogue } from '@/routes/catalogue';
import { show } from '@/routes/compte/commandes';
import type { EtatCommande, Pagine } from '@/types/catalogue';

type Commande = {
    id: number;
    numero: string;
    date: string;
    modeLivraison: string;
    etat: EtatCommande;
    etatLibelle: string;
    montantTotal: string;
    nombreArticles: number;
};

/**
 * Historique des commandes du client (route « compte.commandes.index »).
 */
export default function MesCommandes({
    commandes,
}: {
    commandes: Pagine<Commande>;
}) {
    return (
        <CompteLayout title="Mes commandes">
            {commandes.data.length === 0 ? (
                <div className="animate-apparition flex flex-col items-center gap-3 rounded-3xl border border-dashed py-16 text-center">
                    <PackageOpen className="text-muted-foreground size-12" />
                    <p className="font-semibold">
                        Aucune commande pour le moment
                    </p>
                    <Button
                        render={<Link href={catalogue()} />}
                        nativeButton={false}
                        className="rounded-full"
                    >
                        Commencer mes achats
                    </Button>
                </div>
            ) : (
                <ul className="flex flex-col gap-3">
                    {commandes.data.map((commande) => (
                        <li key={commande.id}>
                            <Link
                                href={show(commande.id)}
                                className="group bg-card hover:border-primary/40 flex flex-wrap items-center gap-4 rounded-2xl border p-4 transition-all hover:shadow-md sm:p-5"
                            >
                                <div className="min-w-32">
                                    <p className="font-mono text-sm font-semibold">
                                        {commande.numero}
                                    </p>
                                    <p className="text-muted-foreground text-xs">
                                        {formatDate(commande.date)}
                                    </p>
                                </div>
                                <EtatBadge
                                    etat={commande.etat}
                                    libelle={commande.etatLibelle}
                                />
                                <p className="text-muted-foreground hidden flex-1 text-sm sm:block">
                                    {pluriel(
                                        commande.nombreArticles,
                                        'article',
                                    )}{' '}
                                    · {commande.modeLivraison}
                                </p>
                                <p className="ml-auto text-lg font-bold">
                                    {formatPrix(commande.montantTotal)}
                                </p>
                                <ChevronRight className="text-muted-foreground size-5" />
                            </Link>
                        </li>
                    ))}
                </ul>
            )}
            <Pagination liens={commandes.links} />
        </CompteLayout>
    );
}
