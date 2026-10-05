import { Form, Link, usePage } from '@inertiajs/react';

import { destroy } from '@/actions/App/Http/Controllers/BackOffice/ProduitController';
import { Button } from '@/components/ui/button';
import {
    Card,
    CardContent,
    CardDescription,
    CardHeader,
    CardTitle,
} from '@/components/ui/card';
import { Input } from '@/components/ui/input';
import BackOfficeLayout from '@/layouts/back-office-layout';
import { create, edit, index } from '@/routes/backoffice/produits';

/**
 * Un produit de la liste, tel que l'envoie BackOffice\ProduitController.
 */
type ProduitLigne = {
    reference: string;
    description: string;
    rayon: string | null;
    fournisseur: string | null;
    prixVente: string;
    stockInternet: number;
    nombrePhotos: number;
};

/**
 * Un lien de pagination Laravel (« Précédent », « 1 », « 2 », « Suivant »…).
 */
type LienPagination = {
    url: string | null;
    label: string;
    active: boolean;
};

/**
 * Liste des produits (route « backoffice.produits.index »), avec recherche par
 * référence ou description, modification et suppression.
 *
 * @param roleLibelle Nom du rôle de l'employé connecté.
 * @param recherche Texte recherché (vide si aucune recherche).
 * @param produits Les produits de la page courante, au format du paginateur Laravel.
 */
export default function ProduitsIndex({
    roleLibelle,
    recherche,
    produits,
}: {
    roleLibelle: string;
    recherche: string;
    produits: {
        data: ProduitLigne[];
        links: LienPagination[];
        total: number;
    };
}) {
    // `status` (message de confirmation) est partagé par HandleInertiaRequests ;
    // `errors.produit` vient du refus de supprimer un produit déjà commandé.
    const { status, errors } = usePage<{
        status?: string | null;
        errors: Record<string, string>;
    }>().props;

    return (
        <BackOfficeLayout title="Produits" roleLibelle={roleLibelle}>
            <Card>
                <CardHeader>
                    <CardTitle>Produits</CardTitle>
                    <CardDescription>
                        {produits.total} produit(s). Gérez les informations, le
                        stock Internet, le stock des magasins et les photos.
                    </CardDescription>
                </CardHeader>
                <CardContent className="flex flex-col gap-4">
                    {status && (
                        <p className="text-primary text-sm font-medium">
                            {status}
                        </p>
                    )}
                    {errors.produit && (
                        <p className="text-destructive text-sm font-medium">
                            {errors.produit}
                        </p>
                    )}

                    <div className="flex flex-wrap items-center justify-between gap-3">
                        <Form {...index.form()} className="flex flex-1 gap-2">
                            <Input
                                name="recherche"
                                defaultValue={recherche}
                                placeholder="Rechercher une référence ou une description"
                                aria-label="Rechercher un produit"
                            />
                            <Button type="submit" variant="outline">
                                Rechercher
                            </Button>
                        </Form>
                        <Button
                            render={<Link href={create()} />}
                            nativeButton={false}
                        >
                            Nouveau produit
                        </Button>
                    </div>

                    {produits.data.length === 0 ? (
                        <p className="text-muted-foreground text-sm">
                            Aucun produit.
                        </p>
                    ) : (
                        <div className="overflow-x-auto">
                            <table className="w-full text-left text-sm">
                                <thead className="text-muted-foreground border-b text-xs uppercase">
                                    <tr>
                                        <th className="py-2 pr-3">Référence</th>
                                        <th className="py-2 pr-3">
                                            Description
                                        </th>
                                        <th className="py-2 pr-3">Rayon</th>
                                        <th className="py-2 pr-3">
                                            Fournisseur
                                        </th>
                                        <th className="py-2 pr-3 text-right">
                                            Prix
                                        </th>
                                        <th className="py-2 pr-3 text-right">
                                            Stock web
                                        </th>
                                        <th className="py-2 pr-3 text-right">
                                            Photos
                                        </th>
                                        <th className="py-2" />
                                    </tr>
                                </thead>
                                <tbody>
                                    {produits.data.map((produit) => (
                                        <tr
                                            key={produit.reference}
                                            className="border-b last:border-0"
                                        >
                                            <td className="py-2 pr-3 font-mono text-xs">
                                                {produit.reference}
                                            </td>
                                            <td className="max-w-xs truncate py-2 pr-3">
                                                {produit.description}
                                            </td>
                                            <td className="py-2 pr-3">
                                                {produit.rayon}
                                            </td>
                                            <td className="py-2 pr-3">
                                                {produit.fournisseur}
                                            </td>
                                            <td className="py-2 pr-3 text-right">
                                                {produit.prixVente} €
                                            </td>
                                            <td className="py-2 pr-3 text-right">
                                                {produit.stockInternet}
                                            </td>
                                            <td className="py-2 pr-3 text-right">
                                                {produit.nombrePhotos}
                                            </td>
                                            <td className="flex justify-end gap-2 py-2">
                                                <Button
                                                    render={
                                                        <Link
                                                            href={edit(
                                                                produit.reference,
                                                            )}
                                                        />
                                                    }
                                                    nativeButton={false}
                                                    variant="outline"
                                                    size="sm"
                                                >
                                                    Modifier
                                                </Button>
                                                <Form
                                                    {...destroy.form(
                                                        produit.reference,
                                                    )}
                                                    onBefore={() =>
                                                        window.confirm(
                                                            'Supprimer ce produit, ses photos et ses stocks ?',
                                                        )
                                                    }
                                                >
                                                    {({ processing }) => (
                                                        <Button
                                                            type="submit"
                                                            variant="destructive"
                                                            size="sm"
                                                            disabled={
                                                                processing
                                                            }
                                                        >
                                                            Supprimer
                                                        </Button>
                                                    )}
                                                </Form>
                                            </td>
                                        </tr>
                                    ))}
                                </tbody>
                            </table>
                        </div>
                    )}

                    {produits.links.length > 3 && (
                        <nav
                            aria-label="Pagination"
                            className="flex flex-wrap justify-center gap-2"
                        >
                            {produits.links.map((lien) =>
                                lien.url ? (
                                    <Button
                                        key={lien.label}
                                        render={<Link href={lien.url} />}
                                        nativeButton={false}
                                        variant={
                                            lien.active ? 'default' : 'outline'
                                        }
                                        size="sm"
                                    >
                                        <span
                                            dangerouslySetInnerHTML={{
                                                __html: lien.label,
                                            }}
                                        />
                                    </Button>
                                ) : null,
                            )}
                        </nav>
                    )}
                </CardContent>
            </Card>
        </BackOfficeLayout>
    );
}
