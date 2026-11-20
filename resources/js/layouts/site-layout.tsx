import { Head, Link, router, usePage } from '@inertiajs/react';
import {
    ChevronDown,
    LogOut,
    Menu,
    Package,
    Search,
    ShieldCheck,
    ShoppingBag,
    Store,
    Truck,
    User,
} from 'lucide-react';
import { type FormEvent, type ReactNode, useEffect, useState } from 'react';

import { destroy } from '@/actions/App/Http/Controllers/Auth/AuthenticatedSessionController';
import { Logo } from '@/components/logo';
import { MessageFlash } from '@/components/message-flash';
import { Button } from '@/components/ui/button';
import {
    DropdownMenu,
    DropdownMenuContent,
    DropdownMenuItem,
    DropdownMenuLabel,
    DropdownMenuSeparator,
    DropdownMenuTrigger,
} from '@/components/ui/dropdown-menu';
import { Input } from '@/components/ui/input';
import {
    Sheet,
    SheetContent,
    SheetHeader,
    SheetTitle,
    SheetTrigger,
} from '@/components/ui/sheet';
import { home, login, register } from '@/routes';
import { index as catalogue } from '@/routes/catalogue';
import { index as mesCommandes } from '@/routes/compte/commandes';
import { edit as monCompte } from '@/routes/compte/profil';
import { index as panier } from '@/routes/panier';
import type { Auth } from '@/types';

/**
 * Mise en page des pages publiques du site marchand : en-tête collant avec le
 * logo, la navigation, la recherche, le panier et le compte client ; contenu ;
 * pied de page. Sur mobile, la navigation passe dans un panneau latéral.
 *
 * @param title Titre de la page, affiché dans l'onglet du navigateur.
 * @param children Contenu de la page.
 * @param pleineLargeur Supprime la largeur maximale et les marges du contenu
 *   (utilisé par l'accueil pour ses bandeaux).
 */
