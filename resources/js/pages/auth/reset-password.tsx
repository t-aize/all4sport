import { Form } from '@inertiajs/react';

import { store } from '@/actions/App/Http/Controllers/Auth/NewPasswordController';
import { Button } from '@/components/ui/button';
import {
    Field,
    FieldError,
    FieldGroup,
    FieldLabel,
} from '@/components/ui/field';
import { Input } from '@/components/ui/input';
import AuthLayout from '@/layouts/auth-layout';

/**
 * Page de choix d'un nouveau mot de passe, ouverte depuis le lien reçu par
 * email (route "password.reset").
 *
 * @param email Email du compte, transmis par NewPasswordController::create.
 * @param token Jeton de réinitialisation présent dans le lien. Il est renvoyé
 *   tel quel à Laravel, qui vérifie qu'il est valide.
 */
export default function ResetPassword({
    email,
    token,
}: {
    email: string;
    token: string;
}) {
    return (
        <AuthLayout
            title="Réinitialiser le mot de passe"
            description="Choisissez un nouveau mot de passe pour votre compte"
        >
            <Form
                {...store.form()}
                resetOnSuccess={['password', 'password_confirmation']}
            >
                {({ errors, processing }) => (
                    <FieldGroup>
                        <input type="hidden" name="token" value={token} />
                        <Field data-invalid={!!errors.email}>
                            <FieldLabel htmlFor="email">Email</FieldLabel>
                            <Input
                                id="email"
                                name="email"
                                type="email"
                                autoComplete="email"
                                defaultValue={email}
                                readOnly
                                aria-invalid={!!errors.email}
                            />
                            <FieldError>{errors.email}</FieldError>
                        </Field>
                        <Field data-invalid={!!errors.password}>
                            <FieldLabel htmlFor="password">
                                Nouveau mot de passe
                            </FieldLabel>
                            <Input
                                id="password"
                                name="password"
                                type="password"
                                autoComplete="new-password"
                                autoFocus
                                aria-invalid={!!errors.password}
                            />
                            <FieldError>{errors.password}</FieldError>
                        </Field>
                        <Field data-invalid={!!errors.password_confirmation}>
                            <FieldLabel htmlFor="password_confirmation">
                                Confirmation du mot de passe
                            </FieldLabel>
                            <Input
                                id="password_confirmation"
                                name="password_confirmation"
                                type="password"
                                autoComplete="new-password"
                                aria-invalid={!!errors.password_confirmation}
                            />
                            <FieldError>
                                {errors.password_confirmation}
                            </FieldError>
                        </Field>
                        <Field>
                            <Button type="submit" disabled={processing}>
                                {processing
                                    ? 'Réinitialisation...'
                                    : 'Réinitialiser le mot de passe'}
                            </Button>
                        </Field>
                    </FieldGroup>
                )}
            </Form>
        </AuthLayout>
    );
}
