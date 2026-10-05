import { Form, Head, Link, usePage } from '@inertiajs/react';
import { type ReactNode } from 'react';

import { destroy } from '@/actions/App/Http/Controllers/Auth/AuthenticatedSessionController';
import { Button } from '@/components/ui/button';
import { home, login, register } from '@/routes';
import { index as catalogue } from '@/routes/catalogue';
import type { Auth } from '@/types';

/**
 * Mise en page des pages publiques du site marchand : un bandeau avec le logo,
 * le lien vers le catalogue et les boutons de connexion (ou de déconnexion si
 * un client est connecté), puis le contenu de la page.
 *
 * @param title Titre de la page, affiché dans l'onglet du navigateur.
 * @param children Contenu de la page.
 */
export default function SiteLayout({
    title,
    children,
}: {
    title: string;
    children: ReactNode;
}) {
    // `auth.user` est partagé par HandleInertiaRequests : le client connecté,
    // ou null pour un visiteur.
    const { auth } = usePage<{ auth: Auth }>().props;

    return (
        <div className="bg-background text-foreground min-h-screen">
            <Head title={title} />
            <header className="border-b">
                <div className="mx-auto flex max-w-6xl items-center justify-between gap-4 p-4">
                    <nav className="flex items-center gap-6">
                        <Link href={home()} className="text-lg font-semibold">
                            ALL4SPORT
                        </Link>
                        <Link
                            href={catalogue()}
                            className="text-muted-foreground hover:text-foreground text-sm font-medium"
                        >
                            Catalogue
                        </Link>
                    </nav>
                    {auth.user ? (
                        <div className="flex items-center gap-3">
                            <span className="text-muted-foreground hidden text-sm sm:inline">
                                {auth.user.prenom} {auth.user.nom}
                            </span>
                            <Form {...destroy.form()}>
                                {({ processing }) => (
                                    <Button
                                        type="submit"
                                        variant="outline"
                                        disabled={processing}
                                    >
                                        Se déconnecter
                                    </Button>
                                )}
                            </Form>
                        </div>
                    ) : (
                        <div className="flex items-center gap-3">
                            <Button
                                render={<Link href={login()} />}
                                nativeButton={false}
                                variant="outline"
                            >
                                Connexion
                            </Button>
                            <Button
                                render={<Link href={register()} />}
                                nativeButton={false}
                            >
                                Inscription
                            </Button>
                        </div>
                    )}
                </div>
            </header>
            <main className="mx-auto max-w-6xl p-4">{children}</main>
        </div>
    );
}
