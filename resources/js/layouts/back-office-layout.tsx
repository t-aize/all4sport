import { Form, Head, Link, usePage } from '@inertiajs/react';
import { type ReactNode } from 'react';

import { destroy } from '@/actions/App/Http/Controllers/BackOffice/AuthenticatedSessionController';
import { Button } from '@/components/ui/button';
import { index as produits } from '@/routes/backoffice/produits';
import type { Auth } from '@/types';

/**
 * Mise en page des pages du back-office réservées aux collaborateurs connectés :
 * un bandeau avec le nom de l'employé, son rôle et le bouton de déconnexion,
 * puis le contenu de la page.
 *
 * @param title Titre de la page, affiché dans l'onglet du navigateur.
 * @param roleLibelle Nom du rôle de l'employé (« Livreur », « Service client »…),
 *   fourni par le contrôleur PHP.
 * @param children Contenu de la page.
 */
export default function BackOfficeLayout({
    title,
    roleLibelle,
    children,
}: {
    title: string;
    roleLibelle: string;
    children: ReactNode;
}) {
    // `auth.employe` est partagé par HandleInertiaRequests : il vaut toujours un
    // collaborateur ici, car ces pages sont protégées par `auth:employe`.
    const { auth } = usePage<{ auth: Auth }>().props;

    return (
        <div className="bg-muted min-h-screen">
            <Head title={title} />
            <header className="bg-background border-b">
                <div className="mx-auto flex max-w-5xl items-center justify-between gap-4 p-4">
                    <div>
                        <p className="text-sm font-semibold">
                            ALL4SPORT · Back-office
                        </p>
                        <p className="text-muted-foreground text-sm">
                            {auth.employe?.prenom} {auth.employe?.nom} ·{' '}
                            {roleLibelle}
                        </p>
                    </div>
                    {/* Seuls les opérateurs et les vendeurs gèrent les produits
                        (voir routes/backoffice.php). */}
                    {(auth.employe?.role === 'operateur' ||
                        auth.employe?.role === 'vendeur') && (
                        <Link
                            href={produits()}
                            className="text-muted-foreground hover:text-foreground ml-auto text-sm font-medium"
                        >
                            Produits
                        </Link>
                    )}
                    {/* `destroy.form()` est généré par Wayfinder à partir du
                        contrôleur PHP (déconnexion en POST). */}
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
            </header>
            <main className="mx-auto max-w-5xl p-4">{children}</main>
        </div>
    );
}
