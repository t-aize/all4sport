import { Form, Link } from '@inertiajs/react';

import { Button } from '@/components/ui/button';
import {
    Field,
    FieldDescription,
    FieldError,
    FieldGroup,
    FieldLabel,
    FieldLegend,
    FieldSet,
} from '@/components/ui/field';
import { Input } from '@/components/ui/input';
import { index } from '@/routes/backoffice/produits';

/**
 * Une option d'une liste déroulante (fournisseur ou rayon).
 */
export type Option = {
    id: number;
    nom: string;
};

/**
 * Un magasin dont on peut saisir le stock.
 */
export type MagasinStock = {
    id: number;
    ville: string;
};

/**
 * Une photo déjà enregistrée pour le produit.
 */
export type PhotoExistante = {
    id: number;
    url: string;
};

/**
 * Les valeurs d'un produit existant, pour préremplir le formulaire de
 * modification.
 */
export type ProduitExistant = {
    reference: string;
    description_produit: string;
    cout_unitaire_ht: string;
    prix_vente: string;
    quantite_stock_internet: number;
    lieu_stockage: string | null;
    fournisseur_id: number | null;
    rayon_id: number | null;
    photos: PhotoExistante[];
    stocks: Record<number, number>;
};

/**
 * Classes CSS communes aux champs natifs (liste déroulante, zone de texte),
 * alignées sur celles du composant Input.
 */
const classeChamp =
    'w-full min-w-0 rounded-lg border border-input bg-transparent px-2.5 py-1 text-base outline-none transition-colors focus-visible:border-ring focus-visible:ring-3 focus-visible:ring-ring/50 aria-invalid:border-destructive md:text-sm dark:bg-input/30';

/**
 * Formulaire de création et de modification d'un produit (back-office) :
 * informations, stock Internet, stock de chaque magasin et photos (de 1 à 5).
 *
 * @param action Adresse d'envoi du formulaire (générée par Wayfinder).
 * @param method Méthode HTTP du formulaire (POST, avec "_method" pour PUT).
 * @param fournisseurs Fournisseurs proposés dans la liste déroulante.
 * @param rayons Rayons proposés dans la liste déroulante.
 * @param magasins Magasins dont on saisit le stock.
 * @param produit Le produit à modifier, ou undefined pour une création.
 * @param libelleBouton Texte du bouton d'envoi.
 */
