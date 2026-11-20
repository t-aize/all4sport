import { Link } from '@inertiajs/react';
import { ImageOff } from 'lucide-react';

import { formatPrix } from '@/lib/format';
import { show } from '@/routes/catalogue';
import type { ProduitCarte } from '@/types/catalogue';

/**
 * Carte d'un produit du catalogue : photo (zoom au survol), rayon,
 * description, prix et disponibilité en ligne.
 *
 * @param produit Le produit à présenter.
 * @param position Position dans la grille (conservée pour compatibilité).
 */
export function ProduitCard({
    produit,
}: {
    produit: ProduitCarte;
    position?: number;
}) {
    const enStock =
        produit.stockInternet === undefined || produit.stockInternet > 0;

    return (
        <Link
            href={show(produit.reference)}
            className="group bg-card hover:border-primary/40 relative flex h-full flex-col overflow-hidden rounded-2xl border transition-colors"
        >
            <div className="bg-muted relative aspect-4/3 overflow-hidden">
                {produit.photo ? (
                    <img
                        src={produit.photo}
                        alt={produit.description}
                        loading="lazy"
                        className="size-full object-cover"
                        onError={(evenement) => {
                            evenement.currentTarget.style.display = 'none';
                        }}
                    />
                ) : (
                    <div className="text-muted-foreground flex size-full items-center justify-center">
                        <ImageOff className="size-8" aria-hidden />
                    </div>
                )}
                {produit.rayon && (
                    <span className="bg-background/85 absolute top-3 left-3 rounded-full px-2.5 py-1 text-[11px] font-semibold tracking-wide uppercase backdrop-blur">
                        {produit.rayon}
                    </span>
                )}
                {!enStock && (
                    <span className="bg-navy/85 text-navy-foreground absolute top-3 right-3 rounded-full px-2.5 py-1 text-[11px] font-semibold backdrop-blur">
                        En magasin uniquement
                    </span>
                )}
            </div>
            <div className="flex flex-1 flex-col gap-2 p-4">
                <h3 className="line-clamp-2 text-sm leading-snug font-medium">
                    {produit.description}
                </h3>
                <div className="mt-auto flex items-end justify-between gap-2">
                    <p className="text-lg font-bold tracking-tight">
                        {formatPrix(produit.prixVente)}
                    </p>
                    <span className="text-primary text-xs font-semibold opacity-0 transition-opacity group-hover:opacity-100">
                        Voir →
                    </span>
                </div>
            </div>
        </Link>
    );
}
