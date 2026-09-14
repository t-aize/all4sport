import { Form, Head, Link, usePage } from '@inertiajs/react';

import { destroy } from '@/actions/App/Http/Controllers/Auth/AuthenticatedSessionController';
import { Button } from '@/components/ui/button';
import { login, register } from '@/routes';
import type { Auth } from '@/types';

export default function Welcome() {
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
        </>
    );
}
