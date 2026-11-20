import { Head, Link } from '@inertiajs/react';
import { ShieldCheck, Store, Truck } from 'lucide-react';
import { type ReactNode } from 'react';

import { Logo } from '@/components/logo';
import { home } from '@/routes';

/**
 * Mise en page commune aux pages d'authentification (connexion, inscription,
 * mot de passe oublié, réinitialisation, connexion du back-office) : un
 * panneau de marque à gauche (sur grand écran) et le formulaire à droite.
 *
 * Un "layout" évite de recopier la même structure dans chaque page : la page
 * fournit seulement son titre, sa description et son formulaire (`children`).
 *
 * @param title Titre affiché au-dessus du formulaire et dans l'onglet.
 * @param description Phrase d'explication sous le titre.
 * @param children Contenu de la page (le formulaire).
 * @param className Classes CSS optionnelles pour changer la largeur du formulaire.
 */
export default function AuthLayout({
    title,
    description,
    children,
    className,
}: {
    title: string;
    description: string;
    children: ReactNode;
    className?: string;
}) {
    return (
        <div className="bg-background grid min-h-screen lg:grid-cols-2">
            {/* `Head` modifie le <title> de la page depuis React. */}
            <Head title={title} />

            <div className="bg-navy fond-sport relative hidden flex-col justify-between overflow-hidden p-10 text-white lg:flex">
                <Link href={home()} aria-label="Accueil">
                    <Logo inverse />
                </Link>
                <img
                    src="/images/all4sport-logo.png"
                    alt=""
                    aria-hidden
                    className="mx-auto w-3/4 max-w-md drop-shadow-2xl"
                />
                <div className="flex flex-col gap-3 text-sm text-white/80">
                    <p className="text-2xl font-bold text-white">
                        L'équipement de tous les sportifs.
                    </p>
                    <p className="flex items-center gap-2">
                        <Store className="text-primary size-4" /> 12 magasins et
                        un site marchand
                    </p>
                    <p className="flex items-center gap-2">
                        <Truck className="text-primary size-4" /> Retrait
                        gratuit ou livraison à 10 €
                    </p>
                    <p className="flex items-center gap-2">
                        <ShieldCheck className="text-primary size-4" />{' '}
                        Connexion et échanges chiffrés
                    </p>
                </div>
            </div>

            <div className="flex items-center justify-center p-6 sm:p-10">
                <div
                    className={`animate-apparition flex w-full max-w-sm flex-col gap-6 ${className ?? ''}`}
                >
                    <Link
                        href={home()}
                        className="lg:hidden"
                        aria-label="Accueil"
                    >
                        <Logo />
                    </Link>
                    <div>
                        <h1 className="text-2xl font-bold tracking-tight">
                            {title}
                        </h1>
                        <p className="text-muted-foreground text-sm">
                            {description}
                        </p>
                    </div>
                    {children}
                </div>
            </div>
        </div>
    );
}
