import { Link, usePage } from '@inertiajs/react';
import { Package, User } from 'lucide-react';
import { type ReactNode } from 'react';

import SiteLayout from '@/layouts/site-layout';
import { index as mesCommandes } from '@/routes/compte/commandes';
import { edit as monCompte } from '@/routes/compte/profil';
import type { Auth } from '@/types';

/**
 * Mise en page de l'espace client : en-tête de bienvenue et onglets « Mon
 * compte » / « Mes commandes ».
 *
 * @param title Titre de la page.
 * @param children Contenu de la page.
 */
export default function CompteLayout({
    title,
    children,
}: {
    title: string;
    children: ReactNode;
}) {
    const { auth } = usePage<{ auth: Auth }>().props;
    const url = usePage().url;
    const onglets = [
        {
            libelle: 'Mon compte',
            href: monCompte.url(),
            icone: User,
            actif: !url.startsWith(mesCommandes.url()),
        },
        {
            libelle: 'Mes commandes',
            href: mesCommandes.url(),
            icone: Package,
            actif: url.startsWith(mesCommandes.url()),
        },
    ];

    return (
        <SiteLayout title={title}>
            <div className="bg-navy text-navy-foreground fond-sport animate-apparition relative mb-8 overflow-hidden rounded-3xl p-6 sm:p-8">
                <p className="text-sm text-white/70">Espace client</p>
                <h1 className="text-2xl font-bold tracking-tight sm:text-3xl">
                    Bonjour {auth.user?.prenom} 👋
                </h1>
                <p className="mt-1 font-mono text-xs text-white/60">
                    Code client : {String(auth.user?.code_cli ?? '')}
                </p>
                <img
                    src="/images/all4sport-logo.png"
                    alt=""
                    aria-hidden
                    className="absolute -right-6 -bottom-10 w-48 opacity-30 sm:w-64"
                />
            </div>

            <nav className="mb-8 flex gap-2 border-b">
                {onglets.map(({ libelle, href, icone: Icone, actif }) => (
                    <Link
                        key={libelle}
                        href={href}
                        className={`-mb-px flex items-center gap-2 border-b-2 px-4 py-3 text-sm font-medium transition-colors ${
                            actif
                                ? 'border-primary text-foreground'
                                : 'text-muted-foreground hover:text-foreground border-transparent'
                        }`}
                    >
                        <Icone className="size-4" /> {libelle}
                    </Link>
                ))}
            </nav>

            {children}
        </SiteLayout>
    );
}