export default function SiteLayout({
    title,
    children,
    pleineLargeur = false,
}: {
    title: string;
    children: ReactNode;
    pleineLargeur?: boolean;
}) {
    const { auth, nombreArticlesPanier } = usePage<{
        auth: Auth;
        nombreArticlesPanier: number;
    }>().props;
    const [recherche, setRecherche] = useState('');
    const [defile, setDefile] = useState(false);

    // Ombre sous l'en-tête dès que la page défile.
    useEffect(() => {
        const surDefilement = () => setDefile(window.scrollY > 8);
        surDefilement();
        window.addEventListener('scroll', surDefilement, { passive: true });

        return () => window.removeEventListener('scroll', surDefilement);
    }, []);

    const rechercher = (evenement: FormEvent) => {
        evenement.preventDefault();
        router.get(
            catalogue.url({
                query: recherche.trim() ? { recherche: recherche.trim() } : {},
            }),
        );
    };

    const liens = [
        { libelle: 'Accueil', href: home() },
        { libelle: 'Catalogue', href: catalogue() },
        { libelle: 'Nos magasins', href: `${home.url()}#magasins` },
    ];

    return (
        <div className="bg-background text-foreground flex min-h-screen flex-col">
            <Head title={title} />

            <div className="bg-navy text-navy-foreground hidden text-xs sm:block">
                <div className="mx-auto flex max-w-7xl items-center justify-center gap-8 px-4 py-2">
                    <span className="flex items-center gap-1.5">
                        <Store className="text-primary size-3.5" /> Retrait
                        gratuit dans nos 12 magasins
                    </span>
                    <span className="flex items-center gap-1.5">
                        <Truck className="text-primary size-3.5" /> Livraison à
                        domicile : forfait 10 €
                    </span>
                    <span className="flex items-center gap-1.5">
                        <ShieldCheck className="text-primary size-3.5" />{' '}
                        Paiement et échanges sécurisés
                    </span>
                </div>
            </div>

            <header
                className={`bg-background/80 sticky top-0 z-40 border-b backdrop-blur-lg transition-shadow duration-300 ${
                    defile ? 'shadow-md' : ''
                }`}
            >
                <div className="mx-auto flex max-w-7xl items-center gap-4 px-4 py-3">
                    <Sheet>
                        <SheetTrigger
                            render={
                                <Button
                                    variant="ghost"
                                    size="icon"
                                    className="md:hidden"
                                    aria-label="Ouvrir le menu"
                                />
                            }
                        >
                            <Menu className="size-5" />
                        </SheetTrigger>
                        <SheetContent side="left" className="w-72">
                            <SheetHeader>
                                <SheetTitle>
                                    <Logo />
                                </SheetTitle>
                            </SheetHeader>
                            <nav className="flex flex-col gap-1 px-4">
                                {liens.map((lien) => (
                                    <Link
                                        key={lien.libelle}
                                        href={lien.href}
                                        className="hover:bg-muted rounded-lg px-3 py-2 font-medium"
                                    >
                                        {lien.libelle}
                                    </Link>
                                ))}
                                <Link
                                    href={panier()}
                                    className="hover:bg-muted rounded-lg px-3 py-2 font-medium"
                                >
                                    Panier ({nombreArticlesPanier})
                                </Link>
                            </nav>
                        </SheetContent>
                    </Sheet>

                    <Link href={home()} aria-label="Accueil ALL4SPORT">
                        <Logo />
                    </Link>

                    <nav className="ml-4 hidden items-center gap-1 md:flex">
                        {liens.map((lien) => (
                            <Link
                                key={lien.libelle}
                                href={lien.href}
                                className="text-muted-foreground hover:text-foreground rounded-md px-3 py-2 text-sm font-medium transition-colors"
                            >
                                {lien.libelle}
                            </Link>
                        ))}
                    </nav>

                    <form
                        onSubmit={rechercher}
                        className="relative ml-auto hidden max-w-xs flex-1 lg:block"
                        role="search"
                    >
                        <Search className="text-muted-foreground pointer-events-none absolute top-1/2 left-3 size-4 -translate-y-1/2" />
                        <Input
                            value={recherche}
                            onChange={(evenement) =>
                                setRecherche(evenement.target.value)
                            }
                            placeholder="Rechercher un article…"
                            aria-label="Rechercher un article"
                            className="rounded-full pl-9"
                        />
                    </form>

                    <div className="ml-auto flex items-center gap-1 lg:ml-0">
                        <Link
                            href={panier()}
                            className="hover:bg-muted relative rounded-full p-2.5 transition-colors"
                            aria-label={`Panier : ${nombreArticlesPanier} article(s)`}
                        >
                            <ShoppingBag className="size-5" />
                            {nombreArticlesPanier > 0 && (
                                <span
                                    key={nombreArticlesPanier}
                                    className="bg-primary text-primary-foreground absolute -top-0.5 -right-0.5 flex min-w-5 items-center justify-center rounded-full px-1 text-[11px] font-bold"
                                >
                                    {nombreArticlesPanier}
                                </span>
                            )}
                        </Link>

                        {auth.user ? (
                            <DropdownMenu>
                                <DropdownMenuTrigger
                                    render={
                                        <Button
                                            variant="ghost"
                                            className="gap-2 rounded-full"
                                        />
                                    }
                                >
                                    <span className="bg-navy text-navy-foreground flex size-7 items-center justify-center rounded-full text-xs font-bold">
                                        {auth.user.prenom.charAt(0)}
                                        {auth.user.nom.charAt(0)}
                                    </span>
                                    <span className="hidden text-sm sm:inline">
                                        {auth.user.prenom}
                                    </span>
                                    <ChevronDown className="size-4 opacity-60" />
                                </DropdownMenuTrigger>
                                <DropdownMenuContent
                                    align="end"
                                    className="w-56"
                                >
                                    <DropdownMenuLabel>
                                        {auth.user.prenom} {auth.user.nom}
                                        <span className="text-muted-foreground block text-xs font-normal">
                                            {String(auth.user.code_cli ?? '')}
                                        </span>
                                    </DropdownMenuLabel>
                                    <DropdownMenuSeparator />
                                    <DropdownMenuItem
                                        render={<Link href={monCompte()} />}
                                    >
                                        <User /> Mon compte
                                    </DropdownMenuItem>
                                    <DropdownMenuItem
                                        render={<Link href={mesCommandes()} />}
                                    >
                                        <Package /> Mes commandes
                                    </DropdownMenuItem>
                                    <DropdownMenuSeparator />
                                    <DropdownMenuItem
                                        onClick={() =>
                                            router.post(destroy.url())
                                        }
                                    >
                                        <LogOut /> Se déconnecter
                                    </DropdownMenuItem>
                                </DropdownMenuContent>
                            </DropdownMenu>
                        ) : (
                            <>
                                <Button
                                    render={<Link href={login()} />}
                                    nativeButton={false}
                                    variant="ghost"
                                    className="hidden sm:inline-flex"
                                >
                                    Connexion
                                </Button>
                                <Button
                                    render={<Link href={register()} />}
                                    nativeButton={false}
                                    className="rounded-full"
                                >
                                    Inscription
                                </Button>
                            </>
                        )}
                    </div>
                </div>
            </header>

            <main
                className={
                    pleineLargeur
                        ? 'flex-1'
                        : 'mx-auto w-full max-w-7xl flex-1 px-4 py-8'
                }
            >
                {children}
            </main>

            <PiedDePage />
            <MessageFlash />
        </div>
    );
}

