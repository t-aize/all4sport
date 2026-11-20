import { Link } from '@inertiajs/react';
import { ArrowLeft, PartyPopper } from 'lucide-react';

import { DetailCommande } from '@/components/detail-commande';
import { EtatBadge } from '@/components/etat-badge';
import { SuiviCommande } from '@/components/suivi-commande';
import CompteLayout from '@/layouts/compte-layout';
import { formatDate } from '@/lib/format';
import { index } from '@/routes/compte/commandes';
import type { CommandeDetail } from '@/types/catalogue';

/**
 * Détail d'une commande du client (route "compte.commandes.show") : suivi
 * des états, produits, quantités, prix, totaux, lieu d'expédition et adresse
 * de livraison. Juste après la commande, un message de confirmation s'affiche.
 */
export default function MaCommande({
    commande,
    confirmation,
}: {
    commande: CommandeDetail;
    confirmation: boolean;
}) {
    return (
        <CompteLayout title={`Commande ${commande.numero}`}>
            <Link
                href={index()}
                className="text-muted-foreground hover:text-foreground mb-6 inline-flex items-center gap-1 text-sm"
            >
                <ArrowLeft className="size-4" /> Toutes mes commandes
            </Link>

            {confirmation && (
                <div className="mb-6 flex items-center gap-4 rounded-3xl border border-emerald-500/30 bg-emerald-500/10 p-5 duration-500">
                    <PartyPopper className="size-10 shrink-0 text-emerald-600" />
                    <div>
                        <p className="text-lg font-semibold">
                            Merci pour votre commande !
                        </p>
                        <p className="text-muted-foreground text-sm">
                            Elle a été payée et transmise à notre service
                            client, qui va la valider. Suivez son avancement
                            ci-dessous.
                        </p>
                    </div>
                </div>
            )}

            <div className="animate-apparition mb-6 flex flex-wrap items-center justify-between gap-3">
                <div>
                    <h2 className="font-mono text-2xl font-bold">
                        {commande.numero}
                    </h2>
                    <p className="text-muted-foreground text-sm">
                        Passée le {formatDate(commande.date)}
                    </p>
                </div>
                <EtatBadge
                    etat={commande.etat}
                    libelle={commande.etatLibelle}
                />
            </div>

            <section className="bg-card mb-6 rounded-3xl border p-6">
                <h3 className="mb-6 font-semibold">Suivi de la commande</h3>
                <SuiviCommande etapes={commande.suivi} />
            </section>

            <DetailCommande commande={commande} />
        </CompteLayout>
    );
}
