import { Form, Link } from '@inertiajs/react';
import {
    ArrowLeft,
    Check,
    Globe,
    ImageOff,
    Minus,
    Plus,
    ShoppingBag,
    Store,
    Truck,
} from 'lucide-react';
import { useState } from 'react';

import { ajouter } from '@/actions/App/Http/Controllers/PanierController';
import { ProduitCard } from '@/components/produit-card';
import { Button } from '@/components/ui/button';
import SiteLayout from '@/layouts/site-layout';
import { formatPrix } from '@/lib/format';
import { index } from '@/routes/catalogue';
import type { ProduitCarte } from '@/types/catalogue';

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
 * Le détail d'un produit, tel que l'envoie CatalogueController.
 */
type Produit = {
    reference: string;
    description: string;
    prixVente: string;
    rayon: string | null;
    rayonId: number | null;
    fournisseur: string | null;
    photos: string[];
    stockInternet: number;
    stockTotal: number;
    stocksMagasins: StockMagasin[];
};

/**
 * Pastille de disponibilité selon la quantité.
 */
function Disponibilite({ quantite }: { quantite: number }) {
    if (quantite === 0) {
        return (
            <span className="text-muted-foreground text-xs font-medium">
                Indisponible
            </span>
        );
    }

    return (
        <span
            className={`inline-flex items-center gap-1.5 text-xs font-semibold ${
                quantite < 5 ? 'text-amber-600' : 'text-emerald-600'
            }`}
        >
            <span
                className={`size-2 rounded-full ${quantite < 5 ? 'bg-amber-500' : 'bg-emerald-500'}`}
            />
            {quantite} en stock
        </span>
    );
}

/**
 * Fiche produit publique (route "catalogue.show") : galerie de 1 à 5
 * photos, prix de vente, référence, disponibilité (stock Internet et stock de
 * chaque magasin), ajout au panier et produits du même rayon.
 */
