import { Link } from '@inertiajs/react';

import { Button } from '@/components/ui/button';
import type { LienPagination } from '@/types/catalogue';

/**
 * Libellés lisibles pour les liens « précédent » / « suivant » de Laravel.
 */
function libelle(label: string): string {
    if (label.includes('Previous') || label.includes('&laquo;')) {
        return '‹ Précédent';
    }

    if (label.includes('Next') || label.includes('&raquo;')) {
        return 'Suivant ›';
    }

    return label;
}

/**
 * Navigation entre les pages d'une liste paginée par Laravel. Rien n'est
 * affiché s'il n'y a qu'une page.
 *
 * @param liens Les liens `links` de la pagination Laravel.
 */
export function Pagination({ liens }: { liens: LienPagination[] }) {
    if (liens.length <= 3) {
        return null;
    }

    return (
        <nav
            aria-label="Pagination"
            className="mt-8 flex flex-wrap items-center justify-center gap-1.5"
        >
            {liens.map((lien, position) =>
                lien.url ? (
                    <Button
                        key={`${lien.label}-${position}`}
                        render={<Link href={lien.url} preserveScroll={false} />}
                        nativeButton={false}
                        variant={lien.active ? 'default' : 'outline'}
                        size="sm"
                        aria-current={lien.active ? 'page' : undefined}
                    >
                        {libelle(lien.label)}
                    </Button>
                ) : (
                    <span
                        key={`${lien.label}-${position}`}
                        className="text-muted-foreground px-2 text-sm"
                    >
                        {libelle(lien.label)}
                    </span>
                ),
            )}
        </nav>
    );
}
