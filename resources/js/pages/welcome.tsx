import { Link } from '@inertiajs/react';
import {
    ArrowRight,
    Bike,
    Volleyball,
    Dumbbell,
    Footprints,
    MapPin,
    Mountain,
    PackageCheck,
    Snowflake,
    Sparkles,
    Store,
    Trophy,
    Truck,
    Waves,
} from 'lucide-react';

import { ProduitCard } from '@/components/produit-card';
import { Button } from '@/components/ui/button';
import SiteLayout from '@/layouts/site-layout';
import { index as catalogue } from '@/routes/catalogue';
import type { ProduitCarte } from '@/types/catalogue';

type Rayon = {
    id: number;
    nom: string;
    code: string;
    nombreProduits: number;
};

type Magasin = {
    id: number;
    ville: string;
    adresse: string;
    entrepot: string | null;
};

/**
 * Icône associée à un rayon d'après son nom (icône générique sinon).
 */
function iconeRayon(nom: string) {
    const n = nom.toLowerCase();

    if (n.includes('cycl') || n.includes('vélo')) {
        return Bike;
    }

    if (n.includes('natation') || n.includes('surf')) {
        return Waves;
    }

    if (n.includes('ski')) {
        return Snowflake;
    }

    if (n.includes('rando') || n.includes('escalade')) {
        return Mountain;
    }

    if (n.includes('running') || n.includes('athl')) {
        return Footprints;
    }

    if (n.includes('fitness') || n.includes('musculation')) {
        return Dumbbell;
    }

    if (
        n.includes('basket') ||
        n.includes('foot') ||
        n.includes('hand') ||
        n.includes('volley') ||
        n.includes('rugby')
    ) {
        return Volleyball;
    }

    return Trophy;
}

/**
 * Page d'accueil du site marchand : bandeau principal, avantages, rayons
 * (sports), nouveautés et réseau de magasins.
 */
