import { Head, Link, router, usePage } from '@inertiajs/react';
import {
    Building2,
    ClipboardList,
    ExternalLink,
    LayoutDashboard,
    LogOut,
    Menu,
    Package,
} from 'lucide-react';
import { type ReactNode } from 'react';

import { destroy } from '@/actions/App/Http/Controllers/BackOffice/AuthenticatedSessionController';
import { Logo } from '@/components/logo';
import { MessageFlash } from '@/components/message-flash';
import { Button } from '@/components/ui/button';
import {
    Sheet,
    SheetContent,
    SheetTitle,
    SheetTrigger,
} from '@/components/ui/sheet';
import { home } from '@/routes';
import { dashboard } from '@/routes/backoffice';
import { index as commandes } from '@/routes/backoffice/commandes';
import { index as fournisseurs } from '@/routes/backoffice/fournisseurs';
import { index as produits } from '@/routes/backoffice/produits';
import type { Auth } from '@/types';

/**
 * Mise en page du back-office réservé aux collaborateurs connectés : barre
 * latérale de navigation (selon le rôle), en-tête avec le collaborateur et
 * son rôle, puis le contenu de la page.
 *
 * @param title Titre de la page, affiché dans l'onglet du navigateur.
 * @param roleLibelle Nom du rôle (sinon lu dans les données partagées).
 * @param actions Boutons affichés à droite du titre.
 * @param children Contenu de la page.
 */
export default function BackOfficeLayout({
    title,
    roleLibelle,
    actions,
    children,
}: {
    title: string;
    roleLibelle?: string;
    actions?: ReactNode;
    children: ReactNode;
}) {
    const page = usePage<{ auth: Auth & { roleLibelle: string | null } }>();
    const { auth } = page.props;
    const role = auth.employe?.role;
    const gereCatalogue = role === 'operateur' || role === 'vendeur';

    const liens = [
        {
            libelle: 'Tableau de bord',
            href: dashboard.url(),
            icone: LayoutDashboard,
            visible: true,
        },
        {
            libelle: 'Commandes',
            href: commandes.url(),
            icone: ClipboardList,
            visible: true,
        },
        {
            libelle: 'Produits',
            href: produits.url(),
            icone: Package,
            visible: gereCatalogue,
        },
        {
            libelle: 'Fournisseurs',
            href: fournisseurs.url(),
            icone: Building2,
            visible: gereCatalogue,
        },
    ].filter((lien) => lien.visible);

    const estActif = (href: string) =>
        href === dashboard.url()
            ? page.url === href
            : page.url.startsWith(href);

    const navigation = (
        <nav className="flex flex-col gap-1">
            {liens.map(({ libelle, href, icone: Icone }) => (
                <Link
                    key={libelle}
                    href={href}
                    className={`flex items-center gap-3 rounded-xl px-3 py-2.5 text-sm font-medium transition-all ${
                        estActif(href)
                            ? 'bg-primary text-primary-foreground shadow-primary/30 shadow-md'
                            : 'text-white/70 hover:bg-white/10 hover:text-white'
                    }`}
                >
                    <Icone className="size-4" /> {libelle}
                </Link>
            ))}
            <a
                href={home.url()}
                className="mt-4 flex items-center gap-3 rounded-xl px-3 py-2.5 text-sm text-white/50 hover:text-white"
            >
                <ExternalLink className="size-4" /> Voir le site
            </a>
        </nav>
    );

    return (
        <div className="bg-muted/40 min-h-screen">
            <Head title={title} />

            <aside className="bg-navy fixed inset-y-0 left-0 hidden w-64 flex-col gap-8 p-5 lg:flex">
                <div>
                    <Logo inverse />
                    <p className="mt-1 pl-12 text-xs text-white/50">
                        Back-office
                    </p>
                </div>
                {navigation}
                <div className="mt-auto rounded-2xl bg-white/5 p-4 text-white">
                    <p className="text-sm font-semibold">
                        {auth.employe?.prenom} {auth.employe?.nom}
                    </p>
                    <p className="text-primary text-xs font-medium">
                        {roleLibelle ?? auth.roleLibelle}
                    </p>
                    <button
                        type="button"
                        onClick={() => router.post(destroy.url())}
                        className="mt-3 flex items-center gap-2 text-xs text-white/60 hover:text-white"
                    >
                        <LogOut className="size-3.5" /> Se déconnecter
                    </button>
                </div>
            </aside>

            <div className="lg:pl-64">
                <header className="bg-background/80 sticky top-0 z-30 flex items-center gap-3 border-b px-4 py-3 backdrop-blur-lg sm:px-8">
                    <Sheet>
                        <SheetTrigger
                            render={
                                <Button
                                    variant="ghost"
                                    size="icon"
                                    className="lg:hidden"
                                    aria-label="Menu"
                                />
                            }
                        >
                            <Menu className="size-5" />
                        </SheetTrigger>
                        <SheetContent
                            side="left"
                            className="bg-navy w-72 border-none p-5"
                        >
                            <SheetTitle>
                                <Logo inverse />
                            </SheetTitle>
                            <div className="mt-6">{navigation}</div>
                        </SheetContent>
                    </Sheet>
                    <h1 className="truncate text-lg font-semibold">{title}</h1>
                    <div className="ml-auto flex items-center gap-2">
                        {actions}
                        <span className="bg-navy text-navy-foreground hidden rounded-full px-3 py-1 text-xs font-medium sm:inline">
                            {roleLibelle ?? auth.roleLibelle}
                        </span>
                    </div>
                </header>
                <main className="animate-apparition mx-auto max-w-7xl p-4 sm:p-8">
                    {children}
                </main>
            </div>
            <MessageFlash />
        </div>
    );
}
