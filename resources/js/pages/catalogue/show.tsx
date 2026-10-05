import { Link } from '@inertiajs/react';

import { Button } from '@/components/ui/button';
import {
    Card,
    CardContent,
    CardDescription,
    CardHeader,
    CardTitle,
} from '@/components/ui/card';
import SiteLayout from '@/layouts/site-layout';
import { index } from '@/routes/catalogue';

/**
 * Le stock d'un produit dans un magasin.
 */
type StockMagasin = {
    id: number;
    ville: string;
    adresse: string;
    quantite: number;
};

/**
 * La fiche détaillée d'un produit, telle que l'envoie CatalogueController.
 */
type Produit = {
    reference: string;
    description: string;
    prixVente: string;
    rayon: string | null;
    rayonId: number | null;
    photos: string[];
    stockInternet: number;
    stocksMagasins: StockMagasin[];
};

/**
 * Fiche produit publique (route « catalogue.show ») : photos, prix de vente et
 * disponibilité (stock Internet et stock de chaque magasin).
 *
 * @param produit Le produit à présenter.
 */
export default function CatalogueShow({ produit }: { produit: Produit }) {
    return (
        <SiteLayout title={produit.description}>
            <Button
                render={
                    <Link
                        href={index(
                            produit.rayonId
                                ? { query: { rayon: produit.rayonId } }
                                : undefined,
                        )}
                    />
                }
                nativeButton={false}
                variant="ghost"
                className="mb-4"
            >
                ← Retour au catalogue
            </Button>

            <div className="grid gap-8 md:grid-cols-2">
                <div className="grid gap-2 sm:grid-cols-2">
                    {produit.photos.length === 0 ? (
                        <div className="bg-muted text-muted-foreground flex aspect-4/3 items-center justify-center rounded-xl text-sm sm:col-span-2">
                            Pas de photo
                        </div>
                    ) : (
                        produit.photos.map((photo, position) => (
                            <img
                                key={photo}
                                src={photo}
                                alt={`${produit.description} (photo ${position + 1})`}
                                className={`aspect-4/3 w-full rounded-xl object-cover ${
                                    position === 0 ? 'sm:col-span-2' : ''
                                }`}
                            />
                        ))
                    )}
                </div>

                <div className="flex flex-col gap-4">
                    <div>
                        <p className="text-muted-foreground text-xs uppercase">
                            {produit.rayon}
                        </p>
                        <h1 className="text-2xl font-semibold">
                            {produit.description}
                        </h1>
                        <p className="text-muted-foreground mt-1 text-xs">
                            Référence : {produit.reference}
                        </p>
                    </div>

                    <p className="text-3xl font-semibold">
                        {produit.prixVente} €
                    </p>

                    <Card>
                        <CardHeader>
                            <CardTitle>Disponibilité</CardTitle>
                            <CardDescription>
                                Quantité disponible pour chaque lieu de stock.
                            </CardDescription>
                        </CardHeader>
                        <CardContent>
                            <ul className="flex flex-col gap-2 text-sm">
                                <li className="bg-muted flex justify-between rounded-md px-3 py-2">
                                    <span className="font-medium">
                                        Internet
                                    </span>
                                    <span>{produit.stockInternet}</span>
                                </li>
                                {produit.stocksMagasins.map((stock) => (
                                    <li
                                        key={stock.id}
                                        className="bg-muted flex justify-between rounded-md px-3 py-2"
                                    >
                                        <span>
                                            <span className="font-medium">
                                                Magasin de {stock.ville}
                                            </span>
                                            <span className="text-muted-foreground block text-xs">
                                                {stock.adresse}
                                            </span>
                                        </span>
                                        <span>{stock.quantite}</span>
                                    </li>
                                ))}
                            </ul>
                        </CardContent>
                    </Card>
                </div>
            </div>
        </SiteLayout>
    );
}