/**
 * Pied de page du site : présentation, liens utiles et rappel des services.
 */
function PiedDePage() {
    return (
        <footer className="bg-navy text-navy-foreground mt-16">
            <div className="mx-auto grid max-w-7xl gap-10 px-4 py-12 sm:grid-cols-2 lg:grid-cols-4">
                <div className="flex flex-col gap-3">
                    <Logo inverse />
                    <p className="text-sm text-white/70">
                        Depuis 2008, ALL4SPORT équipe les sportifs amateurs et
                        passionnés, depuis son siège de Lille.
                    </p>
                </div>
                <div>
                    <p className="mb-3 font-semibold">Boutique</p>
                    <ul className="flex flex-col gap-2 text-sm text-white/70">
                        <li>
                            <Link
                                href={catalogue()}
                                className="hover:text-white"
                            >
                                Catalogue
                            </Link>
                        </li>
                        <li>
                            <Link href={panier()} className="hover:text-white">
                                Mon panier
                            </Link>
                        </li>
                        <li>
                            <Link
                                href={mesCommandes()}
                                className="hover:text-white"
                            >
                                Suivre mes commandes
                            </Link>
                        </li>
                    </ul>
                </div>
                <div>
                    <p className="mb-3 font-semibold">Services</p>
                    <ul className="flex flex-col gap-2 text-sm text-white/70">
                        <li>Retrait en magasin gratuit</li>
                        <li>Livraison à domicile : 10 €</li>
                        <li>3 entrepôts : Le Havre, Lyon, Marseille</li>
                    </ul>
                </div>
                <div>
                    <p className="mb-3 font-semibold">Espace collaborateurs</p>
                    <p className="mb-3 text-sm text-white/70">
                        Service client, opérateurs, livreurs et vendeurs.
                    </p>
                    <a
                        href="/backoffice"
                        className="text-primary text-sm font-semibold hover:underline"
                    >
                        Accéder au back-office →
                    </a>
                </div>
            </div>
            <div className="border-t border-white/10">
                <p className="mx-auto max-w-7xl px-4 py-4 text-xs text-white/50">
                    © {new Date().getFullYear()} ALL4SPORT SA — Lille. Données
                    fictives (projet AO-03569, lot A « Vente »).
                </p>
            </div>
        </footer>
    );
}
