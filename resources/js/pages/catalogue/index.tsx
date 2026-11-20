import { Link, router } from '@inertiajs/react';
import { PackageSearch, Search, SlidersHorizontal, X } from 'lucide-react';
import { type FormEvent, useState } from 'react';

import { Pagination } from '@/components/pagination';
import { ProduitCard } from '@/components/produit-card';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import SiteLayout from '@/layouts/site-layout';
import { pluriel } from '@/lib/format';
import { index } from '@/routes/catalogue';
import type { Pagine, ProduitCarte } from '@/types/catalogue';

/**
 * Un rayon (sport) proposé comme filtre du catalogue.
 */
type Rayon = {
    id: number;
    nom: string;
};

/**
 * Ordres de tri proposés (valeurs comprises par CatalogueController).
 */
const TRIS = [
    { value: 'rayon', label: 'Par rayon' },
    { value: 'prix_croissant', label: 'Prix croissant' },
    { value: 'prix_decroissant', label: 'Prix décroissant' },
    { value: 'nouveautes', label: 'Nouveautés' },
];

/**
 * Catalogue public (route "catalogue.index") : produits triés par rayon
 * (sport), avec filtre par rayon, recherche et tri.
 */
export default function CatalogueIndex({
    rayons,
    rayonActif,
    recherche,
    tri,
    produits,
}: {
    rayons: Rayon[];
    rayonActif: number | null;
    recherche: string;
    tri: string;
    produits: Pagine<ProduitCarte>;
}) {
    const [saisie, setSaisie] = useState(recherche);
    const nomRayonActif = rayons.find((rayon) => rayon.id === rayonActif)?.nom;

    /**
     * Recharge le catalogue avec de nouveaux filtres, en gardant les autres.
     */
    const filtrer = (filtres: Record<string, string | number | null>) => {
        const query = Object.fromEntries(
            Object.entries({
                rayon: rayonActif,
                recherche: recherche || null,
                tri: tri !== 'rayon' ? tri : null,
                ...filtres,
            }).filter(([, valeur]) => valeur !== null && valeur !== ''),
        );

        router.get(index.url({ query }), {}, { preserveState: true });
    };

    const rechercher = (evenement: FormEvent) => {
        evenement.preventDefault();
        filtrer({ recherche: saisie.trim() || null });
    };

    return (
        <SiteLayout
            title={nomRayonActif ? `Rayon ${nomRayonActif}` : 'Catalogue'}
        >
            <div className="animate-apparition mb-8 flex flex-col gap-2">
                <p className="text-primary text-sm font-semibold uppercase">
                    Catalogue
                </p>
                <h1 className="text-3xl font-bold tracking-tight sm:text-4xl">
                    {nomRayonActif ?? 'Tous nos articles'}
                </h1>
                <p className="text-muted-foreground">
                    {pluriel(produits.total, 'article')}
                    {recherche && ` pour "${recherche}"`}
                </p>
            </div>

            <div className="mb-6 flex flex-col gap-3 md:flex-row md:items-center">
                <form
                    onSubmit={rechercher}
                    className="relative flex-1"
                    role="search"
                >
                    <Search className="text-muted-foreground pointer-events-none absolute top-1/2 left-3 size-4 -translate-y-1/2" />
                    <Input
                        value={saisie}
                        onChange={(evenement) =>
                            setSaisie(evenement.target.value)
                        }
                        placeholder="Rechercher par nom, référence ou sport..."
                        aria-label="Rechercher dans le catalogue"
                        className="h-11 rounded-xl pl-9"
                    />
                    {recherche && (
                        <button
                            type="button"
                            onClick={() => {
                                setSaisie('');
                                filtrer({ recherche: null });
                            }}
                            className="text-muted-foreground hover:text-foreground absolute top-1/2 right-3 -translate-y-1/2"
                            aria-label="Effacer la recherche"
                        >
                            <X className="size-4" />
                        </button>
                    )}
                </form>
                <label className="flex items-center gap-2 text-sm">
                    <SlidersHorizontal className="text-muted-foreground size-4" />
                    <span className="sr-only md:not-sr-only">Trier</span>
                    <select
                        value={tri}
                        onChange={(evenement) =>
                            filtrer({
                                tri:
                                    evenement.target.value === 'rayon'
                                        ? null
                                        : evenement.target.value,
                            })
                        }
                        className="border-input bg-background h-11 rounded-xl border px-3 text-sm"
                    >
                        {TRIS.map((option) => (
                            <option key={option.value} value={option.value}>
                                {option.label}
                            </option>
                        ))}
                    </select>
                </label>
            </div>

            <div className="-mx-4 mb-8 overflow-x-auto px-4">
                <div className="flex w-max gap-2 pb-1">
                    <Button
                        render={
                            <Link
                                href={index({
                                    query: recherche ? { recherche } : {},
                                })}
                                preserveScroll
                            />
                        }
                        nativeButton={false}
                        variant={rayonActif === null ? 'default' : 'outline'}
                        className="rounded-full"
                    >
                        Tous les rayons
                    </Button>
                    {rayons.map((rayon) => (
                        <Button
                            key={rayon.id}
                            render={
                                <Link
                                    href={index({
                                        query: {
                                            rayon: rayon.id,
                                            ...(recherche ? { recherche } : {}),
                                        },
                                    })}
                                    preserveScroll
                                />
                            }
                            nativeButton={false}
                            variant={
                                rayonActif === rayon.id ? 'default' : 'outline'
                            }
                            className="rounded-full"
                        >
                            {rayon.nom}
                        </Button>
                    ))}
                </div>
            </div>

            {produits.data.length === 0 ? (
                <div className="animate-apparition flex flex-col items-center gap-3 rounded-2xl border border-dashed py-16 text-center">
                    <PackageSearch className="text-muted-foreground size-12" />
                    <p className="font-semibold">Aucun article trouvé</p>
                    <p className="text-muted-foreground max-w-sm text-sm">
                        Essayez un autre rayon ou une autre recherche.
                    </p>
                    <Button
                        render={<Link href={index()} />}
                        nativeButton={false}
                        variant="outline"
                    >
                        Voir tout le catalogue
                    </Button>
                </div>
            ) : (
                <ul className="grid grid-cols-2 gap-4 lg:grid-cols-4">
                    {produits.data.map((produit, position) => (
                        <li key={produit.reference}>
                            <ProduitCard
                                produit={produit}
                                position={position}
                            />
                        </li>
                    ))}
                </ul>
            )}

            <Pagination liens={produits.links} />
        </SiteLayout>
    );
}
