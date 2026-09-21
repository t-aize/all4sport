import { Form } from '@inertiajs/react';

import { store } from '@/actions/App/Http/Controllers/BackOffice/AuthenticatedSessionController';
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

/**
 * Page de connexion des collaborateurs au back-office (route
 * « backoffice.login »).
 *
 * Contrairement aux clients, un collaborateur se connecte avec un identifiant
 * de connexion (et non un email). Les erreurs de validation renvoyées par
 * BackOffice\LoginRequest s'affichent sous les champs.
 */
export default function BackOfficeLogin() {
    return (
        <AuthLayout
            title="Back-office"
            description="Connectez-vous avec votre identifiant de collaborateur"
        >
            {/* `store.form()` (généré par Wayfinder) fournit l'URL et la méthode
                POST de BackOffice\AuthenticatedSessionController::store. */}
            <Form {...store.form()} resetOnSuccess={['password']}>
                {({ errors, processing }) => (
                    <FieldGroup>
                        <Field data-invalid={!!errors.identifiant_connexion}>
                            <FieldLabel htmlFor="identifiant_connexion">
                                Identifiant
                            </FieldLabel>
                            <Input
                                id="identifiant_connexion"
                                name="identifiant_connexion"
                                autoComplete="username"
                                autoFocus
                                aria-invalid={!!errors.identifiant_connexion}
                            />
                            <FieldError>
                                {errors.identifiant_connexion}
                            </FieldError>
                        </Field>
                        <Field data-invalid={!!errors.password}>
                            <FieldLabel htmlFor="password">
                                Mot de passe
                            </FieldLabel>
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
                                {processing ? 'Connexion…' : 'Se connecter'}
                            </Button>
                            <FieldDescription className="text-center">
                                Espace réservé aux collaborateurs ALL4SPORT.
                            </FieldDescription>
                        </Field>
                    </FieldGroup>
                )}
            </Form>
        </AuthLayout>
    );
}
