import { Form, Link } from '@inertiajs/react';
import {
    ArrowLeft,
    History,
    Lock,
    Mail,
    MapPin,
    Phone,
    UserRound,
} from 'lucide-react';

import { changerEtat } from '@/actions/App/Http/Controllers/BackOffice/CommandeController';
import { DetailCommande } from '@/components/detail-commande';
import { EtatBadge } from '@/components/etat-badge';
import { SuiviCommande } from '@/components/suivi-commande';
import { Button } from '@/components/ui/button';
import BackOfficeLayout from '@/layouts/back-office-layout';
import { formatDateHeure } from '@/lib/format';
import { index } from '@/routes/backoffice/commandes';
import type { CommandeDetail, EtatCommande, Option } from '@/types/catalogue';

type Commande = CommandeDetail & {
    client: {
        codeCli: string;
        nom: string;
        email: string;
        telephone: string | null;
        adresse: string;
    };
    historique: {
        id: number;
        etat: EtatCommande;
        libelle: string;
        date: string;
        employe: string;
    }[];
};

/**
 * Détail d'une commande dans le back-office (route « backoffice.commandes.show ») :
 * suivi, client, produits, historique des états et bouton permettant au
 * collaborateur responsable d'enregistrer l'état suivant.
 */
export default function CommandeShow({
    commande,
    etatSuivant,
    responsableSuivant,
    peutAvancer,
}: {
    commande: Commande;
    etatSuivant: Option | null;
    responsableSuivant: string | null;
    peutAvancer: boolean;
}) {
    return (
        <BackOfficeLayout title={`Commande ${commande.numero}`}>
            <Link
                href={index()}
                className="text-muted-foreground hover:text-foreground mb-6 inline-flex items-center gap-1 text-sm"
            >
                <ArrowLeft className="size-4" /> Retour aux commandes
            </Link>

            <div className="mb-6 flex flex-wrap items-center gap-3">
                <h2 className="font-mono text-2xl font-bold">
                    {commande.numero}
                </h2>
                <EtatBadge
                    etat={commande.etat}
                    libelle={commande.etatLibelle}
                />
            </div>

            <section className="bg-card mb-6 rounded-3xl border p-6">
                <div className="mb-6 flex flex-wrap items-center justify-between gap-4">
                    <h3 className="font-semibold">Suivi</h3>
                    {etatSuivant ? (
                        peutAvancer ? (
                            <Form
                                {...changerEtat.form(commande.id)}
                                options={{ preserveScroll: true }}
                            >
                                {({ processing }) => (
                                    <>
                                        <input
                                            type="hidden"
                                            name="etat"
                                            value={etatSuivant.value}
                                        />
                                        <Button
                                            type="submit"
                                            size="lg"
                                            disabled={processing}
                                            className="shadow-primary/30 rounded-xl shadow-lg"
                                        >
                                            {processing
                                                ? 'Enregistrement…'
                                                : `Passer à « ${etatSuivant.label} »`}
                                        </Button>
                                    </>
                                )}
                            </Form>
                        ) : (
                            <p className="text-muted-foreground flex items-center gap-2 text-sm">
                                <Lock className="size-4" /> Prochaine étape «{' '}
                                {etatSuivant.label} » : rôle{' '}
                                {responsableSuivant}
                            </p>
                        )
                    ) : (
                        <p className="text-sm font-medium text-emerald-600">
                            Commande terminée ✓
                        </p>
                    )}
                </div>
                <SuiviCommande etapes={commande.suivi} />
            </section>

            <div className="mb-6 grid gap-6 lg:grid-cols-2">
                <section className="bg-card rounded-3xl border p-6">
                    <h3 className="mb-4 flex items-center gap-2 font-semibold">
                        <UserRound className="text-primary size-5" /> Client
                    </h3>
                    <p className="font-semibold">{commande.client.nom}</p>
                    <p className="text-muted-foreground mb-3 font-mono text-xs">
                        {commande.client.codeCli}
                    </p>
                    <ul className="flex flex-col gap-2 text-sm">
                        <li className="flex items-center gap-2">
                            <Mail className="text-muted-foreground size-4" />{' '}
                            {commande.client.email}
                        </li>
                        <li className="flex items-center gap-2">
                            <Phone className="text-muted-foreground size-4" />{' '}
                            {commande.client.telephone ?? '—'}
                        </li>
                        <li className="flex items-center gap-2">
                            <MapPin className="text-muted-foreground size-4" />{' '}
                            {commande.client.adresse}
                        </li>
                    </ul>
                </section>
                <section className="bg-card rounded-3xl border p-6">
                    <h3 className="mb-4 flex items-center gap-2 font-semibold">
                        <History className="text-primary size-5" /> Historique
                        des états
                    </h3>
                    <ol className="relative flex flex-col gap-4 border-l pl-5">
                        {commande.historique.map((ligne) => (
                            <li key={ligne.id} className="relative text-sm">
                                <span className="bg-primary ring-background absolute top-1 -left-[1.6rem] size-2.5 rounded-full ring-4" />
                                <div className="flex flex-wrap items-center gap-2">
                                    <EtatBadge
                                        etat={ligne.etat}
                                        libelle={ligne.libelle}
                                    />
                                    <span className="text-muted-foreground text-xs">
                                        {formatDateHeure(ligne.date)}
                                    </span>
                                </div>
                                <p className="text-muted-foreground mt-1 text-xs">
                                    par {ligne.employe}
                                </p>
                            </li>
                        ))}
                    </ol>
                </section>
            </div>

            <DetailCommande commande={commande} />
        </BackOfficeLayout>
    );
}
