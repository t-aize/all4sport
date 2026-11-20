import { Form } from '@inertiajs/react';
import { useState } from 'react';

import { Button } from '@/components/ui/button';
import {
    Field,
    FieldDescription,
    FieldError,
    FieldGroup,
    FieldLabel,
} from '@/components/ui/field';
import { Input } from '@/components/ui/input';

/**
 * Déduit le code fournisseur d'un nom : 3 premières lettres, sans accent, en
 * majuscules (même règle que Fournisseur::codeDepuisNom côté PHP).
 */
function codeDepuisNom(nom: string): string {
    return nom
        .normalize('NFD')
        .replace(/[̀-ͯ]/g, '')
        .toUpperCase()
        .replace(/[^A-Z]/g, '')
        .slice(0, 3);
}

/**
 * Formulaire de création / modification d'un fournisseur.
 *
 * @param action URL d'envoi du formulaire.
 * @param method Méthode HTTP (post à la création, put à la modification).
 * @param valeurs Valeurs initiales.
 * @param codeVerrouille Le code ne peut plus changer (produits rattachés).
 * @param libelleBouton Texte du bouton d'envoi.
 */
export function FournisseurForm({
    action,
    method,
    valeurs = { nom: '', code: '' },
    codeVerrouille = false,
    libelleBouton,
}: {
    action: string;
    method: 'post' | 'put';
    valeurs?: { nom: string; code: string };
    codeVerrouille?: boolean;
    libelleBouton: string;
}) {
    const [nom, setNom] = useState(valeurs.nom);
    const [code, setCode] = useState(valeurs.code);
    const [codeSaisi, setCodeSaisi] = useState(valeurs.code !== '');

    return (
        <Form action={action} method={method} className="max-w-xl">
            {({ errors, processing }) => (
                <FieldGroup>
                    <Field data-invalid={!!errors.nom_fournisseur}>
                        <FieldLabel htmlFor="nom_fournisseur">
                            Nom du fournisseur
                        </FieldLabel>
                        <Input
                            id="nom_fournisseur"
                            name="nom_fournisseur"
                            value={nom}
                            maxLength={40}
                            autoFocus
                            onChange={(evenement) => {
                                setNom(evenement.target.value);

                                if (!codeSaisi && !codeVerrouille) {
                                    setCode(
                                        codeDepuisNom(evenement.target.value),
                                    );
                                }
                            }}
                            aria-invalid={!!errors.nom_fournisseur}
                        />
                        <FieldError>{errors.nom_fournisseur}</FieldError>
                    </Field>
                    <Field data-invalid={!!errors.code_fournisseur}>
                        <FieldLabel htmlFor="code_fournisseur">
                            Code (3 lettres)
                        </FieldLabel>
                        <Input
                            id="code_fournisseur"
                            name="code_fournisseur"
                            value={code}
                            maxLength={3}
                            readOnly={codeVerrouille}
                            onChange={(evenement) => {
                                setCodeSaisi(true);
                                setCode(evenement.target.value.toUpperCase());
                            }}
                            className="w-28 font-mono text-lg tracking-widest uppercase"
                            aria-invalid={!!errors.code_fournisseur}
                        />
                        <FieldDescription>
                            {codeVerrouille
                                ? 'Code figé : il figure dans la référence des produits de ce fournisseur.'
                                : 'Proposé automatiquement à partir du nom ; il débute la référence des produits.'}
                        </FieldDescription>
                        <FieldError>{errors.code_fournisseur}</FieldError>
                    </Field>
                    <Button
                        type="submit"
                        disabled={processing}
                        className="w-fit"
                    >
                        {processing ? 'Enregistrement...' : libelleBouton}
                    </Button>
                </FieldGroup>
            )}
        </Form>
    );
}
