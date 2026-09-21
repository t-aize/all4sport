import { Form, Head, Link, usePage } from '@inertiajs/react';

import { destroy } from '@/actions/App/Http/Controllers/Auth/AuthenticatedSessionController';
import { Button } from '@/components/ui/button';
import { login, register } from '@/routes';
import type { Auth } from '@/types';

/**
 * Page d'accueil (route « home », déclarée dans routes/web.php).
 *
 * Affiche un bouton de déconnexion si un client est connecté, ou les boutons
 * « Connexion » et « Inscription » dans le cas contraire.
 */
export default function Welcome() {
    // `usePage().props` contient les données partagées par le middleware
    // HandleInertiaRequests, dont `auth.user` (le client connecté ou null).
    const { auth } = usePage<{ auth: Auth }>().props;

    return (
        <>
            <Head title="Welcome" />
            <div className="bg-background text-foreground flex min-h-screen flex-col items-center justify-center gap-4">
                <h1 className="text-2xl font-medium">Hello World</h1>
                {auth.user ? (
                    <div className="flex items-center gap-3">
                        <span className="text-muted-foreground text-sm">
                            Connecté en tant que {auth.user.prenom}
                        </span>
                        {/* `destroy.form()` est généré par Wayfinder à partir du
                            contrôleur PHP : l'URL et la méthode HTTP (POST) ne
                            sont donc jamais écrites à la main. */}
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
                        {/* `login()` et `register()` renvoient l'URL des routes
                            nommées « login » et « register » de Laravel. */}
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
        </>
    );
}