export default function Welcome({
    rayons,
    nouveautes,
    magasins,
    chiffres,
}: {
    rayons: Rayon[];
    nouveautes: ProduitCarte[];
    magasins: Magasin[];
    chiffres: { produits: number; rayons: number; magasins: number };
}) {
    return (
        <SiteLayout title="Accueil" pleineLargeur>
            {/* Bandeau principal */}
            <section className="fond-sport relative overflow-hidden border-b">
                <div
                    className="grille-points absolute inset-0 opacity-60"
                    aria-hidden
                />
                <div className="relative mx-auto grid max-w-7xl items-center gap-10 px-4 py-16 md:grid-cols-2 md:py-24">
                    <div className="flex flex-col gap-6">
                        <span className="animate-apparition bg-primary/10 text-primary inline-flex w-fit items-center gap-2 rounded-full px-3 py-1 text-xs font-semibold">
                            <Sparkles className="size-3.5" /> Nouvelle
                            collection disponible
                        </span>
                        <h1 className="animate-apparition text-4xl leading-[1.05] font-black tracking-tight sm:text-5xl lg:text-6xl">
                            Le sport,{' '}
                            <span className="from-primary bg-linear-to-r to-orange-400 bg-clip-text text-transparent">
                                à votre rythme
                            </span>
                            .
                        </h1>
                        <p className="animate-apparition text-muted-foreground max-w-lg text-lg">
                            Équipements pour sportifs amateurs et passionnés :
                            {` ${chiffres.produits} articles`} dans{' '}
                            {chiffres.rayons} sports, disponibles en ligne et
                            dans nos {chiffres.magasins} magasins.
                        </p>
                        <div className="animate-apparition flex flex-wrap gap-3">
                            <Button
                                render={<Link href={catalogue()} />}
                                nativeButton={false}
                                size="lg"
                                className="group h-12 rounded-full px-6 text-base"
                            >
                                Découvrir le catalogue
                                <ArrowRight />
                            </Button>
                            <Button
                                render={<a href="#magasins" />}
                                nativeButton={false}
                                size="lg"
                                variant="outline"
                                className="h-12 rounded-full px-6 text-base"
                            >
                                <MapPin /> Trouver un magasin
                            </Button>
                        </div>
                    </div>
                    <div className="relative flex justify-center">
                        <div
                            className="bg-primary/20 absolute size-72 rounded-full blur-3xl md:size-96"
                            aria-hidden
                        />
                        <img
                            src="/images/all4sport-logo.png"
                            alt="Emblème ALL4SPORT"
                            className="relative w-72 drop-shadow-xl md:w-full md:max-w-md"
                        />
                    </div>
                </div>
            </section>

            {/* Avantages */}
            <section className="mx-auto grid max-w-7xl gap-4 px-4 py-10 sm:grid-cols-3">
                {[
                    {
                        icone: Store,
                        titre: 'Retrait magasin gratuit',
                        texte: 'Choisissez votre magasin par défaut et récupérez vos articles sans frais.',
                    },
                    {
                        icone: Truck,
                        titre: 'Livraison à domicile',
                        texte: 'Un forfait unique de 10 € par commande, expédiée depuis nos entrepôts.',
                    },
                    {
                        icone: PackageCheck,
                        titre: 'Suivi en temps réel',
                        texte: 'Transmise, validée, en préparation, expédiée… suivez chaque étape.',
                    },
                ].map(({ icone: Icone, titre, texte }) => (
                    <div
                        key={titre}
                        className="bg-card hover:border-primary/40 flex gap-4 rounded-2xl border p-5 transition-colors"
                    >
                        <span className="bg-primary/10 text-primary flex size-11 shrink-0 items-center justify-center rounded-xl">
                            <Icone className="size-5" />
                        </span>
                        <div>
                            <p className="font-semibold">{titre}</p>
                            <p className="text-muted-foreground text-sm">
                                {texte}
                            </p>
                        </div>
                    </div>
                ))}
            </section>

            {/* Rayons */}
            <section className="mx-auto max-w-7xl px-4 py-10">
                <div className="mb-6 flex items-end justify-between gap-4">
                    <div>
                        <p className="text-primary text-sm font-semibold uppercase">
                            Nos rayons
                        </p>
                        <h2 className="text-3xl font-bold tracking-tight">
                            Tous les sports, un seul magasin
                        </h2>
                    </div>
                </div>
                <div className="grid grid-cols-2 gap-3 sm:grid-cols-3 lg:grid-cols-6">
                    {rayons.map((rayon) => {
                        const Icone = iconeRayon(rayon.nom);

                        return (
                            <Link
                                key={rayon.id}
                                href={catalogue({ query: { rayon: rayon.id } })}
                                className="group bg-card hover:border-primary/40 flex flex-col items-center gap-2 rounded-2xl border p-5 text-center transition-colors"
                            >
                                <span className="bg-primary/10 text-primary flex size-12 items-center justify-center rounded-full">
                                    <Icone className="size-6" />
                                </span>
                                <span className="text-sm font-semibold">
                                    {rayon.nom}
                                </span>
                                <span className="text-muted-foreground text-xs">
                                    {rayon.nombreProduits} articles
                                </span>
                            </Link>
                        );
                    })}
                </div>
            </section>

            {/* Nouveautés */}
            {nouveautes.length > 0 && (
                <section className="mx-auto max-w-7xl px-4 py-10">
                    <div className="mb-6 flex items-end justify-between gap-4">
                        <div>
                            <p className="text-primary text-sm font-semibold uppercase">
                                Nouveautés
                            </p>
                            <h2 className="text-3xl font-bold tracking-tight">
                                Fraîchement arrivés
                            </h2>
                        </div>
                        <Link
                            href={catalogue()}
                            className="text-primary hidden text-sm font-semibold hover:underline sm:block"
                        >
                            Tout voir →
                        </Link>
                    </div>
                    <div className="grid grid-cols-2 gap-4 lg:grid-cols-4">
                        {nouveautes.map((produit, position) => (
                            <ProduitCard
                                key={produit.reference}
                                produit={produit}
                                position={position}
                            />
                        ))}
                    </div>
                </section>
            )}

            {/* Magasins */}
            <section
                id="magasins"
                className="mx-auto max-w-7xl scroll-mt-24 px-4 py-10"
            >
                <div className="mb-6">
                    <p className="text-primary text-sm font-semibold uppercase">
                        Nos magasins
                    </p>
                    <h2 className="text-3xl font-bold tracking-tight">
                        {magasins.length} magasins partout en France
                    </h2>
                    <p className="text-muted-foreground mt-1">
                        Chaque magasin est approvisionné par l'entrepôt de sa
                        zone d'expédition.
                    </p>
                </div>
                <div className="grid gap-3 sm:grid-cols-2 lg:grid-cols-4">
                    {magasins.map((magasin) => (
                        <div
                            key={magasin.id}
                            className="bg-card hover:border-primary/40 group flex items-start gap-3 rounded-2xl border p-4 transition-colors"
                        >
                            <MapPin className="text-primary mt-0.5 size-5 shrink-0" />
                            <div>
                                <p className="font-semibold">{magasin.ville}</p>
                                <p className="text-muted-foreground text-sm">
                                    {magasin.adresse}
                                </p>
                                {magasin.entrepot && (
                                    <p className="text-muted-foreground mt-1 text-xs">
                                        Entrepôt : {magasin.entrepot}
                                    </p>
                                )}
                            </div>
                        </div>
                    ))}
                </div>
            </section>
        </SiteLayout>
    );
}