export default function ProduitForm({
    action,
    method,
    fournisseurs,
    rayons,
    magasins,
    produit,
    libelleBouton,
}: {
    action: string;
    method: 'post' | 'put';
    fournisseurs: Option[];
    rayons: Option[];
    magasins: MagasinStock[];
    produit?: ProduitExistant;
    libelleBouton: string;
}) {
    return (
        <Form action={action} method={method}>
            {({ errors, processing }) => (
                <FieldGroup>
                    <Field data-invalid={!!errors.description_produit}>
                        <FieldLabel htmlFor="description_produit">
                            Description
                        </FieldLabel>
                        <textarea
                            id="description_produit"
                            name="description_produit"
                            rows={3}
                            maxLength={500}
                            defaultValue={produit?.description_produit}
                            aria-invalid={!!errors.description_produit}
                            className={classeChamp}
                        />
                        <FieldError>{errors.description_produit}</FieldError>
                    </Field>

                    <div className="grid gap-5 sm:grid-cols-2">
                        <Field data-invalid={!!errors.fournisseur_id}>
                            <FieldLabel htmlFor="fournisseur_id">
                                Fournisseur
                            </FieldLabel>
                            <select
                                id="fournisseur_id"
                                name="fournisseur_id"
                                defaultValue={produit?.fournisseur_id ?? ''}
                                aria-invalid={!!errors.fournisseur_id}
                                className={classeChamp}
                            >
                                <option value="" disabled>
                                    Choisir un fournisseur
                                </option>
                                {fournisseurs.map((fournisseur) => (
                                    <option
                                        key={fournisseur.id}
                                        value={fournisseur.id}
                                    >
                                        {fournisseur.nom}
                                    </option>
                                ))}
                            </select>
                            <FieldError>{errors.fournisseur_id}</FieldError>
                        </Field>
                        <Field data-invalid={!!errors.rayon_id}>
                            <FieldLabel htmlFor="rayon_id">Rayon</FieldLabel>
                            <select
                                id="rayon_id"
                                name="rayon_id"
                                defaultValue={produit?.rayon_id ?? ''}
                                aria-invalid={!!errors.rayon_id}
                                className={classeChamp}
                            >
                                <option value="" disabled>
                                    Choisir un rayon
                                </option>
                                {rayons.map((rayon) => (
                                    <option key={rayon.id} value={rayon.id}>
                                        {rayon.nom}
                                    </option>
                                ))}
                            </select>
                            <FieldError>{errors.rayon_id}</FieldError>
                        </Field>
                        <Field data-invalid={!!errors.cout_unitaire_ht}>
                            <FieldLabel htmlFor="cout_unitaire_ht">
                                Coût unitaire HT (€)
                            </FieldLabel>
                            <Input
                                id="cout_unitaire_ht"
                                name="cout_unitaire_ht"
                                type="number"
                                step="0.01"
                                min="0"
                                defaultValue={produit?.cout_unitaire_ht}
                                aria-invalid={!!errors.cout_unitaire_ht}
                            />
                            <FieldError>{errors.cout_unitaire_ht}</FieldError>
                        </Field>
                        <Field data-invalid={!!errors.prix_vente}>
                            <FieldLabel htmlFor="prix_vente">
                                Prix de vente (€)
                            </FieldLabel>
                            <Input
                                id="prix_vente"
                                name="prix_vente"
                                type="number"
                                step="0.01"
                                min="0"
                                defaultValue={produit?.prix_vente}
                                aria-invalid={!!errors.prix_vente}
                            />
                            <FieldError>{errors.prix_vente}</FieldError>
                        </Field>
                        <Field data-invalid={!!errors.lieu_stockage}>
                            <FieldLabel htmlFor="lieu_stockage">
                                Lieu de stockage
                            </FieldLabel>
                            <Input
                                id="lieu_stockage"
                                name="lieu_stockage"
                                maxLength={15}
                                placeholder="M2D3.5"
                                defaultValue={produit?.lieu_stockage ?? ''}
                                aria-invalid={!!errors.lieu_stockage}
                            />
                            <FieldDescription>
                                Module M1-M6, rangée A-L, section 1-12, étagère
                                1-8.
                            </FieldDescription>
                            <FieldError>{errors.lieu_stockage}</FieldError>
                        </Field>
                        <Field data-invalid={!!errors.quantite_stock_internet}>
                            <FieldLabel htmlFor="quantite_stock_internet">
                                Stock Internet
                            </FieldLabel>
                            <Input
                                id="quantite_stock_internet"
                                name="quantite_stock_internet"
                                type="number"
                                step="1"
                                min="0"
                                defaultValue={
                                    produit?.quantite_stock_internet ?? 0
                                }
                                aria-invalid={!!errors.quantite_stock_internet}
                            />
                            <FieldError>
                                {errors.quantite_stock_internet}
                            </FieldError>
                        </Field>
                    </div>

                    <FieldSet>
                        <FieldLegend>Stock en magasin</FieldLegend>
                        <FieldDescription>
                            Laissez vide un magasin qui ne stocke pas ce
                            produit.
                        </FieldDescription>
                        <div className="grid gap-4 sm:grid-cols-3">
                            {magasins.map((magasin) => (
                                <Field
                                    key={magasin.id}
                                    data-invalid={
                                        !!errors[`stocks.${magasin.id}`]
                                    }
                                >
                                    <FieldLabel htmlFor={`stock-${magasin.id}`}>
                                        {magasin.ville}
                                    </FieldLabel>
                                    <Input
                                        id={`stock-${magasin.id}`}
                                        name={`stocks[${magasin.id}]`}
                                        type="number"
                                        step="1"
                                        min="0"
                                        defaultValue={
                                            produit?.stocks[magasin.id] ?? ''
                                        }
                                        aria-invalid={
                                            !!errors[`stocks.${magasin.id}`]
                                        }
                                    />
                                    <FieldError>
                                        {errors[`stocks.${magasin.id}`]}
                                    </FieldError>
                                </Field>
                            ))}
                        </div>
                    </FieldSet>

                    <FieldSet>
                        <FieldLegend>Photos</FieldLegend>
                        <FieldDescription>
                            Entre 1 et 5 photos par produit (JPG, PNG ou WebP, 4
                            Mo maximum chacune).
                        </FieldDescription>
                        {produit && produit.photos.length > 0 && (
                            <ul className="grid gap-3 sm:grid-cols-5">
                                {produit.photos.map((photo) => (
                                    <li
                                        key={photo.id}
                                        className="flex flex-col gap-1 text-xs"
                                    >
                                        <img
                                            src={photo.url}
                                            alt=""
                                            className="aspect-4/3 w-full rounded-lg object-cover"
                                        />
                                        <label className="flex items-center gap-1.5">
                                            <input
                                                type="checkbox"
                                                name="photos_supprimees[]"
                                                value={photo.id}
                                            />
                                            Supprimer
                                        </label>
                                    </li>
                                ))}
                            </ul>
                        )}
                        <Field data-invalid={!!errors.photos}>
                            <FieldLabel htmlFor="photos">
                                {produit ? 'Ajouter des photos' : 'Photos'}
                            </FieldLabel>
                            <Input
                                id="photos"
                                name="photos[]"
                                type="file"
                                accept="image/jpeg,image/png,image/webp"
                                multiple
                                aria-invalid={!!errors.photos}
                            />
                            <FieldError>{errors.photos}</FieldError>
                            {Object.entries(errors)
                                .filter(([champ]) =>
                                    champ.startsWith('photos.'),
                                )
                                .map(([champ, message]) => (
                                    <FieldError key={champ}>
                                        {message}
                                    </FieldError>
                                ))}
                        </Field>
                    </FieldSet>

                    <div className="flex gap-3">
                        <Button type="submit" disabled={processing}>
                            {processing ? 'Enregistrement...' : libelleBouton}
                        </Button>
                        <Button
                            render={<Link href={index()} />}
                            nativeButton={false}
                            variant="outline"
                        >
                            Annuler
                        </Button>
                    </div>
                </FieldGroup>
            )}
        </Form>
    );
}