export default function CatalogueShow({
    produit,
    similaires,
}: {
    produit: Produit;
    similaires: ProduitCarte[];
}) {
    const [photoActive, setPhotoActive] = useState(0);
    const [quantite, setQuantite] = useState(1);
    const disponibleMax = Math.max(
        produit.stockInternet,
        ...produit.stocksMagasins.map((stock) => stock.quantite),
        0,
    );

    return (
        <SiteLayout title={produit.description}>
            <Link
                href={index(
                    produit.rayonId
                        ? { query: { rayon: produit.rayonId } }
                        : undefined,
                )}
                className="text-muted-foreground hover:text-foreground mb-6 inline-flex items-center gap-1 text-sm"
            >
                <ArrowLeft className="size-4" /> Retour au rayon {produit.rayon}
            </Link>

            <div className="grid gap-10 lg:grid-cols-2">
                <div className="animate-apparition flex flex-col gap-3">
                    <div className="bg-muted relative aspect-4/3 overflow-hidden rounded-3xl border">
                        {produit.photos.length === 0 ? (
                            <div className="text-muted-foreground flex size-full items-center justify-center">
                                <ImageOff className="size-12" />
                            </div>
                        ) : (
                            <img
                                key={photoActive}
                                src={produit.photos[photoActive]}
                                alt={`${produit.description} (photo ${photoActive + 1})`}
                                className="size-full object-cover"
                            />
                        )}
                    </div>
                    {produit.photos.length > 1 && (
                        <div className="grid grid-cols-5 gap-2">
                            {produit.photos.map((photo, position) => (
                                <button
                                    key={photo}
                                    type="button"
                                    onClick={() => setPhotoActive(position)}
                                    className={`aspect-square overflow-hidden rounded-xl border-2 transition-all ${
                                        position === photoActive
                                            ? 'border-primary scale-95'
                                            : 'border-transparent opacity-70 hover:opacity-100'
                                    }`}
                                    aria-label={`Afficher la photo ${position + 1}`}
                                >
                                    <img
                                        src={photo}
                                        alt=""
                                        className="size-full object-cover"
                                    />
                                </button>
                            ))}
                        </div>
                    )}
                </div>

                <div className="animate-apparition flex flex-col gap-6">
                    <div className="flex flex-col gap-2">
                        <div className="flex flex-wrap items-center gap-2">
                            <span className="bg-primary/10 text-primary rounded-full px-3 py-1 text-xs font-semibold uppercase">
                                {produit.rayon}
                            </span>
                            {produit.fournisseur && (
                                <span className="text-muted-foreground text-xs">
                                    par {produit.fournisseur}
                                </span>
                            )}
                        </div>
                        <h1 className="text-3xl font-bold tracking-tight">
                            {produit.description}
                        </h1>
                        <p className="text-muted-foreground font-mono text-xs">
                            Réf. {produit.reference}
                        </p>
                    </div>

                    <p className="text-4xl font-black tracking-tight">
                        {formatPrix(produit.prixVente)}
                    </p>

                    <Form
                        {...ajouter.form(produit.reference)}
                        options={{ preserveScroll: true }}
                        className="flex flex-col gap-3"
                    >
                        {({ processing, errors, recentlySuccessful }) => (
                            <>
                                <div className="flex gap-3">
                                    <div className="flex items-center rounded-xl border">
                                        <button
                                            type="button"
                                            onClick={() =>
                                                setQuantite((q) =>
                                                    Math.max(1, q - 1),
                                                )
                                            }
                                            className="hover:bg-muted flex size-11 items-center justify-center rounded-l-xl"
                                            aria-label="Diminuer la quantité"
                                        >
                                            <Minus className="size-4" />
                                        </button>
                                        <input
                                            name="quantite"
                                            type="number"
                                            min={1}
                                            max={Math.max(1, disponibleMax)}
                                            value={quantite}
                                            onChange={(evenement) =>
                                                setQuantite(
                                                    Math.max(
                                                        1,
                                                        Number(
                                                            evenement.target
                                                                .value,
                                                        ) || 1,
                                                    ),
                                                )
                                            }
                                            className="w-12 [appearance:textfield] bg-transparent text-center font-semibold outline-none [&::-webkit-inner-spin-button]:appearance-none"
                                            aria-label="Quantité"
                                        />
                                        <button
                                            type="button"
                                            onClick={() =>
                                                setQuantite((q) =>
                                                    Math.min(
                                                        Math.max(
                                                            1,
                                                            disponibleMax,
                                                        ),
                                                        q + 1,
                                                    ),
                                                )
                                            }
                                            className="hover:bg-muted flex size-11 items-center justify-center rounded-r-xl"
                                            aria-label="Augmenter la quantité"
                                        >
                                            <Plus className="size-4" />
                                        </button>
                                    </div>
                                    <Button
                                        type="submit"
                                        size="lg"
                                        disabled={
                                            processing || disponibleMax === 0
                                        }
                                        className="h-11 flex-1 rounded-xl text-base"
                                    >
                                        {recentlySuccessful ? (
                                            <>
                                                <Check /> Ajouté !
                                            </>
                                        ) : (
                                            <>
                                                <ShoppingBag />
                                                {disponibleMax === 0
                                                    ? 'Rupture de stock'
                                                    : 'Ajouter au panier'}
                                            </>
                                        )}
                                    </Button>
                                </div>
                                {errors.quantite && (
                                    <p className="text-destructive text-sm">
                                        {errors.quantite}
                                    </p>
                                )}
                            </>
                        )}
                    </Form>

                    <div className="grid grid-cols-2 gap-3 text-sm">
                        <div className="bg-muted/60 flex items-center gap-2 rounded-xl p-3">
                            <Store className="text-primary size-4" /> Retrait
                            magasin gratuit
                        </div>
                        <div className="bg-muted/60 flex items-center gap-2 rounded-xl p-3">
                            <Truck className="text-primary size-4" /> Livraison
                            10 €
                        </div>
                    </div>

                    <div className="rounded-2xl border">
                        <div className="flex items-center justify-between border-b p-4">
                            <div>
                                <p className="font-semibold">Disponibilité</p>
                                <p className="text-muted-foreground text-xs">
                                    Quantité disponible pour chaque lieu de
                                    stock
                                </p>
                            </div>
                            <span className="text-muted-foreground text-xs">
                                Total : {produit.stockTotal}
                            </span>
                        </div>
                        <ul className="divide-y text-sm">
                            <li className="flex items-center justify-between gap-3 p-4">
                                <span className="flex items-center gap-2 font-medium">
                                    <Globe className="text-primary size-4" />{' '}
                                    Internet
                                </span>
                                <Disponibilite
                                    quantite={produit.stockInternet}
                                />
                            </li>
                            {produit.stocksMagasins.map((stock) => (
                                <li
                                    key={stock.id}
                                    className="flex items-center justify-between gap-3 p-4"
                                >
                                    <span className="flex items-center gap-2">
                                        <Store className="text-muted-foreground size-4" />
                                        <span>
                                            <span className="font-medium">
                                                Magasin de {stock.ville}
                                            </span>
                                            <span className="text-muted-foreground block text-xs">
                                                {stock.adresse}
                                            </span>
                                        </span>
                                    </span>
                                    <Disponibilite quantite={stock.quantite} />
                                </li>
                            ))}
                        </ul>
                    </div>
                </div>
            </div>

            {similaires.length > 0 && (
                <section className="mt-16">
                    <h2 className="mb-6 text-2xl font-bold tracking-tight">
                        Vous aimerez aussi
                    </h2>
                    <div className="grid grid-cols-2 gap-4 lg:grid-cols-4">
                        {similaires.map((similaire, position) => (
                            <ProduitCard
                                key={similaire.reference}
                                produit={similaire}
                                position={position}
                            />
                        ))}
                    </div>
                </section>
            )}
        </SiteLayout>
    );
}
