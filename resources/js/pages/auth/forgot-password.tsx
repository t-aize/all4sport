import { Form, Link } from '@inertiajs/react';

import { store } from '@/actions/App/Http/Controllers/Auth/PasswordResetLinkController';
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
import { login } from '@/routes';

/**
 * Page "Mot de passe oublié" (route "password.request").
 *
 * Le client saisit son email ; Laravel lui envoie un lien de réinitialisation
 * (voir PasswordResetLinkController::store).
 *
 * @param status Message de confirmation affiché une fois l'email envoyé.
 */
export default function ForgotPassword({ status }: { status?: string }) {
    return (
        <AuthLayout
            title="Mot de passe oublié"
            description="Recevez un lien pour réinitialiser votre mot de passe"
        >
            {status && (
                <p className="text-primary mb-4 text-center text-sm font-medium">
                    {status}
                </p>
            )}
            <Form {...store.form()}>
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
                        <Field>
                            <Button type="submit" disabled={processing}>
                                {processing
                                    ? 'Envoi...'
                                    : 'Envoyer le lien de réinitialisation'}
                            </Button>
                            <FieldDescription className="text-center">
                                <Link href={login()}>
                                    Retour à la connexion
                                </Link>
                            </FieldDescription>
                        </Field>
                    </FieldGroup>
                )}
            </Form>
        </AuthLayout>
    );
}
