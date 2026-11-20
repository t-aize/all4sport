import { Form, Link } from '@inertiajs/react';

import { store } from '@/actions/App/Http/Controllers/Auth/AuthenticatedSessionController';
import { Button } from '@/components/ui/button';
import {
    Field,
    FieldDescription,
    FieldError,
    FieldGroup,
    FieldLabel,
} from '@/components/ui/field';
import { Input } from '@/components/ui/input';
import AuthLayout from '@/layouts/auth-layout';
import { register } from '@/routes';
import { request } from '@/routes/password';

/**
 * Page de connexion d'un client (route "login").
 *
 * @param status Message de confirmation envoyé par le contrôleur, par exemple
 *   "Votre mot de passe a été réinitialisé" après une réinitialisation.
 */
export default function Login({ status }: { status?: string }) {
    return (
        <AuthLayout
            title="Connexion"
            description="Connectez-vous à votre compte ALL4SPORT"
        >
            {status && (
                <p className="text-primary mb-4 text-center text-sm font-medium">
                    {status}
                </p>
            )}
            {/* `Form` envoie le formulaire sans recharger la page. `store.form()`
                (généré par Wayfinder) fournit l'URL et la méthode POST du
                contrôleur AuthenticatedSessionController::store. `errors`
                contient les messages de validation renvoyés par Laravel et
                `processing` vaut true pendant l'envoi. */}
            <Form {...store.form()} resetOnSuccess={['password']}>
                {({ errors, processing }) => (
                    <FieldGroup>
                        <Field data-invalid={!!errors.email}>
                            <FieldLabel htmlFor="email">Email</FieldLabel>
                            <Input
                                id="email"
                                name="email"
                                type="email"
                                autoComplete="email"
                                autoFocus
                                aria-invalid={!!errors.email}
                            />
                            <FieldError>{errors.email}</FieldError>
                        </Field>
                        <Field data-invalid={!!errors.password}>
                            <div className="flex items-center">
                                <FieldLabel htmlFor="password">
                                    Mot de passe
                                </FieldLabel>
                                <Link
                                    href={request()}
                                    className="ml-auto text-sm underline-offset-4 hover:underline"
                                >
                                    Mot de passe oublié ?
                                </Link>
                            </div>
                            <Input
                                id="password"
                                name="password"
                                type="password"
                                autoComplete="current-password"
                                aria-invalid={!!errors.password}
                            />
                            <FieldError>{errors.password}</FieldError>
                        </Field>
                        <Field>
                            <Button type="submit" disabled={processing}>
                                {processing ? 'Connexion...' : 'Se connecter'}
                            </Button>
                            <FieldDescription className="text-center">
                                Pas encore de compte ?{' '}
                                <Link href={register()}>Inscrivez-vous</Link>
                            </FieldDescription>
                        </Field>
                    </FieldGroup>
                )}
            </Form>
        </AuthLayout>
    );
}
