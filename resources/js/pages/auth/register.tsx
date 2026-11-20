import { Form, Link } from '@inertiajs/react';

import { store } from '@/actions/App/Http/Controllers/Auth/RegisteredUserController';
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
 * Page d'inscription d'un nouveau client (route "register").
 *
 * Les champs correspondent aux colonnes de la table `clients` et aux règles de
 * validation de RegisteredUserController::store. Les messages d'erreur
 * renvoyés par Laravel s'affichent sous chaque champ (`errors.nom`, etc.).
 */
export default function Register() {
    return (
        <AuthLayout
            title="Créer un compte"
            description="Inscrivez-vous pour commander sur ALL4SPORT"
            className="max-w-md"
        >
            <Form
                {...store.form()}
                resetOnSuccess={['password', 'password_confirmation']}
            >
                {({ errors, processing }) => (
                    <FieldGroup>
                        <div className="grid grid-cols-2 gap-4">
                            <Field data-invalid={!!errors.nom}>
                                <FieldLabel htmlFor="nom">Nom</FieldLabel>
                                <Input
                                    id="nom"
                                    name="nom"
                                    autoComplete="family-name"
                                    autoFocus
                                    aria-invalid={!!errors.nom}
                                />
                                <FieldError>{errors.nom}</FieldError>
                            </Field>
                            <Field data-invalid={!!errors.prenom}>
                                <FieldLabel htmlFor="prenom">Prénom</FieldLabel>
                                <Input
                                    id="prenom"
                                    name="prenom"
                                    autoComplete="given-name"
                                    aria-invalid={!!errors.prenom}
                                />
                                <FieldError>{errors.prenom}</FieldError>
                            </Field>
                        </div>
                        <Field data-invalid={!!errors.email}>
                            <FieldLabel htmlFor="email">Email</FieldLabel>
                            <Input
                                id="email"
                                name="email"
                                type="email"
                                autoComplete="email"
                                aria-invalid={!!errors.email}
                            />
                            <FieldError>{errors.email}</FieldError>
                        </Field>
                        <Field data-invalid={!!errors.telephone}>
                            <FieldLabel htmlFor="telephone">
                                Téléphone
                            </FieldLabel>
                            <Input
                                id="telephone"
                                name="telephone"
                                type="tel"
                                autoComplete="tel"
                                aria-invalid={!!errors.telephone}
                            />
                            <FieldError>{errors.telephone}</FieldError>
                        </Field>
                        <Field data-invalid={!!errors.adresse}>
                            <FieldLabel htmlFor="adresse">Adresse</FieldLabel>
                            <Input
                                id="adresse"
                                name="adresse"
                                autoComplete="address-line1"
                                aria-invalid={!!errors.adresse}
                            />
                            <FieldError>{errors.adresse}</FieldError>
                        </Field>
                        <div className="grid grid-cols-2 gap-4">
                            <Field data-invalid={!!errors.code_postal}>
                                <FieldLabel htmlFor="code_postal">
                                    Code postal
                                </FieldLabel>
                                <Input
                                    id="code_postal"
                                    name="code_postal"
                                    autoComplete="postal-code"
                                    aria-invalid={!!errors.code_postal}
                                />
                                <FieldError>{errors.code_postal}</FieldError>
                            </Field>
                            <Field data-invalid={!!errors.ville}>
                                <FieldLabel htmlFor="ville">Ville</FieldLabel>
                                <Input
                                    id="ville"
                                    name="ville"
                                    autoComplete="address-level2"
                                    aria-invalid={!!errors.ville}
                                />
                                <FieldError>{errors.ville}</FieldError>
                            </Field>
                        </div>
                        <Field data-invalid={!!errors.date_naissance}>
                            <FieldLabel htmlFor="date_naissance">
                                Date de naissance
                            </FieldLabel>
                            <Input
                                id="date_naissance"
                                name="date_naissance"
                                type="date"
                                autoComplete="bday"
                                aria-invalid={!!errors.date_naissance}
                            />
                            <FieldError>{errors.date_naissance}</FieldError>
                        </Field>
                        <Field data-invalid={!!errors.password}>
                            <FieldLabel htmlFor="password">
                                Mot de passe
                            </FieldLabel>
                            <Input
                                id="password"
                                name="password"
                                type="password"
                                autoComplete="new-password"
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
                                    ? 'Création du compte...'
                                    : 'Créer mon compte'}
                            </Button>
                            <FieldDescription className="text-center">
                                Déjà un compte ?{' '}
                                <Link href={login()}>Connectez-vous</Link>
                            </FieldDescription>
                        </Field>
                    </FieldGroup>
                )}
            </Form>
        </AuthLayout>
    );
}
