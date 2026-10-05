import { Link } from '@inertiajs/react';

import { Button } from '@/components/ui/button';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import SiteLayout from '@/layouts/site-layout';
import { index, show } from '@/routes/catalogue';

/**
 * Un rayon (sport) proposé comme filtre, tel que l'envoie CatalogueController.
 */
type Rayon = {
    id: number;
    nom: string;
};

/**
 * Un produit affiché sous forme de carte dans la liste du catalogue.
 */
type ProduitCarte = {
    reference: string;
    description: string;
    prixVente: string;
    rayon: string | null;
    photo: string | null;
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
 * Les produits d'une page, au format du paginateur Laravel.
 */
type ProduitsPagines = {
    data: ProduitCarte[];
    links: LienPagination[];
    total: number;
};

/**
 * Catalogue public (route « catalogue.index ») : les produits triés par rayon,
 * avec un filtre pour n'afficher qu'un seul sport.
 *
 * @param rayons Tous les rayons, pour les boutons de filtre.
 * @param rayonActif Identifiant du rayon filtré, ou null pour tous les rayons.
 * @param produits Les produits de la page courante.
 */
export default function CatalogueIndex({
    rayons,
    rayonActif,
    produits,
}: {
    rayons: Rayon[];
    rayonActif: number | null;
    produits: ProduitsPagines;
}) {
    return (
        <SiteLayout title="Catalogue">
            <h1 className="mb-4 text-2xl font-semibold">Catalogue</h1>

            <div className="mb-6 flex flex-wrap gap-2">
                <Button
                    render={<Link href={index()} />}
                    nativeButton={false}
                    variant={rayonActif === null ? 'default' : 'outline'}
                >
                    Tous les rayons
                </Button>
                {rayons.map((rayon) => (
                    <Button
                        key={rayon.id}
                        render={
                            <Link
                                href={index({ query: { rayon: rayon.id } })}
                            />
                        }
                        nativeButton={false}
                        variant={
                            rayonActif === rayon.id ? 'default' : 'outline'
                        }
                    >
                        {rayon.nom}
                    </Button>
                ))}
            </div>

            {produits.data.length === 0 ? (
                <p className="text-muted-foreground">
                    Aucun produit dans ce rayon pour le moment.
                </p>
            ) : (
                <ul className="grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
                    {produits.data.map((produit) => (
                        <li key={produit.reference}>
                            <Link
                                href={show(produit.reference)}
                                className="block h-full"
                            >
                                <Card className="h-full">
                                    {produit.photo ? (
                                        <img
                                            src={produit.photo}
                                            alt={produit.description}
                                            className="aspect-4/3 w-full object-cover"
                                        />
                                    ) : (
                                        <div className="bg-muted text-muted-foreground flex aspect-4/3 items-center justify-center text-sm">
                                            Pas de photo
                                        </div>
                                    )}
                                    <CardHeader>
                                        <p className="text-muted-foreground text-xs uppercase">
                                            {produit.rayon}
                                        </p>
                                        <CardTitle className="line-clamp-2">
                                            {produit.description}
                                        </CardTitle>
                                    </CardHeader>
                                    <CardContent>
                                        <p className="text-lg font-semibold">
                                            {produit.prixVente} €
                                        </p>
                                    </CardContent>
                                </Card>
                            </Link>
                        </li>
                    ))}
                </ul>
            )}

            {produits.links.length > 3 && (
                <nav
                    aria-label="Pagination"
                    className="mt-6 flex flex-wrap justify-center gap-2"
                >
                    {produits.links.map((lien) =>
                        lien.url ? (
                            <Button
                                key={lien.label}
                                render={<Link href={lien.url} />}
                                nativeButton={false}
                                variant={lien.active ? 'default' : 'outline'}
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
        </SiteLayout>
    );
}
